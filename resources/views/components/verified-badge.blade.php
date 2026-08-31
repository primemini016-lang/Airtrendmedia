@props(['size' => 'w-5 h-5', 'class' => ''])
@php
    // Resolve the platform's primary blue colour so the badge always matches
    // the admin-configured theme. Falls back to Facebook's #1877F2.
    $badgeColor = '#1877F2';
    try {
        $s = app(\App\Services\SettingService::class)->all();
        if (! empty($s->primary_color)) {
            $badgeColor = $s->primary_color;
        }
    } catch (\Throwable $e) {}
@endphp
<span {{ $attributes->merge(['class' => "verified-badge inline-flex items-center justify-center align-middle {$class}"]) }}
      role="img" aria-label="Verified">
    <svg viewBox="0 0 36 36" class="{{ $size }}" aria-hidden="true" fill="none" xmlns="http://www.w3.org/2000/svg">
        {{-- Facebook-style scalloped verification seal (12-point sunburst) --}}
        <path d="M18 1.5l3.07 2.65 4.04-.66 1.31 3.92 3.92 1.31-.66 4.04L35.5 18l-2.82 3.24.66 4.04-3.92 1.31-1.31 3.92-4.04-.66L18 35.5l-3.24-2.82-4.04.66-1.31-3.92-3.92-1.31.66-4.04L.5 18l2.65-3.07-.66-4.04 3.92-1.31 1.31-3.92 4.04.66z"
              fill="{{ $badgeColor }}"/>
        <path d="M11.5 18.5l4.2 4.2 8.8-8.8"
              stroke="#fff" stroke-width="2.6"
              stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</span>
