@props(['html'])
{!! app(\App\Services\ArticleHtmlSanitizer::class)->sanitize((string) $html) !!}
