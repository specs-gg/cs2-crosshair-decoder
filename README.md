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
const info = CrosshairDecoder.decode("CSGO-qyhQR-ykFSd-xweKy-YebYX-MDr7C");
console.log(info.toString());
```

### PHP

Use the decoder in your PHP project by including the file:

```php
<?php
require 'src/php/CrosshairDecoder.php';
$info = CrosshairDecoder::decode("CSGO-qyhQR-ykFSd-xweKy-YebYX-MDr7C");
echo $info;
```

### Python

Run the decoder script directly or import it as a module:

```bash
python src/python/crosshair_decoder.py
```

Or in your Python project:

```python
from crosshair_decoder import CrosshairDecoder
info = CrosshairDecoder.decode("CSGO-qyhQR-ykFSd-xweKy-YebYX-MDr7C")
print(info)
```

## Contributing

Contributions are welcome! If you have suggestions, pull requests, or bug reports, please open an issue or reach out via the repository’s discussion channels.

## License

This project is licensed under the GNU General Public License v3.0. For more information, see the `LICENSE` file.

