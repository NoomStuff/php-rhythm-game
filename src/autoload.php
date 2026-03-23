<?php

spl_autoload_register(function ($className) {
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $className . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});