<?php // path: src/Class/Service/srv.StableDiffusionService.php

require_once __DIR__ . '/../../../config/cfg_stableDiffusionConfig.php';

class StableDiffusionService extends AbstractRepository
{
    private static ?StableDiffusionService $instance = null;

    private function __construct() {}

    /**
     * Function to get the instance of the StableDiffusionService
     *
     * @return StableDiffusionService
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new StableDiffusionService();
        }
        return self::$instance;
    }

    /**
     * Function to generate an image from a prompt
     *
     * @param string $prompt
     * @param int $attempt
     * @return string
     */
    public function generateImage($prompt, $attempt = 0) {

        // The configuration parameters are defined in the config file
        // You can find more about the Stable Diffusion API and the parameters here: https://stablediffusionapi.com/docs/
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

    /**
     * Function to send the request to the Stable Diffusion API using parameters from the config file
     * You can find more about the Stable Diffusion API and the Curl options here: https://stablediffusionapi.com/docs/
     *
     * @param string $payload
     * @param string $originalPrompt
     * @param int $attempt
     * @return string
     */
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

        // Log the response from the Stable Diffusion API
        $this->logResponse($response);
    
        if ($response === false) {
            $this->logError("Erreur cURL lors de la connexion a Stable Diffusion: " . $error);
            return 'Image non disponible #1';
        }
    
        $responseData = json_decode($response, true);

        // Handle the different statuses returned by the Stable Diffusion API
        switch ($responseData['status'] ?? '') {
            case 'success':

                // If the response contains an output, download the image
                if (isset($responseData['output']) && is_array($responseData['output']) && count($responseData['output']) > 0) {
                    $imageDownloadResponse = $this->downloadImage($responseData['output'][0], $originalPrompt, $attempt);

                    // Regenerate the prompt if the image is not available
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
                $this->logError("La requete a echoue : " . $responseData['messege']);

                // Regenerate the prompt if the attempt failed to increase success rate or return an error message if the maximum number of attempts is reached
                if ($attempt < 3) {
                    $newPrompt = $this->regeneratePrompt($originalPrompt);
                    return $this->generateImage($newPrompt, $attempt + 1);
                } else {
                    return 'Image non disponible apres plusieurs tentatives';
                }
            break;
            case 'error':
                $this->logError("Erreur de reponse API: " . $responseData['error']['message']);
                return 'Image non disponible #2';
            break;
            default:
                $this->logError("Reponse API invalide ou image non generee.");
                return 'Image non disponible #4';
            break;
        }
    }

    /**
     * Function to download the image created using the Stable Diffusion API
     *
     * @param string $imageUrl
     * @param string $originalPrompt is the former prompt used to generate the image, a new prompt is generated if the image is not available
     * @param int $attempt is the number of attempts done to download the image
     * @param int $maxAttempts is the maximum number of attempts to download the image
     * @return string
     */
    private function downloadImage($imageUrl, $originalPrompt, $attempt = 0, $maxAttempts = 3) {

        // Download the image from the Stable Diffusion API
        $imageContent = file_get_contents($imageUrl);
        if ($imageContent === false) {
            $this->logError("Erreur lors du telechargement de l'image depuis l'URL : $imageUrl");
    
            // Regenerate the prompt if the attempt is the second or fourth one to increase success rate
            if ($attempt < $maxAttempts) {

                $newPrompt = $this->regeneratePrompt($originalPrompt);
                return $this->generateImage($newPrompt, $attempt + 1);
            } else {
                return 'Image non disponible #5';
            }
        }
        $uploadPath = __DIR__ . '/../../../uploads/';

        // If the upload directory does not exist or is not writable, log an error and return a message
        if (!file_exists($uploadPath) || !is_writable($uploadPath)) {
            $this->logError("Le repertoire d'upload n'existe pas ou n'est pas accessible en ecriture : $uploadPath");
            return 'Image non disponible #6';
        }
    
        // Generate a unique filename for the image
        $imageFileName = uniqid() . '.png';
        $imageFullPath = $uploadPath . $imageFileName;
    
        // Save the image in the uploads directory
        if (file_put_contents($imageFullPath, $imageContent) === false) {
            $this->logError("Erreur lors de l'enregistrement de l'image : $imageFullPath");
            return 'Image non disponible #7';
        }
    
        return $imageFileName;
    }

    /**
     * Function to handle the images when the Stable Diffusion API returns a "processing" status
     *
     * @param array $responseData
     * @param string $originalPrompt
     * @return string
     */
    private function handleProcessingImage($responseData, $originalPrompt) {

        $fetchUrl = $responseData['fetch_result'] ?? null;
        $eta = $responseData['eta'] ?? 0;
        
        $maxAttempts = 5;
        $attempt = 0;
    
        // Try to fetch the image from the Stable Diffusion API until the maximum number of attempts is reached
        while ($attempt < $maxAttempts) {
            if ($fetchUrl) {
                $nb = $attempt + 1;
                $this->logAttempt("Tentative de recuperation de l'image $nb/$maxAttempts, URL: $fetchUrl");
    
                // Wait for the x amount of time equal to the ETA returned by the Stable Diffusion API
                sleep($eta);
    
                // Regenerate the prompt if the attempt is the second or fourth one to increase success rate
                if (in_array($attempt, [2, 4])) {
                    $originalPrompt = $this->regeneratePrompt($originalPrompt);
                }
    
                // Fetch the image from the Stable Diffusion API
                $imageResponse = $this->fetchImage($fetchUrl, $originalPrompt);
                if ($imageResponse !== 'Image non disponible') {
                    return $imageResponse;
                }
            }
    
            $attempt++;
            $eta += 5;
        }
    
        $this->logError("Image non recuperee apres $maxAttempts tentatives.");
        return 'Image non disponible';
    }
    
    /**
     * Function to fetch the image from the Stable Diffusion API after dealing with the "processing" status
     * You can find more about the Stable Diffusion API and the Curl options here: https://stablediffusionapi.com/docs/
     *
     * @param string $fetchUrl
     * @return string
     */
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
            $this->logError("Erreur cURL lors du telechargement de l'image depuis l'URL: $fetchUrl, Erreur: $error");
            return 'Image non disponible';
        }
    
        $responseData = json_decode($response, true);

        // If the response contains an output, download the image
        if (isset($responseData['output']) && is_array($responseData['output']) && count($responseData['output']) > 0) {
            return $this->downloadImage($responseData['output'][0]);
        }
    
        return 'Image non disponible';
    }

    /**
     * Function to delete an image when deleting an entity if it is not used by any other entity
     * Otherwise, the image is not deleted and only the row concerning the entity is deleted in the image_references table
     *
     * @param string $imageFileName
     * @param int $entityId
     * @param string $entityType
     * @return void or string
     */
    public function deleteImageIfUnused($imageFileName, $entityId, $entityType) {
        switch ($entityType) {
            case 'universe':
                $universeRepository = new UniverseRepository();

                // If the image is used by other universes, do not delete it
                if ($universeRepository->isImageUsedByOthers($imageFileName, $entityId, $entityType)) {
                    $this->logError("L'univers $entityId utilise l'image $imageFileName. L'image ne sera pas supprimee.");
                } else {
                    $this->deleteImage($imageFileName);
                }
            break;
            case 'character':
                $characterRepository = new CharacterRepository();

                // If the image is used by other characters, do not delete it
                if ($characterRepository->isImageUsedByOthers($imageFileName, $entityId, $entityType)) {
                    $this->logError("Le personnage $entityId utilise l'image $imageFileName. L'image ne sera pas supprimee.");
                } else {
                    $this->deleteImage($imageFileName);
                }
                $this->logError("L'utilisateur $entityId utilise l'image $imageFileName. L'image ne sera pas supprimee.");
            break;
            default:
                $this->logError("L'entite $entityId de type $entityType utilise l'image $imageFileName. L'image ne sera pas supprimee.");
            break;
        }
    }

    /**
     * Function to delete an image using its filename
     *
     * @param string $imageFileName
     * @return void
     */
    public function deleteImage($imageFileName) {
        $uploadPath = __DIR__ . '/../../../uploads/';
        $imageFullPath = $uploadPath . $imageFileName;
    
        // If the image exists, delete it
        if (file_exists($imageFullPath)) {
            unlink($imageFullPath);
        }
    }

    /**
     * Function to regenerate a new prompt if the image is not available after several attempts
     * Used to deal with failed requests to the Stable Diffusion API
     *
     * @param string $originalPrompt
     * @return string
     */
    private function regeneratePrompt($originalPrompt) {
    
        $newPromptRequest = "Reformule ce prompt d'une maniere differente pour que Stable Diffusion genere une nouvelle image : \"$originalPrompt\" Rappelles-toi que le prompt ne doit pas depasser 300 caracteres.";
    
        $openAIService = OpenAIService::getInstance();

        // Generate a new prompt using the OpenAI API
        $newPrompt = $openAIService->generateDescription($newPromptRequest);
    
        // If the new prompt is empty or the default prompt, log an error and return the original prompt
        if (empty($newPrompt) || $newPrompt === 'Description non disponible') {
            $this->logError("Erreur lors de la regeneration du prompt via OpenAI.");
            return $originalPrompt;
        }
    
        return $newPrompt;
    }    
    
    /**
     * Function to log an attempt result into the log file
     *
     * @param string $message
     * @return void
     */
    private function logAttempt($message) {
        $logFile = __DIR__ . '/../../../logs/stable_diffusion.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] Tentative: $message\n", FILE_APPEND);
    }
    
    /**
     * Function to log an error into the log file
     *
     * @param string $message
     * @return void
     */
    private function logError($message) {
        $logFile = __DIR__ . '/../../../logs/stable_diffusion.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    /**
     * Function to log the response from the Stable Diffusion API into the log file
     *
     * @param string $response
     * @return void
     */
    private function logResponse($response) {
        $logFile = __DIR__ . '/../../../logs/stable_diffusion.log';
        $timestamp = date('Y-m-d H:i:s');
        $formattedResponse = json_encode($response, JSON_PRETTY_PRINT);
        file_put_contents($logFile, "[$timestamp] Reponse API Stable Diffusion : $formattedResponse\n", FILE_APPEND);
    }
}