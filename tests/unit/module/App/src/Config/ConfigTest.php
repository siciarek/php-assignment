<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use App\Config\Config;

/**
 * Class ATestTest
 * @covers App\Config\Config
 * @package Tests\unit
 */
class ConfigTest extends TestCase
{
    /**
     * @test
     */
    public function getTest(): void
    {
        $group = "routes";
        $keys = ["/"];
        $expected = 'App\Controller\IndexController@indexAction';

        $data = Config::get($group);
        foreach($keys as $key) {
            $this->assertArrayHasKey($key, $data);
        }

        $actual = $data[$key[0]];
        $this->assertEquals($expected, $actual);

        $group = "controllers";
        $keys = ["invokables", "factories"];

        $data = Config::get($group);
        foreach($keys as $key) {
            $this->assertArrayHasKey($key, $data);
        }
    }
}
