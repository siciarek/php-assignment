<?php

use App\DataSource\Factory\DataBaseClientFactory;
use App\Repository\UserRepository;
use App\Entity\User;
use App\Helper\MasterEmail;

# Usually the following logic should be moved to Dispatcher than to Controller
# But there is no information about the url and path so I have decided
# to create something simple in front controller.

$masterEmail = MasterEmail::email($_REQUEST);
echo "The master email is $masterEmail\n";

if ($masterEmail !== MasterEmail::UNKNOWN_EMAIL) {
    try {
        $database = DataBaseClientFactory::create();
        $user = (new UserRepository($database))->fetchOneByEmail($masterEmail);

        if ($user instanceof User) {
            echo $user->getUsername() . "\n";
        }
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
}
