<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Models\MessageModel;
use App\Models\MessageRoomModel;

class RabbitMQConsumer extends BaseCommand
{
    protected $group       = 'RabbitMQ';
    protected $name        = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ and process AI response';

    public function run(array $params)
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // ประกาศ Queue
        $channel->queue_declare('ai_response_queue', false, true, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        // ฟังก์ชัน Callback เมื่อได้รับข้อความจาก Queue
        $callback = function (AMQPMessage $msg) {
            echo " [x] Processing message: ", $msg->body, "\n";

            // แปลง JSON เป็น Array
            $data = json_decode($msg->body, true);
            $this->processAIResponse($data['message_room'], $data['user_social']);

            echo " [✓] AI Response sent successfully!\n";
        };

        // Consumer รอรับข้อความ
        $channel->basic_consume('ai_response_queue', '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            try {
                $channel->wait();
            } catch (\Throwable $e) {
                log_message('error', "RabbitMQ Error: " . $e->getMessage());
            }
        }

        $channel->close();
        $connection->close();
    }

    private function processAIResponse($messageRoom, $userSocial)
    {
        helper('my_hashids');

        $messageRoom = json_decode(json_encode($messageRoom));
        $messageRoomID = $messageRoom->id;
        $userSocial = json_decode(json_encode($userSocial));

        $messageModel = new MessageModel();
        $messageRoomModel = new MessageRoomModel();

        $messageRoom = $messageRoomModel->getMessageRoomByID($messageRoomID);

        // ดึงข้อความล่าสุดของห้องแชท
        $lastContextTimestamp = $messageModel->lastContextTimestamp($messageRoomID);

        if (!$lastContextTimestamp) return;

        $timeoutSeconds = 5;
        sleep($timeoutSeconds);

        // ตรวจสอบว่ามีข้อความใหม่หรือไม่
        $newContextCount = $messageModel->newContextCount($messageRoomID, $lastContextTimestamp->_time);

        log_message('info', "Debug lastContextTimestamp {$lastContextTimestamp->_time} newContextCount: " . json_encode($newContextCount, JSON_PRETTY_PRINT));

        if ($newContextCount->_count > 0) {
            log_message('info', "timeout: ");
            // มีข้อความใหม่เข้ามาในช่วง Timeout
            return; // ถ้ามีข้อความใหม่ ให้รอไปก่อน
        }

        // AI ตอบ และ ลบบริบทหลังจากใช้งาน
        // $this->handleAIResponse($messageRoom, $userSocial);
        (new \App\Factories\HandlerFactory())
            ->createHandler($userSocial->platform, new \App\Services\MessageService())
            ->handleReplyByAI($messageRoom, $userSocial);
    }
}
