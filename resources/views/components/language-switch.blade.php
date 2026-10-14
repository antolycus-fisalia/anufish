<form method="POST" action="{{ route('locale.update') }}" class="inline-flex shrink-0 items-center rounded-lg border border-anufish-border bg-white p-1" aria-label="{{ __('Bahasa') }}">
    @csrf
    <input type="hidden" name="return_to" value="{{ request()->getRequestUri() }}">
    @foreach(['id' => 'ID', 'en' => 'EN'] as $locale => $label)
        <button type="submit" name="locale" value="{{ $locale }}" aria-label="{{ $locale === 'id' ? 'Bahasa Indonesia' : 'English' }}" aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}" class="flex min-h-11 min-w-11 items-center justify-center rounded-md px-2 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-anufish-teal {{ app()->getLocale() === $locale ? 'bg-anufish-teal text-white' : 'text-anufish-muted hover:bg-anufish-pale' }}">{{ $label }}</button>
    @endforeach
</form>
