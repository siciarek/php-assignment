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
 * @package Tests\unit
 * @covers App\Config\Config
 * @covers App\Dispatcher\RouteDispatcher
 * @covers Statistics\Calculator\Factory\StatisticsCalculatorFactory
 * @covers Statistics\Calculator\CalculatorComposite
 * @covers Statistics\Calculator\AbstractCalculator
 * @covers Statistics\Calculator\AveragePostLength
 * @covers Statistics\Calculator\MaxPostLength
 * @covers Statistics\Calculator\AveragePostNumberPerUser
 * @covers Statistics\Calculator\TotalPostsPerWeek
 * @covers App\Controller\Controller
 * @covers App\Controller\IndexController
 * @covers App\Controller\StatisticsController
 * @covers App\Controller\ErrorController
 * @covers App\Controller\Factory\StatisticsControllerFactory
 * @covers SocialPost\Client\Factory\FictionalClientFactory
 * @covers SocialPost\Client\FictionalClient
 * @covers SocialPost\Client\SocialClientCacheDecorator
 * @covers SocialPost\Driver\Factory\FictionalDriverFactory
 * @covers SocialPost\Driver\FictionalDriver
 * @covers SocialPost\Hydrator\FictionalPostHydrator
 * @covers SocialPost\Service\SocialPostService
 * @covers SocialPost\Service\Factory\SocialPostServiceFactory
 * @covers SocialPost\Cache\Factory\CacheFactory
 * @covers SocialPost\Dto\FetchParamsTo
 * @covers SocialPost\Dto\SocialPostTo
 */
class RouteDispatcherTest extends TestCase
{
    public static function invalidRequestDataProvider()
    {
        return [
            ["/invalid-request-path", '<p class="lead">Page not found</p>'],
        ];
    }

    public static function basicRequestDataProvider()
    {
        return [
            ["/", '<p class="lead">posts statistics</p>'],
        ];
    }

    public static function avgStatsRequestsDataProvider()
    {
        return [
            [
                "/statistics?month=April, 2022",
                [
                    StatsEnum::AVERAGE_POST_NUMBER_PER_USER => 7.75,
                    StatsEnum::AVERAGE_POST_LENGTH => 387.52,
                    StatsEnum::MAX_POST_LENGTH => 746,
                    StatsEnum::TOTAL_POSTS_PER_WEEK => 5,
                ]
            ],
            ["/statistics?month=May, 2022", [StatsEnum::AVERAGE_POST_NUMBER_PER_USER => 7.85]],
            ["/statistics?month=June, 2022", [StatsEnum::AVERAGE_POST_NUMBER_PER_USER => 7.6]],
            ["/statistics?month=July, 2022", [StatsEnum::AVERAGE_POST_NUMBER_PER_USER => 8.15]],
            ["/statistics?month=August, 2022", [StatsEnum::AVERAGE_POST_NUMBER_PER_USER => 7.9]],
            ["/statistics?month=September, 2022", [StatsEnum::AVERAGE_POST_NUMBER_PER_USER => 4.32]],
        ];
    }

    public function setUp(): void
    {
        $dotEnv = Dotenv::createImmutable(__DIR__ . '/../../../../../..', '.env.test');
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
     * @dataProvider invalidRequestDataProvider
     */
    public function locateControllerInvalidRequest($requestUri, $match): void
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
     * @dataProvider avgStatsRequestsDataProvider
     */
    public function locateControllerAvgStatsRequests($requestUri, $expected): void
    {
        ob_start();
        @RouteDispatcher::dispatch($requestUri);
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertNotNull($actual);
        $actual = json_decode($actual, true);

        $this->assertArrayHasKey("stats", $actual);
        $this->assertArrayHasKey("children", $actual["stats"]);

        foreach ($expected as $statsName => $expectedValue) {

            $result = array_filter(
                $actual["stats"]["children"],
                function ($el) use ($statsName) {
                    return $el["name"] === $statsName;
                }
            );

            $this->assertCount(1, $result);

            $data = array_values($result)[0];

            if ($statsName === StatsEnum::TOTAL_POSTS_PER_WEEK) {
                $this->assertGreaterThanOrEqual(0, count($data["children"]), $statsName);
            }
            else {
                $this->assertGreaterThanOrEqual(0, $data["value"], $statsName);
            }
        }
    }
}
