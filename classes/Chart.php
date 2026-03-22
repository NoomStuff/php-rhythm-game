<?php

class Chart
{
    static $notes = [];

    /**
     * Loads notes from a JSON chart file into the shared note list.
     *
     * @param string $fileName Chart file path.
     * @return void
     */
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

            if (!is_array($data))
            {
                throw new Exception("Invalid chart format in: " . $fileName);
            }

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

    /**
     * Appends a single note to the chart note pool.
     *
     * @param int|float $position Note timestamp in milliseconds.
     * @param int $lane Lane index for the note.
     * @return void
     */
    function addNote($position, $lane)
    {
        $note = new Note();
        $note->position = $position;
        $note->lane = $lane;
        Chart::$notes[] = $note;
    }

    /**
     * Returns notes currently within the visible playfield window.
     *
     * @param Game $game Current game state.
     * @return Note[] Active notes.
     */
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

    /**
     * Returns notes that are within the largest hittable timing window.
     *
     * @param Game $game Current game state.
     * @return Note[] Hittable notes.
     */
    static function getHittableNotes($game)
    {
        $activeNotes = [];
        foreach (Chart::$notes as $note)
        {
            if (abs($note->position - $game->currentTime) <= $game->ratings["Shit"]["hitHindow"] && !isset($note->hitTime))
            {
                $activeNotes[] = $note;
            }
        }
        return $activeNotes;
    }

    /**
     * Returns the most recently hit note.
     *
     * @param Game $game Current game state.
     * @return Note|null Latest hit note, or null when none were hit.
     */
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