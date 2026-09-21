@props([
    'title',
])

<header
    class="
        flex
        h-[70px]
        items-center
        border-b
        border-slate-200
        bg-white
        px-5
        md:px-8
    "
>
    <h1 class="text-xl font-semibold">
        {{ $title }}
    </h1>
</header>