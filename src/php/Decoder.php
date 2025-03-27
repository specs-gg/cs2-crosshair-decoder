<?php

require_once 'Crosshair.php';

class Decoder {
    public const DICTIONARY = "ABCDEFGHJKLMNOPQRSTUVWXYZabcdefhijkmnopqrstuvwxyz23456789";
    public const SHARECODE_PATTERN = '/^CSGO(-?[\w]{5}){5}$/';

    // Predefined colors from JS implementation
    private static $predefinedColors = [
        [255, 0, 0],    // Red
        [0, 255, 0],    // Green
        [255, 255, 0],  // Yellow
        [0, 0, 255],    // Blue
        [0, 255, 255]   // Cyan
    ];

    private static function decodeSignedByte(int $val): int {
        return $val > 127 ? $val - 256 : $val;
    }

    public static function decode(string $shareCode): Crosshair {
        if (!preg_match(self::SHARECODE_PATTERN, $shareCode)) {
            throw new Exception("Invalid share code format");
        }

        // Remove "CSGO-" prefix and dashes
        $code = str_replace(["CSGO-", "-"], "", $shareCode);

        // Reverse the characters as per JS implementation
        $codeChars = str_split($code);
        $reversedChars = array_reverse($codeChars);

        // Convert to big integer
        $big = "0";
        $dictLength = (string)strlen(self::DICTIONARY);

        foreach ($reversedChars as $c) {
            $idx = strpos(self::DICTIONARY, $c);
            if ($idx === false) {
                throw new Exception("Invalid character in share code");
            }
            $big = bcmul($big, $dictLength);
            $big = bcadd($big, (string)$idx);
        }

        // Convert to bytes (18 bytes total)
        $bytes = array_fill(0, 18, 0);
        for ($i = 17; $i >= 0; $i--) {
            $bytes[$i] = (int)bcmod($big, "256");
            $big = bcdiv($big, "256", 0);
        }

        // Verify checksum
        $sum = 0;
        for ($i = 1; $i < 18; $i++) {
            $sum += $bytes[$i];
        }
        if ($bytes[0] !== ($sum % 256)) {
            // Just log warning, continue with decode like JS does
            error_log("Checksum mismatch in crosshair share code");
        }

        // Extract color index and RGB values exactly like JS
        $colorIndex = $bytes[10] & 7;
        $red = $bytes[4];
        $green = $bytes[5];
        $blue = $bytes[6];

        // Use predefined colors if specified
        if ($colorIndex !== 5) {
            if ($colorIndex >= 0 && $colorIndex < count(self::$predefinedColors)) {
                list($red, $green, $blue) = self::$predefinedColors[$colorIndex];
            } else {
                // Default to green like JS
                $red = 0;
                $green = 255;
                $blue = 0;
            }
        }

        // Create crosshair object
        return new Crosshair(
            ($bytes[13] & 14) >> 1,                   // style
            ($bytes[13] & 16) === 16,                 // centerDotEnabled
            ((($bytes[15] & 31) << 8) + $bytes[14]) / 10.0, // length
            ($bytes[12] & 63) / 10.0,                 // thickness
            self::decodeSignedByte($bytes[2]) / 10.0, // gap
            ($bytes[10] & 8) === 8,                   // outlineEnabled
            ($bytes[3] & 255) / 2.0,                  // outline
            $red,                                     // red
            $green,                                   // green
            $blue,                                    // blue
            ($bytes[13] & 64) === 64,                 // alphaEnabled
            $bytes[7],                                // alpha
            $bytes[8] & 127,                          // splitDistance
            self::decodeSignedByte($bytes[9]) / 10.0, // fixedCrosshairGap
            $colorIndex,                              // color
            (($bytes[10] >> 4) & 15) / 10.0,          // innerSplitAlpha
            ($bytes[11] & 15) / 10.0,                 // outerSplitAlpha
            (($bytes[11] >> 4) & 15) / 10.0,          // splitSizeRatio
            ($bytes[13] & 128) === 128,               // tStyleEnabled
            ($bytes[13] & 32) === 32,                 // deployedWeaponGapEnabled
            ($bytes[8] & 128) !== 0                   // followRecoil
        );
    }
}
