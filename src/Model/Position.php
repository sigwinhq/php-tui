<?php declare(strict_types=1);

namespace PhpTui\Tui\Model;

use OutOfBoundsException;
use Stringable;

/**
 * Zero-based co-ordinates for cells.
 * Top-left cell will be 0,0
 */
final class Position implements Stringable
{
    public function __construct(public int $x, public int $y)
    {
    }

    public function __toString(): string
    {
        return sprintf('(%s,%s)', $this->x, $this->y);
    }

    public static function fromIndex(int $i, Area $area): self
    {
        if (
            false === (
                $i < $area->area()
            )
        ) {
            throw new OutOfBoundsException(sprintf(
                'Index %d outside of area %s',
                $i,
                $area->__toString()
            ));
        }
        return new Position(
            $area->position->x + ($i % $area->width),
            $area->position->y + intval($i / $area->width),
        );
    }

    public function toIndex(Area $area): int
    {
        if (
            false === (
                $this->x >= $area->left()
                && $this->x < $area->right()
                && $this->y >= $area->top()
                && $this->y < $area->bottom()
            )
        ) {
            throw new OutOfBoundsException(sprintf(
                'Position %s outside of area %s when trying to get index',
                $this->__toString(),
                $area->__toString()
            ));
        }
        return ($this->y - $area->position->y) * $area->width + ($this->x - $area->position->x);
    }

    public static function at(int $x, int $y): self
    {
        return new self($x, $y);
    }

    public function withX(int $x): self
    {
        return new self($x, $this->y);
    }

    public function withY(int $y): self
    {
        return new self($this->x, $y);
    }
}
