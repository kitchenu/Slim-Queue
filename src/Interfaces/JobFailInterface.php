<?php

namespace SlimQueue\Interfaces;

interface JobFailInterface
{
    /**
     * @param \Throwable $exception
     */
    public function fail($exception = null);
}
