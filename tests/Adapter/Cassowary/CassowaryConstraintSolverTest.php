<?php

namespace PhpTui\Tui\Tests\Adapter\Cassowary;

use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Constraint;
use PhpTui\Tui\Model\Direction;
use PhpTui\Tui\Model\Layout;
use PHPUnit\Framework\TestCase;

class CassowaryConstraintSolverTest extends TestCase
{
    public function testPercentage(): void
    {
        $splits = Layout::default()
            ->direction(Direction::Horizontal)
            ->constraints([
                Constraint::percentage(50),
                Constraint::percentage(50),
            ])
            ->split(Area::fromDimensions(100, 100));

        self::assertEquals([0, 0, 50, 100], $splits->get(0)->toArray());
        self::assertEquals([50, 0, 50, 100], $splits->get(1)->toArray());
    }

    public function testMultiplePercentages(): void
    {
        $splits = Layout::default()
            ->direction(Direction::Horizontal)
            ->constraints([
                Constraint::percentage(50),
                Constraint::percentage(25),
                Constraint::percentage(25),
            ])
            ->split(Area::fromDimensions(100, 100));

        self::assertEquals([0, 0, 50, 100], $splits->get(0)->toArray());
        self::assertEquals([50, 0, 25, 100], $splits->get(1)->toArray());
        self::assertEquals([75, 0, 25, 100], $splits->get(2)->toArray());
    }
}
