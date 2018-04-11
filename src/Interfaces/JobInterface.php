<?php

namespace SlimQueue\Interfaces;

use Interop\Container\ContainerInterface;

interface JobInterface
{
    public function __construct(ContainerInterface $container, array $params);

    public function handle();
}
