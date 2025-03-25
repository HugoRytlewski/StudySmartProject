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



    public function getCalendar(string $chat,string $chat2 ,string $model = 'microsoft/phi-3-medium-128k-instruct:free'): array
    {
        try {
            $apiKey = $this->parameterBag->get('OPENROUTER_API_KEY');

            $body = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Je te fournis un tableau d'événements : " . $chat . " ainsi qu'un événement spécifique : " . $chat2 . ". Génére un événement de révision pour cet événement en tenant compte des disponibilités du tableau. Retourne le résultat sous forme de JSON avec les éléments suivants :  - 'title' : un titre commençant par 'Révision : ' suivi du titre de l'événement. - 'datestart' : la date et l'heure de début de la révision, formatée sous forme de chaîne de caractères 'YYYY-MM-DD HH:MM:SS'. - 'dateend' : la date et l'heure de fin de la révision, formatée sous forme de chaîne de caractères 'YYYY-MM-DD HH:MM:SS'. - 'user' : un tableau contenant un sous-tableau avec la clé 'id' pour l'utilisateur concerné, sous la forme : [{'id': <user_id>}]. Assure-toi que tous ces champs soient présents dans la réponse sous forme exacte et ne rajoute aucun commentaire dans ta réponse."
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
            var_dump($data);

            
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

    public function getQuizz(string $chat ,string $model = 'microsoft/phi-3-medium-128k-instruct:free'): array
    {
        try {
            $apiKey = $this->parameterBag->get('OPENROUTER_API_KEY');

            $body = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "VOici le cours ". $chat . "Genere une page html css javascript en une seule page avec un formulaire de quizz. Le formulaire doit contenir 5 questions avec 4 réponses possibles pour chaque question. Une seule réponse est correcte pour chaque question. Le formulaire doit être soumis en POST à l'URL '/submit'. Le formulaire doit être stylisé avec du CSS et les réponses doivent être vérifiées en JavaScript. Le formulaire doit être valide HTML5."
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