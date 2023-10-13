<?php

namespace DTL\PhpTui\Adapter\Cassowary;

use DTL\Cassowary\Expression;
use DTL\Cassowary\Variable;

final class Element
{
    public function __construct(public Variable $start, public Variable $end)
    {
    }

    public static function empty(): self
    {
        return new self(
            Variable::new(),
            Variable::new(),
        );
    }

    public function start(): Variable
    {
        return $this->start;
    }

    public function end(): Variable
    {
        return $this->end;
    }

    public function size(): Expression
    {
        return $this->end->sub($this->start);
    }
}
