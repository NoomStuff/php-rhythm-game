<?php

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