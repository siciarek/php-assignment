<?php

declare(strict_types=1);

namespace Tests\unit;

use App\Controller\ErrorController;
use App\Controller\Factory\StatisticsControllerFactory;
use App\Controller\IndexController;
use App\Controller\StatisticsController;
use PHPUnit\Framework\TestCase;
use App\Dispatcher\RouteDispatcher;
use Dotenv\Dotenv;
use Statistics\Enum\StatsEnum;
use Mockery as m;

/**
 * Class ATestTest
 * @package Tests\unit
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @covers App\Config\Config
 * @covers App\Dispatcher\RouteDispatcher
 * @covers App\Controller\Controller
 * @covers App\Controller\IndexController
 * @covers App\Controller\StatisticsController
 * @covers App\Controller\ErrorController
 * @covers App\Controller\Factory\StatisticsControllerFactory
 * @covers Statistics\Calculator\Factory\StatisticsCalculatorFactory
 * @covers Statistics\Calculator\CalculatorComposite
 * @covers Statistics\Calculator\AbstractCalculator
 * @covers Statistics\Calculator\AveragePostLength
 * @covers Statistics\Calculator\MaxPostLength
 * @covers Statistics\Calculator\AveragePostNumberPerUser
 * @covers Statistics\Calculator\TotalPostsPerWeek
 * @covers Statistics\Service\Factory\StatisticsServiceFactory
 * @covers Statistics\Service\StatisticsService
 * @covers Statistics\Dto\ParamsTo
 * @covers Statistics\Dto\StatisticsTo
 * @covers Statistics\Builder\ParamsBuilder
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
    public static function exceptionsDataProvider()
    {
        return [
            [
                [
                    StatisticsController::class => StatisticsControllerFactory::class,
                    "App\Controller\YetAnotherController" => \stdClass::class
                ],
                "Wrong factory registered for App\Controller\YetAnotherController",
            ],
            [
                [
                    StatisticsController::class => StatisticsControllerFactory::class,
                ],
                "Unable instantiate controller App\Controller\YetAnotherController",

            ],
        ];
    }

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
    }


    /**
     * @test
     * @dataProvider basicRequestDataProvider
     */
    public function locateControllerBasicRequest($requestUri, $match): void
    {
        \App\Config\Config::init();

        ob_start();
        @RouteDispatcher::dispatch($requestUri);
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertNotNull($actual);
        $this->assertStringContainsString($match, $actual);
    }

    /**
     * @test
     * @dataProvider exceptionsDataProvider
     */
    public function locateControllerExceptions($factories, $exceptionMessage): void
    {
        $mockConfiguration = [
            "routes" => [
                '/' => "App\Controller\IndexController@indexAction",
                '/404' => "App\Controller\ErrorController@notFoundAction",
                '/statistics' => "App\Controller\StatisticsController@indexAction",
                '/yet-another-route' => "App\Controller\YetAnotherController@indexAction",
            ],
            'controllers' => [
                'invokables' => [
                    IndexController::class,
                    ErrorController::class,
                ],
                'factories' => $factories,
            ],
        ];

        $conf = m::mock('overload:\App\Config\Config')->makePartial();

        $conf->shouldReceive('get')
            ->andReturnUsing(function ($key) use ($mockConfiguration) {
                return $mockConfiguration[$key];
            });

        $conf->shouldReceive('init')->andReturnSelf();

        \App\Config\Config::init();

        $requestUri = "/yet-another-route?month=April, 2022";
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($exceptionMessage);
        @RouteDispatcher::dispatch($requestUri);
    }

    /**
     * @test
     * @dataProvider invalidRequestDataProvider
     */
    public function locateControllerInvalidRequest($requestUri, $match): void
    {
        \App\Config\Config::init();

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
        \App\Config\Config::init();

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
            } else {
                $this->assertGreaterThanOrEqual(0, $data["value"], $statsName);
            }
        }
    }
}
