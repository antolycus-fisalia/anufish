@props([
    'state' => 'idle',
    'title',
    'message' => null,
    'compact' => false,
])

@php
    $allowedStates = [
        'idle',
        'loading',
        'success',
        'validation',
        'empty',
        'unknown',
        'partial',
        'failure',
    ];

    $state = in_array($state, $allowedStates, true)
        ? $state
        : 'idle';

    $role = match ($state) {
        'loading', 'success', 'unknown', 'partial' => 'status',
        'validation', 'failure' => 'alert',
        default => null,
    };

    $live = match ($state) {
        'loading', 'success', 'unknown', 'partial' => 'polite',
        'validation', 'failure' => 'assertive',
        default => null,
    };
@endphp

<section
    {{ $attributes->class([
        'ui-state',
        "ui-state-{$state}",
        'ui-state-compact' => $compact,
    ]) }}
    data-ui-state="{{ $state }}"
    @if ($role) role="{{ $role }}" @endif
    @if ($live) aria-live="{{ $live }}" @endif
    @if ($state === 'loading') aria-busy="true" @endif
    aria-atomic="true"
>
    @if ($state === 'loading')
        <span
            class="ui-state-spinner"
            aria-hidden="true"
        ></span>
    @endif

    <div class="ui-state-content">
        <h2 class="ui-state-title">
            {{ $title }}
        </h2>

        @if (isset($body))
            <div class="ui-state-message">
                {{ $body }}
            </div>
        @elseif ($message)
            <p class="ui-state-message">
                {{ $message }}
            </p>
        @endif

        @if (trim((string) $slot) !== '')
            <div class="ui-state-actions">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
