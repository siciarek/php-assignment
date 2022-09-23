<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Statistics\Calculator\Factory\StatisticsCalculatorFactory;
use Statistics\Calculator\CalculatorComposite;
use Statistics\Dto\ParamsTo;
use Statistics\Enum\StatsEnum;

/**
 * Class StatisticsServiceTest
 * @package Tests\unit
 * @covers Statistics\Calculator\Factory\StatisticsCalculatorFactory
 */
class StatisticsCalculatorFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function create(): void
    {
        $parameters = [
            (new ParamsTo()),
            (new ParamsTo())->setStatName("invalid-stat-name"),
            (new ParamsTo())->setStatName(StatsEnum::AVERAGE_POST_LENGTH),
        ];

        $actual = StatisticsCalculatorFactory::create($parameters);
        $this->assertInstanceOf(CalculatorComposite::class, $actual);
    }
}
