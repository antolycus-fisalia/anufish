@props(['icon', 'href' => null, 'active' => false])
<li>
    @if ($href)
        <a href="{{ $href }}" @if ($active) aria-current="page" @endif
            class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold {{ $active ? 'bg-anufish-pale text-anufish-navy' : 'text-anufish-muted hover:bg-anufish-pale hover:text-anufish-navy' }} focus-visible:outline-2 focus-visible:outline-anufish-teal">
            <x-navigation.icon :name="$icon" /> {{ $slot }}
        </a>
    @else
        <span aria-disabled="true"
            class="flex min-h-11 items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold text-anufish-muted"><x-navigation.icon
                :name="$icon" /> {{ $slot }} <span
                class="ml-auto text-xs font-normal">{{ __('Segera') }}</span></span>
    @endif
</li>
