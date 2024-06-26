<?php

$timeStart = microtime(true);

echo "Generating datasets...\n";

/**
 * We generate the dataset in advance for the entire year.
 *
 * We use this year as the seed for deterministic extracting random functions from the list,
 * this allows us to create pseudo-random datasets that are the same each time the script is run.
 */
const YEAR = 2024;

require_once __DIR__ . '/lib/dumper.php';

createFunctionDataset();
downloadFunctionIndexes();
parseFunctionIndexes();
createFinalDatabase();

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

        $index = md5($matches[2]);

        $functions[$index] = [
            'name' => $matches[2] ?: null,
            'url' => $matches[1] ?: null,
            'description' => $matches[3] ?: null,
        ];
    }

    assert(count($functions) > 1000);

    file_put_contents(__DIR__ . '/../data/php-function-indexes.json', json_encode($functions, JSON_PRETTY_PRINT));
}

function createFinalDatabase(): void
{
    $functions = explode("\n", trim(file_get_contents(__DIR__ . '/../data/php-functions.txt')));
    $common = explode("\n", trim(file_get_contents(__DIR__ . '/../data/top-100-functions.txt')));

    // Remove common functions from the list
    $functions = array_diff($functions, $common);

    // Remove undocumented functions from the list
    $indexes = json_decode(file_get_contents(__DIR__ . '/../data/php-function-indexes.json'), true);
    $functions = array_filter($functions, fn (string $function) => isset($indexes[md5($function)]));

    // Extract random functions from the list
    $functions = extractRandomItems($functions, getNumberOfDaysInYear(YEAR), YEAR);
    $functions = array_values($functions);
    deterministicShuffle($functions, YEAR);

    $indexes = json_decode(file_get_contents(__DIR__ . '/../data/php-function-indexes.json'), true);

    $database = [];

    foreach ($functions as $day => $function) {
        // Find the function in the index hash table
        $indexEntry = $indexes[md5($function)];

        $date = date('Y-m-d', mktime(0, 0, 0, 1, $day + 1, YEAR));

        $database[$date] = [
            'name' => $function,
            'url' => 'https://www.php.net/manual/en/' . $indexEntry['url'],
            'description' => $indexEntry['description'],
        ];
    }

    file_put_contents(__DIR__ . '/../data/database.json', json_encode($database, JSON_PRETTY_PRINT));
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

/**
 * Deterministic function to extract random items from an array.
 *
 * The same seed will result in the same items being extracted every time.
 */
function extractRandomItems(array $array, int $count, int $seed): array
{
    mt_srand($seed); // Set seed for deterministic randomness
    $keys = array_rand($array, $count); // Randomly select $count keys from the array
    $result = [];
    if (is_array($keys)) {
        foreach ($keys as $key) {
            $result[$key] = $array[$key]; // Extract corresponding items
        }
    } else {
        $result[$keys] = $array[$keys]; // Extract single item
    }
    return $result;
}

/**
 * Deterministic function to shuffle an array.
 *
 * The same seed will result in the same shuffled array every time.
 */
function deterministicShuffle(array &$array, int $seed): void
{
    mt_srand($seed); // Set seed for deterministic randomness
    $size = count($array);
    for ($i = $size - 1; $i > 0; $i--) {
        $j = mt_rand(0, $i); // Generate a random index within the range
        // Swap elements at $i and $j
        $temp = $array[$i];
        $array[$i] = $array[$j];
        $array[$j] = $temp;
    }
}

function getNumberOfDaysInYear(int $year): int
{
    // Creates a Unix timestamp for January 1st of the given year to check if it's a leap year
    $isLeapYear = date('L', mktime(0, 0, 0, 1, 1, $year));

    return $isLeapYear ? 366 : 365;
}
