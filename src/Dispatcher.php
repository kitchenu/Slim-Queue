<?php

namespace SlimQueue;

use Bernard\Producer;
use SlimQueue\Interfaces\MessageFactoryInterface;

class Dispatcher
{
    /**
     * @var Producer
     */
    protected $producer;

    /**
     * @var MessageFactoryInterface
     */
    protected $messages;

    /**
     * @param Producer                 $producer
     * @param MessageFactoryInterface  $messages
     */
    public function __construct(Producer $producer, MessageFactoryInterface $messages)
    {
        $this->producer = $producer;
        $this->messages = $messages;
    }

    /**
     * @param string $class
     * @param array  $args
     */
    public function dispatch($class, $args)
    {
        $this->producer->produce($this->messages->create($class, $args));
    }
}