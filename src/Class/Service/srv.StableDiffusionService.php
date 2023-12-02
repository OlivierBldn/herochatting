<?php // path: src/Class/Service/srv.StableDiffusionService.php

require_once __DIR__ . '/../../../config/cfg_stableDiffusionConfig.php';

class StableDiffusionService
{
    private static ?StableDiffusionService $instance = null;

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new StableDiffusionService();
        }
        return self::$instance;
    }

    public function generateImage($prompt) {
        $request = array(
            "key" => __STABLE_DIFFUSION_API_KEY__,
            "prompt" => $prompt,
        );

        return $this->sendRequest($request, $prompt);
    }

    private function sendRequest($request, $originalPrompt) {
        $ch = curl_init(__STABLE_DIFFUSION_API_URL__);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . __STABLE_DIFFUSION_API_KEY__
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        $this->logResponse($response);

        if ($response === false) {
            $this->logError("Erreur cURL lors de la connexion à Stable Diffusion: " . $error);
            return 'Image non disponible #1';
        }

        $responseData = json_decode($response, true);
        if (isset($responseData['status']) && $responseData['status'] === 'processing') {
            return $this->handleProcessingImage($responseData, $originalPrompt);
        }

        if (isset($responseData['error'])) {
            $this->logError("Erreur de réponse API: " . $responseData['error']['message']);
            return 'Image non disponible #2';
        }

        if (isset($responseData['output']) && is_array($responseData['output']) && count($responseData['output']) > 0) {
            return $this->downloadImage($responseData['output'][0]);
        }

        if (isset($responseData['status']) && $responseData['status'] !== 'success') {
            $this->logError("Erreur API : " . json_encode($responseData));
            return 'Image non disponible #3';
        }

        $this->logError("Réponse API invalide ou image non générée.");
        return 'Image non disponible #4';
    }

    private function downloadImage($imageUrl) {
        $imageContent = file_get_contents($imageUrl);
        if ($imageContent === false) {
            $this->logError("Erreur lors du téléchargement de l'image depuis l'URL : $imageUrl");
            return 'Image non disponible #5';
        }
    
        $uploadPath = __DIR__ . '/../../../uploads/';
        if (!file_exists($uploadPath) || !is_writable($uploadPath)) {
            $this->logError("Le répertoire d'upload n'existe pas ou n'est pas accessible en écriture : $uploadPath");
            return 'Image non disponible #6';
        }
    
        $imageFileName = uniqid() . '.png';
        $imageFullPath = $uploadPath . $imageFileName;
    
        if (file_put_contents($imageFullPath, $imageContent) === false) {
            $this->logError("Erreur lors de l'enregistrement de l'image : $imageFullPath");
            return 'Image non disponible #7';
        }
    
        return $imageFileName;
    }

    private function handleProcessingImage($responseData, $originalPrompt) {
        $fetchUrl = $responseData['fetch_result'] ?? null;
        $eta = $responseData['eta'] ?? 0;
        
        $maxAttempts = 5;
        $attempt = 0;
    
        while ($attempt < $maxAttempts) {
            if ($fetchUrl) {
                $this->logAttempt("Tentative de récupération de l'image $attempt/$maxAttempts, URL: $fetchUrl");
    
                sleep($eta);
    
                if (in_array($attempt, [1, 3])) {
                    $originalPrompt = $this->regeneratePrompt($originalPrompt);
                    $newRequest = [
                        "key" => __STABLE_DIFFUSION_API_KEY__,
                        "prompt" => $originalPrompt
                    ];
                    $fetchUrl = $this->sendRequest($newRequest, $originalPrompt);
                }
    
                $imageResponse = $this->fetchImage($fetchUrl);
                if ($imageResponse !== 'Image non disponible') {
                    return $imageResponse;
                }
            }
    
            $attempt++;
            $eta += 5;
        }
    
        $this->logError("Image non récupérée après $maxAttempts tentatives.");
        return 'Image non disponible';
    }

    private function fetchImage($fetchUrl) {
        $payload = ["key" => __STABLE_DIFFUSION_API_KEY__];
    
        $curl = curl_init($fetchUrl);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
    
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);
    
        $this->logResponse($response);
    
        if ($response === false) {
            $this->logError("Erreur cURL lors du téléchargement de l'image depuis l'URL: $fetchUrl, Erreur: $error");
            return 'Image non disponible';
        }
    
        $responseData = json_decode($response, true);
        if (isset($responseData['output']) && is_array($responseData['output']) && count($responseData['output']) > 0) {
            return $this->downloadImage($responseData['output'][0]);
        }
    
        return 'Image non disponible';
    }
    
    private function regeneratePrompt($originalPrompt) {
    
        $newPromptRequest = "Reformule ce prompt d'une manière différente pour que Stable Diffusion génère une nouvelle image : \"$originalPrompt\" Rappelles-toi que le prompt ne doit pas dépasser 300 caractères.";
    
        $openAIService = OpenAIService::getInstance();
        $newPrompt = $openAIService->generateDescription($newPromptRequest);
    
        if (empty($newPrompt) || $newPrompt === 'Description non disponible') {
            $this->logError("Erreur lors de la régénération du prompt via OpenAI.");
            return $originalPrompt;
        }
    
        return $newPrompt;
    }    
    
    private function logAttempt($message) {
        $logFile = __DIR__ . '/../../../logs/stable_diffusion.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] Tentative: $message\n", FILE_APPEND);
    }
    
    private function logError($message) {
        $logFile = __DIR__ . '/../../../logs/stable_diffusion.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    private function logResponse($response) {
        $logFile = __DIR__ . '/../../../logs/stable_diffusion.log';
        $timestamp = date('Y-m-d H:i:s');
        $formattedResponse = json_encode($response, JSON_PRETTY_PRINT);
        file_put_contents($logFile, "[$timestamp] Réponse API Stable Diffusion : $formattedResponse\n", FILE_APPEND);
    }
}