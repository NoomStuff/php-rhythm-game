<?php

class Game
{
    public $start = 0;
    public $now = 0;

    public $currentTime = 0; // current time since start in ms
    public $keyStates = []; // array to track which keys are currently pressed
    public $holdTimeout = 0.12; // seconds a key stays active after press

    public $windowWidth = 80; // Updates to amount of character per line in the terminal
    public $windowHeight = 24; // Updates to amount of available lines in the terminal

    public $score = 0;
    public $combo = 0;

    // Config: These values get overwritten by settings.json
    public $keybinds = ["D", "F", "J", "K"]; // keys that have to be pressed to hit the notes, can be any number of keys
    public $scrollSpeed = 125; // amount of time in ms between each row of characters, this does not change the timing of the notes, only approach speed (space between notes)
    public $countdown = 3000; // time in ms to wait before the game starts
    public $strumLinePosition = 0.5; // position of the play field as a percentage of the window width (`0.5` = middle)
    public $advancedNoteDisplay = true; // whether to use braille characters for more precise note display, which allows notes to be at different positions within the same line

    public $ratings = [
        "Epic" => ["display" => "Epic", "window" => 100, "score" => 200, "combo" => true],
        "Good" => ["display" => "Good", "window" => 200, "score" => 100, "combo" => true],
        "Bad" => ["display" => "Bad", "window" => 250, "score" => 50, "combo" => false],
        "Miss" => ["display" => "Miss", "window" => PHP_INT_MAX, "score" => -50, "combo" => false]
    ];
}