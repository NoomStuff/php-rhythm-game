<?php

class Game
{
    public $start = 0;
    public $now = 0;

    public $currentTime = 0; // current time since start in ms
    public $keyStates = []; // array to track which keys are currently pressed

    public $windowWidth = 80; // Updates to amount of character per line in the terminal
    public $windowHeight = 24; // Updates to amount of available lines in the terminal

    public $score = 0;
    public $combo = 0;

    // Config
    public $keybinds = ["Q", "W", "O", "P"]; // keys that have to be pressed to hit the notes
    public $scrollSpeed = 125; // amount of time in ms between each row of characters
    public $countdown = 3000; // ms before the first note appears
    public $strumLinePosition = 0.5; // position of the strum line as a percentage of the window width (0.5 = middle)
    public $holdTimeout = 0.12; // seconds a key stays active after press

    public $ratings = [
        "Epic" => ["display" => "Epic", "window" => 100, "score" => 200, "combo" => true],
        "Good" => ["display" => "Good", "window" => 200, "score" => 100, "combo" => true],
        "Bad" => ["display" => "Bad", "window" => 250, "score" => 50, "combo" => false],
        "Miss" => ["display" => "Miss", "window" => PHP_INT_MAX, "score" => -50, "combo" => false]
    ];
}