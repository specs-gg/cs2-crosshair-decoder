import re
from enum import Enum

class CrosshairDecoder:
    DICTIONARY = "ABCDEFGHJKLMNOPQRSTUVWXYZabcdefhijkmnopqrstuvwxyz23456789"
    SHARECODE_PATTERN = re.compile(r"^CSGO(-?[\w]{5}){5}$")

    class CrosshairStyle(Enum):
        Default = 0
        DefaultStatic = 1
        Classic = 2
        ClassicDynamic = 3
        ClassicStatic = 4

    class Info:
        def __init__(self, style, has_center_dot, length, thickness, gap,
                     has_outline, outline, red, green, blue, has_alpha, alpha,
                     split_distance, inner_split_alpha, outer_split_alpha,
                     split_size_ratio, is_t_style):
            self.Style = style
            self.HasCenterDot = has_center_dot
            self.Length = length
            self.Thickness = thickness
            self.Gap = gap
            self.HasOutline = has_outline
            self.Outline = outline
            self.Red = red
            self.Green = green
            self.Blue = blue
            self.HasAlpha = has_alpha
            self.Alpha = alpha
            self.SplitDistance = split_distance
            self.InnerSplitAlpha = inner_split_alpha
            self.OuterSplitAlpha = outer_split_alpha
            self.SplitSizeRatio = split_size_ratio
            self.IsTStyle = is_t_style

        @staticmethod
        def from_bytes(b):
            outline = b[4] / 2.0
            red = b[5]
            green = b[6]
            blue = b[7]
            alpha = b[8]
            split_distance = b[9]
            inner_split_alpha = (b[11] >> 4) / 10.0
            has_outline = (b[11] & 8) != 0
            outer_split_alpha = (b[12] & 0xF) / 10.0
            split_size_ratio = (b[12] >> 4) / 10.0
            thickness = b[13] / 10.0
            length = b[15] / 10.0
            gap = CrosshairDecoder._to_signed(b[3]) / 10.0
            has_center_dot = ((b[14] >> 4) & 1) != 0
            has_alpha = ((b[14] >> 4) & 4) != 0
            is_t_style = ((b[14] >> 4) & 8) != 0
            style = CrosshairDecoder.CrosshairStyle((b[14] & 0xF) >> 1)
            return CrosshairDecoder.Info(style, has_center_dot, length, thickness, gap,
                                         has_outline, outline, red, green, blue, has_alpha,
                                         alpha, split_distance, inner_split_alpha, outer_split_alpha,
                                         split_size_ratio, is_t_style)

        def __str__(self):
            return (f"Style: {self.Style.name}, HasCenterDot: {self.HasCenterDot}, "
                    f"Length: {self.Length}, Thickness: {self.Thickness}, Gap: {self.Gap}, "
                    f"HasOutline: {self.HasOutline}, Outline: {self.Outline}, "
                    f"Red: {self.Red}, Green: {self.Green}, Blue: {self.Blue}, "
                    f"HasAlpha: {self.HasAlpha}, Alpha: {self.Alpha}, "
                    f"SplitDistance: {self.SplitDistance}, InnerSplitAlpha: {self.InnerSplitAlpha}, "
                    f"OuterSplitAlpha: {self.OuterSplitAlpha}, SplitSizeRatio: {self.SplitSizeRatio}, "
                    f"IsTStyle: {self.IsTStyle}")

    @staticmethod
    def _to_signed(val):
        return val - 256 if val > 127 else val

    @classmethod
    def decode(cls, share_code):
        if not cls.SHARECODE_PATTERN.match(share_code):
            raise Exception("Invalid share code")
        code = share_code[4:].replace("-", "")
        big = 0
        for c in reversed(code):
            idx = cls.DICTIONARY.find(c)
            if idx == -1:
                raise Exception(f"Invalid character: {c}")
            big = big * len(cls.DICTIONARY) + idx
        byte_length = max(1, (big.bit_length() + 7) // 8)
        bytes_le = list(big.to_bytes(byte_length, byteorder='little'))
        if len(bytes_le) == 18:
            bytes_le.append(0)
        bytes_arr = bytes_le[::-1]
        return cls.Info.from_bytes(bytes_arr)

if __name__ == "__main__":
    info = CrosshairDecoder.decode("CSGO-qyhQR-ykFSd-xweKy-YebYX-MDr7C")
    print(f"CSGO-qyhQR-ykFSd-xweKy-YebYX-MDr7C: {info}")
