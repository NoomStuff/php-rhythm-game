<?php

function clearScreen()
{
    echo "\033[2J\033[H";
    flush();
}

function getStrumLinePadding($game, $lineWidth)
{
    $offset = (int) floor($game->windowWidth * $game->strumLinePosition - $lineWidth * $game->strumLinePosition);
    return str_repeat(" ", max(0, $offset));
}

function getScreenSize($game)
{
    static $lastPollAt = 0;
    $now = hrtime(true);

    if (($now - $lastPollAt) >= 100000000) {
        $lastPollAt = $now;
        $size = trim((string) shell_exec('stty size'));
        if ($size) {
            $parts = explode(' ', $size);
            if (count($parts) === 2) {
                [$rows, $cols] = $parts;
                $game->windowHeight = (int) $rows;
                $game->windowWidth = (int) $cols;
                return;
            }
        }

        // fallback
        if ($game->windowHeight <= 0) {
            $game->windowHeight = 24;
        }
        if ($game->windowWidth <= 0) {
            $game->windowWidth = 80;
        }
    }
}

function unicode_chr($code)
{
    return iconv('UCS-4LE', 'UTF-8', pack('V', $code));
}

function constructBraille(array $dots)
{
    $base = 0x2800;
    $mask = 0;

    foreach ($dots as $dot)
    {
        $mask |= 1 << ($dot - 1);
    }

    return unicode_chr($base + $mask);
}

function renderScreen($game, $strumLine, $judgementLine, $hudRenderer)
{
    static $cleared = false;

    // clear only on first frame to avoid flicker, then home cursor
    if (!$cleared) {
        clearScreen();
        $cleared = true;
    }
    echo "\033[H";

    // buffer the whole frame and write once
    ob_start();
    $strumLine->render($game);
    $judgementLine->render($game);
    $hudRenderer->render($game);
    $frame = ob_get_clean();

    echo $frame;
}