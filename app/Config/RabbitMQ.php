<?php

namespace Config;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQ
{
    public static function getConnection()
    {
        $host = 'localhost';
        $port = '5672';
        $username = getenv('RABBITMQ_USERNAME');
        $password = getenv('RABBITMQ_PASSWORD');

        if (getenv('CI_ENVIRONMENT') === 'development') {
            $username = 'guest';
            $password = 'guest';
        }

        return new AMQPStreamConnection(
            $host, // RabbitMQ Host
            $port, // Port
            $username, // Username
            $password // Password
        );
    }
}
