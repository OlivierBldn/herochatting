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

    public function generateImage($prompt, $attempt = 0) {

        $payload = [
            "key" => __STABLE_DIFFUSION_API_KEY__, 
            "prompt" => $prompt, 
            "negative_prompt" => __STABLE_DIFFUSION_NEGATIVE_PROMPT__, 
            "width" => __STABLE_DIFFUSION_IMAGE_WIDTH__, 
            "height" => __STABLE_DIFFUSION_IMAGE_HEIGHT__, 
            "samples" => __STABLE_DIFFUSION_SAMPLES__, 
            "num_inference_steps" => __STABLE_DIFFUSION_NUM_INFERENCES_STEPS__, 
            "seed" => __STABLE_DIFFUSION_SEED__, 
            "guidance_scale" => __STABLE_DIFFUSION_GUIDANCE_SCALE__, 
            "safety_checker" => __STABLE_DIFFUSION_SAFETY_CHECKER__, 
            "multi_lingual" => __STABLE_DIFFUSION_MULTI_LINGUAL__, 
            "panorama" => __STABLE_DIFFUSION_PANORAMA__, 
            "self_attention" => __STABLE_DIFFUSION_SELF_ATTENTION__, 
            "upscale" => __STABLE_DIFFUSION_UPSCALE__, 
            "embeddings_model" => __STABLE_DIFFUSION_EMBEDDINGS_MODEL__, 
            "webhook" => __STABLE_DIFFUSION_WEBHOOK__, 
            "track_id" => __STABLE_DIFFUSION_TRACK_ID__
        ];

        return $this->sendRequest($payload, $prompt, $attempt);
    }

    private function sendRequest($payload, $originalPrompt, $attempt) {
        $ch = curl_init();
    
        curl_setopt_array($ch, array(
            CURLOPT_URL => __STABLE_DIFFUSION_API_URL__,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => array(
              'Content-Type: application/json'
            ),
        ));
    
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        $this->logResponse($response);
    
        if ($response === false) {
            $this->logError("Erreur cURL lors de la connexion à Stable Diffusion: " . $error);
            return 'Image non disponible #1';
        }
    
        $responseData = json_decode($response, true);
        switch ($responseData['status'] ?? '') {
            case 'success':
                if (isset($responseData['output']) && is_array($responseData['output']) && count($responseData['output']) > 0) {
                    $imageDownloadResponse = $this->downloadImage($responseData['output'][0], $originalPrompt, $attempt);
                    if ($imageDownloadResponse === 'Image non disponible #5' && $attempt < 3) {
                        $newPrompt = $this->regeneratePrompt($originalPrompt);
                        return $this->generateImage($newPrompt, $attempt + 1);
                    }
                    return $imageDownloadResponse;
                } else {
                    return 'Image non disponible #3';
                }
            break;
            case 'processing':
                return $this->handleProcessingImage($responseData, $originalPrompt, $attempt);
            break;
            case 'failed':
                $this->logError("La requête a échoué : " . $responseData['messege']);
                if ($attempt < 3) {
                    $newPrompt = $this->regeneratePrompt($originalPrompt);
                    return $this->generateImage($newPrompt, $attempt + 1);
                } else {
                    return 'Image non disponible après plusieurs tentatives';
                }
            break;
            case 'error':
                $this->logError("Erreur de réponse API: " . $responseData['error']['message']);
                return 'Image non disponible #2';
            break;
            default:
                $this->logError("Réponse API invalide ou image non générée.");
                return 'Image non disponible #4';
            break;
        }
    }

    private function downloadImage($imageUrl, $originalPrompt, $attempt = 0, $maxAttempts = 3) {
        $imageContent = file_get_contents($imageUrl);
        if ($imageContent === false) {
            $this->logError("Erreur lors du téléchargement de l'image depuis l'URL : $imageUrl");
    
            if ($attempt < $maxAttempts) {

                $newPrompt = $this->regeneratePrompt($originalPrompt);
                return $this->generateImage($newPrompt, $attempt + 1);
            } else {
                return 'Image non disponible #5';
            }
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
                $nb = $attempt + 1;
                $this->logAttempt("Tentative de récupération de l'image $nb/$maxAttempts, URL: $fetchUrl");
    
                sleep($eta);
    
                if (in_array($attempt, [2, 4])) {
                    $originalPrompt = $this->regeneratePrompt($originalPrompt);
                }
    
                $imageResponse = $this->fetchImage($fetchUrl, $originalPrompt);
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