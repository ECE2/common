<?php

declare(strict_types=1);

return [
    'handler' => [
        'http' => [
            Ece2\Common\Exception\Handler\NoPermissionExceptionHandler::class,
            Ece2\Common\Exception\Handler\NormalStatusExceptionHandler::class,
            Ece2\Common\Exception\Handler\TokenExceptionHandler::class,
            Ece2\Common\Exception\Handler\ValidationExceptionHandler::class,
            Ece2\Common\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
