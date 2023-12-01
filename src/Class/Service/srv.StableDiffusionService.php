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

        return $this->sendRequest($request);
    }

    private function sendRequest($request) {
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

        if (isset($responseData['status']) && $responseData['status'] === 'processing') {
            return $this->handleProcessingImage($responseData);
        }

        $responseData = json_decode($response, true);
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

    private function handleProcessingImage($responseData) {
        $fetchUrl = $responseData['fetch_result'] ?? null;
        $eta = $responseData['eta'] ?? 0;
    
        if (!$fetchUrl) {
            $this->logError("URL de récupération non disponible pour l'image en cours de traitement.");
            return 'Image non disponible #8';
        }
    
        sleep($eta);
    
        $ch = curl_init($fetchUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . __STABLE_DIFFUSION_API_KEY__
        ]);
    
        $imageResponse = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
    
        if ($imageResponse === false || $error) {
            $this->logError("Erreur lors du téléchargement de l'image depuis l'URL : $error");
            return 'Image non disponible #9';
        }
    
        $imageData = json_decode($imageResponse, true);
        if (isset($imageData['output']) && is_array($imageData['output']) && count($imageData['output']) > 0) {
            return $this->downloadImage($imageData['output'][0]);
        }
    
        return 'Image non disponible #10';
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