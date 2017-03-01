<?php


return [

    'plants' => [
        'server' => [
            'workers'  => 5,
            'user'     => 'www-data',
            'group'    => 'www-data',
            'workDir' => '/data/www',
            'logFile' => '/data/logs/server.log',
            'apps' => [
                'web' => [
                    \Arcus\Server\HTTPServer::class,
                    'postMaxSize' => 23 * GiB
                ]
            ]
        ]
    ]
];