<?php

namespace PhpTui\Tui\Widget\Block;

class Padding
{
    private function __construct(public int $left, public int $right, public int $top, public int $bottom)
    {
    }

    public static function none(): self
    {
        return new self(0, 0, 0, 0);
    }

    public static function fromPrimitives(int $left, int $right, int $top, int $bottom): self
    {
        return new self($left, $right, $top, $bottom);
    }

}
