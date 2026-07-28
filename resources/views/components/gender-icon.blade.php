@props([
    'gender' => '',
    'size' => 'md',
])

@php
    $isMale = $gender === 'Laki-laki';
    $isFemale = $gender === 'Perempuan';
    $label = $isMale ? 'Pria' : ($isFemale ? 'Wanita' : ($gender ?: '—'));
    $iconClass = match ($size) {
        'sm' => 'size-4',
        'lg' => 'size-6',
        default => 'size-5',
    };
@endphp

@if ($isMale)
    <span {{ $attributes->class(['gender-icon gender-icon--male']) }} title="{{ $label }}" aria-label="{{ $label }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ $iconClass }}" aria-hidden="true">
            <circle cx="10" cy="14" r="5.2" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.2 9.8 20 4M15.2 4H20v4.8" />
        </svg>
    </span>
@elseif ($isFemale)
    <span {{ $attributes->class(['gender-icon gender-icon--female']) }} title="{{ $label }}" aria-label="{{ $label }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="{{ $iconClass }}" aria-hidden="true">
            <circle cx="12" cy="9" r="5.2" />
            <path stroke-linecap="round" d="M12 14.2V21M9.2 18.2h5.6" />
        </svg>
    </span>
@else
    <span {{ $attributes->class(['text-xs opacity-50']) }}>{{ $label }}</span>
@endif
