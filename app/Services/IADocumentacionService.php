<?php

namespace App\Services;

class IADocumentacionService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected float $temperature = 0.3;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY', '');
        $this->model = env('OPENAI_MODEL', 'gpt-4o-mini');
    }

    /**
     * Genera contenido libre con un prompt directo
     */
    public function generarContenido(string $prompt, int $maxTokens = 1500): string
    {
        if (empty($this->apiKey)) {
            throw new \Exception('OPENAI_API_KEY no configurada en .env');
        }

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $maxTokens
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \Exception('Error de conexion con OpenAI: ' . $curlError);
        }

        if ($httpCode !== 200) {
            $error = json_decode($response, true);
            throw new \Exception('OpenAI Error: ' . ($error['error']['message'] ?? "HTTP {$httpCode}"));
        }

        $result = json_decode($response, true);
        return $result['choices'][0]['message']['content'] ?? '';
    }
}
