<?php

namespace SlimQueue;

use Bernard\Consumer;
use Bernard\QueueFactory;

class Worker
{
    /**
     * @var Consumer
     */
    protected $consumer;

    /**
     * @var QueueFactory
     */
    protected $queues;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $options;

    /**
     * 
     * @param Consumer     $consumer
     * @param QueueFactory $queues
     * @param string       $name
     * @param array        $options
     */
    public function __construct(Consumer $consumer, QueueFactory $queues, $name, array $options = [])
    {
        $this->consumer = $consumer;
        $this->queues = $queues;
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * Starts working
     *
     * @param Queue $queue
     * @param array $options
     */
    public function work()
    {
        $this->consumer->consume($this->queues->create($this->name), $this->options);
    }

    /**
     * Shutdown working
     */
    public function shutdown()
    {
        $this->consumer->shutdown();
    }

    /**
     * Pause working
     */
    public function pause()
    {
        $this->consumer->pause();
    }

    /**
     * Resume working
     */
    public function resume()
    {
        $this->consumer->resume();
    }
}