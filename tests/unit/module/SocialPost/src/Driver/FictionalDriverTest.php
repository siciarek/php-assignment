<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Client\FictionalClient;
use SocialPost\Driver\FictionalDriver;
use Psr\SimpleCache\CacheInterface;
use SocialPost\Exception\BadResponseException;

class MockCache implements CacheInterface {
    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get(string $key, mixed $default = null): mixed {
        return "smslt_a960c7e40fa5bd_761d6fc0ffe";
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool {
        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete(string $key): bool {
        return true;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool {
        return true;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable<string> $keys    A list of keys that can be obtained in a single operation.
     * @param mixed            $default Default value to return for keys that do not exist.
     *
     * @return iterable<string, mixed> A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable {
        return [];
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool {
        return true;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable<string> $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple(iterable $keys): bool {
        return true;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has(string $key): bool {
        return false;
    }
}


/**
 * Class ATestTest
 * @covers SocialPost\Driver\FictionalDriver
 * @package Tests\unit
 */
class FictionalDriverTest extends TestCase
{
    public function getCallback (string $url, array $parameters) {
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
}
