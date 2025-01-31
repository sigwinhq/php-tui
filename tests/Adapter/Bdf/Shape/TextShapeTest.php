<?php

namespace PhpTui\Tui\Tests\Adapter\Bdf\Shape;

use PhpTui\Tui\Adapter\Bdf\FontRegistry;
use PhpTui\Tui\Adapter\Bdf\Shape\TextShape;
use PhpTui\Tui\Model\AnsiColor;
use PhpTui\Tui\Model\Area;
use PhpTui\Tui\Model\Buffer;
use PhpTui\Tui\Model\Marker;
use PhpTui\Tui\Model\Widget\FloatPosition;
use PhpTui\Tui\Widget\Canvas;
use PhpTui\Tui\Widget\Canvas\CanvasContext;
use Generator;
use PHPUnit\Framework\TestCase;

class TextShapeTest extends TestCase
{
    /**
     * @dataProvider provideTextShape
     * @param array<int,string> $expected
     */
    public function testTextShape(TextShape $text, array $expected): void
    {
        $canvas = Canvas::fromIntBounds(0, 65, 0, 6)
            ->marker(Marker::Block)
            ->paint(function (CanvasContext $context) use ($text): void {
                $context->draw($text);
            });
        $area = Area::fromDimensions(65, 6);
        $buffer = Buffer::empty($area);
        $canvas->render($area, $buffer);
        self::assertEquals($expected, $buffer->toLines());
    }
    /**
     * @return Generator<array{TextShape,array<int,string>}>
     */
    public static function provideTextShape(): Generator
    {
        yield 'text' => [
            new TextShape(
                font: FontRegistry::default()->get('default'),
                text: 'Hello World',
                color: AnsiColor::Green,
                position: FloatPosition::at(0, 0),
            ),
            [
                '█   █        ██    ██               █   █              ██       █',
                '█   █  ███    █     █    ███        █   █  ███  █ ██    █    ██ █',
                '█████ █   █   █     █   █   █       █ █ █ █   █ ██  █   █   █  ██',
                '█   █ █████   █     █   █   █       █ █ █ █   █ █       █   █   █',
                '█   █ █       █     █   █   █       ██ ██ █   █ █       █   █  ██',
                '█   █  ███   ███   ███   ███        █   █  ███  █      ███   ██ █',
            ]
        ];
        yield 'scale x' => [
            new TextShape(
                font: FontRegistry::default()->get('default'),
                scaleX: 2,
                text: 'Hello',
                color: AnsiColor::Green,
                position: FloatPosition::at(0, 0),
            ),
            [
                '██      ██                ████        ████                       ',
                '██      ██    ██████        ██          ██        ██████         ',
                '██████████  ██      ██      ██          ██      ██      ██       ',
                '██      ██  ██████████      ██          ██      ██      ██       ',
                '██      ██  ██              ██          ██      ██      ██       ',
                '██      ██    ██████      ██████      ██████      ██████         ',
            ]
        ];
        yield 'scale y' => [
            new TextShape(
                font: FontRegistry::default()->get('default'),
                scaleY: 2,
                text: 'Hello World',
                color: AnsiColor::Green,
                position: FloatPosition::at(0, 0),
            ),
            [
                '█████ █████   █     █   █   █       █ █ █ █   █ ██  █   █   █  ██',
                '█   █ █████   █     █   █   █       █ █ █ █   █ █       █   █   █',
                '█   █ █       █     █   █   █       ██ ██ █   █ █       █   █  ██',
                '█   █ █       █     █   █   █       ██ ██ █   █ █       █   █  ██',
                '█   █  ███   ███   ███   ███        █   █  ███  █      ███   ██ █',
                '█   █  ███   ███   ███   ███        █   █  ███  █      ███   ██ █',
            ]
        ];
    }
}
