<?php

declare(strict_types=1);

namespace Stoffel\Console\Gradient\Tests;

use PHPUnit\Framework\TestCase;
use Stoffel\Console\Gradient\Rgb;

class RgbTest extends TestCase
{
    /**
     * @dataProvider provideRgbHsvValues
     */
    public function testRgbHsvConversion(int $red, int $green, int $blue, int $hue, float $saturation, float $value): void
    {
        $rgb = new Rgb($red, $green, $blue);

        static::assertSame([$hue, $saturation, $value], $rgb->toHSV());
    }

    /**
     * @dataProvider provideRgbHsvValues
     */
    public function testHsvRgbConversion(int $red, int $green, int $blue, int $hue, float $saturation, float $value): void
    {
        $rgb = Rgb::fromHSV($hue, $saturation, $value);

        static::assertSame($red, $rgb->getRed());
        static::assertSame($green, $rgb->getGreen());
        static::assertSame($blue, $rgb->getBlue());
    }

    public function provideRgbHsvValues(): array
    {
        return [
            [0, 0, 0, 0, 0.0, 0.0],
            [255, 255, 255, 0, 0.0, 1.0],
            [255, 0, 0, 0, 1.0, 1.0],
            [0, 255, 0, 120, 1.0, 1.0],
            [0, 0, 255, 240, 1.0, 1.0],
            [255, 255, 0, 60, 1.0, 1.0],
            [0, 255, 255, 180, 1.0, 1.0],
            [255, 0, 255, 300, 1.0, 1.0],
            [191, 191, 191, 0, 0.0, 0.75],
            [128, 128, 128, 0, 0.0, 0.5],
            [128, 0, 0, 0, 1.0, 0.5],
            [128, 128, 0, 60, 1.0, 0.5],
            [0, 128, 0, 120, 1.0, 0.5],
            [128, 0, 128, 300, 1.0, 0.5],
            [0, 128, 128, 180, 1.0, 0.5],
            [0, 0, 128, 240, 1.0, 0.5],
        ];
    }
}
