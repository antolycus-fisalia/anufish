@props(['history'])
<div class="scan-history-photo" data-history-photo>
    <img src="{{ route('scan.history.image', $history) }}" alt="{{ __('history.photo') }}" loading="lazy" data-history-image>
    <p hidden data-history-image-error>{{ __('history.photo_missing') }}</p>
</div>
