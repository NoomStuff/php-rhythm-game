<?php

class Note
{
    public $position; // position in the chart in ms
    public $lane; // index of the lane (0-3 for 4 keybinds)
    public $hitTime = null; // time when the note was hit, null if not hit yet
}