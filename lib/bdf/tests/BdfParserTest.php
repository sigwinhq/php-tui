<?php

namespace PhpTui\Bdf\Tests;

use PHPUnit\Framework\TestCase;
use PhpTui\BDF\BdfBoundingBox;
use PhpTui\BDF\BdfCoord;
use PhpTui\BDF\BdfGlyph;
use PhpTui\BDF\BdfMetadata;
use PhpTui\BDF\BdfParser;
use PhpTui\BDF\BdfProperty;
use PhpTui\BDF\BdfSize;
use RuntimeException;

class BdfParserTest extends TestCase
{
    public function testParseRealFont(): void
    {
        $contents = file_get_contents(__DIR__ . '/../fonts/6x10.bdf');

        if (false === $contents) {
            throw new RuntimeException(
                'Could not read file'
            );
        }

        $font = (new BdfParser())->parse($contents);
        self::assertEquals(new BdfMetadata(
            version: 2.1,
            name: '-Misc-Fixed-Medium-R-Normal--10-100-75-75-C-60-ISO10646-1',
            pointSize: 10,
            resolution: new BdfSize(75, 75),
            boundingBox: new BdfBoundingBox(
                size: new BdfSize(6,10),
                offset: new BdfCoord(0,-2),
            ),
        ), $font->metadata);
        self::assertCount(1597, $font->glyphs());
    }

    public function testParseMinimalExample(): void
    {
        $font = (new BdfParser())->parse($this->font());
        
        self::assertEquals(new BdfMetadata(
            version: 2.1,
            name: '"test font"',
            pointSize: 16,
            resolution: new BdfSize(75, 75),
            boundingBox: new BdfBoundingBox(
                size: new BdfSize(16,24),
                offset: new BdfCoord(0,0),
            ),
        ), $font->metadata);

        self::assertEquals(
            'Copyright123',
            $font->properties->get(BdfProperty::COPYRIGHT)
        );

        self::assertEquals([
                new BdfGlyph(
                    bitmap: [ 0x1f, 0x01 ],
                    boundingBox: BdfBoundingBox::fromPrimitives(8, 8, 0, 0),
                    encoding: 64,
                    name: 'Char 0',
                    deviceWidth: new BdfCoord(8, 0),
                    scalableWidth: null,
                ),
                new BdfGlyph(
                    bitmap: [ 0x2f, 0x02 ],
                    boundingBox: BdfBoundingBox::fromPrimitives(8, 8, 0, 0),
                    encoding: 65,
                    name: 'Char 1',
                    deviceWidth: new BdfCoord(8, 0),
                    scalableWidth: null,
                )
            ],
            $font->glyphs()
        );
    }

    private function font(): string
    {
        return <<<EOT
            STARTFONT 2.1
            FONT "test font"
            SIZE 16 75 75
            FONTBOUNDINGBOX 16 24 0 0
            STARTPROPERTIES 3
            COMMENT   "foo"
            COPYRIGHT "Copyright123"
            FONT_ASCENT 1
            COMMENT comment
            FONT_DESCENT 2
            ENDPROPERTIES
            STARTCHAR Char 0
            ENCODING 64
            DWIDTH 8 0
            BBX 8 8 0 0
            BITMAP
            1f
            01
            ENDCHAR
            STARTCHAR Char 1
            ENCODING 65
            DWIDTH 8 0
            BBX 8 8 0 0
            BITMAP
            2f
            02
            ENDCHAR
            ENDFONT
            EOT;
    }
}
