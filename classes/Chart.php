<?php

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
            if (abs($note->position - $game->currentTime) <= $game->ratings["Shit"]["hitHindow"] && !isset($note->hitTime))
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