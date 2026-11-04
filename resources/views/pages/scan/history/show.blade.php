@extends('layouts.authenticated')
@section('title', __('history.detail').' - Anufish')
@section('page-content')
    <div class="scan-history-page">
        <a href="{{ route('scan.history.index') }}" class="scan-reference-link">{{ __('history.back') }}</a>
        <header class="scan-history-header">
            <div>
                <h1 class="page-title">{{ __('history.detail') }}</h1>
                <p>{{ $history->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>
            </div>
        </header>
        <section class="scan-history-panel scan-history-detail">
            <x-scan.history-photo :history="$history" />
            <div>
                <h2>{{ __('history.candidates') }}</h2>
                <p>{{ __('history.score_note') }}</p>
                <ul class="scan-history-candidates">
                    @foreach ($history->candidates as $index => $candidate)
                        <li>
                            <h3><bdi>{{ $candidate['scientific_name'] }}</bdi></h3>
                            <p>{{ __('history.confidence') }}: {{ isset($candidate['confidence']) ? round($candidate['confidence'] * 100, 2).'%' : __('history.no_score') }}</p>
                            @if ($history->selected_candidate_index === $index)
                                <p>{{ __('history.selected') }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
                @if ($history->inaturalist_taxon_id)
                    <a href="{{ route('fish.show', $history->inaturalist_taxon_id) }}" class="scan-history-secondary-action">{{ __('history.reference') }}</a>
                @else
                    <p>{{ __('history.no_reference') }}</p>
                @endif
            </div>
        </section>
    </div>
@endsection
