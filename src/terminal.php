<?php

function setupTerminal()
{
    $sttyMode = trim((string) shell_exec('stty -g'));
    shell_exec('stty -icanon -echo');

    stream_set_blocking(STDIN, false);
    echo "\033[?1049h\033[?25l";
    usleep(100000);

    return $sttyMode;
}

function restoreTerminal($sttyMode)
{
    echo "\033[?1049l\033[?25h";
    if ($sttyMode !== '') {
        shell_exec('stty ' . escapeshellarg($sttyMode));
    }
}