class CrosshairDecoder {
	static DICTIONARY = "ABCDEFGHJKLMNOPQRSTUVWXYZabcdefhijkmnopqrstuvwxyz23456789";
	static SHARECODE_PATTERN = /^CSGO(-?[\w]{5}){5}$/;
	static CrosshairStyle = {
		Default: 0,
		DefaultStatic: 1,
		Classic: 2,
		ClassicDynamic: 3,
		ClassicStatic: 4
	};

	static _toSigned(val) {
		return val > 127 ? val - 256 : val;
	}

	static decode(shareCode) {
		if (!this.SHARECODE_PATTERN.test(shareCode)) {
			throw new Error("Invalid share code");
		}
		let code = shareCode.substring(4).replace(/-/g, "");
		let big = 0n;
		const dictLength = BigInt(this.DICTIONARY.length);
		for (let i = code.length - 1; i >= 0; i--) {
			const c = code[i];
			const idx = this.DICTIONARY.indexOf(c);
			if (idx === -1) throw new Error(`Invalid character: ${c}`);
			big = big * dictLength + BigInt(idx);
		}
		const bitLength = big.toString(2).length;
		const byteLength = Math.max(1, Math.ceil(bitLength / 8));
		const bytesLE = [];
		for (let i = 0; i < byteLength; i++) {
			bytesLE.push(Number((big >> BigInt(i * 8)) & 0xFFn));
		}
		if (bytesLE.length === 18) {
			bytesLE.push(0);
		}
		const bytesArr = bytesLE.reverse();
		return CrosshairDecoder.Info.fromBytes(bytesArr);
	}
}

CrosshairDecoder.Info = class {
	constructor(style, hasCenterDot, length, thickness, gap, hasOutline, outline,
	            red, green, blue, hasAlpha, alpha, splitDistance, innerSplitAlpha,
	            outerSplitAlpha, splitSizeRatio, isTStyle) {
		this.Style = style;
		this.HasCenterDot = hasCenterDot;
		this.Length = length;
		this.Thickness = thickness;
		this.Gap = gap;
		this.HasOutline = hasOutline;
		this.Outline = outline;
		this.Red = red;
		this.Green = green;
		this.Blue = blue;
		this.HasAlpha = hasAlpha;
		this.Alpha = alpha;
		this.SplitDistance = splitDistance;
		this.InnerSplitAlpha = innerSplitAlpha;
		this.OuterSplitAlpha = outerSplitAlpha;
		this.SplitSizeRatio = splitSizeRatio;
		this.IsTStyle = isTStyle;
	}

	static fromBytes(b) {
		const outline = b[4] / 2.0;
		const red = b[5];
		const green = b[6];
		const blue = b[7];
		const alpha = b[8];
		const splitDistance = b[9];
		const innerSplitAlpha = (b[11] >> 4) / 10.0;
		const hasOutline = (b[11] & 8) !== 0;
		const outerSplitAlpha = (b[12] & 0xF) / 10.0;
		const splitSizeRatio = (b[12] >> 4) / 10.0;
		const thickness = b[13] / 10.0;
		const length = b[15] / 10.0;
		const gap = CrosshairDecoder._toSigned(b[3]) / 10.0;
		const hasCenterDot = ((b[14] >> 4) & 1) !== 0;
		const hasAlpha = ((b[14] >> 4) & 4) !== 0;
		const isTStyle = ((b[14] >> 4) & 8) !== 0;
		const style = Math.floor((b[14] & 0xF) / 2);
		return new CrosshairDecoder.Info(style, hasCenterDot, length, thickness, gap,
		                                  hasOutline, outline, red, green, blue, hasAlpha,
		                                  alpha, splitDistance, innerSplitAlpha, outerSplitAlpha,
		                                  splitSizeRatio, isTStyle);
	}

	toString() {
		return `Style: ${this.Style}, HasCenterDot: ${this.HasCenterDot}, Length: ${this.Length}, ` +
		       `Thickness: ${this.Thickness}, Gap: ${this.Gap}, HasOutline: ${this.HasOutline}, ` +
		       `Outline: ${this.Outline}, Red: ${this.Red}, Green: ${this.Green}, Blue: ${this.Blue}, ` +
		       `HasAlpha: ${this.HasAlpha}, Alpha: ${this.Alpha}, SplitDistance: ${this.SplitDistance}, ` +
		       `InnerSplitAlpha: ${this.InnerSplitAlpha}, OuterSplitAlpha: ${this.OuterSplitAlpha}, ` +
		       `SplitSizeRatio: ${this.SplitSizeRatio}, IsTStyle: ${this.IsTStyle}`;
	}
};

// Export the class if using modules
// module.exports = CrosshairDecoder;
