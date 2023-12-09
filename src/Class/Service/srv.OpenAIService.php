<?php // path: src/Class/Service/srv.OpenAIService.php

require_once __DIR__ . '/../../../config/cfg_openAIConfig.php';

/**
 * OpenAIService
 * Class to handle the requests to the OpenAI API
 * Allows to generate a description from a prompt
 * Allows to generate a prompt used in the StableDiffusionService
 * Implements the Singleton pattern
 */
class OpenAIService
{
    private static ?OpenAIService $instance = null;

    private function __construct() {}

    /**
     * Function to get the instance of the OpenAIService
     *
     * @return OpenAIService
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new OpenAIService();
        }
        return self::$instance;
    }

    /**
     * Function to generate a description for an object
     * The configuration is defined in the config file
    * Tou can find more about the configuration of the OpenAI api request here: https://platform.openai.com/docs/api-reference
     *
     * @param string $prompt
     * @return string
     */
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

    /**
     * Function to send the request to the OpenAI API using parameters from the config file
     * You can find more about the configuration of the OpenAI api request here: https://platform.openai.com/docs/api-reference
     *
     * @param string $request
     * @return string
     */
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
            return 'Erreur lors de la connexion a OpenAI';
        }
    
        $responseData = json_decode($response, true);

        if ($httpCode != 200) {
            return 'Erreur lors de la reponse OpenAI: HTTP Code ' . $httpCode . ' - Reponse: ' . json_encode($responseData);
        }
    
        return $responseData['choices'][0]['text'] ?? 'Description non disponible';
    }
    
    /**
     * Function to generate a prompt for OpenAI to answer to a message sent by a user
     *
     * @param string $userMessage
     * @param Character $character
     * @return string
     */
    public function generateResponse($userMessage, Character $character) {
        $formattedPrompt = $this->formatPrompt($userMessage, $character);

        return $this->generateDescription($formattedPrompt) ?? 'Description non disponible';
    }

    /**
     * Function to format the prompt for OpenAI
     *
     * @param string $userMessage
     * @param Character $character
     * @return string
     */
    private function formatPrompt($userMessage, Character $character) {
        $characterName = $character->getName();
        $characterDescription = $character->getDescription();
        $prompt = "Tu parles avec {$characterName}, un personnage decrit comme {$characterDescription}. L'utilisateur repond : '{$userMessage}'";
        
        return $prompt;
    }

    // private function logAttempt($message) {
    //     $logFile = __DIR__ . '/../../../logs/open_ai.log';
    //     $timestamp = date('Y-m-d H:i:s');
    //     file_put_contents($logFile, "[$timestamp] Tentative: $message\n", FILE_APPEND);
    // }
    
    /** 
     * Function to log an error into the log file
     * 
     * @param string $message
     * @return void
     */
    private function logError($message) {
        $logFile = __DIR__ . '/../../../logs/open_ai.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    /**
     * Function to log the response from the OpenAI API into the log file
     *
     * @param string $response
     * @return void
     */
    private function logResponse($response) {
        $logFile = __DIR__ . '/../../../logs/open_ai.log';
        $timestamp = date('Y-m-d H:i:s');
        $formattedResponse = json_encode($response, JSON_PRETTY_PRINT);
    file_put_contents($logFile, "[$timestamp] Reponse API OpenAI : $formattedResponse\n", FILE_APPEND);
    }
}