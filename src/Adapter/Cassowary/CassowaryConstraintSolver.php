<?php

namespace PhpTui\Tui\Adapter\Cassowary;

use PhpTui\Cassowary\Constraint;
use PhpTui\Cassowary\Solver;
use PhpTui\Cassowary\Strength;
use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Areas;
use PhpTui\Tui\Model\Constraint as DTLConstraint;
use PhpTui\Tui\Model\ConstraintSolver;
use PhpTui\Tui\Model\Constraint\LengthConstraint;
use PhpTui\Tui\Model\Constraint\MaxConstraint;
use PhpTui\Tui\Model\Constraint\MinConstraint;
use PhpTui\Tui\Model\Constraint\PercentageConstraint;
use PhpTui\Tui\Model\Direction;
use PhpTui\Tui\Model\Layout;
use RuntimeException;

final class CassowaryConstraintSolver implements ConstraintSolver
{
    public function solve(Layout $layout, Area $area, array $constraints): Areas
    {
        $solver = Solver::new();
        $inner = $area->inner($layout->margin);

        [$areaStart, $areaEnd] = match ($layout->direction) {
            Direction::Horizontal => [$inner->position->x, $inner->right()],
            Direction::Vertical => [$inner->position->y, $inner->bottom()],
        };

        $areaSize = $areaEnd - $areaStart;

        $elements = array_map(fn () => Element::empty(), $layout->constraints);

        // ensure that all the elements are inside the area
        foreach ($elements as $element) {
            $solver->addConstraints([
                Constraint::greaterThanOrEqualTo($element->start, $areaStart, Strength::REQUIRED),
                Constraint::lessThanOrEqualTo($element->end, $areaEnd, Strength::REQUIRED),
                Constraint::lessThanOrEqualTo($element->start, $element->end, Strength::REQUIRED),
            ]);
        }

        // ensure there are no gaps between the elements
        $previousElement = null;
        foreach ($elements as $element) {
            if ($previousElement === null) {
                $previousElement = $element;
                continue;
            }
            $solver->addConstraint(
                Constraint::equalTo($previousElement->end, $element->start, Strength::REQUIRED)
            );
            $previousElement = $element;
        }

        // ensure the first element toches the left/top edge of the area
        if (count($elements)) {
            $first = $elements[array_key_first($elements)];
            $solver->addConstraint(Constraint::equalTo($first->start, $areaStart, Strength::REQUIRED));
        }

        // ensure the last element touches the right/boottom edge of the area
        if (count($elements)) {
            $last = $elements[array_key_last($elements)];
            $solver->addConstraint(Constraint::equalTo($last->end, $areaEnd, Strength::REQUIRED));
        }

        foreach ($constraints as $i => $constraint) {
            $element = $elements[$i];
            $solver->addConstraints($this->resolveConstraints($constraint, $element, $areaSize));
        }

        $changes = $solver->fetchChanges();

        return new Areas(array_map(function (Element $element) use ($changes, $layout, $inner) {
            $values = $changes->getValues($element->start, $element->end);
            $start = intval($values[0]);
            $end = intval($values[1]);
            $size = $end - $start;

            return match ($layout->direction) {
                Direction::Horizontal => Area::fromPrimitives(
                    $start,
                    $inner->position->y,
                    $size,
                    $inner->height
                ),
                Direction::Vertical => Area::fromPrimitives(
                    $inner->position->x,
                    $start,
                    $inner->width,
                    $size
                ),
            };
        }, $elements));
    }
    /**
     * @return list<Constraint>
     */
    private function resolveConstraints(DTLConstraint $constraint, Element $element, float $areaSize): array
    {
        if ($constraint instanceof PercentageConstraint) {
            $constraint = Constraint::equalTo(
                $element->size(),
                $areaSize * ($constraint->percentage / 100.0),
                Strength::STRONG
            );
            return [
                $constraint
            ];
        }
        if ($constraint instanceof LengthConstraint) {
            return [
                Constraint::equalTo(
                    $element->size(),
                    $constraint->length,
                    Strength::STRONG
                )
            ];
        }
        if ($constraint instanceof MinConstraint) {
            return [
                Constraint::greaterThanOrEqualTo(
                    $element->size(),
                    $constraint->min,
                    Strength::STRONG
                ),
                Constraint::equalTo(
                    $element->size(),
                    $constraint->min,
                    Strength::MEDIUM
                ),
            ];
        }
        if ($constraint instanceof MaxConstraint) {
            return [
                Constraint::lessThanOrEqualTo(
                    $element->size(),
                    $constraint->max,
                    Strength::STRONG
                ),
                Constraint::equalTo(
                    $element->size(),
                    $constraint->max,
                    Strength::MEDIUM
                ),
            ];
        }

        throw new RuntimeException(sprintf(
            'Do not know how to build constraint of class "%s"',
            $constraint::class
        ));
    }
}
