<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ArticleImage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail(__('article.validation.image_invalid'));

            return;
        }
        $size = @getimagesize($value->getRealPath());
        if (! is_array($size) || ! in_array($size[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
            $fail(__('article.validation.image_invalid'));

            return;
        }
        if ($size[0] * $size[1] > 8000000) {
            $fail(__('article.validation.image_dimensions'));

            return;
        }
        $memoryLimit = ini_parse_quantity((string) ini_get('memory_limit'));
        $estimate = $size[0] * $size[1] * 8 + $value->getSize() * 2 + 8 * 1024 * 1024;
        if ($memoryLimit > 0 && $estimate > $memoryLimit - memory_get_usage(true)) {
            $fail(__('article.validation.image_resources'));

            return;
        }
        $image = @imagecreatefromstring(file_get_contents($value->getRealPath()));
        if ($image === false) {
            $fail(__('article.validation.image_invalid'));
        }
        unset($image);
    }
}
