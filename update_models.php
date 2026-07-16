<?php

$dir = __DIR__ . '/app/Models/';
$files = glob($dir . '*.php');

foreach ($files as $file) {
    if (basename($file) == 'User.php') continue; // Skip user model for now
    
    $content = file_get_contents($file);
    if (strpos($content, '$guarded') === false) {
        $replacement = "class " . pathinfo($file, PATHINFO_FILENAME) . " extends Model\n{\n    protected \$guarded = [];";
        
        $pattern = "/class " . pathinfo($file, PATHINFO_FILENAME) . " extends Model\s*\{/";
        
        $newContent = preg_replace($pattern, $replacement, $content);
        file_put_contents($file, $newContent);
        echo "Updated " . basename($file) . "\n";
    }
}
