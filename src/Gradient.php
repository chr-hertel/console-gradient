<?php

declare(strict_types=1);

namespace Stoffel\Console\Gradient;

use InvalidArgumentException;
use Symfony\Component\Console\Color;

class Gradient
{
    public const INTERPOLATION_RGB = 1;
    public const INTERPOLATION_HSV_SHORT = 2;
    public const INTERPOLATION_HSV_LONG = 3;

    /**
     * @var array<int, Rgb>
     */
    private array $colors = [];

    public function __construct(string ...$colors)
    {
        foreach ($colors as $color) {
            $this->colors[] = Rgb::fromHex($color);
        }
    }

    /**
     * @return array<int, Color>
     */
    public function getColors(int $steps, int $interpolation = self::INTERPOLATION_RGB): array
    {
        if (0 >= $steps) {
            throw new InvalidArgumentException('Only positive integer values as steps allowed.');
        }

        if (!in_array($interpolation, [self::INTERPOLATION_RGB, self::INTERPOLATION_HSV_SHORT, self::INTERPOLATION_HSV_LONG])) {
            throw new InvalidArgumentException('Invalid interpolation, see Gradient::INTERPOLATION_* constants');
        }

        $stepSequence = $this->calculateStepSequence($steps);
        $start = reset($this->colors);
        $gradientColors = [new Color($start->toHex())];

        $sequenceNumber = 0;
        while($next = next($this->colors)) {
            if (self::INTERPOLATION_RGB !== $interpolation) {
                $stepColors = $this->interpolateColorsHSV($start, $next, $stepSequence[$sequenceNumber], $interpolation);
            } else {
                $stepColors = $this->interpolateColorsRgb($start, $next, $stepSequence[$sequenceNumber]);
            }
            $gradientColors = [...$gradientColors, ...$stepColors];
            $start = $next;
            ++$sequenceNumber;
        }

        return $gradientColors;
    }

    /**
     * @return array<int, Color>
     */
    private function interpolateColorsRgb(Rgb $start, Rgb $stop, int $steps): array
    {
        $colors = [];
        $deltaRed = ($start->getRed() - $stop->getRed()) / $steps;
        $deltaGreen = ($start->getGreen() - $stop->getGreen()) / $steps;
        $deltaBlue = ($start->getBlue() - $stop->getBlue()) / $steps;

        for($i = 0; $i <= $steps; $i++) {
            $rgb = new Rgb(
                (int)floor($start->getRed() - $deltaRed * $i),
                (int)floor($start->getGreen() - $deltaGreen * $i),
                (int)floor($start->getBlue() - $deltaBlue * $i),
            );

            $colors[] = new Color($rgb->toHex());
        }

        return $colors;
    }
    /**
     * @return array<int, Color>
     */
    private function interpolateColorsHSV(Rgb $start, Rgb $stop, int $steps, int $interpolation): array
    {
        $colors = [];
        [$startHue, $startSaturation, $startValue] = $start->toHSV();
        [$nextHue, $nextSaturation, $nextValue] = $stop->toHSV();
        $hueDelta = $startHue - $nextHue;
        if ((self::INTERPOLATION_HSV_LONG === $interpolation && abs($hueDelta) < 180)
            || (self::INTERPOLATION_HSV_SHORT === $interpolation && abs($hueDelta) > 180)
        ) {
            $hueDelta -= 360;
        }
        $deltaHue = $hueDelta / $steps;

        $deltaSaturation = ($startSaturation - $nextSaturation) / $steps;
        $deltaValue = ($startValue - $nextValue) / $steps;

        for($i = 0; $i <= $steps; $i++) {
            $rgb = Rgb::fromHSV(
                (int)round($startHue - $deltaHue * $i),
                $startSaturation - $deltaSaturation * $i,
                $startValue - $deltaValue * $i,
            );

            $colors[] = new Color($rgb->toHex());
        }

        return $colors;
    }

    private function calculateStepSequence(int $steps): array
    {
        $colorCount = count($this->colors);
        $stepBase = ($steps - $colorCount) / ($colorCount - 1);

        $sequence = [];
        $backlog = 0;
        for ($i = 1; $i < $colorCount; $i++) {
            $step = floor($stepBase);
            $backlog += $stepBase - $step;

            if ($backlog >= 1) {
                --$backlog;
                ++$step;
            }

            $sequence[] = (int)$step;
        }

        return $sequence;
    }
}
