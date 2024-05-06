<?php

$timeStart = microtime(true);

echo "Generating datasets...\n";

// Do stuff

echo "Done! (Completed in " . number_format((microtime(true) - $timeStart) * 1000, 2) . "ms)\n";
