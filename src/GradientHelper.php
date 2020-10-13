<?php

declare(strict_types=1);

namespace Stoffel\Console\Gradient;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class GradientHelper
{
    private const GRADIENT_STYLES = [
        'cristal' => [['#bdfff3', '#4ac29a'], Gradient::INTERPOLATION_RGB],
        'teen' => [['#77a1d3', '#79cbca', '#e684ae'], Gradient::INTERPOLATION_RGB],
        'mind' => [['#473b7b', '#3584a7', '#30d2be'], Gradient::INTERPOLATION_RGB],
        'morning' => [['#ff5f6d', '#ffc371'], Gradient::INTERPOLATION_HSV_SHORT],
        'vice' => [['#5ee7df', '#b490ca'], Gradient::INTERPOLATION_HSV_SHORT],
        'passion' => [['#f43b47', '#453a94'], Gradient::INTERPOLATION_RGB],
        'fruit' => [['#ff4e50', '#f9d423'], Gradient::INTERPOLATION_RGB],
        'instagram' => [['#833ab4', '#fd1d1d', '#fcb045'], Gradient::INTERPOLATION_RGB],
        'atlas' => [['#feac5e', '#c779d0', '#4bc0c8'], Gradient::INTERPOLATION_RGB],
        'retro' => [['#3f51b1', '#5a55ae', '#7b5fac', '#8f6aae', '#a86aa4', '#cc6b8e', '#f18271', '#f3a469', '#f7c978'], Gradient::INTERPOLATION_RGB],
        'summer' => [['#fdbb2d', '#22c1c3'], Gradient::INTERPOLATION_RGB],
        'pastel' => [['#74ebd5', '#74ecd5'], Gradient::INTERPOLATION_HSV_LONG],
        'rainbow' => [['#ff0000', '#ff0100'], Gradient::INTERPOLATION_HSV_LONG],
    ];

    private OutputInterface $output;
    private string $text = '';
    private array $colors;
    private int $interpolation = Gradient::INTERPOLATION_RGB;
    private bool $isMultiline = false;
    private bool $linebreak = true;

    private function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->colors = self::GRADIENT_STYLES['retro'][0];
    }

    public static function create(OutputInterface $output = null): self
    {
        return new self($output ?? new NullOutput());
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        $this->isMultiline = false !== strpos($text, PHP_EOL);

        return $this;
    }

    public function setStyle(string $style): self
    {
        if (!array_key_exists($style, self::GRADIENT_STYLES)) {
            $values = implode(', ', array_keys(self::GRADIENT_STYLES));
            $message = sprintf('Gradient style "%s" does not exists, possible values: %s', $style, $values);

            throw new \InvalidArgumentException($message);
        }

        [$this->colors, $this->interpolation] = self::GRADIENT_STYLES[$style];

        return $this;
    }

    public function setColors(array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function setInterpolation(int $interpolation): self
    {
        $this->interpolation = $interpolation;

        return $this;
    }

    public function disableMultiline(): self
    {
        $this->isMultiline = false;

        return $this;
    }

    public function disableLinebreak(): self
    {
        $this->linebreak = false;

        return $this;
    }

    public function colorize(): string
    {
        $gradient = new Gradient(...$this->colors);
        $steps = $this->getSteps();
        $colors = $gradient->getColors($steps, $this->interpolation);

        $output = '';
        $textLength = mb_strlen($this->text);
        $colorized = 0;
        for ($i = 0; $i < $textLength; $i++) {
            $char = mb_substr($this->text, $i, 1);
            if (PHP_EOL === $char) {
                $output .= $char;
                continue;
            }

            $output .= $colors[$colorized % $steps]->apply($char);
            ++$colorized;
        }

        return $output;
    }

    public function write(): void
    {
        $this->output->write($this->colorize());

        if ($this->linebreak) {
            $this->output->write(PHP_EOL);
        }
    }

    private function getSteps(): int
    {
        if ($this->isMultiline) {
            return max(array_map('mb_strlen', explode(PHP_EOL, $this->text)));
        }

        return mb_strlen($this->text);
    }
}
