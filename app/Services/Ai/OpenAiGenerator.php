<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;

class OpenAiGenerator implements AiTextGenerator
{
    public function __construct(protected ?string $model = null, protected ?string $apiKey = null) {}

    public function key(): string
    {
        return 'openai';
    }

    public function configured(): bool
    {
        return filled($this->apiKey ?: config('services.openai.key'));
    }

    protected function apiKey(): string
    {
        return (string) ($this->apiKey ?: config('services.openai.key'));
    }

    public function generate(string $system, string $user): string
    {
        $response = Http::withToken($this->apiKey())
            ->timeout(90)
            ->retry(2, 500, throw: false)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model ?: config('services.openai.model', 'gpt-5.4-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $user],
                ],
                'response_format' => ['type' => 'json_object'],
                'temperature' => 0.7,
            ])
            ->throw();

        return (string) data_get($response->json(), 'choices.0.message.content', '');
    }
}
