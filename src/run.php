<?php

$timeStart = microtime(true);

echo "Generating datasets...\n";

createFunctionDataset();
downloadFunctionIndexes();

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

function downloadFunctionIndexes(): void
{
    $cacheDir = __DIR__ . '/../data/cache';
    $cacheFile = $cacheDir . '/manual.indexes.functions.php.html';
    if (! file_exists($cacheFile)) {
        file_put_contents($cacheFile, file_get_contents('https://www.php.net/manual/en/indexes.functions.php'));
        assert(str_contains(file_get_contents($cacheFile), 'PHP: Function and Method listing - Manual'));
    }

    assert(filesize($cacheFile) > 1024);
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
