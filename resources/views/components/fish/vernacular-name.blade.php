@props(['name'])
<li class="min-w-0 rounded-lg bg-anufish-pale p-3">
    <span class="text-xs text-anufish-muted">{{ $name['language_label'] }}</span>
    <bdi class="mt-1 block break-words text-sm font-medium leading-[22px] text-anufish-navy" @if($name['language']) lang="{{ $name['language'] }}" @endif>{{ $name['name'] }}</bdi>
</li>
