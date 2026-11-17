@props(['icon', 'title', 'description', 'href'])

<article class="home-shortcut">
    <x-navigation.icon :name="$icon" />
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    <a href="{{ $href }}" class="home-text-link">{{ __('Buka') }} <span aria-hidden="true">→</span><span class="sr-only">: {{ $title }}</span></a>
</article>
