<?php

require_once __DIR__ . '/src/autoload.php';
require_once __DIR__ . '/src/helpers.php';
require_once __DIR__ . '/src/settings.php';
require_once __DIR__ . '/src/terminal.php';

$devMode = false;

if ($devMode)
{
    // Don't mess with the terminal in dev mode so i can actually catch errors. Hope i don't forget to turn this off before committing :D
    $sttyMode = '';
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    stream_set_blocking(STDIN, false);
}
else
{
    // This is all some linux terminal bs to stop the game from flickering, allowing typing without enter and other weirdness
    $sttyMode = setupTerminal();
    register_shutdown_function(function () use (&$sttyMode)
    {
        restoreTerminal($sttyMode);
    });

    if (function_exists('pcntl_async_signals'))
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, function () use (&$sttyMode)
        {
            restoreTerminal($sttyMode);
            exit(0);
        });
    }
}

$game = new Game();
$game->start = hrtime(true);

$settings = loadSettings(__DIR__ . '/settings.json');
applyGameSettings($game, $settings);

$strumLine = new StrumLine();
$judgementLine = new JudgementLine();
$ratingHandler = new RatingHandler();
$hudRenderer = new HUDRenderer($ratingHandler);
$chart = new Chart();
$chart->loadChart("chart.json");

// Get screen size before entering the game loop
getScreenSize($game);

while (true)
{
    $frameNowNs = hrtime(true);
    $frameNowSeconds = $frameNowNs / 1000000000;

    // Input bs
    $char = fgetc(STDIN);

    if ($char !== false)
    {
        $key = strtoupper($char);
        $game->keyStates[$key] = $frameNowSeconds;
    }

    $game->now = $frameNowNs;
    $game->currentTime = (($frameNowNs - $game->start) / 1000000) - $game->countdown; // current time since start in ms

    $ratingHandler->judge($game);
    $ratingHandler->checkMisses($game);

    foreach ($game->keyStates as $key => $time)
    {
        if (($frameNowSeconds - $time) > $game->holdTimeout)
        {
            unset($game->keyStates[$key]);
        }
    }

    getScreenSize($game);
    renderScreen($game, $strumLine, $judgementLine, $hudRenderer);

    usleep(1000);
}
