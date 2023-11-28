<?php // path: src/Class/Service/srv.OpenAIService.php

require_once __DIR__ . '/../../../config/cfg_openAIConfig.php';

class OpenAIService
{
    // Attribut statique privé pour stocker l'unique instance
    private static ?OpenAIService $instance = null;

    // Constructeur privé pour empêcher l'instanciation directe
    private function __construct() {}

    // Méthode statique pour obtenir l'instance du singleton
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new OpenAIService();
        }
        return self::$instance;
    }

    public function generateDescription($prompt) {

        $request = array(
            "model" => __OPEN_AI_MODEL__,
            "temperature" => __OPEN_AI_TEMPERATURE__,
            "max_tokens" => __OPEN_AI_MAX_TOKENS__,
            "top_p" => __OPEN_AI_TOP_P__,
            "frequency_penalty" => __OPEN_AI_FREQUENCY_PENALTY__,
            "presence_penalty" => __OPEN_AI_PRESENCE_PENALTY__,
            "stop" => __OPEN_AI_STOP__,
            "prompt" => $prompt
        );

        return $this->sendRequest($request);
    }

    private function sendRequest($request) {
        $ch = curl_init(__OPEN_AI_API_URL__);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . __OPEN_AI_API_KEY__
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response === false) {
            return 'Erreur lors de la connexion à OpenAI';
        }

        $responseData = json_decode($response, true);
        return $responseData['choices'][0]['text'] ?? 'Description non disponible';
    }
}