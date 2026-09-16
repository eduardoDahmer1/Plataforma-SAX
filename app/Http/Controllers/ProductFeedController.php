<?php

namespace App\Http\Controllers;

use App\Services\ProductFeedService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductFeedController extends Controller
{
    public function show(ProductFeedService $feed): BinaryFileResponse
    {
        abort_unless($feed->exists(), 404, 'O XML de produtos ainda não foi gerado.');

        return response()->file($feed->absolutePath(), [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Content-Disposition' => 'inline; filename="products.xml"',
            'Cache-Control' => 'public, max-age=60, must-revalidate',
        ]);
    }
}
