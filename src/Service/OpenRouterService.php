<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenRouterService
{
    private const API_URL = 'https://openrouter.ai/api/v1/chat/completions';
        
    public function __construct(
        private ParameterBagInterface $parameterBag,
        private HttpClientInterface $httpClient
    ) {
    }

    public function getResponse(string $chat, string $model = 'microsoft/phi-3-medium-128k-instruct:free'): array
    {
        try {
            $apiKey = $this->parameterBag->get('OPENROUTER_API_KEY');

            $body = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Reformule ce document en Markdown de manière claire et bien structurée. Ne modifie pas le contenu, ne rajoute pas d'explications ou de commentaires, et ne génère aucune phrase supplémentaire en dehors du texte reformulé.\n\n" . $chat
                    ]
                ],
                'temperature' => 0.7
            ];

            $response = $this->httpClient->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => 'http://localhost',
                    'X-Title' => 'Mon Application'
                ],
                'json' => $body
            ]);

            $data = $response->toArray();
            
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \RuntimeException('Structure de réponse inattendue : ' . json_encode($data));
            }

            return [
                'choices' => $data['choices'],
                'usage' => $data['usage'] ?? null
            ];

        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur API OpenRouter : ' . $e->getMessage());
        }
    }
}