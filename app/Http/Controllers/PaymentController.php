<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function deposito($orderId)
    {
        $order = \App\Models\Order::findOrFail($orderId);
        
        // Você pode ter um model `BankAccount` com os dados do banco
        $bankAccounts = \App\Models\BankAccount::all(); 

        return view('payment.deposito', compact('order', 'bankAccounts'));
    }

    public function bancard($orderId)
    {
        $order = \App\Models\Order::findOrFail($orderId);

        // Configurações do painel
        $publicKey = config('services.bancard.public_key'); // ou do banco
        $privateKey = config('services.bancard.private_key'); // idem
        $sandbox = config('services.bancard.sandbox'); // true ou false

        $token = sha1($order->id . $order->total . $privateKey);

        $bancardUrl = $sandbox
            ? 'https://sandbox.bancard.com.py/payment'
            : 'https://www.bancard.com.py/payment';

        // Você pode simular o redirect POST ou GET
        return view('payment.bancard', compact('order', 'bancardUrl', 'token'));
    }


}