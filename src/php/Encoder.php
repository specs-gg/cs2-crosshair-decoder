<?php

require_once 'Crosshair.php';

class Encoder {
    public const DICTIONARY = "ABCDEFGHJKLMNOPQRSTUVWXYZabcdefhijkmnopqrstuvwxyz23456789";

    private static function encodeSignedByte(int $value): int {
        return $value < 0 ? $value + 256 : $value;
    }

    private static function calculateChecksum(array $bytes): int {
        $sum = 0;
        for ($i = 1; $i < count($bytes); $i++) {
            $sum += $bytes[$i];
        }
        return $sum % 256;
    }

    public static function encode(Crosshair $crosshair): string {
        // Create byte array exactly like the JavaScript implementation
        $bytes = [0, 1]; // Checksum placeholder and version

        // Add all other bytes in the exact order
        $bytes[] = self::encodeSignedByte(round(10 * $crosshair->Gap));
        $bytes[] = floor(2 * $crosshair->Outline);
        $bytes[] = $crosshair->Red;
        $bytes[] = $crosshair->Green;
        $bytes[] = $crosshair->Blue;
        $bytes[] = $crosshair->Alpha;
        $bytes[] = ($crosshair->SplitDistance & 127) | ($crosshair->FollowRecoil ? 128 : 0);
        $bytes[] = self::encodeSignedByte(round(10 * $crosshair->FixedCrosshairGap));
        $bytes[] = ($crosshair->Color & 7) |
                   ($crosshair->HasOutline ? 8 : 0) |
                   (round(10 * $crosshair->InnerSplitAlpha) << 4);
        $bytes[] = (round(10 * $crosshair->OuterSplitAlpha) & 15) |
                   (round(10 * $crosshair->SplitSizeRatio) << 4);
        $bytes[] = round(10 * $crosshair->Thickness);
        $bytes[] = (($crosshair->Style & 7) << 1) |
                   ($crosshair->HasCenterDot ? 16 : 0) |
                   ($crosshair->DeployedWeaponGapEnabled ? 32 : 0) |
                   ($crosshair->HasAlpha ? 64 : 0) |
                   ($crosshair->IsTStyle ? 128 : 0);

        $lenVal = round(10 * $crosshair->Length);
        $bytes[] = $lenVal % 256;
        $bytes[] = floor($lenVal / 256) & 31;
        $bytes[] = 0; // Reserved
        $bytes[] = 0; // Reserved

        // Calculate checksum
        $bytes[0] = self::calculateChecksum($bytes);

        // Convert to big integer in REVERSE order (crucial for matching JS)
        $big = "0";
        $power = "1";

        for ($i = count($bytes) - 1; $i >= 0; $i--) {
            $term = bcmul((string)$bytes[$i], $power);
            $big = bcadd($big, $term);
            $power = bcmul($power, "256");
        }

        // Convert big integer to share code string like JS
        $dictLen = strlen(self::DICTIONARY);
        $result = "";

        // Generate exactly 25 characters
        for ($i = 0; $i < 25; $i++) {
            $mod = bcmod($big, (string)$dictLen);
            $big = bcdiv($big, (string)$dictLen);
            $result .= self::DICTIONARY[(int)$mod];
        }

        // Format with CSGO- prefix and dashes
        $formatted = "CSGO-" .
                     substr($result, 0, 5) . "-" .
                     substr($result, 5, 5) . "-" .
                     substr($result, 10, 5) . "-" .
                     substr($result, 15, 5) . "-" .
                     substr($result, 20, 5);

        return $formatted;
    }
}
