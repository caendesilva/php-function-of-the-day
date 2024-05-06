<?php

$timeStart = microtime(true);

echo "Generating datasets...\n";

createFunctionDataset();

echo "Done! (Completed in " . number_format((microtime(true) - $timeStart) * 1000, 2) . "ms)\n";

function createFunctionDataset(): void
{
    // Get all defined functions
    $functions = get_defined_functions();

    // Use only internal functions
    $functions = $functions['internal'];

    // Remove functions that are from extensions
    $loadedExtensions = get_loaded_extensions();
    $staticExtensions = ['datefmt', 'finfo', 'intlcal', 'intltz', 'mb', 'msgfmt', 'numfmt', 'transliterator'];
    $extensions = array_merge($loadedExtensions, $staticExtensions);
    $prefixes = array_map(fn (string $extension): string => $extension . '_', $extensions);
    $functions = array_filter($functions, fn (string $function) => ! str_starts_with_any($function, $prefixes));

    file_put_contents(__DIR__ . '/../data/php-functions.txt', implode("\n", $functions));
}

function str_starts_with_any(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if (str_starts_with($haystack, $needle)) {
            return true;
        }
    }

    return false;
}

/** @noinspection PhpUnused */
function dd($data): void
{
    var_dump($data);

    die(1);
}
