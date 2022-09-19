<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Client\FictionalClient;
use SocialPost\Driver\FictionalDriver;
use SocialPost\Exception\BadResponseException;
use Tests\Mocks\MockCache;
use SocialPost\Exception\InvalidTokenException;

/**
 * Class ATestTest
 * @covers SocialPost\Driver\FictionalDriver
 * @package Tests\unit
 */
class FictionalDriverTest extends TestCase
{
    public function getCallback (string $url, array $parameters) {
        if($parameters["page"] == -1) {
            throw new InvalidTokenException();
        }

        # Check no posts behaviour.
        $page = $parameters["page"] == 0 ? "empty.json" : sprintf("%02d.json", $parameters["page"]);

        $fileName = __DIR__ ."/../../../../../data/pages/" . $page;
        $fileName = realpath($fileName);

        return file_get_contents($fileName);
    }

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

        $client = new FictionalClient($guzzleClient, $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID']);

        $client = $this->getMockBuilder(FictionalClient::class)
            ->setConstructorArgs([$guzzleClient, $_ENV['FICTIONAL_SOCIAL_API_CLIENT_ID']])
            ->setMethods(["get"])
            ->getMock();

        $client
            ->method("get")
            ->will(
                $this->returnCallback([$this, "getCallback"])
            );

        $this->fictionalDriver = new FictionalDriver($client);
    }

    /**
     * @test
     */
    public function checkInstance(): void
    {
        $this->assertInstanceOf(FictionalDriver::class, $this->fictionalDriver);
    }

    /**
     * @test
     */
    public function fetchPostByPage(): void
    {
        $this->fictionalDriver->setCache(new MockCache());
        $posts = $this->fictionalDriver->fetchPostsByPage(1);

        foreach($posts as $post) {
            $this->assertIsArray($post);
        }
    }

    /**
     * @test
     */
    public function fetchPostByPageNoPostsException(): void
    {
        $this->fictionalDriver->setCache(new MockCache());
        $posts = $this->fictionalDriver->fetchPostsByPage(0);

        $this->expectException(BadResponseException::class);
        $this->expectExceptionMessage("No posts returned");
        # I need to start iterating for exception to occur.
        foreach($posts as $post) {

        }
    }

    /**
     * @test
     */
    public function fetchPostByPageInvalidTokenException(): void
    {
        $this->fictionalDriver->setCache(new MockCache());
        $posts = $this->fictionalDriver->fetchPostsByPage(-1);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage("");
        # I need to start iterating for exception to occur.
        foreach($posts as $post) {

        }
    }

    /**
     * @test
     */
    public function fetchPostByPageInvalidTokenExceptionNoCache(): void
    {
        $posts = $this->fictionalDriver->fetchPostsByPage(-1);

        $this->expectException(InvalidTokenException::class);
        $this->expectExceptionMessage("");
        # I need to start iterating for exception to occur.
        foreach($posts as $post) {

        }
    }
}
