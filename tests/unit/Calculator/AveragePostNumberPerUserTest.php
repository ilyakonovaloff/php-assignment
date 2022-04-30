<?php

declare(strict_types=1);

namespace Tests\unit\Calculator;

use DateInterval;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;
use Statistics\Calculator\AveragePostNumberPerUser;
use Statistics\Dto\ParamsTo;
use Statistics\Dto\StatisticsTo;

class AveragePostNumberPerUserTest extends TestCase
{
    private const AUTHOR_ID  = 'user_1';
    private const AUTHOR_ID2 = 'user_2';
    private const AUTHOR_ID3 = 'user_3';
    private const STAT_NAME  = 'name';

    private SocialPostTo|MockObject $socialPostTo;
    private ParamsTo|MockObject $paramsTo;

    protected function setUp(): void
    {
        $this->socialPostTo = $this->createMock(SocialPostTo::class);
        $this->paramsTo     = $this->createMock(ParamsTo::class);

        parent::setUp();
    }

    public function testAccumulation(): void
    {
        $this->paramsTo
            ->expects($this->exactly(2))
            ->method('getStartDate')
            ->willReturn((new DateTime)->sub(new DateInterval('P1D')));

        $this->paramsTo
            ->expects($this->exactly(2))
            ->method('getEndDate')
            ->willReturn((new DateTime)->add(new DateInterval('P1D')));

        $this->socialPostTo
            ->expects($this->exactly(2))
            ->method('getAuthorId')
            ->willReturn(self::AUTHOR_ID);

        $this->socialPostTo
            ->expects($this->exactly(2))
            ->method('getDate')
            ->willReturn(new DateTime);

        $calculator = new AveragePostNumberPerUser();
        $calculator->setParameters($this->paramsTo);
        $calculator->accumulateData($this->socialPostTo);
    }

    public function testCalculation(): void
    {
        $this->paramsTo
            ->expects($this->once())
            ->method('getStatName')
            ->willReturn(self::STAT_NAME);

        $calculator = new AveragePostNumberPerUser();
        $calculator->setParameters($this->paramsTo);
        $calculator->accumulateData($this->socialPostTo);
        $result = $calculator->calculate();

        $this->assertInstanceOf(StatisticsTo::class, $result);
        $this->assertEquals(self::STAT_NAME, $result->getName());
        $this->assertEquals(1, $result->getValue());
        $this->assertEquals('posts', $result->getUnits());
    }

    public function testCalculationWithoutPosts(): void
    {
        $this->paramsTo
            ->expects($this->once())
            ->method('getStatName')
            ->willReturn(self::STAT_NAME);

        $calculator = new AveragePostNumberPerUser();
        $calculator->setParameters($this->paramsTo);
        $result = $calculator->calculate();

        $this->assertEquals(0, $result->getValue());
    }

    public function testAverageCalculationWithManyPosts(): void
    {
        $this->paramsTo
            ->expects($this->once())
            ->method('getStatName')
            ->willReturn(self::STAT_NAME);

        $calculator = new AveragePostNumberPerUser();
        $calculator->setParameters($this->paramsTo);

        $authors     = [self::AUTHOR_ID, self::AUTHOR_ID2, self::AUTHOR_ID3, self::AUTHOR_ID, self::AUTHOR_ID, self::AUTHOR_ID];
        $socialPosts = [];
        foreach ($authors as $author) {
            $socialPost = $this->createMock(SocialPostTo::class);
            $socialPost
                ->method('getAuthorId')
                ->willReturn($author);
            $socialPosts[] = $socialPost;
        }

        foreach ($socialPosts as $socialPost) {
            $calculator->accumulateData($socialPost);
        }

        $result = $calculator->calculate();

        $this->assertEquals(2, $result->getValue());
    }
}
