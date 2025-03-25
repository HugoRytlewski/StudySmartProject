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

    public function getCalendar(string $chat, string $chat2, string $model = 'microsoft/phi-3-medium-128k-instruct:free'): array
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

    public function getQuizz(string $chat, string $model = 'microsoft/phi-3-medium-128k-instruct:free'): array
    {
        try {
            $apiKey = $this->parameterBag->get('OPENROUTER_API_KEY');

            $body = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                     'content' => "Voici le cours : " . $chat . " Génère une page HTML simple avec Tailwind CSS contenant un quiz basé sur ce cours. Les réponses incorrectes doivent devenir rouges au survol, et vertes si elles sont correctes. Les questions doivent être disposées en colonnes et utilisables sans action préalable.

Exigences spécifiques :
- Génère exactement 5 questions pertinentes basées sur le cours fourni.
- Chaque question doit avoir 4 options avec une seule réponse correcte.
- Utilise Tailwind CSS pour le style (CDN déjà inclus).
- Le quiz doit être entièrement fonctionnel sans rechargement de page.
- Centrer le quiz sur la page.
- Fournis uniquement le code HTML complet sans commentaires ni explications.
- Tout doit être en français.
- Assure une expérience utilisateur fluide sans bugs.

Modèle HTML :
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Quiz</title>
    <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
    <style>
        .correct\:hover {
            background-color: green;
        }
        .incorrect\:hover {
            background-color: red;
        }
    </style>
</head>
<body class='flex items-center justify-center min-h-screen bg-gray-100'>
    <div class='bg-white p-8 rounded shadow-md w-full max-w-2xl'>
        <h1 class='text-2xl font-bold mb-4'>Quiz</h1>
        <div class='grid grid-cols-1 gap-4 md\:grid-cols-2'>
            <!-- Remplacez les questions et options ci-dessous par celles générées -->
            <div class='question'>
                <p class='font-semibold'>Question 1</p>
                <div class='option incorrect'>Option 1</div>
                <div class='option incorrect'>Option 2</div>
                <div class='option correct'>Option 3</div>
                <div class='option incorrect'>Option 4</div>
            </div>
            <div class='question'>
                <p class='font-semibold'>Question 2</p>
                <div class='option incorrect'>Option 1</div>
                <div class='option correct'>Option 2</div>
                <div class='option incorrect'>Option 3</div>
                <div class='option incorrect'>Option 4</div>
            </div>
            <div class='question'>
                <p class='font-semibold'>Question 3</p>
                <div class='option correct'>Option 1</div>
                <div class='option incorrect'>Option 2</div>
                <div class='option incorrect'>Option 3</div>
                <div class='option incorrect'>Option 4</div>
            </div>
            <div class='question'>
                <p class='font-semibold'>Question 4</p>
                <div class='option incorrect'>Option 1</div>
                <div class='option incorrect'>Option 2</div>
                <div class='option incorrect'>Option 3</div>
                <div class='option correct'>Option 4</div>
            </div>
            <div class='question'>
                <p class='font-semibold'>Question 5</p>
                <div class='option incorrect'>Option 1</div>
                <div class='option incorrect'>Option 2</div>
                <div class='option correct'>Option 3</div>
                <div class='option incorrect'>Option 4</div>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll('.option').forEach(option => {
            option.addEventListener('click', () => {
                if (option.classList.contains('correct')) {
                    option.classList.add('bg-green-500');
                } else {
                    option.classList.add('bg-red-500');
                }
            });
        });
    </script>
</body>
</html>
"

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