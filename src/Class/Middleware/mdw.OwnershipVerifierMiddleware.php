<?php // path: src/Middleware/mdw.OwnershipVerifierMiddleware.php

require_once __DIR__ . '/../Interface/iface.AuthHandlerInterface.php';
require_once __DIR__ . '/../class.JWTFactory.php';
require_once __DIR__ . '/../../Repository/repo.ChatRepository.php';
require_once __DIR__ . '/../../Repository/repo.MessageRepository.php';
require_once __DIR__ . '/../../Repository/repo.UniverseRepository.php';
require_once __DIR__ . '/../../Repository/repo.CharacterRepository.php';
require_once __DIR__ . '/../../Repository/repo.UserRepository.php';


class OwnershipVerifierMiddleware implements AuthHandlerInterface
{
    private $nextHandler;
    private $dbConnector;

    public function __construct() {
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function setNext(AuthHandlerInterface $handler): AuthHandlerInterface {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle($request) {
        $token = JWTFactory::getAuthorizationToken();
        $decodedToken = JWTFactory::validateToken($token);
        if (!$decodedToken) {
            http_response_code(401);
            echo json_encode(['error' => 'Accès non autorisé']);
            return null;
        }

        $userId = $decodedToken->id;

        $requestUri = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestUri);

        $entityType = $segments[2];
        $entityId = $segments[3];

        switch ($entityType) {
            case 'chats':
                $repository = new ChatRepository($this->dbConnector);
                if (!$repository->isUserChatOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé']);
                    return null;
                }
                break;
            case 'messages':
                $repository = new MessageRepository($this->dbConnector);
                if (!$repository->isUserMessageOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé']);
                    return null;
                }
                break;
            case 'users':
                if ($entityId != $userId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé']);
                    return null;
                }
                break;
            case 'universes':
                $repository = new UniverseRepository($this->dbConnector);
                if (!$repository->isUserUniverseOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé']);
                    return null;
                }
                break;
            case 'characters':
                $repository = new CharacterRepository($this->dbConnector);
                if (!$repository->isUserCharacterOwner($entityId, $userId)) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé']);
                    return null;
                }
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ressource non trouvée']);
                return null;
        }

        return $this->nextHandler ? $this->nextHandler->handle($request) : true;
    }
}
