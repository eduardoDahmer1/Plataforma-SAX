<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProductFeedService;
use App\Services\ProductFeedRefreshService;
use Illuminate\Http\RedirectResponse;
use Throwable;

class ProductFeedController extends Controller
{
    public function store(ProductFeedService $feed, ProductFeedRefreshService $refresh): RedirectResponse
    {
        try {
            $refresh->refreshPending(true);
            $status = $feed->status();

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
