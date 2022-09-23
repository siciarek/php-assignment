<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Statistics\Calculator\NoopCalculator;
use Statistics\Dto\StatisticsTo;

class ExposedNoopCalculator extends NoopCalculator
{
    public function exposedDoCalculate(): StatisticsTo
    {
        return $this->doCalculate();
    }
}

/**
 * Class StatisticsServiceTest
 * @package Tests\unit
 * @covers Statistics\Calculator\NoopCalculator
 */
class NoopCalcolatorTest extends TestCase
{
    /**
     * @test
     */
    public function doCalclulate(): void
    {
        $this->assertInstanceOf(StatisticsTo::class, (new ExposedNoopCalculator())->exposedDoCalculate());
    }
}
