<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Client\Factory\FictionalClientFactory;
use SocialPost\Client\FictionalClient;
use SocialPost\Client\SocialClientCacheDecorator;
use Tests\Mocks\MockCache;

/**
 * Class ATestTest
 * @covers SocialPost\Client\SocialClientCacheDecorator
 * @package Tests\unit
 */
class SocialClientCacheDecoratorTest extends TestCase
{


    public function sendMockRequest(\GuzzleHttp\Psr7\Request $request) {
        $path = $request->getUri()->getPath();

        if ($path === "/null") {
            return new \GuzzleHttp\Psr7\Response(200, [], null);
        }

        $respData = [1, 2, 3];
        return new \GuzzleHttp\Psr7\Response(200, [], json_encode($respData));

        if (empty($path)) {
            return new \GuzzleHttp\Psr7\Response(200, [], "Request completed successfully.");
        }

        $exceptionData = ["error" => ["message" => "Unexpected Exception"]];

        if ($path === "/invalid-token") {
            $exceptionData = ["error" => ["message" => "Invalid SL Token"]];
        }
        $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($exceptionData));
        throw new ServerException(
            "Unexpected Exception.",
            $request,
            $response,
        );
    }

    public function setUp(): void
    {
        $dotEnv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../../..', '.env.test');
        $dotEnv->load();

        \App\Config\Config::init();

        $mockedGuzzleClient = $this->getMockBuilder(\GuzzleHttp\Client::class)
            ->setConstructorArgs([[
                "base_uri" => "localhost",
            ]])
            ->setMethods(["send", "get", "authRequest"])
            ->getMock();

        $mockedGuzzleClient
            ->method("send")
            ->will(
                $this->returnCallback([$this, "sendMockRequest"])
            );

        $mockedGuzzleClient
            ->method("get")
            ->will(
                $this->returnCallback([$this, "sendMockRequest"])
            );

        $mockedGuzzleClient
            ->method("authRequest")
            ->will(
                $this->returnCallback([$this, "sendMockRequest"])
            );

        $fallbackClient = new FictionalClient(
            $mockedGuzzleClient,
            $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID']
        );

        $this->decorator = new SocialClientCacheDecorator(
            $fallbackClient,
            new MockCache(),
            'fictional'
        );
    }

    /**
     * @test
     */
    public function getPostAuthRequest(): void
    {
        $url = "http://localhost/dummy";
        $parameters = [];

        $result = $this->decorator->get($url, $parameters);
        $this->assertNotNull($result);
        $result = $this->decorator->post($url, $parameters);
        $this->assertNotNull($result);
        $result = $this->decorator->authRequest($url, $parameters);
        $this->assertNotNull($result);

        $url = "http://localhost/null";
        $parameters = [];
        $result = $this->decorator->get($url, $parameters);
        $this->assertNotNull($result);

        $url = "http://localhost/statis";
        $parameters = [];
        $result = $this->decorator->get($url, $parameters);
        $this->assertNotNull($result);
    }
}
