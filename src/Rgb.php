<?php

declare(strict_types=1);

namespace Stoffel\Console\Gradient;

class Rgb
{
    private int $red;
    private int $green;
    private int $blue;

    public function __construct(int $red, int $green, int $blue)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    public static function fromHex(string $hex): self
    {
        if (!preg_match('/#[a-fA-F0-9]{6}/', $hex)) {
            throw new \InvalidArgumentException(sprintf('Invalid hex value for starting color: %s', $hex));
        }

        return new self(
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        );
    }

    public static function fromHSV(int $hue, float $saturation, float $value): self
    {
        $hue = $hue / 360 * 6;

        $i = floor($hue);
        $f = $hue - $i;
        $p = $value * (1 - $saturation);
        $q = $value * (1 - $f * $saturation);
        $t = $value * (1 - (1 - $f) * $saturation);

        $mod = $i % 6;

        $red = [$value, $q, $p, $p, $t, $value][$mod] * 255;
        $green = [$t, $value, $value, $q, $p, $p][$mod] * 255;
        $blue = [$p, $p, $t, $value, $value, $q][$mod] * 255;

        return new self((int)round($red), (int)round($green), (int)round($blue));
    }

    public function toHex(): string
    {
        return sprintf('#%s%s%s',
            bin2hex(pack('C', $this->red)),
            bin2hex(pack('C', $this->green)),
            bin2hex(pack('C', $this->blue))
        );
    }

    public function toHSV(): array
    {
        $red = $this->red / 255;
        $green = $this->green / 255;
        $blue = $this->blue / 255;

        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $delta = $max - $min;

        $hue = $max;
        $value = $max;
        $saturation = $max === 0 ? 0 : $delta / $max;

        if($max === $min) {
            $hue = 0; // achromatic
        } else {
            switch($max) {
                case $red:
                    $hue = ($green - $blue) / $delta + ($green < $blue ? 6 : 0);
                    break;
                case $green:
                    $hue = ($blue - $red) / $delta + 2;
                    break;
                case $blue:
                    $hue = ($red - $green) / $delta + 4;
                    break;
            }
            $hue = round($hue / 6 * 360);
        }

        return [(int)$hue, round($saturation, 2), round($value, 2)];
    }

    public function getRed(): int
    {
        return $this->red;
    }

    public function getGreen(): int
    {
        return $this->green;
    }

    public function getBlue(): int
    {
        return $this->blue;
    }
}
