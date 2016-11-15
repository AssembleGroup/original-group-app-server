<?php

return [
    'displayErrorDetails' => \Assemble\Server::$DEBUG,
    //'addContentLengthHeader' => false,
    // Monolog settings
    'logger' => [
        'name' => 'Assemble',
        'path' => __DIR__ . '/../../../../Logs/' . date('d-m-y_') . 'assemble.log',
    ],
    'logDir' => __DIR__ . '/../../../../Logs/',
    'debug' => [
        'revealHttpVariables' => \Assemble\Server::$DEBUG
    ],
];