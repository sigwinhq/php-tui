<?php

namespace PhpTui\Tui\Model\LineComposer;

use PhpTui\Tui\Model\LineComposer;
use PhpTui\Tui\Model\Widget\HorizontalAlignment;
use PhpTui\Tui\Model\Widget\StyledGrapheme;
use Generator;

class LineTruncator implements LineComposer
{
    /**
     * @param list<array{list<StyledGrapheme>,HorizontalAlignment}> $lines
     */
    public function __construct(
        private array $lines,
        private int $maxLineWidth,
        private int $horizontalOffset = 0,
        private int $offset = 0,
    ) {
    }

    public function nextLine(): Generator
    {
        if ($this->maxLineWidth === 0) {
            return;
        }
        $currentLineWidth = 0;
        $horizontalOffset = $this->horizontalOffset;
        $currentLine = [];
        $currentAlignment = HorizontalAlignment::Left;

        $line = $this->lines[$this->offset++] ?? null;
        if (null === $line) {
            return;
        }

        /** @var HorizontalAlignment $alignment */
        [$line, $alignment] = $line;

        $currentAlignment = $alignment;

        /** @var StyledGrapheme $styledGrapheme */
        foreach ($line as $styledGrapheme) {
            // ignore characters wider than the total max width
            if ($styledGrapheme->symbolWidth() > $this->maxLineWidth) {
                continue;
            }

            if ($currentLineWidth + $styledGrapheme->symbolWidth() > $this->maxLineWidth) {
                yield [
                    $currentLine,
                    $currentLineWidth,
                    $currentAlignment
                ];
                $currentLine = [];
                $currentLineWidth = 0;
            }

            $symbol = $this->resolveSymbol(
                $horizontalOffset,
                $alignment,
                $styledGrapheme,
            );

            $currentLine[] = new StyledGrapheme($symbol, $styledGrapheme->style);
            $currentLineWidth += mb_strlen($symbol);
        }

        yield [
            $currentLine,
            $currentLineWidth,
            $currentAlignment
        ];
    }

    private function resolveSymbol(int &$horizontalOffset, HorizontalAlignment $alignment, StyledGrapheme $styledGrapheme): string
    {
        if ($horizontalOffset === 0 || HorizontalAlignment::Left !== $alignment) {
            return $styledGrapheme->symbol;
        }

        if ($styledGrapheme->symbolWidth() > $horizontalOffset) {
            $symbol = self::trimOffset($styledGrapheme->symbol, $horizontalOffset);
            $horizontalOffset = 0;
            return $symbol;
        }
        $horizontalOffset -= $styledGrapheme->symbolWidth();

        return '';
    }

    private static function trimOffset(string $string, int $horizontalOffset): string
    {
        return mb_substr($string, 0, $horizontalOffset);
    }
}
