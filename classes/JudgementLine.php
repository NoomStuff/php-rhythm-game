<?php

class JudgementLine extends Renderer
{
    /**
     * Renders lane receptors and highlights keys currently held.
     *
     * @param Game $game Current game state.
     * @return void
     */
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