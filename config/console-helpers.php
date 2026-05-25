<?php

/** @return array<string, mixed> */

return [
    'yarn-path' => match (PHP_OS_FAMILY) {
        'Darwin' => '/opt/homebrew/bin/yarn',
        'Windows' => 'yarn',
        default => '/usr/bin/yarn',
    },
];
