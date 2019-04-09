<?php

namespace go1\rest\util\rabbitmq;

use go1\rest\wrapper\request\Message;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Channel
{
    /** @var AMQPChannel */
    protected $ch;
    protected $exchange;
    protected $type;
    protected $passive    = false;
    protected $durable    = false;
    protected $autoDelete = false;

    public static function create(AMQPStreamConnection $con, string $exchange, string $type)
    {
        $_ = new static;
        $_->ch = $con->channel();
        $_->ch->exchange_declare(
            $_->exchange = $exchange,
            $_->type = $type,
            $_->passive,
            $_->durable,
            $_->autoDelete
        );

        return $_;
    }

    public function publish(Message $msg)
    {
        $this->ch->basic_publish($msg, $this->exchange, $msg->routingKey());
    }
}
