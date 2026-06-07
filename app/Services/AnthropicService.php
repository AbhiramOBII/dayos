<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicService
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private string $baseUrl;
    private string $apiVersion;

    public function __construct()
    {
        $this->apiKey = config('anthropic.api_key') ?? '';
        $this->model = config('anthropic.model');
        $this->maxTokens = config('anthropic.max_tokens');
        $this->baseUrl = config('anthropic.base_url');
        $this->apiVersion = config('anthropic.api_version');
    }

    /**
     * Send a single user message and get a text response.
     */
    public function message(string $userMessage, ?string $systemPrompt = null, ?int $maxTokens = null): string
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => $maxTokens ?? $this->maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        $response = $this->post('/messages', $payload);

        return $response->json('content.0.text') ?? '';
    }

    /**
     * Send a conversation history and get a text response.
     *
     * @param  array<int, array{role: string, content: string}>  $messages
     */
    public function chat(array $messages, ?string $systemPrompt = null, ?int $maxTokens = null): string
    {
        $payload = [
            'model' => $this->model,
            'max_tokens' => $maxTokens ?? $this->maxTokens,
            'messages' => $messages,
        ];

        if ($systemPrompt) {
            $payload['system'] = $systemPrompt;
        }

        $response = $this->post('/messages', $payload);

        return $response->json('content.0.text') ?? '';
    }

    /**
     * Make an authenticated POST request to the Anthropic API.
     */
    private function post(string $endpoint, array $payload): Response
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set in your .env file.');
        }

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => $this->apiVersion,
            'content-type' => 'application/json',
        ])->post($this->baseUrl . $endpoint, $payload);

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new RuntimeException('Anthropic API error: ' . $error);
        }

        return $response;
    }
}
