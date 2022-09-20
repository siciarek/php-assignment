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
    
    

$masterEmail = MasterEmail::email(params: $_REQUEST);
echo "The master email is {$masterEmail}\n";

if ($masterEmail !== MasterEmail::UNKNOWN_EMAIL) {
    $database = new DataBase('localhost', 'root', 'sldjfpoweifns', 'my_database')
    $user = (new UserRepository($database))->fetchOneByEmail($masterEmail);
    
    if (typeof($user) === User::class) {
        echo $user->getUsername() . "\n";
    }
}



