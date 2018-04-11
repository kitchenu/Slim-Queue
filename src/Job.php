<?php

namespace SlimQueue;

use SlimQueue\Interfaces\JobInterface;
use Interop\Container\ContainerInterface;

abstract class Job implements JobInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $params;

    public function __construct(ContainerInterface $container, array $params)
    {
        $this->container = $container;
        $this->params = $params;
    }
}
