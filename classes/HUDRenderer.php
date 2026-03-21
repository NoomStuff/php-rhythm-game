<?php

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