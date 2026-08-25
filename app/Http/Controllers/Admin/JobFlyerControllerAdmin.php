<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobFlyer;
use App\Services\ImageConverterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class JobFlyerControllerAdmin extends Controller
{
    public function index()
    {
        $flyers = JobFlyer::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.trabalhe-conosco.index', compact('flyers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $imagePath = app(ImageConverterService::class)->toWebp($request->file('image'), 'job_flyers');

        $maxOrder = JobFlyer::max('sort_order') ?? 0;

        JobFlyer::create([
            'image' => $imagePath,
            'is_active' => true,
            'sort_order' => $maxOrder + 1,
        ]);
        Cache::forget('contact_active_job_flyers');

        return redirect()->route('admin.trabalhe_conosco.index')
            ->with('success', 'Flyer adicionado com sucesso.');
    }

    public function toggle(JobFlyer $jobFlyer)
    {
        $jobFlyer->update([
            'is_active' => ! $jobFlyer->is_active,
        ]);
        Cache::forget('contact_active_job_flyers');

        return redirect()->back()
            ->with('success', $jobFlyer->is_active ? 'Flyer ativado.' : 'Flyer desativado.');
    }

    public function move(JobFlyer $jobFlyer, string $direction)
    {
        $neighbor = $direction === 'up'
            ? JobFlyer::where('sort_order', '<', $jobFlyer->sort_order)
                ->orderByDesc('sort_order')
                ->first()
            : JobFlyer::where('sort_order', '>', $jobFlyer->sort_order)
                ->orderBy('sort_order')
                ->first();

        if ($neighbor) {
            $currentOrder = $jobFlyer->sort_order;
            $jobFlyer->update(['sort_order' => $neighbor->sort_order]);
            $neighbor->update(['sort_order' => $currentOrder]);
            Cache::forget('contact_active_job_flyers');
        }

        return redirect()->back();
    }

    public function destroy(JobFlyer $jobFlyer)
    {
        Storage::disk('public')->delete($jobFlyer->image);

        $jobFlyer->delete();
        Cache::forget('contact_active_job_flyers');

        return redirect()->route('admin.trabalhe_conosco.index')
            ->with('success', 'Flyer removido.');
    }
}
