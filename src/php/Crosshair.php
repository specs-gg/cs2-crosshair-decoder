<?php

class Crosshair {
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
    public float $FixedCrosshairGap;
    public int $Color;
    public float $InnerSplitAlpha;
    public float $OuterSplitAlpha;
    public float $SplitSizeRatio;
    public bool $IsTStyle;
    public bool $DeployedWeaponGapEnabled;
    public bool $FollowRecoil;

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
        float $fixedCrosshairGap,
        int $color,
        float $innerSplitAlpha,
        float $outerSplitAlpha,
        float $splitSizeRatio,
        bool $isTStyle,
        bool $deployedWeaponGapEnabled,
        bool $followRecoil
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
        $this->FixedCrosshairGap = $fixedCrosshairGap;
        $this->Color = $color;
        $this->InnerSplitAlpha = $innerSplitAlpha;
        $this->OuterSplitAlpha = $outerSplitAlpha;
        $this->SplitSizeRatio = $splitSizeRatio;
        $this->IsTStyle = $isTStyle;
        $this->DeployedWeaponGapEnabled = $deployedWeaponGapEnabled;
        $this->FollowRecoil = $followRecoil;
    }

    public function __toString(): string {
        return "Style: {$this->Style}, HasCenterDot: " . ($this->HasCenterDot ? "true" : "false") .
               ", Length: {$this->Length}, Thickness: {$this->Thickness}, Gap: {$this->Gap}, " .
               "HasOutline: " . ($this->HasOutline ? "true" : "false") . ", Outline: {$this->Outline}, " .
               "Red: {$this->Red}, Green: {$this->Green}, Blue: {$this->Blue}, " .
               "HasAlpha: " . ($this->HasAlpha ? "true" : "false") . ", Alpha: {$this->Alpha}, " .
               "SplitDistance: {$this->SplitDistance}, FixedCrosshairGap: {$this->FixedCrosshairGap}, " .
               "Color: {$this->Color}, InnerSplitAlpha: {$this->InnerSplitAlpha}, " .
               "OuterSplitAlpha: {$this->OuterSplitAlpha}, SplitSizeRatio: {$this->SplitSizeRatio}, " .
               "IsTStyle: " . ($this->IsTStyle ? "true" : "false") .
               ", DeployedWeaponGapEnabled: " . ($this->DeployedWeaponGapEnabled ? "true" : "false") .
               ", FollowRecoil: " . ($this->FollowRecoil ? "true" : "false");
    }

    public function toConfigString(): string {
        $lines = [];
        $lines[] = 'cl_crosshairstyle "' . $this->Style . '"';
        $lines[] = 'cl_crosshairdot "' . ($this->HasCenterDot ? "1" : "0") . '"';
        $lines[] = 'cl_crosshairsize "' . $this->Length . '"';
        $lines[] = 'cl_crosshairthickness "' . $this->Thickness . '"';
        $lines[] = 'cl_crosshairgap "' . $this->Gap . '"';
        $lines[] = 'cl_crosshair_drawoutline "' . ($this->HasOutline ? "1" : "0") . '"';
        $lines[] = 'cl_crosshair_outlinethickness "' . $this->Outline . '"';

        // If using a predefined color (not custom)
        if ($this->Color != 5) {
            $lines[] = 'cl_crosshaircolor "' . $this->Color . '"';
        } else {
            // Custom color RGB values
            $lines[] = 'cl_crosshaircolor "5"'; // 5 = custom
            $lines[] = 'cl_crosshaircolor_r "' . $this->Red . '"';
            $lines[] = 'cl_crosshaircolor_g "' . $this->Green . '"';
            $lines[] = 'cl_crosshaircolor_b "' . $this->Blue . '"';
        }

        $lines[] = 'cl_crosshairalpha "' . $this->Alpha . '"';
        $lines[] = 'cl_crosshair_dynamic_splitdist "' . $this->SplitDistance . '"';
        $lines[] = 'cl_fixedcrosshairgap "' . $this->FixedCrosshairGap . '"';
        $lines[] = 'cl_crosshair_dynamic_splitalpha_innermod "' . $this->InnerSplitAlpha . '"';
        $lines[] = 'cl_crosshair_dynamic_splitalpha_outermod "' . $this->OuterSplitAlpha . '"';
        $lines[] = 'cl_crosshair_dynamic_maxdist_splitratio "' . $this->SplitSizeRatio . '"';
        $lines[] = 'cl_crosshair_recoil "' . ($this->FollowRecoil ? "1" : "0") . '"';
        $lines[] = 'cl_crosshairgap_useweaponvalue "' . ($this->DeployedWeaponGapEnabled ? "1" : "0") . '"';
        $lines[] = 'cl_crosshairusealpha "' . ($this->HasAlpha ? "1" : "0") . '"';
        $lines[] = 'cl_crosshair_t "' . ($this->IsTStyle ? "1" : "0") . '"';
        return implode("\n", $lines);
    }
}
