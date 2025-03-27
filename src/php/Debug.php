<?php

require_once 'Crosshair.php';
require_once 'Encoder.php';
require_once 'Decoder.php';

class Debug {
    public static function runTest(string $shareCode) {
        echo "Testing share code: $shareCode<br>";

        // Decode the share code
        try {
            $crosshair = Decoder::decode($shareCode);
            echo "Successfully decoded share code to crosshair:<br>";
            echo $crosshair . "<br><br>";

            // Re-encode the crosshair
            $reEncodedCode = Encoder::encode($crosshair);
            echo "Re-encoded share code: $reEncodedCode<br>";

            // Compare the two codes
            if ($shareCode === $reEncodedCode) {
                echo "SUCCESS: Original code and re-encoded code match!<br>";
            } else {
                echo "ERROR: Original code and re-encoded code do not match.<br>";
                self::compareShareCodes($shareCode, $reEncodedCode);
            }
        } catch (Exception $e) {
            echo "ERROR decoding share code: " . $e->getMessage() . "<br>";
        }
    }

    // Convert a share code to its byte representation for comparison
    public static function shareCodeToBytes(string $shareCode): array {
        // Remove "CSGO-" prefix and "-" separators
        $code = str_replace("-", "", substr($shareCode, 5));

        // Convert to numeric value
        $big = "0";
        $dictLength = (string)strlen(Decoder::DICTIONARY);

        for ($i = 0; $i < strlen($code); $i++) {
            $c = $code[$i];
            $idx = strpos(Decoder::DICTIONARY, $c);
            if ($idx === false) {
                throw new Exception("Invalid character: $c");
            }
            $big = bcmul($big, $dictLength);
            $big = bcadd($big, (string)$idx);
        }

        // Convert to bytes
        $bytes = [];
        for ($i = 0; $i < 18; $i++) {
            $bytes[$i] = 0; // Initialize with zeros
        }

        for ($i = 0; $i < 18 && bccomp($big, "0") > 0; $i++) {
            $mod = bcmod($big, "256");
            $big = bcdiv($big, "256", 0);
            $bytes[17 - $i] = (int)$mod;
        }

        return $bytes;
    }

    // Compare two share codes in detail
    public static function compareShareCodes(string $original, string $reEncoded) {
        echo "<br>Detailed comparison of share codes:<br>";

        try {
            $originalBytes = self::shareCodeToBytes($original);
            $reEncodedBytes = self::shareCodeToBytes($reEncoded);

            echo "Byte-by-byte comparison:<br>";
            echo "Index | Original | Re-encoded | Match? | Description<br>";
            echo "------------------------------------------------<br>";

            $descriptions = [
                0 => "Checksum",
                1 => "Version",
                2 => "Gap value",
                3 => "Outline × 2",
                4 => "Red color",
                5 => "Green color",
                6 => "Blue color",
                7 => "Alpha",
                8 => "Split distance",
                9 => "Fixed crosshair gap",
                10 => "Color & OutlineFlag & InnerSplitAlpha",
                11 => "OuterSplitAlpha & SplitSizeRatio",
                12 => "Thickness",
                13 => "Style & Flags",
                14 => "Length",
                15 => "Reserved (0)",
                16 => "Reserved (0)",
                17 => "Reserved (0)"
            ];

            for ($i = 0; $i < 18; $i++) {
                $original_byte = isset($originalBytes[$i]) ? $originalBytes[$i] : 0;
                $reEncoded_byte = isset($reEncodedBytes[$i]) ? $reEncodedBytes[$i] : 0;
                $match = $original_byte === $reEncoded_byte ? "Yes" : "No";
                $desc = isset($descriptions[$i]) ? $descriptions[$i] : "Unknown";

                echo sprintf("%5d | %8d | %10d | %6s | %s<br>",
                    $i, $original_byte, $reEncoded_byte, $match, $desc);

                if ($match === "No") {
                    self::explainByteDifference($i, $original_byte, $reEncoded_byte);
                }
            }

            // Also compare the crosshair objects
            $originalCrosshair = Decoder::decode($original);
            $reEncodedCrosshair = Decoder::decode($reEncoded);

            echo "<br>Crosshair property comparison:<br>";
            self::compareCrosshairs($originalCrosshair, $reEncodedCrosshair);

        } catch (Exception $e) {
            echo "Error comparing share codes: " . $e->getMessage() . "<br>";
        }
    }

    // Provide detailed explanation of byte differences
    private static function explainByteDifference(int $index, int $originalByte, int $reEncodedByte) {
        echo "  Difference details for byte $index:<br>";

        // Check bit-by-bit differences
        $originalBits = sprintf("%08b", $originalByte);
        $reEncodedBits = sprintf("%08b", $reEncodedByte);

        echo "    Original bits:  $originalBits<br>";
        echo "    Re-encoded bits: $reEncodedBits<br>";

        // Special explanations for each byte index
        switch ($index) {
            case 0:
                echo "    This is the checksum byte. Difference suggests calculation issue.<br>";
                break;
            case 2:
                $originalGap = ($originalByte > 127 ? $originalByte - 256 : $originalByte) / 10.0;
                $reEncodedGap = ($reEncodedByte > 127 ? $reEncodedByte - 256 : $reEncodedByte) / 10.0;
                echo "    Gap value: original=$originalGap, re-encoded=$reEncodedGap<br>";
                break;
            case 3:
                $originalOutline = $originalByte / 2.0;
                $reEncodedOutline = $reEncodedByte / 2.0;
                echo "    Outline value: original=$originalOutline, re-encoded=$reEncodedOutline<br>";
                break;
            case 10:
                $originalColor = $originalByte & 7;
                $reEncodedColor = $reEncodedByte & 7;
                $originalOutline = ($originalByte & 8) !== 0;
                $reEncodedOutline = ($reEncodedByte & 8) !== 0;
                $originalInnerSplit = ($originalByte >> 4) / 10.0;
                $reEncodedInnerSplit = ($reEncodedByte >> 4) / 10.0;

                echo "    Color: original=$originalColor, re-encoded=$reEncodedColor<br>";
                echo "    Has Outline: original=" . ($originalOutline ? "true" : "false") .
                     ", re-encoded=" . ($reEncodedOutline ? "true" : "false") . "<br>";
                echo "    Inner Split Alpha: original=$originalInnerSplit, re-encoded=$reEncodedInnerSplit<br>";
                break;
            case 11:
                $originalOuterSplit = $originalByte & 0xF;
                $reEncodedOuterSplit = $reEncodedByte & 0xF;
                $originalSplitSize = $originalByte >> 4;
                $reEncodedSplitSize = $reEncodedByte >> 4;

                echo "    Outer Split Alpha: original=" . ($originalOuterSplit/10.0) .
                     ", re-encoded=" . ($reEncodedOuterSplit/10.0) . "<br>";
                echo "    Split Size Ratio: original=" . ($originalSplitSize/10.0) .
                     ", re-encoded=" . ($reEncodedSplitSize/10.0) . "<br>";
                break;
            case 13:
                $originalStyle = ($originalByte & 0x0F) >> 1;
                $reEncodedStyle = ($reEncodedByte & 0x0F) >> 1;
                $originalFlags = ($originalByte & 0xF0) >> 4;
                $reEncodedFlags = ($reEncodedByte & 0xF0) >> 4;

                echo "    Style: original=$originalStyle, re-encoded=$reEncodedStyle<br>";
                echo "    Flags: original=" . sprintf("%04b", $originalFlags) .
                     ", re-encoded=" . sprintf("%04b", $reEncodedFlags) . "<br>";

                $flagNames = ["HasCenterDot", "DeployedWeaponGapEnabled", "HasAlpha", "IsTStyle"];
                for ($i = 0; $i < 4; $i++) {
                    $originalFlag = ($originalFlags & (1 << $i)) !== 0;
                    $reEncodedFlag = ($reEncodedFlags & (1 << $i)) !== 0;
                    if ($originalFlag !== $reEncodedFlag) {
                        echo "    Flag mismatch: $flagNames[$i] - original=" .
                             ($originalFlag ? "true" : "false") . ", re-encoded=" .
                             ($reEncodedFlag ? "true" : "false") . "<br>";
                    }
                }
                break;
        }
    }

    // Compare the two crosshair objects
    private static function compareCrosshairs(Crosshair $original, Crosshair $reEncoded) {
        $reflection = new ReflectionClass(Crosshair::class);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        echo "Property | Original | Re-encoded | Match?<br>";
        echo "----------------------------------------<br>";

        foreach ($properties as $property) {
            $name = $property->getName();
            $originalValue = $property->getValue($original);
            $reEncodedValue = $property->getValue($reEncoded);

            if (is_bool($originalValue)) {
                $originalStr = $originalValue ? "true" : "false";
                $reEncodedStr = $reEncodedValue ? "true" : "false";
            } else {
                $originalStr = (string)$originalValue;
                $reEncodedStr = (string)$reEncodedValue;
            }

            $match = $originalValue === $reEncodedValue ? "Yes" : "No";

            echo sprintf("%-20s | %-10s | %-10s | %s<br>",
                $name, $originalStr, $reEncodedStr, $match);
        }
    }

    // Test just the decoding of a share code
    public static function testDecode(string $shareCode) {
        try {
            $crosshair = Decoder::decode($shareCode);
            echo "Decoded crosshair:<br>";
            echo $crosshair . "<br>";
            echo "<br>Config string:<br>";
            echo $crosshair->toConfigString() . "<br>";
        } catch (Exception $e) {
            echo "Error decoding: " . $e->getMessage() . "<br>";
        }
    }

    // Test just the encoding of a crosshair
    public static function testEncode(Crosshair $crosshair) {
        try {
            $encoded = Encoder::encode($crosshair);
            echo "Encoded share code: $encoded<br>";
        } catch (Exception $e) {
            echo "Error encoding: " . $e->getMessage() . "<br>";
        }
    }

    // Generate byte dump of a share code - useful for deeper analysis
    public static function byteDump(string $shareCode) {
        try {
            $bytes = self::shareCodeToBytes($shareCode);
            echo "Byte dump for $shareCode:<br>";
            echo "Index | Value (dec) | Value (hex) | Value (bin)<br>";
            echo "-----------------------------------------<br>";

            for ($i = 0; $i < count($bytes); $i++) {
                $byte = $bytes[$i];
                echo sprintf("%5d | %11d | %10s | %s<br>",
                    $i, $byte, "0x" . dechex($byte), sprintf("%08b", $byte));
            }
        } catch (Exception $e) {
            echo "Error generating byte dump: " . $e->getMessage() . "<br>";
        }
    }

    // Create a simple test case with a known share code
    public static function createTestCase(string $shareCode) {
        try {
            $crosshair = Decoder::decode($shareCode);
            $properties = [];

            $reflection = new ReflectionClass(Crosshair::class);
            $props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

            foreach ($props as $prop) {
                $name = $prop->getName();
                $value = $prop->getValue($crosshair);
                if (is_bool($value)) {
                    $properties[$name] = $value ? "true" : "false";
                } else {
                    $properties[$name] = $value;
                }
            }

            echo "Test case for $shareCode:<br>";
            echo json_encode($properties, JSON_PRETTY_PRINT);

            echo "<br><br>PHP Code:<br>";
            echo '$testCrosshair = new Crosshair(' . "<br>";
            echo "    " . $crosshair->Style . ", // Style<br>";
            echo "    " . ($crosshair->HasCenterDot ? "true" : "false") . ", // HasCenterDot<br>";
            echo "    " . $crosshair->Length . ", // Length<br>";
            echo "    " . $crosshair->Thickness . ", // Thickness<br>";
            echo "    " . $crosshair->Gap . ", // Gap<br>";
            echo "    " . ($crosshair->HasOutline ? "true" : "false") . ", // HasOutline<br>";
            echo "    " . $crosshair->Outline . ", // Outline<br>";
            echo "    " . $crosshair->Red . ", // Red<br>";
            echo "    " . $crosshair->Green . ", // Green<br>";
            echo "    " . $crosshair->Blue . ", // Blue<br>";
            echo "    " . ($crosshair->HasAlpha ? "true" : "false") . ", // HasAlpha<br>";
            echo "    " . $crosshair->Alpha . ", // Alpha<br>";
            echo "    " . $crosshair->SplitDistance . ", // SplitDistance<br>";
            echo "    " . $crosshair->FixedCrosshairGap . ", // FixedCrosshairGap<br>";
            echo "    " . $crosshair->Color . ", // Color<br>";
            echo "    " . $crosshair->InnerSplitAlpha . ", // InnerSplitAlpha<br>";
            echo "    " . $crosshair->OuterSplitAlpha . ", // OuterSplitAlpha<br>";
            echo "    " . $crosshair->SplitSizeRatio . ", // SplitSizeRatio<br>";
            echo "    " . ($crosshair->IsTStyle ? "true" : "false") . ", // IsTStyle<br>";
            echo "    " . ($crosshair->DeployedWeaponGapEnabled ? "true" : "false") . ", // DeployedWeaponGapEnabled<br>";
            echo "    " . ($crosshair->FollowRecoil ? "true" : "false") . " // FollowRecoil<br>";
            echo ");<br>";

        } catch (Exception $e) {
            echo "Error creating test case: " . $e->getMessage() . "<br>";
        }
    }
}
