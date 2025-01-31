<?php

namespace PhpTui\Tui\Model;

use PhpTui\Tui\Adapter\Cassowary\CassowaryConstraintSolver;
use PhpTui\Tui\Model\Layout\StaticCachingConstraintSolver;

final class Layout
{
    /**
     * @param Constraint[] $constraints
     */
    private function __construct(
        private ConstraintSolver $solver,
        public Direction $direction,
        public Margin $margin,
        public array $constraints,
        public bool $expandToFill,
    ) {
    }

    public static function default(): self
    {
        return new self(
            new StaticCachingConstraintSolver(new CassowaryConstraintSolver()),
            Direction::Vertical,
            new Margin(0, 0),
            [],
            true
        );
    }

    public function direction(Direction $direction): self
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     * @param Constraint[] $constraints
     */
    public function constraints(array $constraints): self
    {
        $this->constraints = $constraints;
        return $this;
    }

    public function split(Area $target): Areas
    {
        return $this->solver->solve($this, $target, $this->constraints);
    }
}
