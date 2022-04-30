<?php

declare(strict_types=1);

namespace Statistics\Calculator;

use SocialPost\Dto\SocialPostTo;
use Statistics\Dto\StatisticsTo;

/**
 * Class Calculator
 *
 * @package Statistics\Calculator
 */
class AveragePostNumberPerUser extends AbstractCalculator
{

    protected const UNITS = 'posts';

    /**
     * @var string[]
     */
    private $uniqueUsers = [];

    /**
     * @var int
     */
    private $postCount = 0;

    /**
     * @param SocialPostTo $postTo
     */
    protected function doAccumulate(SocialPostTo $postTo): void
    {
        $this->postCount++;
        if (!in_array($postTo->getAuthorId(), $this->uniqueUsers)) {
            $this->uniqueUsers[] = $postTo->getAuthorId();
        }
    }

    /**
     * @return StatisticsTo
     */
    protected function doCalculate(): StatisticsTo
    {
        $value = $this->postCount > 0
            ? $this->postCount / count($this->uniqueUsers)
            : 0;

        return (new StatisticsTo())->setValue(round($value,2));
    }
}
