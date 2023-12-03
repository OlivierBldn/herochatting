<?php // path: src/Class/Service/srv.OpenAIService.php

require_once __DIR__ . '/../../../config/cfg_openAIConfig.php';

class OpenAIService
{
    private static ?OpenAIService $instance = null;

    private function __construct() {}

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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        $this->logResponse($response);
    
        if ($response === false) {
            return 'Erreur lors de la connexion à OpenAI';
        }
    
        $responseData = json_decode($response, true);

        if ($httpCode != 200) {
            return 'Erreur lors de la réponse OpenAI: HTTP Code ' . $httpCode . ' - Réponse: ' . json_encode($responseData);
        }
    
        return $responseData['choices'][0]['text'] ?? 'Description non disponible';
    }
    

    public function generateResponse($userMessage, Character $character) {
        $formattedPrompt = $this->formatPrompt($userMessage, $character);

        return $this->generateDescription($formattedPrompt) ?? 'Description non disponible';
    }

    private function formatPrompt($userMessage, Character $character) {
        $characterName = $character->getName();
        $characterDescription = $character->getDescription();
        $prompt = "Tu parles avec {$characterName}, un personnage décrit comme {$characterDescription}. L'utilisateur répond : '{$userMessage}'";
        
        return $prompt;
    }

    // private function logAttempt($message) {
    //     $logFile = __DIR__ . '/../../../logs/open_ai.log';
    //     $timestamp = date('Y-m-d H:i:s');
    //     file_put_contents($logFile, "[$timestamp] Tentative: $message\n", FILE_APPEND);
    // }
    
    private function logError($message) {
        $logFile = __DIR__ . '/../../../logs/open_ai.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    private function logResponse($response) {
        $logFile = __DIR__ . '/../../../logs/open_ai.log';
        $timestamp = date('Y-m-d H:i:s');
        $formattedResponse = json_encode($response, JSON_PRETTY_PRINT);
    file_put_contents($logFile, "[$timestamp] Réponse API OpenAI : $formattedResponse\n", FILE_APPEND);
    }
}