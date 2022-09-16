<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Statistics\Extractor\StatisticsToExtractor;
use Statistics\Dto\StatisticsTo;

/**
 * Class ATestTest
 *
 * @package Tests\unit
 */
class StatisticsToExtractorTest extends TestCase
{
    const REQUIRED_KEYS = ["name", "label", "value", "units", "splitPeriod", "children"];

    public function setUp(): void
    {
        $this->srv = new StatisticsToExtractor();
    }

    /**
     * @test
     */
    public function extractBasic(): void
    {
        $stats = new StatisticsTo();
        $labels = [];
        $given = $this->srv->extract($stats, $labels);
        $this->assertNotNull($given);

        foreach ($this::REQUIRED_KEYS as $key) {
            $this->assertArrayHasKey($key, $given);
        }
    }
}
