<?php

namespace SlimQueue;

use SlimQueue\Interfaces\MessageFactoryInterface;

class MessageFactory implements MessageFactoryInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function create($class, $args)
    {
        return new SlimMessage($this->name, $class, $args);
    }
}
