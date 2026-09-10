<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductFeedService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ProductFeedController extends Controller
{
    public function store(ProductFeedService $feed): RedirectResponse
    {
        try {
            $status = $feed->generate();

            return back()->with(
                'success',
                sprintf('XML gerado com sucesso com %d produto(s). O link público continua o mesmo.', $status['count'] ?? 0)
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Não foi possível gerar o XML: '.$exception->getMessage());
        }
    }
}
