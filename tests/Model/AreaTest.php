<?php

namespace PhpTui\Tui\Tests\Model;

use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Margin;
use PHPUnit\Framework\TestCase;

class AreaTest extends TestCase
{
    public function testInnerEmpty(): void
    {
        $a = Area::empty();
        self::assertEquals(Area::empty(), $a->inner(new Margin(10, 10)));
    }
    public function testInner(): void
    {
        $a = Area::fromDimensions(10, 10);
        self::assertEquals(Area::fromPrimitives(2, 2, 6, 6), $a->inner(new Margin(2, 2)));
    }
}
