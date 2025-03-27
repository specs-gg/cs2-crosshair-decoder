# CS2 Crosshair Decoder

CS2 Crosshair Decoder is a multi-language repository that provides decoders to convert encoded CS2 crosshair strings into structured, human-readable settings. The decoders extract parameters such as style, dimensions, color components, and additional attributes, and are implemented in JavaScript, PHP, and Python.

## Overview

The crosshair code used in CS2 is a compact representation of various settings, including:
- **Style:** Determines the type of crosshair (e.g., Classic, Dynamic, or Static).
- **Dimensions:** Includes measurements like gap, thickness, and length.
- **Color Components:** Extracts RGB values along with alpha (transparency) information.
- **Additional Attributes:** Such as center dot preference, outlines, and split style.

Each implementation leverages the strengths of its respective programming language:
- **JavaScript:** Uses modern ES6 classes and BigInt arithmetic.
- **PHP:** Leverages strict types and BCMath functions for big integer operations.
- **Python:** Adopts object-oriented design with enums and intuitive methods.

## Repository Structure

- **src/javascript/**
  Contains the JavaScript implementation (`crosshairDecoder.js`).

- **src/php/**
  Features the PHP implementation (`CrosshairDecoder.php`).

- **src/python/**
  Includes the Python implementation (`crosshair_decoder.py`).

## Usage

### JavaScript

You can import and use the decoder in your JavaScript project:

```javascript
const info = CrosshairDecoder.decode("CSGO-u6Njm-ssz8u-CN9eo-cWyr7-ZmRBD");
console.log(info.toString());
```

### PHP

Use the decoder and encoder in your PHP project by including the relevant files:

```php
<?php
require 'src/php/Decoder.php';
require 'src/php/Encoder.php';
use Decoder;
use Encoder;

$crosshair = Decoder::decode("CSGO-u6Njm-ssz8u-CN9eo-cWyr7-ZmRBD");
echo $crosshair;

// Optionally, generate an SVG representation:
// require 'src/php/SvgGenerator.php';
// echo SvgGenerator::generateSvg($crosshair);
```

## PHP Classes Overview

The PHP implementation provides several classes:

- **Crosshair:** Represents the crosshair settings.
- **Encoder:** Encodes a Crosshair object into a share code.
- **Decoder:** Decodes a share code into a Crosshair object.
- **SvgGenerator:** Generates an SVG representation of a crosshair.
- **Debug:** Provides utility methods for testing, debugging, and comparing share codes.

## Usage in PHP

### Decoding and Displaying a Crosshair
```php
<?php
require 'src/php/Decoder.php';
use Decoder;

$crosshair = Decoder::decode("CSGO-u6Njm-ssz8u-CN9eo-cWyr7-ZmRBD");
echo $crosshair;
```

### Encoding a Crosshair
```php
<?php
require 'src/php/Crosshair.php';
require 'src/php/Encoder.php';
use Encoder;

// Create a Crosshair instance with your parameters
$testCrosshair = new Crosshair(
    /* int */ 0,        // Style
    /* bool */ true,    // HasCenterDot
    /* float */ 5.0,    // Length
    /* float */ 1.5,    // Thickness
    /* float */ 2.0,    // Gap
    /* bool */ true,    // HasOutline
    /* float */ 1.0,    // Outline
    /* int */ 255,      // Red
    /* int */ 0,        // Green
    /* int */ 0,        // Blue
    /* bool */ false,   // HasAlpha
    /* int */ 255,      // Alpha
    /* int */ 10,       // SplitDistance
    /* float */ 0.0,    // FixedCrosshairGap
    /* int */ 5,        // Color (5 means custom)
    /* float */ 0.5,    // InnerSplitAlpha
    /* float */ 0.5,    // OuterSplitAlpha
    /* float */ 1.0,    // SplitSizeRatio
    /* bool */ false,   // IsTStyle
    /* bool */ false,   // DeployedWeaponGapEnabled
    /* bool */ false    // FollowRecoil
);
echo Encoder::encode($testCrosshair);
```

### Generating an SVG Representation
```php
<?php
require 'src/php/Decoder.php';
require 'src/php/SvgGenerator.php';
use SvgGenerator;
use Decoder;

$crosshair = Decoder::decode("CSGO-u6Njm-ssz8u-CN9eo-cWyr7-ZmRBD");
echo SvgGenerator::generateSvg($crosshair);
```

### Debugging Share Codes
```php
<?php
require 'src/php/Debug.php';
use Debug;

Debug::runTest("CSGO-u6Njm-ssz8u-CN9eo-cWyr7-ZmRBD");
```

### Python

Run the decoder script directly or import it as a module:

```bash
python src/python/crosshair_decoder.py
```

Or in your Python project:

```python
from crosshair_decoder import CrosshairDecoder
info = CrosshairDecoder.decode("CSGO-u6Njm-ssz8u-CN9eo-cWyr7-ZmRBD")
print(info)
```

## Contributing

Contributions are welcome! If you have suggestions, pull requests, or bug reports, please open an issue or reach out via the repository’s discussion channels.

## License

This project is licensed under the GNU General Public License v3.0. For more information, see the `LICENSE` file.
