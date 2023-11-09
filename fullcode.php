Tu n'as pas pris en compte le reste de mon code, je vais te le donner :

<?php // path: config/api_config.php

const __WEBSITE_URL__ = 'apidesignpatern';

<?php // path: config/db_config.php

$GLOBALS['dbinfos'] = [
    'database_type' => 'pgsql',
    'mysql' => [
        'host' => 'localhost',
        'dbname' => 'apidesignpatern',
        'username' => 'root',
        'password' => '',
    ],
    'sqlite' => [
        'database_file' => 'apidesignpatern.db',
    ],
    'pgsql' => [
        'host' => 'flora.db.elephantsql.com',
        'port' => '5432',
        'dbname' => 'rekwtfak',
        'username' => 'rekwtfak',
        'password' => 'MKoVH6WVNuv0gJrMomPasBezSOTDeTgb',
    ],
];

return $GLOBALS['dbinfos'];

<?php // path: src/Class/interface/iface.DBConnectorInterface.php

interface DBConnectorInterface
{
    public function select($query, $params = []): array;
    public function execute($query, $params = []): bool;
    public function lastInsertRowID(): int;
}

<?php // path: src/Class/Interface/iface.UniversePrototypeInterface.php

interface UniversePrototype
{
    public function clone(): UniversePrototype;
    public function setName($name);
    public function setImage($image);
    public function setDescription($description);
    public function setUserId($userId);
    public function toMap(): array;
}

<?php // path: src/Class/class.AbstractDatabase.php

abstract class AbstractDatabase implements DBConnectorInterface
{
    protected $connection;
    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function lastInsertRowID(): int
    {
        return $this->connection->lastInsertRowID();
    }
}

<?php // path: src/Class/class.Autoloader.php

class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($className) {
            $directories = [
                __DIR__ . '/',
                __DIR__ . '/../Controller/',
            ];

            $classFile = (strpos($className, 'Controller') !== false ? 'ctrl.' : 'class.') . $className . '.php';

            foreach ($directories as $directory) {
                $file = $directory . $classFile;
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        });
    }
}

Autoloader::register();

<?php // path: src/Class/class.Character.php

class Character
{
    private $id;
    private $name;
    private $description;
    private $image;
    private $universeId;

    public function __construct($id = null, $name = null, $description = null, $image = null, $universeId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->universeId = $universeId;
    }

    // Getter pour l'ID du personnage
    public function getId()
    {
        return $this->id;
    }

    // Setter pour l'ID du personnage
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter pour le nom du personnage
    public function getName()
    {
        return $this->name;
    }

    // Setter pour le nom du personnage
    public function setName($name)
    {
        $this->name = $name;
    }

    // Getter pour la description du personnage
    public function getDescription()
    {
        return $this->description;
    }

    // Setter pour la description du personnage
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // Getter pour l'image du personnage
    public function getImage()
    {
        return $this->image;
    }

    // Setter pour l'image du personnage
    public function setImage($image)
    {
        $this->image = $image;
    }

    // Getter pour l'ID de l'univers associé au personnage (clé étrangère)
    public function getUniverseId()
    {
        return $this->universeId;
    }

    // Setter pour l'ID de l'univers associé au personnage (clé étrangère)
    public function setUniverseId($universeId)
    {
        $this->universeId = $universeId;
    }

    public static function fromMap($map): Character
    {
        $character = new Character();
        $character->setId($map['id'] ?? null);
        $character->setName($map['name'] ?? null);
        $character->setDescription($map['description'] ?? null);
        $character->setImage($map['image'] ?? null);
        $character->setUniverseId($map['id_universe'] ?? null);
        return $character;
    }

    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'id_universe' => $this->universeId
        ];
    }
}

<?php // path: src/Class/class.DatabaseProxy.php

class DatabaseProxy implements DBConnectorInterface
{
    private $realDatabase;

    public function __construct(DBConnectorInterface $realDatabase)
    {
        $this->realDatabase = $realDatabase;
    }

    public function select($query, $params = []): array
    {
        // Redirigez l'appel vers la base de données réelle
        return $this->realDatabase->select($query, $params);
    }

    public function execute($query, $params = []): bool
    {
        // Redirigez l'appel vers la base de données réelle
        return $this->realDatabase->execute($query, $params);
    }

    public function lastInsertRowID(): int
    {
        // Redirigez l'appel vers la base de données réelle
        return $this->realDatabase->lastInsertRowID();
    }
}

<?php // path: src/Class/class.DBConnectorFactory.php

class DBConnectorFactory
{
    public static function getConnector()
    {

        $config = require(__DIR__ . '/../../config/db_config.php');

        $databaseType = $config['database_type'];

        switch ($databaseType) {
            case 'sqlite':
                $realDatabase = new SQLiteDatabase();
                break;
            case 'mysql':
                $realDatabase = new MySQLDatabase();
                break;
            case 'pgsql':
                $realDatabase = new PostgreSQLDatabase();
                break;
            default:
                throw new Exception("Type de base de données non supporté");
        }

        return new DatabaseProxy($realDatabase);
    }
}

<?php // path: src/Class/class.MySQLDatabase.php

// require __DIR__ . '/../../config/db_config.php';
// require __DIR__ . '/iface.dbconnector.php';

// class MySQLDatabase implements DBConnectorInterface
// {
//     private static $instance;
//     private $connection;

//     private function __construct()
//     {
//         global $dbinfos;

//         $mysqlConfig = $dbinfos['mysql'];

//         try {
//             $this->connection = new PDO(
//                 'mysql:host=' . $mysqlConfig['host'] . ';dbname=' . $mysqlConfig['dbname'],
//                 $mysqlConfig['username'],
//                 $mysqlConfig['password']
//             );
//             $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         } catch (PDOException $e) {
//             die("Erreur de connexion à la base de données : " . $e->getMessage());
//         }
//     }

//     public static function getInstance()
//     {
//         if (!self::$instance) {
//             self::$instance = new self();
//         }
//         return self::$instance;
//     }

//     public function getConnection()
//     {
//         return $this->connection;
//     }

//     public function select($query, $params = []): array
//     {
//         try {
//             $stmt = $this->connection->prepare($query);
//             $stmt->execute($params);
//             return $stmt->fetchAll(PDO::FETCH_ASSOC);
//         } catch (PDOException $e) {
//             die("Erreur lors de la sélection dans la base de données MySQL : " . $e->getMessage());
//         }
//     }

//     public function execute($query, $params = []): bool
//     {
//         try {
//             $stmt = $this->connection->prepare($query);
//             return $stmt->execute($params);
//         } catch (PDOException $e) {
//             die("Erreur lors de l'exécution de la requête MySQL : " . $e->getMessage());
//         }
//     }

//     public function lastInsertRowID(): int
//     {
//         return $this->connection->lastInsertId();
//     }
// }


require __DIR__ . '/../../config/db_config.php';
require __DIR__ . '/Interface/iface.DBConnectorInterface.php';

class MySQLDatabase extends AbstractDatabase
{
    public function __construct()
    {
        global $dbinfos;

        $mysqlConfig = $dbinfos['mysql'];

        try {
            $this->connection = new PDO(
                'mysql:host=' . $mysqlConfig['host'] . ';dbname=' . $mysqlConfig['dbname'],
                $mysqlConfig['username'],
                $mysqlConfig['password']
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public function select($query, $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la sélection dans la base de données MySQL : " . $e->getMessage());
        }
    }

    public function execute($query, $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            die("Erreur lors de l'exécution de la requête MySQL : " . $e->getMessage());
        }
    }
}


<?php // path: src/Class/PostgreSQLDatabase.php

require __DIR__ . '/../../config/db_config.php'; // Assurez-vous que ce chemin est correct
require __DIR__ . '/Interface/iface.DBConnectorInterface.php';

class PostgreSQLDatabase extends AbstractDatabase
{
    // public function __construct()
    // {
    //     global $dbinfos;

    //     $pgsqlConfig = $dbinfos['pgsql'];

    //     try {
    //         $this->connection = new PDO(
    //             'pgsql:host=' . $pgsqlConfig['host'] . ';dbname=' . $pgsqlConfig['dbname'],
    //             $pgsqlConfig['username'],
    //             $pgsqlConfig['password']
    //         );
    //         $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //     } catch (PDOException $e) {
    //         die("Erreur de connexion à la base de données : " . $e->getMessage());
    //     }
    // }

    // public function select($query, $params = []): array
    // {
    //     try {
    //         $stmt = $this->connection->prepare($query);
    //         $stmt->execute($params);
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     } catch (PDOException $e) {
    //         die("Erreur lors de la sélection dans la base de données PostgreSQL : " . $e->getMessage());
    //     }
    // }

    // public function execute($query, $params = []): bool
    // {
    //     try {
    //         $stmt = $this->connection->prepare($query);
    //         return $stmt->execute($params);
    //     } catch (PDOException $e) {
    //         die("Erreur lors de l'exécution de la requête PostgreSQL : " . $e->getMessage());
    //     }
    // }

    protected $connection;

    public function __construct()
    {
        global $dbinfos;

        $pgsqlConfig = $dbinfos['pgsql'];

        $connectionString = "host={$pgsqlConfig['host']} port={$pgsqlConfig['port']} dbname={$pgsqlConfig['dbname']} user={$pgsqlConfig['username']} password={$pgsqlConfig['password']}";

        $this->connection = pg_connect($connectionString);

        if (!$this->connection) {
            die("Erreur de connexion à la base de données : " . pg_last_error());
        }
    }

    public function select($query, $params = []): array
    {
        $result = pg_query_params($this->connection, $query, $params);

        if (!$result) {
            die("Erreur lors de la sélection dans la base de données PostgreSQL : " . pg_last_error($this->connection));
        }

        return pg_fetch_all($result);
    }

    public function execute($query, $params = []): bool
    {
        $result = pg_query_params($this->connection, $query, $params);

        if (!$result) {
            die("Erreur lors de l'exécution de la requête PostgreSQL : " . pg_last_error($this->connection));
        }

        return true;
    }

    public function close()
    {
        pg_close($this->connection);
    }
}


<?php // path: src/Class/class.RouteHandler.php

class RouteHandler
{
    // Fonction de routage
    function routeRequest($uri, $routes, $requestMethod)
    {
        // Validation de la méthode de requête HTTP
        if (!in_array($requestMethod, ['GET', 'POST', 'PUT', 'DELETE'])) {
            http_response_code(405); // Méthode non autorisée
            echo json_encode(['message' => 'Méthode de requête non autorisée']);

            return;
        }

        // Parcourir les routes pour trouver une correspondance
        foreach ($routes as $pattern => $route) {

            if (preg_match($pattern, $uri, $matches)) {
                $className = $route['class'];
                $controllerName = $route['controller'];
                $methodName = $route['methods'][$requestMethod];

                if (!class_exists($className)) {
                    // Classe non trouvée
                    http_response_code(404);
                    echo json_encode(['message' => 'Classe non trouvée']);
                    return;
                }
                $controller = new $controllerName();

                if (!method_exists($controller, $methodName)) {
                    // Méthode non trouvée
                    http_response_code(404);
                    echo json_encode(['message' => 'Méthode non trouvée']);
                    return;
                }

                // Récupation les segments de l'URI
                $uriSegments = explode('/', $uri);
                
                // Suppression des segments vides
                $uriSegments = array_filter($uriSegments);

                // Suppression du premier segment (nom de la classe)
                array_shift($uriSegments);

                // L'ID est le dernier segment de l'URI
                $entityId = (int) end($uriSegments);

                // Appel de la méthode du contrôleur avec l'ID
                $controller->$methodName($requestMethod, $entityId);

                return;
            }
        }

        // Si aucune correspondance n'a été trouvée, renvoyer une réponse 404
        http_response_code(404);
        echo json_encode(['message' => 'Route non trouvée']);
    }
}

<?php // path: src/Class/class.SQLiteDatabase.php

// require __DIR__ . '/iface.dbconnector.php';
// require __DIR__ . '/../../config/db_config.php';

// class SQLiteDatabase implements DBConnectorInterface
// {
//     private static $instance;
//     private $connection;

//     private function __construct()
//     {
//         global $dbinfos;

//         $databaseFile = __DIR__ . '/../../database/'.$dbinfos['sqlite']['database_file'];

//         try {
//             $this->connection = new SQLite3($databaseFile);
//         } catch (Exception $e) {
//             die("Erreur de connexion à la base de données : " . $e->getMessage());
//         }
//     }

//     public static function getInstance()
//     {
//         if (!self::$instance) {
//             self::$instance = new self();
//         }
//         return self::$instance;
//     }

//     public function getConnection()
//     {
//         return $this->connection;
//     }

//     public function select($query, $params = []): array
//     {
//         try {
//             $stmt = $this->connection->prepare($query);

//             if ($stmt === false) {
//                 die("Erreur de préparation de la requête SQLite : " . $this->connection->lastErrorMsg());
//             }

//             foreach ($params as $param => $value) {
//                 $stmt->bindValue($param, $value);
//             }

//             $result = $stmt->execute();

//             if ($result === false) {
//                 die("Erreur lors de l'exécution de la requête SQLite : " . $this->connection->lastErrorMsg());
//             }

//             $return = [];

//             while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
//                 $return[] = $row;
//             }

//             return $return;
//         } catch (Exception $e) {
//             die("Erreur lors de la sélection dans la base de données SQLite : " . $e->getMessage());
//         }
//     }

//     public function execute($query, $params = []): bool
//     {
//         try {
//             $stmt = $this->connection->prepare($query);

//             if ($stmt === false) {
//                 die("Erreur de préparation de la requête SQLite : " . $this->connection->lastErrorMsg());
//             }

//             foreach ($params as $param => $value) {
//                 $stmt->bindValue($param, $value);
//             }

//             $result = $stmt->execute();

//             if ($result === false) {
//                 die("Erreur lors de l'exécution de la requête SQLite : " . $this->connection->lastErrorMsg());
//             }

//             return true;
//         } catch (Exception $e) {
//             die("Erreur lors de l'exécution de la requête SQLite : " . $e->getMessage());
//         }
//     }

    
//     public function lastInsertRowID(): int
//     {
//         return $this->connection->lastInsertRowID();
//     }
// }



require __DIR__ . '/../../config/db_config.php';
require __DIR__ . '/Interface/iface.DBConnectorInterface.php';

class SQLiteDatabase extends AbstractDatabase
{
    public function __construct()
    {
        global $dbinfos;

        $databaseFile = __DIR__ . '/../../database/'.$dbinfos['sqlite']['database_file'];

        try {
            $this->connection = new SQLite3($databaseFile);
        } catch (Exception $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    public function select($query, $params = []): array
    {
        try {
            $stmt = $this->connection->prepare($query);

            if ($stmt === false) {
                die("Erreur de préparation de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $result = $stmt->execute();

            if ($result === false) {
                die("Erreur lors de l'exécution de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            $return = [];

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $return[] = $row;
            }

            return $return;
        } catch (Exception $e) {
            die("Erreur lors de la sélection dans la base de données SQLite : " . $e->getMessage());
        }
    }

    public function execute($query, $params = []): bool
    {
        try {
            $stmt = $this->connection->prepare($query);

            if ($stmt === false) {
                die("Erreur de préparation de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            foreach ($params as $param => $value) {
                $stmt->bindValue($param, $value);
            }

            $result = $stmt->execute();

            if ($result === false) {
                die("Erreur lors de l'exécution de la requête SQLite : " . $this->connection->lastErrorMsg());
            }

            return true;
        } catch (Exception $e) {
            die("Erreur lors de l'exécution de la requête SQLite : " . $e->getMessage());
        }
    }
}

<?php // path: src/Class/class.UniversePrototype.php

require __DIR__ . '/Interface/iface.UniversePrototypeInterface.php';

class Universe implements UniversePrototype
{
    private $id;
    private $name;
    private $description;
    private $image;
    private $userId;

    public function __construct($id = null, $name = null, $description = null, $image = null, $userId = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image ?? "placeholder.png";
        $this->userId = $userId;
    }

    public function clone(): UniversePrototype
    {
        $universeClone = new Universe();
        $universeClone->setId($this->id);
        $universeClone->setName($this->name);
        $universeClone->setDescription($this->description);
        $universeClone->setImage($this->image);
        $universeClone->setUserId($this->userId);
        return $universeClone;
    }

    // Getter pour l'ID de l'univers
    public function getId()
    {
        return $this->id;
    }

    // Setter pour l'ID de l'univers
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter pour le nom de l'univers
    public function getName()
    {
        return $this->name;
    }

    // Setter pour le nom de l'univers
    public function setName($name)
    {
        $this->name = $name;
    }

    // Getter pour la description de l'univers
    public function getDescription()
    {
        return $this->description;
    }

    // Setter pour la description de l'univers
    public function setDescription($description)
    {
        $this->description = $description;
    }

    // Getter pour l'image de l'univers
    public function getImage()
    {
        return $this->image;
    }

    // Setter pour l'image de l'univers
    public function setImage($image)
    {
        $this->image = $image;
    }

    // Getter pour l'ID de l'utilisateur associé à l'univers (clé étrangère)
    public function getUserId()
    {
        return $this->userId;
    }

    // Setter pour l'ID de l'utilisateur associé à l'univers (clé étrangère)
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public static function fromMap($map): Universe
    {
        $universe = new Universe();
        $universe->setId($map['id'] ?? null);
        $universe->setName($map['name'] ?? null);
        $universe->setDescription($map['description'] ?? null);
        $universe->setImage($map['image'] ?? null);
        $universe->setUserId($map['id_user'] ?? null);
        return $universe;
    }

    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
            'id_user' => $this->userId
        ];
    }
}

<?php // path: src/Class/class.User.php

class User
{
    private $id;
    private $email;
    private $password;
    private $username;
    private $firstName;
    private $lastName;
    
    public function __construct($id = null, $email = null, $password = null, $username = null, $firstName = null, $lastName = null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    // Getter pour l'ID de l'utilisateur
    public function getId()
    {
        return $this->id;
    }

    // Setter pour l'ID de l'utilisateur
    public function setId($id)
    {
        $this->id = $id;
    }

    // Getter pour l'e-mail de l'utilisateur
    public function getEmail()
    {
        return $this->email;
    }

    // Setter pour l'e-mail de l'utilisateur
    public function setEmail($email)
    {
        $this->email = $email;
    }

    // Getter pour le mot de passe de l'utilisateur
    public function getPassword()
    {
        return $this->password;
    }

    // Setter pour le mot de passe de l'utilisateur
    public function setPassword($password)
    {
        // Hasher le mot de passe avant de le stocker
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->password = $hashedPassword;
    }

    // Getter pour le nom d'utilisateur de l'utilisateur
    public function getUsername()
    {
        return $this->username;
    }

    // Setter pour le nom d'utilisateur de l'utilisateur
    public function setUsername($username)
    {
        $this->username = $username;
    }

    // Getter pour le prénom de l'utilisateur
    public function getFirstName()
    {
        return $this->firstName;
    }

    // Setter pour le prénom de l'utilisateur
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    // Getter pour le nom de famille de l'utilisateur
    public function getLastName()
    {
        return $this->lastName;
    }

    // Setter pour le nom de famille de l'utilisateur
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    public function validatePassword($password)
    {
        // Vérification du mot de passe haché
        return password_verify($password, $this->password);
    }

    public static function fromMap($map): ?User
    {
        if (!$map) {
            return null; // Gestion d'une entrée vide ou invalide
        }
    
        $user = new User();
        $user->setId($map['id'] ?? null);
        $user->setEmail($map['email'] ?? null);
        $user->setPassword($map['password'] ?? null);
        $user->setUsername($map['username'] ?? null);
        $user->setFirstName($map['firstName'] ?? null);
        $user->setLastName($map['lastName'] ?? null);
    
        return $user;
    }

    public function toMap(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName
        ];
    }
}

<?php // path: src/Controller/ctrl.CharacterController.php

require_once __DIR__ . '/../Repository/repo.CharacterRepository.php';

class CharacterController
{
    public function createCharacter($requestMethod, $universeId)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            if (!isset($requestData['name'], $requestData['description'], $requestData['image']) ||
                empty($requestData['name']) || empty($requestData['description']) || empty($requestData['image'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides']);
                return;
            }

            $requestData['universeId'] = $universeId;

            $characterRepository = new CharacterRepository();
            $success = $characterRepository->create($requestData);

            if ($success) {
                $successResponse = [
                    'success' => true,
                    'message' => 'Personnage créé avec succès.'
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la création du personnage");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la création du personnage : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getAllCharacters($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $characterRepository = new CharacterRepository();
            $characters = $characterRepository->getAll();

            if (empty($characters)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun personnage trouvé.',
                ];
            } else {
                $responseData = [];
                foreach ($characters as $character) {
                    $responseData[] = $character->toMap();
                }

                $response = [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des personnages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getAllCharactersByUniverseId($requestMethod, $universeId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $characterRepository = new CharacterRepository();
            $characters = $characterRepository->getAllByUniverseId($universeId);

            if (empty($characters)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun personnage trouvé.',
                ];
            } else {
                $responseData = [];
                foreach ($characters as $character) {
                    $responseData[] = $character->toMap();
                }

                $response = [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des personnages : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getCharacterById($requestMethod, $characterId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $characterId = (int) $characterId;

        try {
            $characterRepository = new CharacterRepository();
            $character = $characterRepository->getById($characterId);

            if ($character !== null) {
                $characterData = $character->toMap();

                http_response_code(200);
                echo json_encode($characterData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération du personnage : ' . $e->getMessage()]);
        }
    }

    public function updateCharacter($requestMethod, $characterId)
    {
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $characterId = (int) $characterId;

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            if ($characterId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'identifiant du personnage est invalide']);
                return;
            }

            if (empty($requestData)) {
                http_response_code(400);
                echo json_encode(['message' => 'Aucune donnée fournie pour la mise à jour']);
                return;
            }

            $characterRepository = new CharacterRepository();
            $success = $characterRepository->update($characterId, $requestData);

            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Personnage mis à jour avec succès']);
            } else {
                throw new Exception("Erreur lors de la mise à jour du personnage");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise à jour du personnage : ' . $e->getMessage()]);
        }
    }

    public function deleteCharacter($requestMethod, $characterId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $characterId = (int) $characterId;

        try {
            $characterRepository = new CharacterRepository();
            $character = $characterRepository->getById($characterId);

            if ($character) {
                $characterRepository->delete($characterId);

                http_response_code(200);
                echo json_encode(['message' => 'Personnage supprimé avec succès']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Personnage non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression du personnage : ' . $e->getMessage()]);
        }
    }
}

<?php // path: src/Controller/ctrl.ErrorHandlerController.php

class CustomErrorHandler
{
    public static function handleException($exception)
    {
        // Log the exception
        error_log("Exception: " . $exception->getMessage());

        // You can customize how you handle different types of exceptions here
        if ($exception instanceof ApiException) {
            // Handle API-specific exceptions
            self::respondWithError($exception->getMessage(), $exception->getCode());
        } elseif ($exception instanceof DatabaseException) {
            // Handle database-related exceptions
            self::respondWithError("Une erreur de base de données s'est produite.", 500);
        } else {
            // Handle other unanticipated exceptions
            self::respondWithError("Une erreur interne s'est produite.", 500);
        }
    }

    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        // Log the error
        error_log("Erreur : [$errno] $errstr dans le fichier $errfile à la ligne $errline");

        // You can customize how you handle different types of errors here
        self::respondWithError("Une erreur s'est produite.", 500);
    }

    public static function respondWithError($message, $statusCode)
    {
        http_response_code($statusCode);
        echo json_encode(array('error' => $message));
        exit();
    }
}

<?php // path: src/Controller/ctrl.UniverseController.php

require_once __DIR__ . '/../Repository/repo.UniverseRepository.php';

class UniverseController
{
    public function createUniverse($requestMethod, $userId)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            // if (!isset($requestData['name'], $requestData['description'], $requestData['image']) ||
            //     empty($requestData['name']) || empty($requestData['description']) || empty($requestData['image'])) {
            //     http_response_code(400);
            //     echo json_encode(['message' => 'Données manquantes ou invalides']);
            //     return;
            // }

            print_r($requestData);

            if (!isset($requestData['id_user']) || empty($requestData['id_user'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Données manquantes ou invalides']);
            return;
            }

            $requestData['userId'] = $userId;


            //--------clone-------
            // $newUniverse = new Universe();
            // $newUniverse->setName($requestData['name']);
            // $newUniverse->setDescription($requestData['description']);
            // $newUniverse->setImage($requestData['image']);
            // $newUniverse->setUserId($requestData['userId']);

            // $universeRepository = new UniverseRepository();
            // $success = $universeRepository->create($newUniverse->toMap());



            //------clone2------

            $universeRepository = new UniverseRepository();

            $existingUniverse = new Universe();
            
            if (!$existingUniverse) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
                return;
            }

            $newUniverse = $existingUniverse->clone();

            if (isset($requestData['name'])) {
                $newUniverse->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $newUniverse->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $newUniverse->setImage($requestData['image']);
            }
            $newUniverse->setUserId($requestData['userId']);

            $success = $universeRepository->create($newUniverse->toMap());

            //-----------------
            
            // $universeRepository = new UniverseRepository();
            // $success = $universeRepository->create($requestData);

            if ($success) {
                $successResponse = [
                    'success' => true,
                    'message' => 'Univers créé avec succès.'
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la création de l'univers");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'univers : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getAllUniversesByUserId($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $requestUri = $_SERVER['REQUEST_URI'];

        $segments = explode('/', $requestUri);

        if(!isset($segments[3])) {
            http_response_code(400);
            echo json_encode(['message' => 'URL malformée']);
            return;
        }

        $userId = (int) $segments[3];

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['message' => 'Utilisateur invalide']);
            return;
        }

        try {
            $universeRepository = new UniverseRepository();
            $universes = $universeRepository->getAllByUserId($userId);

            if (empty($universes)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun univers trouvé.',
                ];
            } else {
                $responseData = [];
                foreach ($universes as $universe) {
                    $responseData[] = $universe->toMap();
                }

                $response = [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des univers : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }


    public function getAllUniverses($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        try {
            $universeRepository = new UniverseRepository();
            $universes = $universeRepository->getAll();

            if (empty($universes)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun univers trouvé.',
                    'data' => []
                ];
            } else {
                $responseData = [];
                foreach ($universes as $universe) {
                    $responseData[] = $universe->toMap();
                }

                $response = [
                    'success' => true,
                    'data' => $responseData
                ];
            }

            header('Content-Type: application/json');
            http_response_code(200);
            echo json_encode($response);
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des univers : ' . $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }

    public function getUniverseById($requestMethod, $universeId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $universeId = (int) $universeId;

        try {
            $universeRepository = new UniverseRepository();
            $universe = $universeRepository->getById($universeId);

            if ($universe !== null) {
                $universeData = $universe->toMap();

                http_response_code(200);
                echo json_encode($universeData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération de l\'univers : ' . $e->getMessage()]);
        }
    }

    public function updateUniverse($requestMethod, $universeId)
    {
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $universeId = (int) $universeId;

        try {
            $requestData = json_decode(file_get_contents('php://input'), true);

            if ($universeId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'identifiant de l\'univers est invalide']);
                return;
            }

            if (empty($requestData)) {
                http_response_code(400);
                echo json_encode(['message' => 'Aucune donnée fournie pour la mise à jour']);
                return;
            }

            $universeRepository = new UniverseRepository();
            // $success = $universeRepository->update($universeId, $requestData);

            $existingUniverse = new Universe();

            if (!$existingUniverse) {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
                return;
            }

            $updatedUniverse = $existingUniverse->clone();

            if (isset($requestData['name'])) {
                $updatedUniverse->setName($requestData['name']);
            }
            if (isset($requestData['description'])) {
                $updatedUniverse->setDescription($requestData['description']);
            }
            if (isset($requestData['image'])) {
                $updatedUniverse->setImage($requestData['image']);
            }
            if (isset($requestData['userId'])) {
                $updatedUniverse->setUserId($requestData['userId']);
            }

            $success = $universeRepository->update($universeId, $updatedUniverse->toMap());

            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Univers mis à jour avec succès']);
            } else {
                throw new Exception("Erreur lors de la mise à jour de l'univers");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise à jour de l\'univers : ' . $e->getMessage()]);
        }
    }

    public function deleteUniverse($requestMethod, $universeId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $universeId = (int) $universeId;

        try {
            $universeRepository = new UniverseRepository();
            $universe = $universeRepository->getById($universeId);

            if ($universe) {
                $universeRepository->delete($universeId);

                http_response_code(200);
                echo json_encode(['message' => 'Univers supprimé avec succès']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Univers non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'univers : ' . $e->getMessage()]);
        }
    }
}


<?php // path: src/Controller/ctrl.UserController.php

require_once __DIR__ . '/../Repository/repo.UserRepository.php';

class UserController
{
    public function createUser($requestMethod, $id)
    {
        if ($requestMethod !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
            if (!isset($requestData['email'], $requestData['password'], $requestData['username'], $requestData['firstName'], $requestData['lastName']) ||
                empty($requestData['email']) || empty($requestData['password']) || empty($requestData['username']) ||
                empty($requestData['firstName']) || empty($requestData['lastName'])) {
                http_response_code(400);
                echo json_encode(['message' => 'Données manquantes ou invalides']);
                return;
            }
    
            $userRepository = new UserRepository();
            $success = $userRepository->create($requestData);
    
            if ($success) {
                $successResponse = [
                    'success' => true,
                    'message' => 'Utilisateur créé avec succès.'
                ];
                http_response_code(201);
                echo json_encode($successResponse);
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'utilisateur : ' . $e->getMessage()
            ];
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }


    public function getAllUsers($requestMethod)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        try {
            $userRepository = new UserRepository();
            $users = $userRepository->getAll();
    
            if (empty($users)) {
                $response = [
                    'success' => true,
                    'message' => 'Aucun utilisateur trouvé.',
                    'data' => []
                ];
            } else {
                $responseData = [];
                foreach ($users as $user) {
                    $responseData[] = $user->toMap();
                }
    
                $response = [
                    'success' => true,
                    'data' => $responseData
                ];
            }
    
            header('Content-Type: application/json');
            http_response_code(200); // OK
            echo json_encode($response);
        } catch (Exception $e) {
            $errorResponse = [
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs : ' . $e->getMessage()
            ];
    
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode($errorResponse);
        }
    }


    public function getUserById($requestMethod, $userId)
    {
        if ($requestMethod !== 'GET') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $userId = (int) $userId;

        try {
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

            if ($user !== null) {
                $userData = $user->toMap();

                http_response_code(200);
                echo json_encode($userData);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la récupération de l\'utilisateur : ' . $e->getMessage()]);
        }
    }

    public function updateUser($requestMethod, $userId)
    {
        if ($requestMethod !== 'PUT') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }
    
        $userId = (int) $userId;
    
        try {
            $requestData = json_decode(file_get_contents('php://input'), true);
    
            if ($userId <= 0) {
                http_response_code(400);
                echo json_encode(['message' => 'L\'identifiant de l\'utilisateur est invalide']);
                return;
            }
    
            if (empty($requestData)) {
                http_response_code(400);
                echo json_encode(['message' => 'Aucune donnée fournie pour la mise à jour']);
                return;
            }
    
            $userRepository = new UserRepository();
            $success = $userRepository->update($userId, $requestData);
    
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Utilisateur mis à jour avec succès']);
            } else {
                throw new Exception("Erreur lors de la mise à jour de l'utilisateur");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la mise à jour de l\'utilisateur : ' . $e->getMessage()]);
        }
    }


    public function deleteUser($requestMethod, $userId)
    {
        if ($requestMethod !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $userId = (int) $userId;

        try {
            $userRepository = new UserRepository();
            $user = $userRepository->getById($userId);

            if ($user) {
                $userRepository->delete($userId);

                http_response_code(200);
                echo json_encode(['message' => 'Utilisateur supprimé avec succès']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Erreur lors de la suppression de l\'utilisateur : ' . $e->getMessage()]);
        }
    }
}


<?php // path: src/Repository/repo.CharacterRepository.php

// require __DIR__ . '/../Class/factory.dbconnector.php';
require __DIR__ . '/../Class/class.DBConnectorFactory.php';

class CharacterRepository
{
    private $dbConnector;
    // private $dbType;

    public function __construct()
    {
        // $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($characterData)
    {
        $newCharacter = Character::fromMap($characterData);

        if ($newCharacter === null) {
            return false;
        }

        $name = $newCharacter->getName();
        $description = $newCharacter->getDescription();
        $image = $newCharacter->getImage();
        $universeId = $newCharacter->getUniverseId();

        $sql = 'INSERT INTO `character` (name, description, image, id_universe) 
                VALUES (:name, :description, :image, :id_universe)';

        $parameters = [
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':id_universe' => $universeId
        ];

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                $id = $this->dbConnector->lastInsertRowID();

                return $id;
            } else {
                throw new Exception("Erreur lors de la création du personnage");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création du personnage : " . $e->getMessage());
        }
    }
    
    public function getAll()
    {
        $sql = 'SELECT * FROM `character`';

        try {
            $allCharactersArraySql = $this->dbConnector->select($sql);

            $allCharactersArrayObject = [];

            foreach ($allCharactersArraySql as $key => $character) {
                $allCharactersArrayObject[] = Character::fromMap($character);
            }

            return $allCharactersArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de tous les personnages : " . $e->getMessage());
        }
    }

    public function getAllByUniverseId($universeId)
    {
        $sql = 'SELECT * FROM `character` WHERE id_universe = :id_universe';
        $params = [':id_universe' => $universeId];

        try {
            $characters = $this->dbConnector->select($sql, $params);
            $characterObjects = [];

            foreach ($characters as $character) {
                $characterObjects[] = Character::fromMap($character);
            }

            return $characterObjects;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des personnages de l'univers : " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        $sql = 'SELECT * FROM `character` WHERE id = :id';
        $params = [':id' => $id];
    
        try {
            $characterMap = $this->dbConnector->select($sql, $params);
    
            if (count($characterMap) === 1) {
                return Character::fromMap($characterMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération du personnage : " . $e->getMessage());
        }
    }

    public function update($characterId, $characterData)
    {
        $existingCharacter = $this->getById($characterId);
    
        if (!$existingCharacter) {
            throw new Exception("Personnage non trouvé");
        }
    
        $name = $characterData['name'] ?? $existingCharacter->getName();
        $description = $characterData['description'] ?? $existingCharacter->getDescription();
        $image = $characterData['image'] ?? $existingCharacter->getImage();
        $universeId = $characterData['universeId'] ?? $existingCharacter->getUniverseId();
    
        $sql = 'UPDATE `character` SET name = :name, description = :description, image = :image, id_universe = :id_universe WHERE id = :characterId';
    
        $parameters = [
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':id_universe' => $universeId,
            ':characterId' => $characterId
        ];
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
    
            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de la mise à jour du personnage");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour du personnage : " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM `character` WHERE id = :id";
        $params = [':id' => $id];

        $success = $this->dbConnector->execute($sql, $params);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression du personnage");
        }

        return $success;
    }
}

<?php // path: src/Repository/repo.UniverseRepository.php

require __DIR__ . '/../Class/class.DBConnectorFactory.php';

class UniverseRepository
{
    private $dbConnector;
    // private $dbType;

    public function __construct()
    {
        // $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($universeData)
    {
        $newUniverse = Universe::fromMap($universeData);

        if ($newUniverse === null) {
            return false;
        }

        $name = $newUniverse->getName();
        $description = $newUniverse->getDescription();
        $image = $newUniverse->getImage();
        $userId = $newUniverse->getUserId();

        $sql = 'INSERT INTO universe (name, description, image, id_user) 
                VALUES (:name, :description, :image, :id_user)';

        $parameters = [
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':id_user' => $userId
        ];

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                $id = $this->dbConnector->lastInsertRowID();

                return $id;
            } else {
                throw new Exception("Erreur lors de la création de l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de l'univers : " . $e->getMessage());
        }
    }

    public function getAll()
    {
        $sql = 'SELECT * FROM universe';

        try {
            $allUniversesArraySql = $this->dbConnector->select($sql);

            $allUniversesArrayObject = [];

            foreach ($allUniversesArraySql as $key => $universe) {
                $allUniversesArrayObject[] = Universe::fromMap($universe);
            }

            return $allUniversesArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de tous les univers : " . $e->getMessage());
        }
    }

    public function getAllByUserId($userId)
    {
        $sql = 'SELECT * FROM universe WHERE id_user = :userId';
        $params = [':userId' => $userId];

        try {
            $universes = $this->dbConnector->select($sql, $params);
            $universeObjects = [];

            foreach ($universes as $universe) {
                $universeObjects[] = Universe::fromMap($universe);
            }

            return $universeObjects;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération des univers de l'utilisateur : " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        $sql = 'SELECT * FROM universe WHERE id = :id';
        $params = [':id' => $id];
    
        try {
            $universeMap = $this->dbConnector->select($sql, $params);
    
            if (count($universeMap) === 1) {
                return Universe::fromMap($universeMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'univers : " . $e->getMessage());
        }
    }

    public function update($universeId, $universeData)
    {
        $existingUniverse = $this->getById($universeId);
    
        if (!$existingUniverse) {
            throw new Exception("Univers non trouvé");
        }
    
        $name = $universeData['name'] ?? $existingUniverse->getName();
        $description = $universeData['description'] ?? $existingUniverse->getDescription();
        $image = $universeData['image'] ?? $existingUniverse->getImage();
        $userId = $universeData['userId'] ?? $existingUniverse->getUserId();
    
        $sql = 'UPDATE universe SET name = :name, description = :description, image = :image WHERE id = :universeId';
    
        $parameters = [
            ':name' => $name,
            ':description' => $description,
            ':image' => $image,
            ':universeId' => $universeId
        ];
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
    
            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de la mise à jour de l'univers");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de l'univers : " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM universe WHERE id = :id";
        $params = [':id' => $id];

        $success = $this->dbConnector->execute($sql, $params);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression de l'univers");
        }

        return $success;
    }
}


<?php // path: src/Repository/repo.UserRepository.php

require __DIR__ . '/../Class/class.DBConnectorFactory.php';

class UserRepository
{
    private $dbConnector;
    // private $dbType;

    public function __construct()
    {
        // $this->dbType = $GLOBALS['dbinfos']['database_type'];
        
        $this->dbConnector = DBConnectorFactory::getConnector();
    }

    public function create($userData)
    {
        $newUser = User::fromMap($userData);

        if ($newUser === null) {
            return false;
        }

        $email = $newUser->getEmail();
        $password = $newUser->getPassword();
        $username = $newUser->getUsername();
        $firstName = $newUser->getFirstName();
        $lastName = $newUser->getLastName();

        $sql = 'INSERT INTO user (email, password, username, firstName, lastName) 
                VALUES (:email, :password, :username, :firstName, :lastName)';

        $parameters = [
            ':email' => $email,
            ':password' => $password,
            ':username' => $username,
            ':firstName' => $firstName,
            ':lastName' => $lastName
        ];

        try {
            $success = $this->dbConnector->execute($sql, $parameters);

            if ($success) {
                $id = $this->dbConnector->lastInsertRowID();

                return $id;
            } else {
                throw new Exception("Erreur lors de la création de l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la création de l'utilisateur : " . $e->getMessage());
        }
    }

    public function getById($id)
    {
        $sql = 'SELECT * FROM user WHERE id = :id';
        $params = [':id' => $id];
    
        try {
            $userMap = $this->dbConnector->select($sql, $params);
    
            if (count($userMap) === 1) {
                return User::fromMap($userMap[0]);
            } else {
                return null;
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
        }
    }

    public function update($userId, $userData)
    {
        $existingUser = $this->getById($userId);
    
        if (!$existingUser) {
            throw new Exception("Utilisateur non trouvé");
        }
    
        $email = $userData['email'] ?? $existingUser->getEmail();
        $username = $userData['username'] ?? $existingUser->getUsername();
        $firstName = $userData['firstName'] ?? $existingUser->getFirstName();
        $lastName = $userData['lastName'] ?? $existingUser->getLastName();
        $password = $userData['password'] ?? $existingUser->getPassword();
    
        $sql = 'UPDATE user SET email = :email, username = :username, firstName = :firstName, lastName = :lastName, password = :password WHERE id = :userId';
    
        $parameters = [
            ':email' => $email,
            ':username' => $username,
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':userId' => $userId,
            ':password' => $password
        ];
    
        try {
            $success = $this->dbConnector->execute($sql, $parameters);
    
            if ($success) {
                return true;
            } else {
                throw new Exception("Erreur lors de la mise à jour de l'utilisateur");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la mise à jour de l'utilisateur : " . $e->getMessage());
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM user WHERE id = :id";
        $params = [':id' => $id];

        $success = $this->dbConnector->execute($sql, $params);

        if (!$success) {
            throw new Exception("Erreur lors de la suppression de l'utilisateur");
        }

        return $success;
    }

    public function getAll()
    {
        $sql = 'SELECT * FROM user';

        try {
            $allUsersArraySql = $this->dbConnector->select($sql);

            $allUsersArrayObject = [];

            foreach ($allUsersArraySql as $key => $user) {
                $allUsersArrayObject[] = User::fromMap($user);
            }

            return $allUsersArrayObject;
        } catch (Exception $e) {
            throw new Exception("Erreur lors de la récupération de tous les utilisateurs : " . $e->getMessage());
        }
    }
}

<?php // path: index.php

// Appel du fichier de l'autoloader
require __DIR__ . '/src/Class/class.Autoloader.php';
require __DIR__ . '/config/api_config.php';
require __DIR__ . '/src/Class/class.RouteHandler.php';
$userRoutes = require __DIR__ . '/config/routes/user-routes.php';
$universeRoutes = require __DIR__ . '/config/routes/universe-routes.php';
$characterRoutes = require __DIR__ . '/config/routes/character-routes.php';

// Enregistrement de l'autoloader
Autoloader::register();

// Récupérer la méthode de requête et l'URI de la demande
$requestMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$basePath = '/'.__WEBSITE_URL__;
$uri = str_replace($basePath, '', $uri);

// Chargement de configuration de routage
$routes = array_merge($userRoutes, $universeRoutes, $characterRoutes);

// Création d'une instance de RouterController
$routeHandler = new RouteHandler();

// Appel de la fonction de routage
$routeHandler->routeRequest($uri, $routes, $requestMethod);