<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketingSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.marketing.edit', [
            'settings' => MarketingSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'default_meta_title' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string', 'max:500'],
            'default_meta_keywords' => ['nullable', 'string', 'max:20000'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:500'],
            'og_image_url' => ['nullable', 'url', 'max:2048'],
            'twitter_site' => ['nullable', 'string', 'max:100', 'regex:/^@?[A-Za-z0-9_]+$/'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'bing_site_verification' => ['nullable', 'string', 'max:255'],
            'meta_domain_verification' => ['nullable', 'string', 'max:255'],
            'google_tag_manager_id' => ['nullable', 'regex:/^GTM-[A-Z0-9]+$/'],
            'google_analytics_id' => ['nullable', 'regex:/^G-[A-Z0-9]+$/'],
            'google_ads_id' => ['nullable', 'regex:/^AW-[0-9]+$/'],
            'google_ads_conversion_label' => ['nullable', 'string', 'max:100'],
            'meta_pixel_id' => ['nullable', 'regex:/^[0-9]+$/'],
            'tiktok_pixel_id' => ['nullable', 'regex:/^[A-Z0-9]+$/'],
            'pinterest_tag_id' => ['nullable', 'regex:/^[0-9]+$/'],
            'linkedin_partner_id' => ['nullable', 'regex:/^[0-9]+$/'],
            'microsoft_clarity_id' => ['nullable', 'regex:/^[a-z0-9]+$/'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'organization_url' => ['nullable', 'url', 'max:255'],
            'organization_logo_url' => ['nullable', 'url', 'max:2048'],
            'organization_social_urls' => ['nullable', 'string', 'max:4000'],
            'custom_head_scripts' => ['nullable', 'string', 'max:50000'],
            'custom_body_start_scripts' => ['nullable', 'string', 'max:50000'],
            'custom_body_end_scripts' => ['nullable', 'string', 'max:50000'],
        ], [
            'google_tag_manager_id.regex' => 'Use o formato GTM-XXXXXXX.',
            'google_analytics_id.regex' => 'Use o formato G-XXXXXXXXXX.',
            'google_ads_id.regex' => 'Use o formato AW-123456789.',
        ]);

        $data['enabled'] = $request->boolean('enabled');

        $settings = MarketingSetting::query()->first() ?? new MarketingSetting();
        $settings->fill($data)->save();
        MarketingSetting::clearCache();

        return back()->with('success', 'Configurações de SEO e marketing atualizadas.');
    }
}
