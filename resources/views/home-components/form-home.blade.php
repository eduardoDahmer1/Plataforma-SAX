@php
    $formHomeSettings = $settings ?? App\Models\Generalsetting::first();
    $formHomeSections = $formHomeSettings?->resolvedHomeSections()
        ?? App\Models\Generalsetting::defaultHomeSections();
    $helpSectionContent = App\Models\Generalsetting::contentForLocale($formHomeSections['help']);
    $newsletterSectionContent = App\Models\Generalsetting::contentForLocale($formHomeSections['newsletter']);
@endphp

@include('home-components.help-section', ['sectionContent' => $helpSectionContent])
@include('home-components.newsletter-section', ['sectionContent' => $newsletterSectionContent])
