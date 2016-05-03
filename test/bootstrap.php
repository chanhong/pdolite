<?php

require_once __DIR__ . '/../src/PdoLite.php';

spl_autoload_register(function($class) {
    if (strpos($class, 'PdoLite\\') === 0) {
        $dir = strcasecmp(substr($class, -4), 'Test') ? 'src/' : 'test/';
        $name = substr($class, strlen('PdoLite'));
        require __DIR__ . '/../' . $dir . strtr($name, '\\', DIRECTORY_SEPARATOR) . '.php';
    }
});
