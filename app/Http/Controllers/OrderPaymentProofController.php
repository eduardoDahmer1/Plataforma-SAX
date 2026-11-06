<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderPaymentProof;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderPaymentProofController extends Controller
{
    private const MAX_FILES_PER_ORDER = 10;

    public function store(Request $request, Order $order, AdminNotificationService $notifications): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) $request->user()?->getKey(), 403);

        $validated = $request->validate([
            'proofs' => ['required', 'array', 'min:1', 'max:5'],
            'proofs.*' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ]);

        $files = $validated['proofs'];
        $existingCount = $order->paymentProofs()->count();

        if ($existingCount + count($files) > self::MAX_FILES_PER_ORDER) {
            return back()->withErrors([
                'proofs' => __('messages.payment_proof_order_limit', ['limit' => self::MAX_FILES_PER_ORDER]),
            ]);
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($files, $order, $request, &$storedPaths): void {
                foreach ($files as $file) {
                    $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
                    $path = $file->storeAs(
                        'payment-proofs/'.$order->getKey(),
                        Str::uuid().'.'.$extension,
                        'local'
                    );
                    $storedPaths[] = $path;

                    $order->paymentProofs()->create([
                        'user_id' => $request->user()->getKey(),
                        'path' => $path,
                        'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
                        'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                        'size' => $file->getSize(),
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            report($exception);

            return back()->withErrors(['proofs' => __('messages.payment_proof_upload_error')]);
        }

        $notifications->notifyAdmins(
            type: 'deposit_receipt',
            title: 'Novos comprovantes de pagamento',
            message: sprintf(
                '%d arquivo(s) foram enviados para o pedido #%s.',
                count($files),
                $order->order_number ?: $order->getKey()
            ),
            actionUrl: route('admin.orders.show', $order),
            data: ['order_id' => $order->getKey(), 'files_count' => count($files)]
        );

        return back()->with('success', __('messages.payment_proof_sent'));
    }

    public function show(Request $request, OrderPaymentProof $paymentProof): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->isAdmin() || (int) $paymentProof->order->user_id === (int) $user->getKey()),
            403
        );

        $disk = Storage::disk('local');
        abort_unless($disk->exists($paymentProof->path), 404);

        $safeName = str_replace(["\r", "\n", '"'], '', $paymentProof->original_name);

        return response()->file($disk->path($paymentProof->path), [
            'Content-Type' => $paymentProof->mime_type,
            'Content-Disposition' => 'inline; filename="'.$safeName.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
