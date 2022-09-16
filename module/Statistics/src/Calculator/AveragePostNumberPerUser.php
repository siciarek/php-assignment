<?php

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class TotalPosts
 *
 * @package Statistics\Calculator
 */
class AveragePostNumberPerUser extends AbstractCalculator
{

    protected const UNITS = 'posts';

    /**
     * @var array
     */
    private $totals = [];

    /**
     * @param SocialPostTo $postTo
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $key = $postTo->getAuthorId();
        $this->totals[$key] = ($this->totals[$key] ?? 0) + 1;
    }

    /**
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $value = 0;
        $userCount = count($this->totals);

        if ($userCount > 0) {
            $postsPerUser = array_sum(array_filter(array_values($this->totals)));
            $value = $postsPerUser / $userCount;
        }

        return (new StatisticsTo())->setValue(round($value, 2));
    }
}
