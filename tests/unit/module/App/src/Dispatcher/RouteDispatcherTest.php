<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use App\Dispatcher\RouteDispatcher;
use Dotenv\Dotenv;
use Statistics\Enum\StatsEnum;
use Statistics\Calculator\AveragePostNumberPerUser;

/**
 * Class ATestTest
 * @covers App\Dispatcher\RouteDispatcher
 * @covers Statistics\Calculator\AveragePostNumberPerUser
 * @package Tests\unit
 */
class RouteDispatcherTest extends TestCase
{
    public static function avgStatsRequestsDataProvider() {
        return [
            [ "/statistics?month=April, 2022", StatsEnum::AVERAGE_POST_NUMBER_PER_USER, 7.8 ],
            [ "/statistics?month=May, 2022", StatsEnum::AVERAGE_POST_NUMBER_PER_USER, 7.85 ],
            [ "/statistics?month=June, 2022", StatsEnum::AVERAGE_POST_NUMBER_PER_USER, 7.6 ],
            [ "/statistics?month=July, 2022", StatsEnum::AVERAGE_POST_NUMBER_PER_USER, 8.15 ],
            [ "/statistics?month=August, 2022", StatsEnum::AVERAGE_POST_NUMBER_PER_USER, 7.9 ],
            [ "/statistics?month=September, 2022", StatsEnum::AVERAGE_POST_NUMBER_PER_USER, 4.32 ],
        ];
    }

    public static function basicRequestDataProvider() {
        return [
            [ "/", '<title>Hello, world!</title>' ],
        ];
    }

    public function setUp(): void
    {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/../../../../../..');
        $dotEnv->load();

        \App\Config\Config::init();
    }


    /**
     * @test
     * @dataProvider basicRequestDataProvider
     */
    public function locateControllerBasicRequest($requestUri, $match): void
    {
        ob_start();
        @RouteDispatcher::dispatch($requestUri);
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertNotNull($actual);
        $this->assertStringContainsString($match, $actual);
    }

    /**
     * @test
     * @dataProvider statsRequestsDataProvider
     */
    public function locateControllerAvgStatsRequests($requestUri, $statsName, $avgVal): void
    {
        ob_start();
        @RouteDispatcher::dispatch($requestUri);
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertNotNull($actual);
        $actual = json_decode($actual, true);

        $this->assertArrayHasKey("stats", $actual);
        $this->assertArrayHasKey("children", $actual["stats"]);

        $result = array_filter(
            $actual["stats"]["children"],
            function($el) use ($statsName) {return $el["name"] === $statsName;}
        );

        $this->assertCount(1, $result);

        $this->assertEquals($avgVal, array_values($result)[0]["value"]);
    }
}
