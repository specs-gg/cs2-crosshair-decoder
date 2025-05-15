<?php

require_once 'Crosshair.php';

class SvgGenerator {
    // Define center point constant
    private const SCALE_FACTOR = 1;

    public static function generateSvg(Crosshair $crosshair): string {

        $thickness = max($crosshair->Thickness, 0.5) * self::SCALE_FACTOR;
        $length = $crosshair->Length * self::SCALE_FACTOR;
        $gap = $crosshair->Gap * self::SCALE_FACTOR;
        $outline = $crosshair->Outline * self::SCALE_FACTOR;
        $correction = 3 * self::SCALE_FACTOR;
        // Define SVG canvas with calculated dimensions
        $width = $length * 4;
        $width += $thickness * 2;
        $width += $gap * 2;
        $width += $outline * 2;
        $width += $correction * 2;
        $height = $width;
        $viewBoxX = $viewBoxY = $width * - 0.5;
        $alpha = ($crosshair->HasAlpha ? $crosshair->Alpha : 200.0) / 255.0;
        $mainColor = "rgb({$crosshair->Red}, {$crosshair->Green}, {$crosshair->Blue})";

        $svg = "<svg viewBox='{$viewBoxX} {$viewBoxY} {$width} {$height}' width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg' fill-opacity='{$alpha}'>\n";

        // Generate crosshair
        switch ($crosshair->Style) {
            case 0: // Default Static
            case 1: // Default Dynamic
            case 2: // Classic Static
            case 3: // Classic Dynamic
            case 4: // Classic Static Small
            case 5: // Classic Dynamic Small
                $svg .= self::generateClassicCrosshair(
                    $thickness,
                    $length,
                    $gap,
                    $outline,
                    $correction,
                    $crosshair->HasOutline,
                    $crosshair->HasCenterDot,
                    $crosshair->IsTStyle,
                    $mainColor,
                );
                break;
        }

        $svg .= "</svg>";
        return $svg;
    }

    private static function generateClassicCrosshair(
        float $thickness,
        float $length,
        float $gap,
        float $outline,
        float $correction,
        bool $hasOutline,
        bool $hasCenterDot,
        bool $isTStyle,
        string $mainColor
    ) {
        $length *= 2; // Double length for rendering
        $thicknessTimesTwo = $thickness * 2; // Double thickness for rendering
        $elements = [];

        // Define rectangle coordinates
        $innerRects = [];

        // Left rect
        $innerRects[] = [
            'x' => -($length + $gap + $thicknessTimesTwo + $correction),
            'y' => -$thickness,
            'width' => $length,
            'height' => $thicknessTimesTwo,
        ];

        // Right rect
        $innerRects[] = [
            'x' => $gap + $thicknessTimesTwo + $correction,
            'y' => -$thickness,
            'width' => $length,
            'height' => $thicknessTimesTwo,
        ];

        // Top rect (skip if T-style)
        if (!$isTStyle) {
            $innerRects[] = [
                'x' => -$thickness,
                'y' => -($length + $gap + $thicknessTimesTwo + $correction),
                'width' => $thicknessTimesTwo,
                'height' => $length,
            ];
        }

        // Bottom rect
        $innerRects[] = [
            'x' => -$thickness,
            'y' => $gap + $thicknessTimesTwo + $correction,
            'width' => $thicknessTimesTwo,
            'height' => $length,
        ];

        // Center point
        if ($hasCenterDot) {
            $innerRects[] = [
                'x' => -$thickness,
                'y' => -$thickness,
                'width' => $thicknessTimesTwo,
                'height' => $thicknessTimesTwo,
            ];
        }

        // Calculate outer rectangles
        foreach ($innerRects as $rect) {

            if ($hasOutline) {
                $x = $rect['x'] - $outline;
                $y = $rect['y'] - $outline;
                $width = $rect['width'] + ($outline * 2);
                $height = $rect['height'] + ($outline * 2);
                // Draw Outline rectangle
                $elements[] = "<rect x='{$x}' y='{$y}' width='{$width}' height='{$height}' fill='#000'/>";
            }
            // Draw main rectangle
            $elements[] = "<rect x='{$rect['x']}' y='{$rect['y']}' width='{$rect['width']}' height='{$rect['height']}' fill='{$mainColor}'/>";
        }

        return implode("\n", $elements);
    }
}
