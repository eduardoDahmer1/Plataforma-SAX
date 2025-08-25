<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function clearCache()
    {
        $output = [];
    
        Artisan::call('cache:clear'); $output[] = Artisan::output();
        Artisan::call('route:clear'); $output[] = Artisan::output();
        Artisan::call('view:clear'); $output[] = Artisan::output();
        Artisan::call('config:clear'); $output[] = Artisan::output();

        Log::info('Caches limpos manualmente pelo admin.', [
            'executado_por' => auth()->user()->name ?? 'sistema',
            'outputs' => $output
        ]);
    
        return response()->json([
            'message' => 'Cache limpo com sucesso!',
            'output' => implode("\n", $output),
        ]);
    }

    public function maintenanceIndex()
    {
        $setting = SystemSetting::first();
        return view('admin.manutencao.index', compact('setting'));
    }

    public function toggleMaintenance()
    {
        $user = auth()->user();
    
        // Só admins podem alterar
        if (!$user || $user->user_type != 1) {
            return redirect()->back()->with('error', 'Você não tem permissão.');
        }
    
        // Busca a primeira configuração do sistema
        $setting = SystemSetting::firstOrFail();
    
        // Alterna entre 1 (manutenção) e 2 (ativo)
        $setting->maintenance = $setting->maintenance == 1 ? 2 : 1;
        $setting->save();
    
        $msg = $setting->maintenance == 1
            ? '🔧 Sistema em manutenção (admins ainda podem acessar)!'
            : '🚀 Sistema ativado!';
    
        return redirect()->route('admin.maintenance.index')->with('success', $msg);
    }
    
}
