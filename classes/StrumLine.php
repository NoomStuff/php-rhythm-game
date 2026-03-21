<?php

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