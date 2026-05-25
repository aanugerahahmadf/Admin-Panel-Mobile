<?php
function get_dir_size($dir) {
    $size = 0;
    try {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );
        foreach ($files as $file) {
            $size += $file->getSize();
        }
    } catch (Exception $e) {
    }
    return $size;
}

$results = [];
foreach (glob('nativephp/android/app/*') as $file) {
    if (is_dir($file)) {
        $results[$file] = round(get_dir_size($file) / 1024 / 1024, 2);
    }
}
arsort($results);
print_r($results);
