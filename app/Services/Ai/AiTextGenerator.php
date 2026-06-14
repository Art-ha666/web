<?php

namespace App\Services\Ai;

interface AiTextGenerator
{
    /**
     * The provider key, e.g. "openai", "gemini", "template".
     */
    public function key(): string;

    /**
     * Whether this provider has the credentials it needs to run.
     */
    public function configured(): bool;

    /**
     * Generate raw model output (expected to be a JSON string).
     */
    public function generate(string $system, string $user): string;
}
