<?php

namespace App\DataSource;


interface DataBaseClientInterface
{
    public static function getInstance(
        string $host,
        string $name,
        string $username,
        string $password
    ): DataBaseClientInterface;

    /**
     * @param string $query
     * @param bool $strict
     * @return array|null
     */
    public function getSingleRecord(string $query, bool $strict = false): ?array;
}
