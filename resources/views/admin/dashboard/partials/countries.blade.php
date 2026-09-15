<div class="dashboard-modal-period">
    <i class="fa-regular fa-calendar"></i>
    <span>{{ $selection['label'] }}</span>
</div>
<p class="small text-muted">{{ __('messages.report_country_tracking_note') }}</p>
<div class="country-list">
    @forelse($countries as $country)
        @php $percentage = max(4, round(((int) $country->visitors / max(1, (int) $countries->sum('visitors'))) * 100)); @endphp
        <div class="country-row">
            <div class="country-row__label">
                <span class="country-code">{{ $country->country_code !== 'XX' ? $country->country_code : '—' }}</span>
                <span>{{ $country->country_name }}</span>
            </div>
            <div class="country-row__value">{{ number_format($country->visitors, 0, ',', '.') }}</div>
            <div class="country-row__bar"><span style="width: {{ $percentage }}%"></span></div>
        </div>
    @empty
        <div class="dashboard-empty-state">
            <i class="fa-solid fa-earth-americas"></i>
            <span>{{ __('messages.dashboard_no_country_data') }}</span>
        </div>
    @endforelse
</div>
