<?php

/**
 * Registers a class autoloader that resolves class files from the classes directory.
 *
 * @param string $className Requested class name.
 * @return void
 */
spl_autoload_register(function ($className) {
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $className . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});