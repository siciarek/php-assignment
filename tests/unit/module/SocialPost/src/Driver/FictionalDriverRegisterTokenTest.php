<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Client\FictionalClient;
use SocialPost\Driver\FictionalDriver;
use Tests\Mocks\MockCache;

/**
 * Class ATestTest
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @covers SocialPost\Driver\FictionalDriver
 * @package Tests\unit
 */
class FictionalDriverRegisterTokenTest extends TestCase
{
    public function setUp(): void
    {
        $dotEnv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../../..', '.env.test');
        $dotEnv->load();

        \App\Config\Config::init();
    }

    public function authRequestMock(string $url, array $body): string
    {
        $file = realpath(__DIR__ . "/../../../../../data/auth-token-response.json");
        $data = json_decode(file_get_contents($file), true);

        $data["data"]["sl_token"] = null;

        return json_encode($data);
    }

    /**
     * @test
     */
    public function registerTokenWithCache(): void
    {
        $guzzleClient = new \GuzzleHttp\Client(
            [
                'base_uri' => $_ENV['FICTIONAL_SOCIAL_API_HOST'],
            ]
        );

        $client = new FictionalClient($guzzleClient, $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID']);
        $driver = new FictionalDriver($client);
        $driver->setCache(new MockCache());

        $class = new \ReflectionClass(FictionalDriver::class);
        $method = $class->getMethod("registerToken");
        $method->setAccessible(true);

        $token = $method->invokeArgs($driver, []);

        $this->assertNotNull($token);
    }

    /**
     * @test
     */
    public function registerToken(): void
    {
        $guzzleClient = new \GuzzleHttp\Client(
            [
                'base_uri' => $_ENV['FICTIONAL_SOCIAL_API_HOST'],
            ]
        );

        $client = $this->getMockBuilder(FictionalClient::class)
            ->setConstructorArgs([$guzzleClient, $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID']])
            ->setMethods(["authRequest"])
            ->getMock();

        $client
            ->method("authRequest")
            ->will(
                $this->returnCallback([$this, "authRequestMock"])
            );

        $driver = new FictionalDriver($client);

        $class = new \ReflectionClass(FictionalDriver::class);
        $method = $class->getMethod("registerToken");
        $method->setAccessible(true);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("No access token returned");
        $token = $method->invokeArgs($driver, []);
    }
}
