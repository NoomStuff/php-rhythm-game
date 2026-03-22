<?php

/**
 * Loads JSON settings from disk.
 *
 * @param string $filePath Path to the settings file.
 * @return array Parsed settings, or an empty array when missing or invalid.
 */
function loadSettings($filePath)
{
    if (!file_exists($filePath))
    {
        return [];
    }

    $json = file_get_contents($filePath);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

/**
 * Applies user settings to the game object with basic validation and clamping.
 *
 * @param Game $game Current game state.
 * @param array $settings Parsed settings values.
 * @return void
 */
function applyGameSettings($game, $settings)
{
    if (isset($settings['keybinds']) && is_array($settings['keybinds']))
    {
        $game->keybinds = $settings['keybinds'];
    }

    if (isset($settings['scrollSpeed']) && is_numeric($settings['scrollSpeed']))
    {
        $game->scrollSpeed = (int) max(1, $settings['scrollSpeed']);
    }

    if (isset($settings['countdown']) && is_numeric($settings['countdown']))
    {
        $game->countdown = (int) max(0, $settings['countdown']);
    }

    if (isset($settings['strumLinePosition']) && is_numeric($settings['strumLinePosition']))
    {
        $game->strumLinePosition = (float) max(0, min(1, $settings['strumLinePosition']));
    }

    if (isset($settings['advancedNoteDisplay']) && is_bool($settings['advancedNoteDisplay']))
    {
        $game->advancedNoteDisplay = $settings['advancedNoteDisplay'];
    }
}
