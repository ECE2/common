<?php

declare(strict_types=1);

return [
    'http' => [
        \Hyperf\Tracer\Middleware\TraceMiddleware::class,
    ],
    'jsonrpc-http' => [
        \Hyperf\Tracer\Middleware\TraceMiddleware::class,
    ],
];
