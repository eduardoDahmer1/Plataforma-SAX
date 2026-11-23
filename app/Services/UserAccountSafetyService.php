<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class UserAccountSafetyService
{
    private const SPAM_TERMS = [
        'http://', 'https://', 'www.', '.com/', '.net/', '.org/',
        'promo code', 'bonus code', 'welcome bonus', 'reward', 'balance notice',
        'account alert', 'casino', 'crypto', 'depositing a cent',
    ];

    public function applyFilter(Builder $query, ?string $filter): void
    {
        match ($filter) {
            'suspicious' => $this->applySuspicious($query),
            'unverified' => $query->whereNull('email_verified_at'),
            'verified' => $query->whereNotNull('email_verified_at'),
            'without_orders' => $query->whereDoesntHave('orders'),
            default => null,
        };
    }

    public function applySuspicious(Builder $query): void
    {
        $query
            ->where('user_type', User::TYPE_CUSTOMER)
            ->whereDoesntHave('orders')
            ->where(function (Builder $suspicious) {
                $suspicious->where(function (Builder $name) {
                    foreach (self::SPAM_TERMS as $term) {
                        $name->orWhereRaw('LOWER(name) LIKE ?', ['%'.$term.'%']);
                    }
                    $name->orWhereRaw('LENGTH(name) > 100');
                })->orWhere(function (Builder $recentUnverified) {
                    $recentUnverified
                        ->whereNull('email_verified_at')
                        ->where('created_at', '>=', now()->subDays(30));
                });
            });
    }

    public function reasons(User $user): array
    {
        $reasons = [];
        $name = Str::lower(Str::ascii((string) $user->name));

        if (Str::contains($name, self::SPAM_TERMS) || mb_strlen((string) $user->name) > 100) {
            $reasons[] = 'Nome com link ou promoção';
        }
        if (! $user->email_verified_at) {
            $reasons[] = 'E-mail não verificado';
        }
        if ((int) ($user->orders_count ?? $user->orders()->count()) === 0) {
            $reasons[] = 'Sem pedidos';
        }
        if ($user->created_at?->gte(now()->subDays(30))) {
            $reasons[] = 'Cadastro recente';
        }

        return $reasons;
    }
}
