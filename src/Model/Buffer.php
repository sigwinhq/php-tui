<?php

namespace DTL\PhpTui\Model;

use Countable;
use DTL\PhpTui\Model\Widget\Line;
use OutOfBoundsException;

final class Buffer implements Countable
{
    /**
     * @param Cell[] $content
     */
    private function __construct(
        private Area $area,
        private array $content
    ) {
    }

    public static function empty(Area $area): self
    {
        return self::filled($area, Cell::empty());
    }

    public static function filled(Area $area, Cell $cell): self
    {
        $content = [];
        for ($i = 0; $i < $area->area(); $i++) {
            $content[] = clone $cell;
        }
        return new self($area, $content);
    }

    /**
     * @param string[] $lines
     */
    public static function fromLines(array $lines): self
    {
        $height = count($lines);
        $width = array_reduce(
            $lines,
            fn ($acc, $line) => mb_strlen($line) > $acc ? mb_strlen($line) : $acc,
            0
        );

        $buffer = self::empty(Area::fromPrimitives(0, 0, $width, $height));
        foreach ($lines as $y => $line) {
            $buffer->putString(new Position(0, $y), $line, Style::default());
        }

        return $buffer;
    }

    /**
     * @return Cell[]
     */
    public function content(): array
    {
        return $this->content;
    }

    public function diff(Buffer $buffer): BufferUpdates
    {
        $previous = $this->content();
        $next     = $buffer->content();
        $updates = [];

        for ($i = 0; $i < count($next); $i++) {
            $previousCell = $previous[$i];
            $currentCell = $next[$i];
            if ($previousCell->char !== $currentCell->char) {
                $updates[] = new BufferUpdate(Position::fromIndex($i, $this->area), $currentCell->char);
            }
        }

        return new BufferUpdates($updates);

    }

    public function count(): int
    {
        return count($this->content);
    }

    public function toString(): string
    {
        $string = '';
        foreach ($this->content as $i => $cell) {
            if ($i > 0 && $i % $this->area->width === 0) {
                $string .= "\n";
            }
            $string .= $cell->char;
        }
        return $string;
    }

    public function putString(Position $position, string $line, ?Style $style = null, int $width = PHP_INT_MAX): Position
    {
        $style = $style ?: Style::default();
        try {
            $index = $position->toIndex($this->area);
        } catch (OutOfBoundsException $e) {
            return $position;
        }
        $chars = mb_str_split($line, 1);
        $chars = array_slice(
            $chars,
            0,
            min($width, count($this->content) - 1)
        );
        foreach ($chars as $char) {
            $this->content[$index]->setChar($char);
            $this->content[$index]->setStyle($style);
            $index++;
        }

        return $position->withX($position->x + count($chars));
    }

    public function resize(Area $area): void
    {
        (function () use ($area): void {
            if (count($this->content) > $area->area()) {
                $this->content = array_slice($this->content, 0, $area->area());
                return;
            }
            for ($i = count($this->content); $i < $area->area(); $i++) {
                $this->content[] = Cell::empty();
            }
        })();
        $this->area = $area;
    }
    /**
     * @return string[]
     */
    public function toLines(): array
    {
        $text = $this->toString();
        $lines = explode("\n", $text);

        return $lines;
    }

    public function area(): Area
    {
        return $this->area;
    }

    public function reset(): void
    {
        foreach ($this->content as $cell) {
            $cell->reset();
        }
    }

    public function putLine(Position $position, Line $line, int $width): Position
    {
        $remainingWidth = $width;
        $x = $position->x;
        foreach ($line as $span) {
            if ($remainingWidth === 0) {
                return $position;
            }
            $newPosition = $this->putString(
                $position,
                $span->content,
                $span->style,
                $remainingWidth,
            );
            $w = max(0, $position->x - $newPosition->x);
            $position = $position->withX($newPosition->x);
            $remainingWidth -= max(0, $remainingWidth - $x);
        }

        return $position;
    }

    public function get(Position $position): Cell
    {
        $index = $position->toIndex($this->area);
        return $this->content[$index];

    }
}
