<?php

namespace PhpTui\Tui\Widget;

use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Buffer;
use PhpTui\Tui\Model\Position;
use PhpTui\Tui\Model\Style;
use PhpTui\Tui\Model\Widget;
use PhpTui\Tui\Model\Widget\BorderType;
use PhpTui\Tui\Model\Widget\Borders;
use PhpTui\Tui\Model\Widget\HorizontalAlignment;
use PhpTui\Tui\Model\Widget\Title;
use PhpTui\Tui\Model\Widget\VerticalAlignment;
use PhpTui\Tui\Widget\Block\Padding;

/**
 * The block widget is a container for other widgets and can provide a border,
 * title and padding.
 */
final class Block implements Widget
{
    /**
     * @param int-mask-of<Borders::*> $borders
     * @param Title[] $titles
     */
    public function __construct(
        /**
         * Bit mask which determines the border configuration, e.g. Borders::ALL
         */
        private int $borders,
        /**
         * Titles for the block. You can have multiple titles and each title can
         * be positioned in a different place.
         */
        private array $titles,
        /**
         * Type of border, e.g. `BorderType::Rounded`
         */
        private BorderType $borderType,
        /**
         * Style of the border.
         */
        private Style $borderStyle,
        /**
         * Style of the block's inner area.
         */
        private Style $style,
        /**
         * Style of the titles.
         */
        private Style $titleStyle,
        /**
         * Padding to apply to the inner widget.
         */
        private Padding $padding,
        /**
         * The inner widget.
         */
        private ?Widget $widget,
    ) {
    }

    public static function default(): self
    {
        return new self(
            Borders::NONE,
            [],
            BorderType::Plain,
            Style::default(),
            Style::default(),
            Style::default(),
            Padding::none(),
            null,
        );
    }

    public function widget(Widget $widget): self
    {
        $this->widget = $widget;
        return $this;
    }

    /**
     * @param int-mask-of<Borders::*> $flag
     */
    public function borders(int $flag): self
    {
        $this->borders = $flag;
        return $this;
    }

    public function titles(Title ...$titles): self
    {
        $this->titles = $titles;
        return $this;
    }

    public function render(Area $area, Buffer $buffer): void
    {
        if ($area->area() === 0) {
            return;
        }
        $this->renderBorders($area, $buffer);
        $this->renderTitles($area, $buffer);
    }

    public function borderType(BorderType $borderType): self
    {
        $this->borderType = $borderType;
        return $this;
    }

    public function style(Style $style): self
    {
        $this->style = $style;
        return $this;
    }

    public function borderStyle(Style $style): self
    {
        $this->borderStyle = $style;
        return $this;
    }

    public function titleStyle(Style $style): self
    {
        $this->titleStyle = $style;
        return $this;
    }

    public function padding(Padding $padding): self
    {
        $this->padding = $padding;
        return $this;
    }

    public function inner(Area $area): Area
    {
        $x = $area->position->x;
        $y = $area->position->y;
        $width = $area->width;
        $height = $area->height;
        if ($this->borders & Borders::LEFT) {
            $x = min($x + 1, $area->right());
            $width = max(0, $width - 1);
        }
        if ($this->borders & Borders::TOP || [] !== $this->titles) {
            $y = min($y + 1, $area->bottom());
            $height = max(0, $height-1);
        }
        if ($this->borders & Borders::RIGHT) {
            $width = max(0, $width - 1);
        }
        if ($this->borders & Borders::BOTTOM) {
            $height = max(0, $height - 1);
        }
        $x += $this->padding->left;
        $y += $this->padding->top;
        $width = $width - ($this->padding->left + $this->padding->right);
        $height = $height - ($this->padding->top + $this->padding->bottom);

        return Area::fromPrimitives($x, $y, $width, $height);
    }

    private function renderBorders(Area $area, Buffer $buffer): void
    {
        $buffer->setStyle($area, $this->style);
        $lineSet = $this->borderType->lineSet();
        if ($this->borders & Borders::LEFT) {
            foreach (range($area->top(), $area->bottom() - 1) as $y) {
                $buffer->get(Position::at($area->left(), $y))
                    ->setStyle($this->borderStyle)
                    ->setChar($lineSet->vertical);
            }
        }
        if ($this->borders & Borders::TOP) {
            foreach (range($area->left(), $area->right() - 1) as $x) {
                $buffer->get(Position::at($x, $area->top()))
                    ->setStyle($this->borderStyle)
                    ->setChar($lineSet->horizontal);
            }
        }
        if ($this->borders & Borders::RIGHT) {
            $x = $area->right() - 1;
            foreach (range($area->top(), $area->bottom() - 1) as $y) {
                $buffer->get(Position::at($x, $y))
                    ->setStyle($this->borderStyle)
                    ->setChar($lineSet->vertical);
            }
        }
        if ($this->borders & Borders::BOTTOM) {
            $y = $area->bottom() - 1;
            foreach (range($area->left(), $area->right() - 1) as $x) {
                $buffer->get(Position::at($x, $y))
                    ->setStyle($this->borderStyle)
                    ->setChar($lineSet->horizontal);
            }
        }
        if ($this->borders & (Borders::RIGHT | Borders::BOTTOM)) {
            $buffer->get(Position::at($area->right() - 1, $area->bottom() - 1))
                ->setChar($lineSet->bottomRight)
                ->setStyle($this->borderStyle);
        }
        if ($this->borders & (Borders::RIGHT | Borders::TOP)) {
            $buffer->get(Position::at($area->right() - 1, $area->top()))
                ->setChar($lineSet->topRight)
                ->setStyle($this->borderStyle);
        }
        if ($this->borders & (Borders::LEFT | Borders::TOP)) {
            $buffer->get(Position::at($area->left(), $area->top()))
                ->setChar($lineSet->topLeft)
                ->setStyle($this->borderStyle);
        }
        if ($this->borders & (Borders::LEFT | Borders::BOTTOM)) {
            $buffer->get(Position::at($area->left(), $area->bottom() - 1))
                ->setChar($lineSet->bottomLeft)
                ->setStyle($this->borderStyle);
        }

        if ($this->widget) {
            $this->widget->render($this->inner($area), $buffer);
        }
    }

    private function renderTitles(Area $area, Buffer $buffer): void
    {
        $this->renderTitlePosition(VerticalAlignment::Top, $area, $buffer);
        $this->renderTitlePosition(VerticalAlignment::Bottom, $area, $buffer);
    }

    private function renderTitlePosition(VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        $this->renderRightTitles($alignment, $area, $buffer);
        $this->renderCenterTitles($alignment, $area, $buffer);
        $this->renderLeftTitles($alignment, $area, $buffer);
    }

    private function renderRightTitles(VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        [$_, $rightBorderDx, $titleAreaWidth] = $this->calculateTitleAreaOffsets($area);
        $offset = $rightBorderDx;
        foreach (array_filter(
            $this->titles,
            function (Title $title) use ($alignment) {
                return
                    $title->horizontalAlignment === HorizontalAlignment::Right
                    && $title->verticalAlignment === $alignment;
            }
        ) as $title) {
            $offset += $title->title->width() + 1;
            $titleX = $offset - 1;

            $buffer->putLine(
                Position::at(
                    max(0, $area->width - $titleX) + $area->left(),
                    match ($alignment) {
                        VerticalAlignment::Bottom => $area->bottom() - 1,
                        VerticalAlignment::Top => $area->top(),
                    }
                ),
                $title->title,
                $titleAreaWidth,
            );
        }
    }

    private function renderLeftTitles(VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        [$leftBorderDx, $_, $titleAreaWidth] = $this->calculateTitleAreaOffsets($area);
        $offset = $leftBorderDx;
        foreach (array_filter(
            $this->titles,
            function (Title $title) use ($alignment) {
                return
                    $title->horizontalAlignment === HorizontalAlignment::Left
                    && $title->verticalAlignment === $alignment;
            }
        ) as $title) {
            $titleX = $offset;
            $offset += $title->title->width() + 1;

            foreach ($title->title->spans as $span) {
                $titleStyle = clone $this->titleStyle;
                $span->style = $titleStyle->patch($span->style);
            }

            $buffer->putLine(
                Position::at(
                    $titleX + $area->left(),
                    match ($alignment) {
                        VerticalAlignment::Bottom => $area->bottom() - 1,
                        VerticalAlignment::Top => $area->top(),
                    }
                ),
                $title->title,
                $titleAreaWidth,
            );
        }
    }

    private function renderCenterTitles(VerticalAlignment $alignment, Area $area, Buffer $buffer): void
    {
        [$_, $_, $titleAreaWidth] = $this->calculateTitleAreaOffsets($area);
        $titles = array_filter(
            $this->titles,
            function (Title $title) use ($alignment) {
                return
                    $title->horizontalAlignment === HorizontalAlignment::Center
                    && $title->verticalAlignment === $alignment;
            }
        );
        $sumWidth = array_reduce(
            $titles,
            fn (int $acc, Title $title) => $acc + $title->title->width(),
            0
        );
        $offset = intval(max(0, $area->width - $sumWidth) / 2);
        foreach ($titles as $title) {
            $titleX = $offset;
            $offset += $title->title->width() + 1;

            $buffer->putLine(
                Position::at(
                    $titleX + $area->left(),
                    match ($alignment) {
                        VerticalAlignment::Bottom => $area->bottom() - 1,
                        VerticalAlignment::Top => $area->top(),
                    }
                ),
                $title->title,
                $titleAreaWidth,
            );
        }
    }

    /**
     * @return array{int,int,int}
     */
    private function calculateTitleAreaOffsets(Area $area): array
    {
        $leftBorderDx = (bool)($this->borders & Borders::LEFT);
        $rightBorderDx = (bool)($this->borders & Borders::RIGHT);

        return [
            $leftBorderDx ? 1 : 0,
            $rightBorderDx ? 1 : 0,
            $area->width - max(0, $leftBorderDx, $rightBorderDx),
        ];
    }

}
