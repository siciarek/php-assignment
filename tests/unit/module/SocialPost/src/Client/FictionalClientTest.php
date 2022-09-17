<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Client\FictionalClient;

/**
 * Class ATestTest
 * @covers SocialPost\Client\FictionalClient
 * @package Tests\unit
 */
class FictionalClientTest extends TestCase
{
    public function setUp(): void
    {
        $dotEnv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../../..', '.env.test');
        $dotEnv->load();

        \App\Config\Config::init();

        $guzzleClient = new \GuzzleHttp\Client(
            [
                'base_uri' => $_ENV['FICTIONAL_SOCIAL_API_HOST'],
            ]
        );
        $this->fictionalClient = new FictionalClient(
            $guzzleClient,
            $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID']
        );
    }

    /**
     * @test
     */
    public function checkInstance(): void
    {
        $this->assertInstanceOf(FictionalClient::class, $this->fictionalClient);
    }
}
