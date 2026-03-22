<?php

class StrumLine extends Renderer
{
    /**
     * Returns a braille character that represents one of the sub-row note offsets.
     *
     * @param int $subPosition Sub-row position from 0 to 4.
     * @return string Braille character for that position.
     */
    function getBrailleNote($subPosition)
    {
        switch ($subPosition)
        {
            case 0:
                $dots = [1, 4]; // ⠉
                break;
            case 1:
                $dots = [1, 4, 2, 5]; // ⠛
                break;
            case 2:
                $dots = [2, 5, 3, 6]; // ⠶
                break;
            case 3:
                $dots = [3, 6, 7, 8]; // ⣤
                break;
            case 4:
                $dots = [7, 8]; // ⣀
                break;
            default:
                $dots = [];
        }

        return constructBraille($dots);
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
            // Render the notes using braille
            foreach ($notes as $note)
            {
                if ($note->lane >= count($game->keybinds) || isset($note->hitTime)) continue;

                // use the note position and time to determine which row it should be on
                $rawNotePosition = $note->position - $game->currentTime;
                $noteRow = (int) floor($game->windowHeight - 2 - ($rawNotePosition / $game->scrollSpeed));

                // take only the leftover inside the row and map it to 5 braille phases
                $subPosition = (int) floor((($game->windowHeight - 2 - ($rawNotePosition / $game->scrollSpeed)) - $noteRow) * 5);
                $subPosition = max(0, min(4, $subPosition));

                if ($noteRow >= 0 && $noteRow < $game->windowHeight - 2)
                {
                    $screen[$noteRow][$note->lane] = $this->getBrailleNote($subPosition);
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
