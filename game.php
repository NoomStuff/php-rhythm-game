<?php
// This is all some linux terminal bs to stop the game from flickering, allowing typing without enter and other weirdness 
$sttyMode = trim(shell_exec('stty -g'));
shell_exec('stty -icanon -echo');

function restoreTerminal() {
    global $sttyMode;
    echo "\033[?1049l\033[?25h";
    shell_exec('stty ' . escapeshellarg($sttyMode));
}

register_shutdown_function('restoreTerminal');

if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
    pcntl_signal(SIGINT, fn() => exit(restoreTerminal() ?? 0));
}

stream_set_blocking(STDIN, false);
echo "\033[?1049h\033[?25l";
usleep(100000);


abstract class Renderer
{
    abstract public function render($game);
}

class StrumLine extends Renderer
{
    function render($game)
    {
        // Place the notes in a big ahh array
        $notes = Chart::getActiveNotes($game);

        $screen = [];
        for ($row = 0; $row < $game->windowHeight - 2; $row++)
        {
            $screen[$row] = array_fill(0, count($game->keybinds), ' ');
        }

        foreach ($notes as $note)
        {
            if ($note->lane >= count($game->keybinds) || isset($note->hitTime)) continue;

            $notePosition = (($note->position - $game->currentTime) / $game->scrollSpeed) - 1;
            $noteLane = (int) round($game->windowHeight - 3 - $notePosition);

            if ($noteLane >= 0 && $noteLane < $game->windowHeight - 2)
            {
                $screen[$noteLane][$note->lane] = '•';
            }
        }

        // Render from the array
        $padding = getStrumLinePadding($game, count($game->keybinds));
        foreach ($screen as $row)
        {
            echo $padding;
            foreach ($row as $cell)
            {
                echo $cell;
            }
            echo $padding . "\n";
        }
    }
}

class JudgementLine extends Renderer
{
    public function render($game)
    {
        echo getStrumLinePadding($game, count($game->keybinds));
        foreach ($game->keybinds as $keybind)
        {
            if (isset($game->keyStates[$keybind]))
            {
                echo "o";
            }
            else
            {
                echo "O";
            }
        }
        echo getStrumLinePadding($game, count($game->keybinds)) . "\n";
    }
}

class HUDRenderer extends Renderer
{
    private $ratingHandler;

    public function __construct($ratingHandler)
    {
        $this->ratingHandler = $ratingHandler;
    }

    public function render($game)
    {
        $latestHit = Chart::getLatestHit($game);
        $latestRating = $latestHit ? $this->ratingHandler->getRating($game, $latestHit->position, $latestHit->hitTime) : null;

        if ($latestHit != null && $game->currentTime - $latestHit->hitTime <= 1000)
        {
            if ($game->currentTime - $latestHit->hitTime < 100)
            {
                $rating = strtoupper($latestRating);
            }
            else
            {
                $rating = strtolower($latestRating);
            }
        }
        else
        {
            $rating = "....";
        }

        $score = str_pad($game->score, 6, " ", STR_PAD_LEFT);
        $combo = $game->combo > 1 ? str_pad($game->combo . "x", 4, " ", STR_PAD_RIGHT) : "    ";

        $hud = $score . " - " . $rating . " - " . $combo;
        echo getStrumLinePadding($game, strlen($hud)) . $hud . getStrumLinePadding($game, strlen($hud));
    }
}

class RatingHandler
{
    public function checkMisses($game)
    {
        foreach (Chart::getActiveNotes($game) as $note)
        {
            if (!isset($note->hitTime) && $game->currentTime - $note->position > $game->ratings["Bad"]["window"])
            {
                $note->hitTime = $game->currentTime;
                $game->score += $game->ratings["Miss"]["score"];
                $game->combo = 0;
            }
        }
    }

    public function judge($game)
    {
        foreach ($game->keybinds as $key)
        {
            if (!isset($game->keyStates[$key]))
            {
                continue; // key not pressed
            }

            $closestNote = null;
            $closestDistance = PHP_INT_MAX;

            $lane = array_search($key, $game->keybinds);
            foreach (Chart::getHittableNotes($game) as $note)
            {
                if ($note->lane != $lane || isset($note->hitTime))
                {
                    continue; // not the same lane or already hit
                }

                $distance = abs($note->position - $game->currentTime);
                if ($distance < $closestDistance)
                {
                    $closestDistance = $distance;
                    $closestNote = $note;
                }
            }

            if ($closestNote !== null)
            {
                $closestNote->hitTime = $game->currentTime;
                $ratingName = $this->getRating($game, $closestNote->position, $closestNote->hitTime);
                $game->score += $game->ratings[$ratingName]["score"];
                if ($game->ratings[$ratingName]["combo"])
                {
                    $game->combo++;
                }
                else
                {
                    $game->combo = 0;
                }
            }
        }
    }

    public function getRating($game, $targetTime, $hitTime)
    {
        $timeDifference = abs($targetTime - $hitTime);

        foreach ($game->ratings as $ratingName => $ratingData)
        {
            if ($timeDifference <= $ratingData["window"])
            {
                return $ratingName;
            }
        }

        return "Miss";
    }
}


class Chart
{
    static $notes = [];

    function loadChart($fileName = "chart.json")
    {
        try
        {
            if (!file_exists($fileName))
            {
                throw new Exception("Chart file not found: " . $fileName);
            }

            $json = file_get_contents($fileName);
            $data = json_decode($json, true);

            foreach ($data as $noteData)
            {
                $this->addNote($noteData["position"], $noteData["lane"]);
            }
        }
        catch (Exception $e)
        {
            echo "Error loading chart: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    function addNote($position, $lane)
    {
        $note = new Note();
        $note->position = $position;
        $note->lane = $lane;
        Chart::$notes[] = $note;
    }

    static function getActiveNotes($game)
    {
        $activeNotes = [];
        foreach (Chart::$notes as $note)
        {
            if (abs($note->position - $game->currentTime) <= $game->windowHeight * $game->scrollSpeed)
            {
                $activeNotes[] = $note;
            }
        }
        return $activeNotes;
    }

    static function getHittableNotes($game)
    {
        $activeNotes = [];
        foreach (Chart::$notes as $note)
        {
            if (abs($note->position - $game->currentTime) <= $game->ratings["Bad"]["window"] && !isset($note->hitTime))
            {
                $activeNotes[] = $note;
            }
        }
        return $activeNotes;
    }

    static function getLatestHit($game)
    {
        $latestHitTime = 0;
        $latestHit = null;

        foreach (Chart::$notes as $note)
        {
            if (isset($note->hitTime))
            {
                $hitTime = $note->hitTime;
                if ($hitTime >= $latestHitTime)
                {
                    $latestHitTime = $hitTime;
                    $latestHit = $note;
                }
            }
        }
        return $latestHit;
    }
}

class Note
{
    public $position; // position in the chart in ms
    public $lane; // index of the lane (0-3 for 4 keybinds)
    public $hitTime = null; // time when the note was hit, null if not hit yet
}

class Game
{
    public $start = 0;
    public $now = 0;

    public $currentTime = 0; // current time since start in ms
    public $keyStates = []; // array to track which keys are currently pressed

    public $windowWidth = 80; // Updates to amount of character per line in the terminal
    public $windowHeight = 24; // Updates to amount of available lines in the terminal

    public $score = 0;
    public $combo = 0;

    // Config
    public $keybinds = ["Q", "W", "O", "P"]; // keys that have to be pressed to hit the notes
    public $scrollSpeed = 125; // amount of time in ms between each row of characters
    public $countdown = 3000; // ms before the first note appears
    public $strumLinePosition = 0.5; // position of the strum line as a percentage of the window width (0.5 = middle)

    public $ratings = [
        "Epic" => ["display" => "Epic", "window" => 100, "score" => 200, "combo" => true],
        "Good" => ["display" => "Good", "window" => 200, "score" => 100, "combo" => true],
        "Bad" => ["display" => "Bad", "window" => 250, "score" => 50, "combo" => false],
        "Miss" => ["display" => "Miss", "window" => PHP_INT_MAX, "score" => -50, "combo" => false]
    ];
}

$game = new Game();
$game->start = microtime(true);

$strumLine = new StrumLine();
$judgementLine = new JudgementLine();
$ratingHandler = new RatingHandler();
$hudRenderer = new HUDRenderer($ratingHandler);
$chart = new Chart();
$chart->loadChart("chart.json");

// Get screen size before entering the game loop
getScreenSize($game);

const HOLD_TIMEOUT = 0.12;

while (true)
{
    // Input
    $char = fgetc(STDIN);

    if ($char !== false)
    {
        $key = strtoupper($char);
        $game->keyStates[$key] = microtime(true);
    }

    $now = microtime(true);
    $game->now = $now;
    $game->currentTime = ($now - $game->start) * 1000 - $game->countdown; // current time since start in ms

    $ratingHandler->judge($game);
    $ratingHandler->checkMisses($game);

    foreach ($game->keyStates as $key => $time)
    {
        if (($now - $time) > HOLD_TIMEOUT)
        {
            unset($game->keyStates[$key]);
        }
    }

    getScreenSize($game);

    render($game, $strumLine, $judgementLine, $hudRenderer);

    usleep(1000);
}

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
    $size = trim(shell_exec('stty size'));
    if ($size)
    {
        [$rows, $cols] = explode(' ', $size);
        $game->windowHeight = (int)$rows;
        $game->windowWidth = (int)$cols;
    }
    else // fallback
    {
        $game->windowHeight = 24;
        $game->windowWidth = 80;
    }
}

function render($game, $strumLine, $judgementLine, $hudRenderer)
{
    static $cleared = false;

    // clear only on first frame to avoid flicker, then home cursor
    if (!$cleared)
    {
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
