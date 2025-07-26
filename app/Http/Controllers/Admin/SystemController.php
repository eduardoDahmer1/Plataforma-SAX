<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function clearCache()
    {
        $output = [];
    
        Artisan::call('cache:clear');
        $output[] = Artisan::output();
    
        Artisan::call('route:clear');
        $output[] = Artisan::output();
    
        Artisan::call('view:clear');
        $output[] = Artisan::output();

        Artisan::call('config:clear');
        $output[] = Artisan::output();
    
        // ⚠️ LOG antes de config:clear
        \Log::info('Caches limpos manualmente pelo admin.', [
            'executado_por' => auth()->user()->name ?? 'sistema',
            'outputs' => $output
        ]);
    
        // Agora sim: limpar config
        Artisan::call('config:clear');
        $output[] = Artisan::output();
    
        return response()->json([
            'message' => 'Cache limpo com sucesso!',
            'output' => implode("\n", $output),
        ]);
    }
}
