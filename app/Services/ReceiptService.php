<?php

namespace App\Services;

use App\Models\Attribute;
use App\Models\Order;
use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReceiptService
{

    public function issueForOrder(Order $order): Receipt
    {
        // si el pedido ya tiene un recibo, lo devolvemos. 
        if ($order->receipt) {
            return $order->receipt;
        }

        return DB::transaction(function () use ($order) {

            $receiptNumber = $this->generateReceiptNumber();

            $receipt = Receipt::create([
                'order_id'       => $order->id,
                'receipt_number' => $receiptNumber,
                'issued_at'      => now(),
            ]);

            $this->generatePdf($receipt);

            return $receipt;
        });
    }

    public function generatePdf(Receipt $receipt): void
    {
        $order = $receipt->order->load('items.product');

        $attribute = Attribute::first();
        $logoPath  = $attribute?->header_image
            ? storage_path('app/public/uploads/' . $attribute->header_image)
            : null;

        $pdf = Pdf::loadView('receipts.pdf', compact('receipt', 'order', 'logoPath'));

        $path = 'receipts/' . $receipt->receipt_number . '.pdf';

        Storage::put($path, $pdf->output());

        $receipt->pdf_path = $path;
        $receipt->save();
    }

    private function generateReceiptNumber(): string
    {
        $last = Receipt::lockForUpdate()->orderByDesc('id')->first();

        $next = $last ? ((int) substr($last->receipt_number, 4)) + 1 : 1;

        return 'SAX-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
