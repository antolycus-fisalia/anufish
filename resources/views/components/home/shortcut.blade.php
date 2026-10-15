@props(['icon', 'title', 'description', 'href' => null])

<article class="home-shortcut">
    <x-navigation.icon :name="$icon" />
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    @if ($href)
        <a href="{{ $href }}" class="home-text-link">{{ __('Buka') }} <span aria-hidden="true">→</span><span class="sr-only">: {{ $title }}</span></a>
    @else
        <span class="home-coming-soon">{{ __('Segera') }}</span>
    @endif
</article>
