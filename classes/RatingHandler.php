<?php

class RatingHandler
{
    /**
     * Marks notes beyond the hit window as misses.
     *
     * @param Game $game Current game state.
     * @return void
     */
    public function checkMisses($game)
    {
        foreach (Chart::getActiveNotes($game) as $note)
        {
            if (!isset($note->hitTime) && $game->currentTime - $note->position > $game->ratings["Shit"]["hitHindow"])
            {
                $note->hitTime = $game->currentTime;
                $game->score += $game->ratings["Miss"]["scoreValue"];
                $game->combo = 0;
            }
        }
    }

    /**
     * Judges key presses against nearest note.
     *
     * @param Game $game Current game state.
     * @return void
     */
    public function judge($game)
    {
        foreach (array_keys($game->keyPresses) as $key)
        {
            if (!in_array($key, $game->keybinds, true))
            {
                continue; // ignore keys that are not bound to lanes
            }

            $closestNote = null;
            $closestDistance = PHP_INT_MAX;

            $lane = array_search($key, $game->keybinds, true);
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
                // Get the rating for this hit
                $closestNote->hitTime = $game->currentTime;
                $ratingName = $this->getRating($game, $closestNote->position, $closestNote->hitTime);
                
                if ($game->ratings[$ratingName]["keepCombo"])
                {
                    $game->combo++;
                }
                else
                {
                    $game->combo = 0;
                }

                if ($game->ratings[$ratingName]["scoreValue"] > 0)
                {
                    $game->score += $game->ratings[$ratingName]["scoreValue"] * ($game->combo * $game->comboMultiplier + 1);
                }
                else
                {
                    $game->score += $game->ratings[$ratingName]["scoreValue"];
                }
            }

            unset($game->keyPresses[$key]);
        }
    }

    /**
     * Resolves a rating name from the timing delta between target and hit.
     *
     * @param Game $game Current game state.
     * @param int|float $targetTime Note target timestamp in milliseconds.
     * @param int|float $hitTime Actual hit timestamp in milliseconds.
     * @return string Rating key.
     */
    public function getRating($game, $targetTime, $hitTime)
    {
        $timeDifference = abs($targetTime - $hitTime);

        foreach ($game->ratings as $ratingName => $ratingData)
        {
            if ($timeDifference <= $ratingData["hitHindow"])
            {
                return $ratingName;
            }
        }

        return "Miss";
    }
}
