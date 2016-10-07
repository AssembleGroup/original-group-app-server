<?php

return [
    'displayErrorDetails' => \Assemble\Server::$DEBUG,
    //'addContentLengthHeader' => false,
    // Monolog settings
    'logger' => [
        'name' => 'Ramble',
        'path' => __DIR__ . '/../../Logs/assemble.log',
    ],
    'debug' => [
        'revealHttpVariables' => \Assemble\Server::$DEBUG
    ],
];