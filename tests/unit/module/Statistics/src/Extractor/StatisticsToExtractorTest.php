<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use Statistics\Extractor\StatisticsToExtractor;
use Statistics\Dto\StatisticsTo;

/**
 * Class ATestTest
 * @covers Statistics\Extractor\StatisticsToExtractor
 * @package Tests\unit
 */
class StatisticsToExtractorTest extends TestCase
{
    const DUMMY_UNIT = "dummy";
    const REQUIRED_KEYS = ["name", "label", "value", "units", "splitPeriod", "children"];

    public static function statisticsToDataProvider()
    {
        $statisticsWithChildren = (new StatisticsTo())
            ->setName("test-statistics-with-children");
        foreach ([1, 2, 3, 4] as $childId) {
            $statisticsWithChildren->addChild(
                (new StatisticsTo())
                    ->setName("Child #{$childId}")
                    ->setValue($childId * 100)
                    ->setUnits(self::DUMMY_UNIT)
            );
        }

        return [
            [new StatisticsTo(),
                [],
                ["name" => null, "label" => null,
                    "value" => null, "units" => null, "splitPeriod" => null, "children" => null]],
            [(new StatisticsTo())->setName("test-statistics")->setValue(1024),
                ["test-statistics" => "test-statistics-label"],
                ["name" => "test-statistics", "label" => "test-statistics-label",
                    "value" => 1024, "units" => null, "splitPeriod" => null, "children" => null]],
            [
                $statisticsWithChildren,
                ["test-statistics-with-children" => "test-statistics-with-children-label"],
                ["name" => "test-statistics-with-children", "label" => "test-statistics-with-children-label",
                    "value" => null, "units" => null, "splitPeriod" => null,
                    "children" => [
                        [
                            "name" => "Child #1", "label" => null,
                            "value" => 100, "units" => self::DUMMY_UNIT, "splitPeriod" => null, "children" => null
                        ],
                        [
                            "name" => "Child #2", "label" => null,
                            "value" => 200, "units" => self::DUMMY_UNIT, "splitPeriod" => null, "children" => null
                        ],
                        [
                            "name" => "Child #3", "label" => null,
                            "value" => 300, "units" => self::DUMMY_UNIT, "splitPeriod" => null, "children" => null
                        ],
                        [
                            "name" => "Child #4", "label" => null,
                            "value" => 400, "units" => self::DUMMY_UNIT, "splitPeriod" => null, "children" => null
                        ],
                    ]
                ]
            ]
        ];
    }

    public function setUp(): void
    {
        $this->srv = new StatisticsToExtractor();
    }

    /**
     * @test
     * @dataProvider statisticsToDataProvider
     */
    public function extractBasic($stats, $labels, $expected): void
    {
        $given = $this->srv->extract($stats, $labels);

        $this->assertIsArray($given);

        foreach ($this::REQUIRED_KEYS as $key) {
            $this->assertArrayHasKey($key, $given);
        }

        if ($expected !== null) {
            foreach ($expected as $key => $val) {
                $this->assertEquals($given[$key], $val);
            }
        }
    }
}
