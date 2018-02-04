<?php

namespace SlimQueue\Interfaces;

interface MessageFactoryInterface
{
    /**
     * @param string $class
     * @param array  $args
     * 
     * @return \Bernard\Message
     */
    public function create($class, $args);
}
