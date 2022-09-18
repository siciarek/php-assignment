<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Cache\Factory\CacheFactory;
use Psr\SimpleCache\CacheInterface;


/**
 * Class ATestTest
 * @covers SocialPost\Cache\Factory\CacheFactory
 * @package Tests\unit
 */
class CacheFactoryTest extends TestCase
{
    public function setUp(): void
    {
        $this->cacheFactory = new CacheFactory();
    }

    /**
     * @test
     */
    public function checkInstance(): void
    {
        $this->assertInstanceOf(CacheFactory::class, $this->cacheFactory);
    }

    protected static function getMethod($cls, $method) {
        $class = new \ReflectionClass($cls);
        $method = $class->getMethod($method);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * @test
     */
    public function create(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No cache :(");
        CacheFactory::create();
    }

    /**
     * @test
     */
    public function getClient(): void
    {
        $this->assertInstanceOf(\Memcached::class,
            self::getMethod(CacheFactory::class, "getClient")->invokeArgs(new CacheFactory(), []));
    }
}
