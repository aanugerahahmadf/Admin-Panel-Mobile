<?php

$files = glob(base_path('lang/*.json'));
foreach ($files as $file) {
    if (! file_exists($file)) {
        continue;
    }
    $data = json_decode(file_get_contents($file), true) ?? [];
    $cleaned = [];
    foreach ($data as $k => $v) {
        // Hapus key yang memiliki :: (namespace translation laravel/filament)
        // Hapus juga key yang ada spasi berlebihan dan \n akibat regex
        if (! str_contains($k, '::') && ! str_starts_with($k, 'validation.') && ! str_starts_with($k, 'pagination.')) {
            $cleaned[$k] = $v;
        }
    }
    file_put_contents($file, json_encode($cleaned, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo 'Cleaned '.basename($file)."\n";
}
