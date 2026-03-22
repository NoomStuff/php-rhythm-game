<?php

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
