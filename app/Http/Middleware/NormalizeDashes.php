<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

/**
 * Site style bans em/en dashes: replace every dash variant with a plain
 * hyphen on admin input, so pasted copy (LLM drafts especially) can never
 * reintroduce them into rendered content. Secrets are left untouched.
 */
class NormalizeDashes extends TransformsRequest
{
    protected const DASHES = ["\u{2014}", "\u{2013}", "\u{2012}", "\u{2015}", "\u{2212}"];

    /** @var array<int, string> */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'api_key',
    ];

    /**
     * @param  string  $key
     */
    protected function transform($key, $value): mixed
    {
        if (! is_string($value) || in_array($key, $this->except, true)) {
            return $value;
        }

        return str_replace(self::DASHES, '-', $value);
    }
}
