<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;
use Statistics\Service\StatisticsService;
use Statistics\Service\Factory\StatisticsServiceFactory;
use SocialPost\Dto\SocialPostTo;

/**
 * Class ATestTest
 * @covers Statistics\Service\StatisticsService
 * @covers Statistics\Service\Factory\StatisticsServiceFactory
 * @covers Statistics\Calculator\AveragePostLength
 * @package Tests\unit
 */
class StatisticsServiceTest extends TestCase
{
    /**
     * @test
     */
    public function checkInstance(): void
    {
        $service = StatisticsServiceFactory::create();
        $this->assertInstanceOf(StatisticsService::class, $service);
    }

    /**
     * @test
     */
    public function calculateStats(): void
    {
        $items = [new SocialPostTo()];

        $posts = new \ArrayIterator($items);
        $params = [(new ParamsTo())->setStatName(StatsEnum::AVERAGE_POST_LENGTH)];
        $service = StatisticsServiceFactory::create();
        $service->calculateStats($posts, $params);

        $this->assertInstanceOf(StatisticsService::class, $service);
    }

    /**
     * @test
     */
    public function calculateStatsInvalidPosts(): void
    {
        $posts = new \ArrayIterator([new \stdClass(), new \stdClass(), new \stdClass()]);
        $params = [(new ParamsTo())->setStatName(StatsEnum::AVERAGE_POST_LENGTH)];
        $service = StatisticsServiceFactory::create();
        $service->calculateStats($posts, $params);

        $this->assertInstanceOf(StatisticsService::class, $service);
    }
}
