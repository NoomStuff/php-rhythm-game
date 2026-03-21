<?php

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