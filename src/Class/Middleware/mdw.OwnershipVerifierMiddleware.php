<?php // path: src/Middleware/mdw.OwnershipVerifierMiddleware.php

require_once __DIR__ . '/../Interface/iface.AuthHandlerInterface.php';
require_once __DIR__ . '/../class.JWTFactory.php';
require_once __DIR__ . '/../../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../../Repository/repo.UserRepository.php';

/**
 * OwnershipVerifierMiddleware
 * Class to handle the ownership verification
 * Implements the AuthHandlerInterface
 * Used to check if the user is the owner of the requested resource
 */
class OwnershipVerifierMiddleware implements AuthHandlerInterface
{
    private $nextHandler;
    private $dbConnector;

    public function __construct() {
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    /**
     * Function to set the next handler in the chain
     *
     * @param AuthHandlerInterface $handler
     * @return AuthHandlerInterface
     */
    public function setNext(AuthHandlerInterface $handler): AuthHandlerInterface {
        $this->nextHandler = $handler;
        return $handler;
    }

    /**
     * Function to handle the request submitted to the handler using the JWTFactory
     *
     * @param Request $request
     */
    public function handle($entityId, $entityType = null) {
        $token = JWTFactory::getAuthorizationToken();
        $decodedToken = JWTFactory::validateToken($token);
        if (!$decodedToken) {
            http_response_code(401);
            echo json_encode(['error' => 'Acces non autorise']);
            return null;
        }

        // Use the token payload to get the user ID and the uri to get the entity type and ID
        $userId = $decodedToken->id;

        // Depending on the entity type and id, check if the user is the owner of the requested resource using the corresponding repository
        switch ($entityType) {
            case 'chat':
                $repository = new ChatRepository($this->dbConnector);
                if (!$repository->isUserChatOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acces refuse']);
                    return null;
                }
                break;
            case 'message':
                $repository = new MessageRepository($this->dbConnector);
                if (!$repository->isUserMessageOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acces refuse']);
                    return null;
                }
                break;
            case 'user':
                if ($entityId != $userId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acces refuse']);
                    return null;
                }
                break;
            case 'universe':
                $repository = new UniverseRepository($this->dbConnector);
                if (!$repository->isUserUniverseOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acces refuse']);
                    return null;
                }
                break;
            case 'character':
                $repository = new CharacterRepository($this->dbConnector);
                if (!$repository->isUserCharacterOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Acces refuse']);
                    return null;
                }
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ressource non trouvee']);
                return null;
        }

        return $this->nextHandler ? $this->nextHandler->handle($request) : true;
    }
}
