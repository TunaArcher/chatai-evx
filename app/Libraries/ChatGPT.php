<?php

namespace App\Libraries;

use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class ChatGPT
{
    private $http;
    private $baseURL;
    private $channelAccessToken;
    private $debug = false;
    private $accessToekn;

    public function __construct($config)
    {
        $this->baseURL = 'https://api.openai.com/v1/chat/completions';
        $this->accessToekn = $config['GPTToken'];
        $this->http = new Client();
    }

    public function setDebug($value)
    {
        $this->debug = $value;
    }

    /*********************************************************************
     * 1. Message | ส่งข้อความ
     */

    public function message($messages)
    {
        try {

            $endPoint = $this->baseURL . '/message';
            $headers = [
                'Authorization' => "Bearer " . $this->accessToekn,
                'Content-Type' => 'application/json',
            ];

            // กำหนดข้อมูล Body ที่จะส่งไปยัง API
            $data = [
                'messages' => [
                    [
                        'type' => 'text',
                        'text' => $messages
                    ],
                ],
            ];

            // ส่งคำขอ POST ไปยัง API
            $response = $this->http->request('POST', $endPoint, [
                'headers' => $headers,
                'json' => $data, // ใช้ 'json' เพื่อแปลงข้อมูลให้อยู่ในรูปแบบ JSON
            ]);

            // แปลง Response กลับมาเป็น Object
            $responseData = json_decode($response->getBody());

            // ตรวจสอบสถานะ HTTP Code และข้อมูลใน Response
            $statusCode = $response->getStatusCode();
            if ($statusCode === 200 || isset($responseData->statusCode) && (int)$responseData->statusCode === 0) {
                return true; // ส่งข้อความสำเร็จ
            }

            // กรณีส่งข้อความล้มเหลว
            log_message('error', "Failed to send message to Line API: " . json_encode($responseData));
            return false;
        } catch (\Exception $e) {
            // จัดการข้อผิดพลาด
            log_message('error', 'ChatGPT::message error {message}', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function askChatGPT($question, $message_setting)
    {
        try {
            $message_user = $message_setting;
            // log_message("info", "message_setting: " . $message_user);
            $response = $this->http->post($this->baseURL, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToekn,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'ขุนเป็นพนักงานรถให้กับบริษัท EVX'
                        ],
                        [
                            "role" => "assistant",
                            "content" => 'GPT ตัวนี้ควรทำหน้าที่อะไรบ้างครับ เช่น:
                            ตอบคำถามเกี่ยวกับการใช้งานรถ EV
                            ช่วยแก้ปัญหาที่พบบ่อยของลูกค้า
                            ช่วยจัดการเส้นทางและการบำรุงรักษา
                            หรือมีหน้าที่พิเศษอื่น ๆ?
                            อยากให้ GPT นี้มีบุคลิกแบบไหนครับ เช่น สุภาพ เป็นกันเอง หรือเป็นมืออาชีพสุด ๆ?'
                        ],
                        [
                            'role' => 'user',
                            'content' =>  'ตกลง'
                        ],
                        [
                            'role' => 'user',
                            'content' =>  $question
                        ]
                    ]
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            return $responseBody['choices'][0]['message']['content'];
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
