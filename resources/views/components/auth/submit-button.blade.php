<button type="submit"
    {{ $attributes->class('flex w-full items-center justify-center rounded-lg bg-linear-to-r from-anufish-navy via-anufish-teal to-anufish-cyan px-4 py-3 text-sm font-bold text-white shadow-lg shadow-anufish-cyan/20 transition hover:from-anufish-teal hover:via-anufish-cyan hover:to-anufish-cyan focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-anufish-teal focus-visible:ring-offset-2 active:from-anufish-navy active:via-anufish-navy active:to-anufish-teal') }}>
    {{ $slot }}
</button>
