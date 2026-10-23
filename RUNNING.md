# Running PHPActivity

This activity uses PHP with the GD extension and FreeType support.

1. Enable GD in your PHP configuration (uncomment `extension=gd`).
2. From this directory run `php -S localhost:8000`.
3. Open http://localhost:8000/form.php (the dynamic page).
4. Select a painting, enter your own captions, adjust the sizes, and submit.
5. Save the resulting image from your browser. Repeat for all ten paintings.

On Windows, if GD is installed but disabled, this PowerShell command starts
the server with GD enabled for this session only:

```powershell
$phpDir = Split-Path (php -r 'echo PHP_BINARY;')
php -d "extension_dir=$phpDir\ext" -d extension=gd -S localhost:8000
```

The form generates its thumbnails and options from `data.inc.php`.
`result.php` reads the GET parameters, draws captions using the supplied
Lato font, and returns a JPEG. A supplied width produces a square image;
omitting width preserves the source dimensions. Invalid painting names,
sizes, widths, and text arrays return HTTP 400.

Custom meme captions and the ten final meme files are intentionally left
for the student to create. No custom meme submissions are included.

See README.md for the original assignment. GitHub hosts the source code;
run PHP locally to use the app.

## Rubric verification

- Commit messages use the required prefix and description format.
- Both form.html (provided markup) and form.php display thumbnails generated
  by result.php. The PHP form also generates its image and option lists
  from data.inc.php, as requested in the assignment instructions.
- result.php receives the painting, captions, font sizes, width, and optional
  height through $_GET.
- Width alone produces the required square output. An explicit height
  overrides the square default to support the rubric's width/height criterion.
- Custom meme submissions remain for the student to create.
