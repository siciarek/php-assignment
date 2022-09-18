<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Client\FictionalClient;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Client;
use SocialPost\Exception\BadResponseException;
use SocialPost\Exception\InvalidTokenException;

class FictionalClientExposed extends FictionalClient
{
    public function exposedIsTokenInvalid(ServerException $exception)
    {
        return $this->isTokenInvalid($exception);
    }
}

/**
 * Class ATestTest
 * @covers SocialPost\Client\FictionalClient
 * @package Tests\unit
 */
class FictionalClientTest extends TestCase
{
    /**
     * @var FictionalClient
     */
    private FictionalClient $fictionalClient;
    /**
     * @var FictionalClientExposed
     */
    private FictionalClientExposed $fictionalClientExposed;

    public static function getExceptionDataProvider()
    {
        return [
            ["http://localhost", null, "Request completed successfully."],
            ["http://localhost/invalid-token", InvalidTokenException::class, ""],
            ["http://localhost/unexpected-exception", ServerException::class, "Unexpected Exception."],
        ];
    }

    public static function isTokenValidDataProvider()
    {
        return [
            [
                [], false
            ],
            [
                ["error" => ["message" => "Invalid SL Token"]], true
            ],
            [
                ["error" => ["message" => "Lorem ipsum dolor sit amet"]], false
            ],
        ];
    }

    public function sendMockRequest(\GuzzleHttp\Psr7\Request $request) {
        $path = $request->getUri()->getPath();

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
            ->setMethods(["send"])
            ->getMock();

        $mockedGuzzleClient
            ->method("send")
            ->will(
                $this->returnCallback([$this, "sendMockRequest"])
            );

        $this->fictionalClient = new FictionalClient(
            $mockedGuzzleClient,
            $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID']
        );

        $this->fictionalClientExposed = new FictionalClientExposed(
            $mockedGuzzleClient,
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

    /**
     * @test
     * @dataProvider isTokenValidDataProvider
     */
    public function isTokenInvalid(array $exceptionData, bool $expected): void
    {
        $request = new \GuzzleHttp\Psr7\Request("GET", "http://localhost");
        $response = new \GuzzleHttp\Psr7\Response(200, [], json_encode($exceptionData));

        $exception = new ServerException(
            "Unexpected Exception.",
            $request,
            $response,
        );

        $actual = $this->fictionalClientExposed->exposedIsTokenInvalid($exception);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider getExceptionDataProvider
     */
    public function getException($url, $expectedException, $message): void
    {
        if ($expectedException !== null) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($message);
            $this->fictionalClient->get($url, ["page" => 1, "token" => "smslt_a960c7e40fa5bd_761d6fc0ffe"]);
        }
        else {
            $resp = $this->fictionalClient->get($url, ["page" => 1, "token" => "smslt_a960c7e40fa5bd_761d6fc0ffe"]);
            $this->assertEquals($message, $resp);
        }
    }
}
