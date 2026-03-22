<?php

/**
 * Clears the terminal and moves the cursor to the home position.
 *
 * @return void
 */
function clearScreen()
{
    echo "\033[2J\033[H";
    flush();
}

/**
 * Calculates horizontal padding so the playfield aligns with the configured strum line position.
 *
 * @param Game $game Current game state.
 * @param int $lineWidth Width of the line content to align.
 * @return string Left/right padding string.
 */
function getStrumLinePadding($game, $lineWidth)
{
    $offset = (int) floor($game->windowWidth * $game->strumLinePosition - $lineWidth * $game->strumLinePosition);
    return str_repeat(" ", max(0, $offset));
}

/**
 * Polls terminal size periodically and updates the game window dimensions.
 *
 * @param Game $game Current game state.
 * @return void
 */
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

/**
 * Converts a Unicode code point to a UTF-8 character.
 *
 * @param int $code Unicode code point.
 * @return string UTF-8 encoded character.
 */
function unicode_chr($code)
{
    return iconv('UCS-4LE', 'UTF-8', pack('V', $code));
}

/**
 * Builds a braille glyph from a list of braille dot indices.
 *
 * @param int[] $dots Active braille dots from 1 to 8.
 * @return string Single braille character.
 */
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

/**
 * Renders the full frame buffer for the current game state.
 *
 * @param Game $game Current game state.
 * @param StrumLine $strumLine Note field renderer.
 * @param JudgementLine $judgementLine Judgement lane renderer.
 * @param HUDRenderer $hudRenderer Heads-up display renderer.
 * @return void
 */
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