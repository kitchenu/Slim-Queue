<?php

namespace SlimQueue;

use SlimQueue\SlimMessage;
use Psr\Container\ContainerInterface;

class JobReceiver
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function job(SlimMessage $message)
    {
        $class = $message->getClass();
        (new $class)($this->container, $message->all());
    }
}
