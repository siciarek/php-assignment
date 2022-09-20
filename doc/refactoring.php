<?php
    if ($_REQUEST['email']) {
        $masterEmail = $_REQUEST['email'];
    }
    $masterEmail = isset($masterEmail) && $masterEmail
        ? $masterEmail
        : array_key_exists('masterEmail', $_REQUEST) && $_REQUEST["masterEmail"]
        ? $_REQUEST['masterEmail'] : 'unknown';

    echo 'The master email is ' . $masterEmail . '\n';
    $conn = mysqli_connect('localhost', 'root', 'sldjfpoweifns', 'my_database');
    $res = mysqli_query($conn, "SELECT * FROM users WHERE email='" .
    $masterEmail . "'");
    $row = mysqli_fetch_row($res);
    echo $row['username'] . "\n";
    
    

$masterEmail = MasterEmail::email();
echo "The master email is {$masterEmail}\n";

if ($masterEmail !== MasterEmail::UNKNOWN_EMAIL) {
    $database = new DataBase('localhost', 'root', 'sldjfpoweifns', 'my_database')
    $user = (new UserRepository($database))->fetchOneByEmail($masterEmail);
    
    if (typeof($user) === User::class) {
        echo $user->getUsername() . "\n";
    }
}


class MasterEmail {
    const UNKNOWN_EMAIL = "unknown";

    public static function email() 
    {
        foreach(["email", "masterEmail"] as $key) {
            $email = Request::get($key);
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }        
        }
        return self::UNKNOWN_EMAIL;
    }
}

class Request {
    public static function get($key) {
        if (array_key_exists($key, $_REQUEST)) {
            return $_REQUEST[$key];
        }
        return null;
    }
}

class UserRepository {
    const TABLE_NAME = "users";
    
    /**
     * @var DataBase
     */
    private $database;
     
    function __construct(DataBase $database) {
        $this->database = $database;    
    }
    
    function fetchOneByEmail(string $email) {
        $email = real_escape_string($email);
        $query = sprintf("SELECT `id`, `username`, `email` FROM `%s` WHERE `email` = '%s'", self::TABLE_NAME, $email);
        $result = $this->database->getOne($query);
        
        if (empty($result)) {
            return null;
        }
        
        return new User($result["id"], $result["email"], $result["username"])
    }
}

class User {
    private $id;
    private $email;
    private $username;
    
    function __construct(int $id, string $email, string $username) {
        $this->id = $id;
        $this->email = $email;
        $this->username = $username;
    }
    # TODO: add setters and getters
}

# TODO: use as singletone
class DataBase {
    private $connection;
    
    function __construct(string $host, string $name, string $username, string $password) {
        $this->connection = mysqli_connect($host, $username, $password, $name);    
    }
    
    function getOne(string $query) {
        $res = mysqli_query($this->connection, real_escape_string($_POST['firstname']));
        $row = mysqli_fetch_row($res);
    }
}