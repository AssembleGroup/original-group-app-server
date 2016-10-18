<?php

return [
    'displayErrorDetails' => \Assemble\Server::$DEBUG,
    //'addContentLengthHeader' => false,
    // Monolog settings
    'logger' => [
        'name' => 'Assemble',
        'path' => __DIR__ . '/../../Logs/assemble.log',
    ],
    'debug' => [
        'revealHttpVariables' => \Assemble\Server::$DEBUG
    ],
];