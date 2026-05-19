<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show(Receipt $receipt)
    {
        $this->authorizeAccess($receipt);

        $order = $receipt->order->load('items.product', 'user');

        // El layout depende de quien abre el recibo
        $layout = auth()->user()->user_type == 1
            ? 'layout.admin'
            : 'layout.dashboard';

        return view('receipts.show', compact('receipt', 'order', 'layout'));
    }

    public function download(Receipt $receipt)
    {
        $this->authorizeAccess($receipt);

        if (!$receipt->pdf_path || !\Storage::exists($receipt->pdf_path)) {
            abort(404, 'PDF no disponible.');
        }

        return \Storage::download($receipt->pdf_path, $receipt->receipt_number . '.pdf');
    }

    private function authorizeAccess(Receipt $receipt): void
    {
        $user = auth()->user();

        $isAdmin = $user->user_type == 1;
        $isOwner = (int) $receipt->order->user_id === (int) $user->id;

        if (!$isAdmin && !$isOwner) {
            abort(403);
        }
    }
}
