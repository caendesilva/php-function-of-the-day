<?php

$timeStart = microtime(true);

echo "Generating datasets...\n";

createFunctionDataset();

echo "Done! (Completed in " . number_format((microtime(true) - $timeStart) * 1000, 2) . "ms)\n";

function createFunctionDataset()
{
    // Get all defined functions
    $functions = get_defined_functions();

    // Use only internal functions
    $functions = $functions['internal'];

    file_put_contents(__DIR__ . '/../data/php-functions.txt', implode("\n", $functions));
}

function dd($data)
{
    var_dump($data);
    die();
}
