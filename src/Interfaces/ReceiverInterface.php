<?php

namespace SlimQueue\Interfaces;

use Bernard\Message;

interface ReceiverInterface
{
    /**
     * @param Message $message
     */
    public function receive(Message $message);
}
