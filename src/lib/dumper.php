<?php

use JetBrains\PhpStorm\NoReturn;

#[NoReturn]
function dd(...$data): void
{
    dump(...$data);

    die(1);
}

function dump(...$data): void
{
    foreach ($data as $item) {
        var_dump($item);
    }
}
