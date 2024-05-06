<?php

$timeStart = microtime(true);

echo "Generating datasets...\n";

createFunctionDataset();
downloadFunctionIndexes();
parseFunctionIndexes();

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

function parseFunctionIndexes(): void
{
    $cacheDir = __DIR__ . '/../data/cache';
    $cacheFile = $cacheDir . '/manual.indexes.functions.php.html';

    $html = file_get_contents($cacheFile);

    $start = '<h2 class="title">Function and Method listing</h2>';
    $end = '<h3 class="title">Improve This Page</h3>';
    $html = substr($html, strpos($html, $start) + strlen($start));
    $html = substr($html, 0, strpos($html, $end));

    // Remove newlines inside <li> and </li> tags
    $html = preg_replace('/(<li>.*?)(\R+)(.*?<\/li>)/s', '$1 $3', $html);

    $lines = explode("\n", $html);

    $relevantLines = [];
    foreach ($lines as $line) {
        if (str_contains($line, 'class="index"')) {
            $relevantLines[] = $line;
        }
    }

    $functions = [];
    foreach ($relevantLines as $line) {
        // example line: <li><a href='function.abs.php' class='index'>abs</a> - Absolute value</li>
        preg_match('/<li><a href="(.*?)" class="index">(.*?)<\/a> - (.*?)<\/li>/', $line, $matches);

        if (count($matches) !== 4) {
            throw new Exception('Invalid match count: ' . count($matches) . ' for line: ' . $line);
        }

        $functions[] = [
            'name' => $matches[2] ?: null,
            'url' => $matches[1] ?: null,
            'description' => $matches[3] ?: null,
        ];
    }

    assert(count($functions) > 1000);

    file_put_contents(__DIR__ . '/../data/php-functions-indexes.json', json_encode($functions, JSON_PRETTY_PRINT));
}

// Helper functions

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
