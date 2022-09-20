<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace App\DataSource\Factory;

use App\Config\DataSourceConfig;
use App\DataSource\Exception\DataBaseClientException;
use App\DataSource\DataBaseMySqlClient;
use App\DataSource\DataBaseClientInterface;


class DataBaseClientFactory
{
    const SUPPORTED_TYPES = [
        "MySQL",
    ];

    /**
     * @param string $dataBaseName
     * @return DataBaseClientInterface
     * @throws DataBaseClientException
     */
    public static function create(string $dataBaseName = "my_database"): DataBaseClientInterface
    {
        if (!array_key_exists(DataSourceConfig::TYPE, self::SUPPORTED_TYPES)) {
            throw new DataBaseClientException(sprintf('Data source type "%s" is not supported.',
                DataSourceConfig::TYPE));
        }

        if (DataSourceConfig::TYPE == "MySQL") {
            return DataBaseMySqlClient::getInstance(
                host: DataSourceConfig::HOST,
                name: $dataBaseName,
                username: DataSourceConfig::USER,
                password: DataSourceConfig::PASS,
            );
        }
    }
}