<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Mockery as m;
use SocialPost\Client\Factory\FictionalClientFactory;
use SocialPost\Client\FictionalClient;
use SocialPost\Client\SocialClientCacheDecorator;
use Tests\Mocks\MockCache;

/**
 * Class ATestTest
 * @package Tests\unit
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @covers SocialPost\Client\Factory\FictionalClientFactory
 * @covers SocialPost\Client\SocialClientCacheDecorator
 */
class FictionalClientFactoryTest extends TestCase
{
    public function setUp(): void
    {
        $dotEnv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../../../../', '.env.test');
        $dotEnv->load();

        \App\Config\Config::init();
    }

    /**
     * @test
     */
    public function createRegular(): void
    {
        $client = FictionalClientFactory::create();
        $this->assertArrayHasKey(\SocialPost\Client\SocialClientInterface::class, class_implements($client));
        $this->assertInstanceOf(FictionalClient::class, $client);
    }

    /**
     * @test
     */
    public function createWithMockedCache(): void
    {
        m::mock('overload:\SocialPost\Cache\Factory\CacheFactory')
            ->shouldReceive('create')
            ->andReturn(new MockCache());

        $client = FictionalClientFactory::create();
        $this->assertArrayHasKey(\SocialPost\Client\SocialClientInterface::class, class_implements($client));
        $this->assertInstanceOf(SocialClientCacheDecorator::class, $client);
    }
}
