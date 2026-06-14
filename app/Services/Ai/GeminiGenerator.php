<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class GeminiGenerator implements AiTextGenerator
{
    public function __construct(protected ?string $model = null, protected ?string $apiKey = null) {}

    public function key(): string
    {
        return 'gemini';
    }

    public function configured(): bool
    {
        return filled($this->apiKey ?: config('services.gemini.key'));
    }

    public function generate(string $system, string $user): string
    {
        $model = (string) ($this->model ?: config('services.gemini.model', 'gemini-3.5-flash'));
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = Http::timeout(90)
            ->retry(2, 500, throw: false)
            ->acceptJson()
            ->withHeaders(['x-goog-api-key' => (string) ($this->apiKey ?: config('services.gemini.key'))])
            ->post($url, [
                'system_instruction' => ['parts' => [['text' => $system]]],
                'contents' => [['role' => 'user', 'parts' => [['text' => $user]]]],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'temperature' => 0.7,
                ],
            ])
            ->throw();

        return (string) data_get($response->json(), 'candidates.0.content.parts.0.text', '');
    }
}
