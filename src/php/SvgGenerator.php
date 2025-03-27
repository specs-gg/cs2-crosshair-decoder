<?php

require_once 'Crosshair.php';

class SvgGenerator {
    // Define constants for better scaling and to match in-game appearance
    private const CANVAS_SIZE = 200;
    private const CENTER_POINT = 100;
    private const THICKNESS_SCALE = 2;
    private const LENGTH_SCALE = 3;
    private const GAP_SCALE = 3;
    private const OUTLINE_SCALE = 1.5;
    private const DOT_SCALE = 1.8;

    public static function generateSvg(Crosshair $crosshair): string {
        // Define SVG canvas with viewBox
        $svg = "<svg viewBox='0 0 " . self::CANVAS_SIZE . " " . self::CANVAS_SIZE . "' xmlns='http://www.w3.org/2000/svg'>\n";

        // Add background for transparency visibility (optional, commented out by default)
        // $svg .= "<rect width='100%' height='100%' fill='#333333'/>\n";

        // Calculate actual values based on scaling factors
        $centerX = self::CENTER_POINT;
        $centerY = self::CENTER_POINT;
        $thickness = max(0.5, $crosshair->Thickness * self::THICKNESS_SCALE);
        $length = max(1, $crosshair->Length * self::LENGTH_SCALE);
        $gap = $crosshair->Gap * self::GAP_SCALE;

        // Apply fixed crosshair gap if necessary
        if ($crosshair->Style === 4 || $crosshair->Style === 5) {
            $gap = $crosshair->FixedCrosshairGap * self::GAP_SCALE;
        }

        // Handle negative gap values
        if ($gap < 0) {
            $gap = abs($gap);
        }

        // Colors with alpha support
        $alpha = $crosshair->HasAlpha ? $crosshair->Alpha / 255 : 1.0;
        $mainColor = "rgba({$crosshair->Red}, {$crosshair->Green}, {$crosshair->Blue}, {$alpha})";
        $outlineColor = "rgba(0, 0, 0, {$alpha})";

        // Outline thickness
        $outlineThickness = $crosshair->HasOutline ? $crosshair->Outline * self::OUTLINE_SCALE : 0;

        // Generate crosshair based on style
        switch ($crosshair->Style) {
            case 0: // Default Static
            case 1: // Default Dynamic
            case 2: // Classic Static
            case 3: // Classic Dynamic
            case 4: // Classic Static Small
            case 5: // Classic Dynamic Small
                $svg .= self::generateClassicCrosshair(
                    $centerX,
                    $centerY,
                    $thickness,
                    $length,
                    $gap,
                    $mainColor,
                    $outlineColor,
                    $outlineThickness,
                    $crosshair->HasCenterDot,
                    $crosshair->IsTStyle,
                    $crosshair->SplitDistance,
                    $crosshair->FollowRecoil,
                    $crosshair->SplitSizeRatio,
                    $crosshair->InnerSplitAlpha,
                    $crosshair->OuterSplitAlpha,
                    $alpha
                );
                break;
        }

        $svg .= "</svg>";
        return $svg;
    }

    private static function generateClassicCrosshair(
        $centerX,
        $centerY,
        $thickness,
        $length,
        $gap,
        $mainColor,
        $outlineColor,
        $outlineThickness,
        $hasCenterDot,
        $isTStyle,
        $splitDistance,
        $followRecoil,
        $splitSizeRatio,
        $innerSplitAlpha,
        $outerSplitAlpha,
        $mainAlpha
    ) {
        $elements = [];

        // Calculate actual gap (half-gap from center to start of line)
        $actualGap = max(0, $gap);

        // Define line coordinates
        $lines = [];

        // Left line
        $lines[] = [
            'x1' => $centerX - $actualGap,
            'y1' => $centerY,
            'x2' => $centerX - $actualGap - $length,
            'y2' => $centerY
        ];

        // Right line
        $lines[] = [
            'x1' => $centerX + $actualGap,
            'y1' => $centerY,
            'x2' => $centerX + $actualGap + $length,
            'y2' => $centerY
        ];

        // Top line (skip if T-style)
        if (!$isTStyle) {
            $lines[] = [
                'x1' => $centerX,
                'y1' => $centerY - $actualGap,
                'x2' => $centerX,
                'y2' => $centerY - $actualGap - $length
            ];
        }

        // Bottom line
        $lines[] = [
            'x1' => $centerX,
            'y1' => $centerY + $actualGap,
            'x2' => $centerX,
            'y2' => $centerY + $actualGap + $length
        ];

        // Apply split distance if follow recoil is enabled
        if ($followRecoil && $splitDistance > 0) {
            $innerLines = $lines;
            $outerLines = [];

            // Calculate outer lines with split distance
            foreach ($lines as $line) {
                // Calculate direction vector
                $dx = $line['x2'] - $line['x1'];
                $dy = $line['y2'] - $line['y1'];

                // Normalize
                $length = sqrt($dx * $dx + $dy * $dy);
                if ($length > 0) {
                    $dx = $dx / $length;
                    $dy = $dy / $length;
                }

                // Scale by split distance
                $splitOffsetX = $dx * $splitDistance;
                $splitOffsetY = $dy * $splitDistance;

                // Apply size ratio to outer lines
                $outerLength = $length * $splitSizeRatio;

                // Create outer line with offset
                $outerLines[] = [
                    'x1' => $line['x1'] + $splitOffsetX,
                    'y1' => $line['y1'] + $splitOffsetY,
                    'x2' => $line['x1'] + $splitOffsetX + $dx * $outerLength,
                    'y2' => $line['y1'] + $splitOffsetY + $dy * $outerLength
                ];
            }

            // Draw inner lines with inner alpha
            $innerAlpha = $mainAlpha * $innerSplitAlpha;
            $innerColor = str_replace("rgba(", "rgba(", $mainColor);
            $innerColor = preg_replace('/,[^,]*\)$/', ", {$innerAlpha})", $innerColor);

            foreach ($innerLines as $line) {
                // Draw outline first if needed
                if ($outlineThickness > 0) {
                    $elements[] = "<line x1='{$line['x1']}' y1='{$line['y1']}' x2='{$line['x2']}' y2='{$line['y2']}' stroke='{$outlineColor}' stroke-width='" . ($thickness + $outlineThickness * 2) . "' stroke-linecap='butt'/>";
                }
                // Draw main line
                $elements[] = "<line x1='{$line['x1']}' y1='{$line['y1']}' x2='{$line['x2']}' y2='{$line['y2']}' stroke='{$innerColor}' stroke-width='{$thickness}' stroke-linecap='butt'/>";
            }

            // Draw outer lines with outer alpha
            $outerAlpha = $mainAlpha * $outerSplitAlpha;
            $outerColor = str_replace("rgba(", "rgba(", $mainColor);
            $outerColor = preg_replace('/,[^,]*\)$/', ", {$outerAlpha})", $outerColor);

            foreach ($outerLines as $line) {
                // Draw outline first if needed
                if ($outlineThickness > 0) {
                    $elements[] = "<line x1='{$line['x1']}' y1='{$line['y1']}' x2='{$line['x2']}' y2='{$line['y2']}' stroke='{$outlineColor}' stroke-width='" . ($thickness + $outlineThickness * 2) . "' stroke-linecap='butt'/>";
                }
                // Draw main line
                $elements[] = "<line x1='{$line['x1']}' y1='{$line['y1']}' x2='{$line['x2']}' y2='{$line['y2']}' stroke='{$outerColor}' stroke-width='{$thickness}' stroke-linecap='butt'/>";
            }
        } else {
            // Standard rendering without split
            foreach ($lines as $line) {
                // Draw outline first if needed
                if ($outlineThickness > 0) {
                    $elements[] = "<line x1='{$line['x1']}' y1='{$line['y1']}' x2='{$line['x2']}' y2='{$line['y2']}' stroke='{$outlineColor}' stroke-width='" . ($thickness + $outlineThickness * 2) . "' stroke-linecap='butt'/>";
                }
                // Draw main line
                $elements[] = "<line x1='{$line['x1']}' y1='{$line['y1']}' x2='{$line['x2']}' y2='{$line['y2']}' stroke='{$mainColor}' stroke-width='{$thickness}' stroke-linecap='butt'/>";
            }
        }

        // Draw center dot if enabled
        if ($hasCenterDot) {
            $dotRadius = $thickness * self::DOT_SCALE / 2;

            if ($outlineThickness > 0) {
                $elements[] = "<circle cx='{$centerX}' cy='{$centerY}' r='" . ($dotRadius + $outlineThickness) . "' fill='{$outlineColor}'/>";
            }

            $elements[] = "<circle cx='{$centerX}' cy='{$centerY}' r='{$dotRadius}' fill='{$mainColor}'/>";
        }

        return implode("\n", $elements);
    }
}
