<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ThemeSetting;
use App\Services\ThemeSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ThemeSettingController extends Controller
{
    public function edit(ThemeSettingsService $themes): View
    {
        return view('admin.theme-settings.edit', [
            'themes' => $themes->allForAdmin(),
            'fontOptions' => ThemeSettingsService::FONT_OPTIONS,
        ]);
    }

    public function update(Request $request, string $scope, ThemeSettingsService $themes): JsonResponse
    {
        abort_unless(array_key_exists($scope, ThemeSettingsService::SCOPES), 404);

        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.background_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.surface_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.heading_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.subtitle_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.body_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.inverse_text_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.link_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.button_background' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.button_text' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.header_background_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.header_text_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.header_accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.footer_background_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.footer_heading_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.footer_text_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.border_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.hover_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'settings.heading_font' => ['required', Rule::in(array_keys(ThemeSettingsService::FONT_OPTIONS))],
            'settings.body_font' => ['required', Rule::in(array_keys(ThemeSettingsService::FONT_OPTIONS))],
            'settings.header_font' => ['required', Rule::in(array_keys(ThemeSettingsService::FONT_OPTIONS))],
            'settings.footer_font' => ['required', Rule::in(array_keys(ThemeSettingsService::FONT_OPTIONS))],
            'settings.heading_weight' => ['required', Rule::in(['original', '400', '500', '600', '700', '800', '900'])],
            'settings.base_font_size' => ['required', Rule::in(['original', '13', '14', '15', '16', '17', '18', '19', '20'])],
            'settings.button_radius' => ['required', Rule::in(['original', '0', '4', '8', '12', '20', '999'])],
            'settings.card_radius' => ['required', Rule::in(['original', '0', '4', '8', '12', '16', '24'])],
        ]);

        $settings = $themes->normalize($scope, $validated['settings']);
        $record = ThemeSetting::query()->updateOrCreate(
            ['scope' => $scope],
            ['settings' => $settings, 'is_active' => true, 'updated_by' => $request->user()?->id]
        );
        $themes->clear($scope);

        return response()->json([
            'success' => true,
            'message' => 'Identidade visual salva.',
            'scope' => $scope,
            'settings' => $settings,
            'updated_at' => $record->updated_at?->toIso8601String(),
        ]);
    }

    public function reset(string $scope, ThemeSettingsService $themes): JsonResponse
    {
        abort_unless(array_key_exists($scope, ThemeSettingsService::SCOPES), 404);

        ThemeSetting::query()->where('scope', $scope)->delete();
        $themes->clear($scope);

        return response()->json([
            'success' => true,
            'message' => 'Identidade original restaurada.',
            'scope' => $scope,
            'settings' => $themes->defaults($scope),
        ]);
    }
}
