<?php
/**
 * Cache Clear Helper
 * Touch CSS files to update modification time
 */
$css_files = [
    'assets/css/style.css',
    'assets/css/layout.css',
    'assets/css/animations.css'
];

foreach ($css_files as $file) {
    if (file_exists($file)) {
        touch($file);
        echo "Updated: $file\n";
    } else {
        echo "Not found: $file\n";
    }
}

echo "\nCache cleared! Please hard refresh your browser (Ctrl+F5 or Cmd+Shift+R)\n";
?>

