<?php

class StrumLine extends Renderer
{
    /**
        * Maps a sub-row phase to its braille bit mask.
     *
     * @param int $subPosition Sub-row position from 0 to 4.
     * @return int Braille bit mask.
     */
        function subNoteMask($subPosition)
    {
        switch ($subPosition)
        {
            case 0:
                return (1 << 0) | (1 << 3); // ⠉
            case 1:
                return (1 << 0) | (1 << 1) | (1 << 3) | (1 << 4); // ⠛
            case 2:
                return (1 << 1) | (1 << 2) | (1 << 4) | (1 << 5); // ⠶
            case 3:
                return (1 << 2) | (1 << 5) | (1 << 6) | (1 << 7); // ⣤
            case 4:
                return (1 << 6) | (1 << 7); // ⣀
            default:
                return 0;
        }
    }

    /**
     * Renders all visible notes.
     *
     * @param Game $game Current game state.
     * @return void
     */
    function render($game)
    {
        // Place the notes in a big ahh array
        $notes = Chart::getActiveNotes($game);
        $screen = [];

        for ($row = 0; $row < $game->windowHeight - 2; $row++)
        {
            $screen[$row] = array_fill(0, count($game->keybinds), ' ');
        }

        if ($game->advancedNoteDisplay)
        {
            $brailleMasks = [];
            for ($row = 0; $row < $game->windowHeight - 2; $row++)
            {
                $brailleMasks[$row] = array_fill(0, count($game->keybinds), 0);
            }

            // Render the notes using braille
            foreach ($notes as $note)
            {
                if ($note->lane >= count($game->keybinds) || isset($note->hitTime)) continue;

                // use the note position and time to determine which row it should be on
                $rawNotePosition = $note->position - $game->currentTime;
                $noteRow = (int) floor($game->windowHeight - 2 - ($rawNotePosition / $game->scrollSpeed));

                // take only the leftover inside the row and map it to 5 braille phases
                $subPosition = (int) floor((($game->windowHeight - 2 - ($rawNotePosition / $game->scrollSpeed)) - $noteRow) * 4 + 1);
                $subPosition = max(0, min(4, $subPosition));

                if ($noteRow >= 0 && $noteRow < $game->windowHeight - 2)
                {
                    $brailleMasks[$noteRow][$note->lane] |= $this->subNoteMask($subPosition);

                    // When the note crosses into the next row, render the top cap there too.
                    if ($subPosition === 4)
                    {
                        $nextRow = $noteRow + 1;
                        if ($nextRow >= 0 && $nextRow < $game->windowHeight - 2)
                        {
                            $brailleMasks[$nextRow][$note->lane] |= $this->subNoteMask(0);
                        }
                    }
                }
            }

            for ($row = 0; $row < $game->windowHeight - 2; $row++)
            {
                for ($lane = 0; $lane < count($game->keybinds); $lane++)
                {
                    $mask = $brailleMasks[$row][$lane];
                    if ($mask !== 0)
                    {
                        $screen[$row][$lane] = brailleFromMask($mask);
                    }
                }
            }
        }
        else
        {
            // Render the notes normally
            foreach ($notes as $note)
            {
                if ($note->lane >= count($game->keybinds) || isset($note->hitTime)) continue;

                // use the note position and time to determine which row it should be on
                $rawNotePosition = $note->position - $game->currentTime;
                $noteRow = (int) floor($game->windowHeight - 2 - ($rawNotePosition / $game->scrollSpeed));

                if ($noteRow >= 0 && $noteRow < $game->windowHeight - 2)
                {
                    $screen[$noteRow][$note->lane] = '•';
                }
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
