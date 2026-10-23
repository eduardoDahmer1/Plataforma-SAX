<?php

namespace App\Services;

use App\Models\CheckoutStockReservation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CheckoutStockReservationService
{
    public const CHECKOUT_MINUTES = 15;
    public const PAYMENT_MINUTES = 30;

    public function __construct(private CuponService $cupons)
    {
    }

    public function reserveCart(User $user, Collection $cart): Carbon
    {
        $expiresAt = now()->addMinutes(self::CHECKOUT_MINUTES);
        if (! $this->supported()) {
            return $expiresAt;
        }

        return DB::transaction(function () use ($user, $cart, $expiresAt): Carbon {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $reservations = CheckoutStockReservation::query()
                ->where('user_id', $user->getKey())
                ->where('status', 'held')
                ->whereNull('order_id')
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $wanted = $cart
                ->filter(fn ($item): bool => $item->product_id && $item->product)
                ->groupBy('product_id')
                ->map(fn (Collection $items): int => (int) $items->sum('quantity'));

            foreach ($reservations as $productId => $reservation) {
                if ($reservation->expires_at?->isPast() || ! $wanted->has($productId)) {
                    $this->releaseLocked($reservation, $reservation->expires_at?->isPast() ? 'checkout_expired' : 'cart_changed');
                    $reservations->forget($productId);
                }
            }

            $products = Product::query()
                ->whereIn('id', $wanted->keys())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($wanted as $productId => $quantity) {
                $product = $products->get($productId);
                $reservation = $reservations->get($productId);
                $alreadyHeld = (int) ($reservation?->quantity ?? 0);
                $availableToCustomer = (int) ($product?->stock ?? 0) + $alreadyHeld;

                if (! $product || ! $this->commerciallyAvailable($product) || $quantity < 1 || $quantity > $availableToCustomer) {
                    throw new RuntimeException(__('messages.checkout_pause_stock_changed'));
                }

                $difference = $quantity - $alreadyHeld;
                if ($difference > 0) {
                    $updated = Product::query()
                        ->whereKey($productId)
                        ->where('stock', '>=', $difference)
                        ->decrement('stock', $difference);

                    if ($updated !== 1) {
                        throw new RuntimeException(__('messages.checkout_pause_stock_changed'));
                    }
                } elseif ($difference < 0) {
                    Product::query()->whereKey($productId)->increment('stock', abs($difference));
                }

                if ($reservation) {
                    $reservation->update(['quantity' => $quantity, 'expires_at' => $expiresAt]);
                } else {
                    CheckoutStockReservation::query()->create([
                        'user_id' => $user->getKey(),
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'status' => 'held',
                        'expires_at' => $expiresAt,
                    ]);
                }
            }

            return $expiresAt;
        }, 3);
    }

    public function refreshCheckout(User $user): ?Carbon
    {
        if (! $this->supported()) {
            return null;
        }

        $expiresAt = now()->addMinutes(self::CHECKOUT_MINUTES);
        $updated = CheckoutStockReservation::query()
            ->where('user_id', $user->getKey())
            ->where('status', 'held')
            ->whereNull('order_id')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => $expiresAt, 'updated_at' => now()]);

        return $updated > 0 ? $expiresAt : null;
    }

    public function attachToOrder(User $user, Order $order): Carbon
    {
        $expiresAt = now()->addMinutes(self::PAYMENT_MINUTES);
        if (! $this->supported()) {
            return $expiresAt;
        }

        $updated = CheckoutStockReservation::query()
            ->where('user_id', $user->getKey())
            ->where('status', 'held')
            ->whereNull('order_id')
            ->where('expires_at', '>', now())
            ->update([
                'order_id' => $order->getKey(),
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]);

        if ($updated < $order->items()->distinct()->count('product_id')) {
            throw new RuntimeException(__('messages.checkout_pause_stock_changed'));
        }

        return $expiresAt;
    }

    public function releaseCheckout(User $user, string $reason = 'checkout_left'): void
    {
        if (! $this->supported()) {
            return;
        }

        DB::transaction(function () use ($user, $reason): void {
            User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            CheckoutStockReservation::query()
                ->where('user_id', $user->getKey())
                ->where('status', 'held')
                ->whereNull('order_id')
                ->lockForUpdate()
                ->get()
                ->each(fn (CheckoutStockReservation $reservation) => $this->releaseLocked($reservation, $reason));
        }, 3);
    }

    public function releaseOrder(Order $order, string $reason, bool $failOrder = true): void
    {
        if (! $this->supported() || $order->isPaid()) {
            return;
        }

        DB::transaction(function () use ($order, $reason, $failOrder): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());
            if ($lockedOrder->isPaid()) {
                return;
            }

            CheckoutStockReservation::query()
                ->where('order_id', $lockedOrder->getKey())
                ->where('status', 'held')
                ->lockForUpdate()
                ->get()
                ->each(fn (CheckoutStockReservation $reservation) => $this->releaseLocked($reservation, $reason));

            $this->cupons->devolverUso($lockedOrder);

            if ($failOrder) {
                $lockedOrder->update([
                    'status' => 'failed',
                    'payment_status' => 'failed',
                    'payment_response_code' => $reason,
                    'payment_response_message' => $this->expiredMessage(),
                    'payment_failed_at' => $lockedOrder->payment_failed_at ?: now(),
                ]);
            }
        }, 3);
    }

    public function commitOrder(Order $order): void
    {
        if (! $this->supported()) {
            $this->decrementLegacyOrder($order);
            return;
        }

        DB::transaction(function () use ($order): void {
            $reservations = CheckoutStockReservation::query()
                ->where('order_id', $order->getKey())
                ->lockForUpdate()
                ->get();

            if ($reservations->where('status', 'committed')->isNotEmpty()) {
                return;
            }

            $held = $reservations->where('status', 'held');
            if ($held->isNotEmpty()) {
                foreach ($held as $reservation) {
                    $reservation->update([
                        'status' => 'committed',
                        'committed_at' => now(),
                        'expires_at' => now(),
                    ]);
                }
                return;
            }

            // Compatibilidade para pedidos iniciados antes da reserva ou pagos após
            // uma confirmação muito tardia do provedor. Nunca faça baixa parcial.
            $this->decrementLegacyOrder($order, true);
        }, 3);
    }

    public function restoreCommittedOrder(Order $order, string $reason = 'order_canceled'): void
    {
        if (! $this->supported()) {
            $this->restoreLegacyOrder($order);
            $this->cupons->devolverUso($order);
            return;
        }

        DB::transaction(function () use ($order, $reason): void {
            $reservations = CheckoutStockReservation::query()
                ->where('order_id', $order->getKey())
                ->lockForUpdate()
                ->get();
            $committed = $reservations->where('status', 'committed');

            $committed->each(function (CheckoutStockReservation $reservation) use ($reason): void {
                Product::query()->whereKey($reservation->product_id)->increment('stock', $reservation->quantity);
                $reservation->update([
                    'status' => 'restored',
                    'released_at' => now(),
                    'release_reason' => $reason,
                ]);
            });

            // Pedidos pagos antes da implantação não possuem reserva registrada.
            // Restaura uma única vez e grava o histórico para manter idempotência.
            if ($reservations->isEmpty()) {
                $this->restoreLegacyOrder($order, true, $reason);
            }

            $this->cupons->devolverUso($order);
        }, 3);
    }

    public function expireDueReservations(int $limit = 200): int
    {
        if (! $this->supported()) {
            return 0;
        }

        $groups = CheckoutStockReservation::query()
            ->where('status', 'held')
            ->where('expires_at', '<=', now())
            ->orderBy('expires_at')
            ->limit(max(1, min(1000, $limit)))
            ->get()
            ->groupBy(fn (CheckoutStockReservation $reservation): string => $reservation->order_id ? 'order:'.$reservation->order_id : 'user:'.$reservation->user_id);

        foreach ($groups as $reservations) {
            $orderId = $reservations->first()->order_id;
            if ($orderId) {
                $order = Order::query()->find($orderId);
                if ($order?->isPaid()) {
                    $this->commitOrder($order);
                } elseif ($order) {
                    $this->releaseOrder($order, 'reservation_expired');
                }
            } else {
                $user = User::query()->find($reservations->first()->user_id);
                if ($user) {
                    $this->releaseCheckout($user, 'checkout_expired');
                }
            }
        }

        return $groups->count();
    }

    public function orderHasActiveReservation(Order $order): bool
    {
        if (! $this->supported()) {
            return true;
        }

        return CheckoutStockReservation::query()
            ->where('order_id', $order->getKey())
            ->where('status', 'held')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function checkoutQuantities(User $user): Collection
    {
        if (! $this->supported()) {
            return collect();
        }

        return CheckoutStockReservation::query()
            ->where('user_id', $user->getKey())
            ->where('status', 'held')
            ->whereNull('order_id')
            ->where('expires_at', '>', now())
            ->pluck('quantity', 'product_id');
    }

    public function expiredMessage(): string
    {
        $translated = __('messages.checkout_reservation_expired_order');
        if ($translated !== 'messages.checkout_reservation_expired_order') {
            return $translated;
        }

        return match (app()->getLocale()) {
            'en' => 'The stock and coupon reservation has expired. Please return to the cart and place a new order.',
            'es' => 'La reserva de stock y del cupón venció. Vuelve al carrito y crea un nuevo pedido.',
            default => 'A reserva de estoque e do cupom expirou. Volte ao carrinho e faça um novo pedido.',
        };
    }

    private function releaseLocked(CheckoutStockReservation $reservation, string $reason): void
    {
        if ($reservation->status !== 'held') {
            return;
        }

        Product::query()->whereKey($reservation->product_id)->increment('stock', $reservation->quantity);
        $reservation->update([
            'status' => 'released',
            'released_at' => now(),
            'release_reason' => $reason,
        ]);
    }

    private function decrementLegacyOrder(Order $order, bool $record = false): void
    {
        $order->loadMissing('items.product');
        foreach ($order->items as $item) {
            $product = Product::query()->lockForUpdate()->find($item->product_id);
            if (! $product) {
                continue;
            }

            $quantity = max(0, (int) $item->quantity);
            if ((int) $product->stock < $quantity) {
                Log::critical('Paid order exceeded available stock', [
                    'order_id' => $order->getKey(),
                    'product_id' => $product->getKey(),
                    'required' => $quantity,
                    'available' => (int) $product->stock,
                ]);
            }

            Product::query()->whereKey($product->getKey())->decrement('stock', $quantity);
            if ($record) {
                CheckoutStockReservation::query()->create([
                    'user_id' => $order->user_id,
                    'product_id' => $product->getKey(),
                    'order_id' => $order->getKey(),
                    'quantity' => $quantity,
                    'status' => 'committed',
                    'expires_at' => now(),
                    'committed_at' => now(),
                ]);
            }
        }
    }

    private function restoreLegacyOrder(Order $order, bool $record = false, string $reason = 'legacy_order_restored'): void
    {
        $order->loadMissing('items.product');
        foreach ($order->items as $item) {
            $quantity = max(0, (int) $item->quantity);
            if ($quantity < 1 || ! $item->product_id) {
                continue;
            }

            Product::query()->whereKey($item->product_id)->increment('stock', $quantity);
            if ($record) {
                CheckoutStockReservation::query()->create([
                    'user_id' => $order->user_id,
                    'product_id' => $item->product_id,
                    'order_id' => $order->getKey(),
                    'quantity' => $quantity,
                    'status' => 'restored',
                    'expires_at' => now(),
                    'released_at' => now(),
                    'release_reason' => $reason,
                ]);
            }
        }
    }

    private function commerciallyAvailable(Product $product): bool
    {
        return ! $product->is_outlet
            && (int) $product->status === 1
            && $product->category()->where('status', 1)->exists();
    }

    private function supported(): bool
    {
        return Schema::hasTable('checkout_stock_reservations');
    }
}
