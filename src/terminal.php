<?php

/**
 * Configures the terminal for interactive gameplay.
 *
 * @return string Previous stty mode so it can be restored later.
 */
function setupTerminal()
{
    $sttyMode = trim((string) shell_exec('stty -g'));
    shell_exec('stty -icanon -echo');

    stream_set_blocking(STDIN, false);
    echo "\033[?1049h\033[?25l";
    usleep(100000);

    return $sttyMode;
}

/**
 * Restores terminal state after gameplay ends.
 *
 * @param string $sttyMode Previously saved stty mode.
 * @return void
 */
function restoreTerminal($sttyMode)
{
    echo "\033[?1049l\033[?25h";
    if ($sttyMode !== '') {
        shell_exec('stty ' . escapeshellarg($sttyMode));
    }
}