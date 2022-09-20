<?php

namespace App\Repository;

use App\Entity\User;
use App\DataSource\DataBaseClientInterface;

class UserRepository
{
    const TABLE_NAME = "users";

    /**
     * @var DataBaseClientInterface
     */
    private $database;

    function __construct(DataBaseClientInterface $database)
    {
        $this->database = $database;
    }

    function fetchOneByEmail(string $email)
    {
        $query = sprintf("SELECT `id`, `username`, `email` FROM `%s` WHERE `email` = '%s'", self::TABLE_NAME, $email);
        $result = $this->database->getOne(query: $query);

        if (null === $result) {
            return null;
        }

        return new User(
            id: $result["id"],
            email: $result["email"],
            username: $result["username"],
        );
    }
}
