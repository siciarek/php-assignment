<?php

namespace App\DataSource;


class DataBaseMySqlClient implements DataBaseClientInterface
{
    /**
     * @var DataBaseMySqlClient
     */
    private static array $instances;

    /**
     * @var false|mysqli
     */
    private static $connection;

    protected function __construct()
    {
    }

    public static function getInstance(
        string $host,
        string $name,
        string $username,
        string $password
    ): DataBaseMySqlClient
    {
        $instanceKey = md5([$host, $name, $username, $password]);

        if (!isset(self::$instances[$instanceKey])) {
            self::$instances[$instanceKey] = new static();
            self::$connection = mysqli_connect($host, $username, $password, $name);
        }

        return self::$instances[$instanceKey];
    }

    /**
     * @param string $query
     * @param bool $strict
     * @return array|false|null
     * @throws DataBaseClientException
     */
    public function getSingleRecord(string $query, bool $strict = false): ?array
    {
        $result = mysqli_query($this->connection, real_escape_string($query));
        $rowCount = mysqli_num_rows($result);

        if ($strict && $rowCount !== 1 || $rowCount > 1) {
            throw new DataBaseClientException("Returned number of rows is invalid ({$rowCount}).");
        }

        if ($rowCount === 0) {
            return null;
        }

        return mysqli_fetch_row($result);
    }
}
