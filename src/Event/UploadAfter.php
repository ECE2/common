<?php

namespace Ece2\Common\Event;

class UploadAfter
{
    public function __construct(public array $fileInfo)
    {
    }
}
