<?php

namespace SlimQueue;

use ArrayAccess;
use Bernard\Message\AbstractMessage;

class SlimMessage extends AbstractMessage implements ArrayAccess
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $class; 

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @param string $name
     * @param string $class
     * @param array  $arguments
     */
    public function __construct($name, $class, array $arguments = [])
    {
        $this->name = $name; 
        $this->class = $class;
        $this->arguments = $arguments;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->arguments;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function get($name)
    {
        return $this->offsetGet($name);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return $this->offsetExists($name);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->arguments);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->arguments[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Message is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Message is immutable');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    public function __get($property)
    {
        return $this->offsetGet($property);
    }

    public function __set($property, $value)
    {
        $this->offsetSet($property, $value);
    }
}
