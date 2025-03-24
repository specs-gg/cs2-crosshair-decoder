<?php

class CrosshairDecoder {
    public const DICTIONARY = "ABCDEFGHJKLMNOPQRSTUVWXYZabcdefhijkmnopqrstuvwxyz23456789";
    public const SHARECODE_PATTERN = '/^CSGO(-?[\w]{5}){5}$/';

    public static function _toSigned(int $val): int {
        return $val > 127 ? $val - 256 : $val;
    }

    public static function decode(string $shareCode): CrosshairDecoderInfo {
        if (!preg_match(self::SHARECODE_PATTERN, $shareCode)) {
            throw new Exception("Invalid share code");
        }
        $code = str_replace("-", "", substr($shareCode, 4));
        $big = "0";
        $dictLength = (string)strlen(self::DICTIONARY);
        $codeLength = strlen($code);
        for ($i = $codeLength - 1; $i >= 0; $i--) {
            $c = $code[$i];
            $idx = strpos(self::DICTIONARY, $c);
            if ($idx === false) {
                throw new Exception("Invalid character: $c");
            }
            $big = bcmul($big, $dictLength);
            $big = bcadd($big, (string)$idx);
        }
        $bigFloat = (float)$big;
        $byteLength = max(1, (int)ceil((log($bigFloat, 2) + 1) / 8));
        $bytesLE = [];
        for ($i = 0; $i < $byteLength; $i++) {
            $mod = bcmod($big, "256");
            $bytesLE[] = (int)$mod;
            $big = bcdiv($big, "256", 0);
        }
        if (count($bytesLE) === 18) {
            $bytesLE[] = 0;
        }
        $bytesArr = array_reverse($bytesLE);
        return CrosshairDecoderInfo::fromBytes($bytesArr);
    }
}

class CrosshairDecoderInfo {
    public int $Style;
    public bool $HasCenterDot;
    public float $Length;
    public float $Thickness;
    public float $Gap;
    public bool $HasOutline;
    public float $Outline;
    public int $Red;
    public int $Green;
    public int $Blue;
    public bool $HasAlpha;
    public int $Alpha;
    public int $SplitDistance;
    public float $InnerSplitAlpha;
    public float $OuterSplitAlpha;
    public float $SplitSizeRatio;
    public bool $IsTStyle;

    public function __construct(
        int $style,
        bool $hasCenterDot,
        float $length,
        float $thickness,
        float $gap,
        bool $hasOutline,
        float $outline,
        int $red,
        int $green,
        int $blue,
        bool $hasAlpha,
        int $alpha,
        int $splitDistance,
        float $innerSplitAlpha,
        float $outerSplitAlpha,
        float $splitSizeRatio,
        bool $isTStyle
    ) {
        $this->Style = $style;
        $this->HasCenterDot = $hasCenterDot;
        $this->Length = $length;
        $this->Thickness = $thickness;
        $this->Gap = $gap;
        $this->HasOutline = $hasOutline;
        $this->Outline = $outline;
        $this->Red = $red;
        $this->Green = $green;
        $this->Blue = $blue;
        $this->HasAlpha = $hasAlpha;
        $this->Alpha = $alpha;
        $this->SplitDistance = $splitDistance;
        $this->InnerSplitAlpha = $innerSplitAlpha;
        $this->OuterSplitAlpha = $outerSplitAlpha;
        $this->SplitSizeRatio = $splitSizeRatio;
        $this->IsTStyle = $isTStyle;
    }

    public static function fromBytes(array $b): CrosshairDecoderInfo {
        $outline = $b[4] / 2.0;
        $red = $b[5];
        $green = $b[6];
        $blue = $b[7];
        $alpha = $b[8];
        $splitDistance = $b[9];
        $innerSplitAlpha = ($b[11] >> 4) / 10.0;
        $hasOutline = (($b[11] & 8) !== 0);
        $outerSplitAlpha = ($b[12] & 0xF) / 10.0;
        $splitSizeRatio = ($b[12] >> 4) / 10.0;
        $thickness = $b[13] / 10.0;
        $length = $b[15] / 10.0;
        $gap = CrosshairDecoder::_toSigned($b[3]) / 10.0;
        $hasCenterDot = ((($b[14] >> 4) & 1) !== 0);
        $hasAlpha = ((($b[14] >> 4) & 4) !== 0);
        $isTStyle = ((($b[14] >> 4) & 8) !== 0);
        $style = intdiv($b[14] & 0xF, 2);
        return new CrosshairDecoderInfo(
            $style,
            $hasCenterDot,
            $length,
            $thickness,
            $gap,
            $hasOutline,
            $outline,
            $red,
            $green,
            $blue,
            $hasAlpha,
            $alpha,
            $splitDistance,
            $innerSplitAlpha,
            $outerSplitAlpha,
            $splitSizeRatio,
            $isTStyle
        );
    }

    public function __toString(): string {
        return "Style: {$this->Style}, HasCenterDot: " . ($this->HasCenterDot ? "true" : "false") .
               ", Length: {$this->Length}, Thickness: {$this->Thickness}, Gap: {$this->Gap}, " .
               "HasOutline: " . ($this->HasOutline ? "true" : "false") . ", Outline: {$this->Outline}, " .
               "Red: {$this->Red}, Green: {$this->Green}, Blue: {$this->Blue}, " .
               "HasAlpha: " . ($this->HasAlpha ? "true" : "false") . ", Alpha: {$this->Alpha}, " .
               "SplitDistance: {$this->SplitDistance}, InnerSplitAlpha: {$this->InnerSplitAlpha}, " .
               "OuterSplitAlpha: {$this->OuterSplitAlpha}, SplitSizeRatio: {$this->SplitSizeRatio}, " .
               "IsTStyle: " . ($this->IsTStyle ? "true" : "false");
    }
}
