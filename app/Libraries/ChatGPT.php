<?php

namespace App\Libraries;

use \GuzzleHttp\Client;
use \GuzzleHttp\Handler\CurlHandler;
use \GuzzleHttp\HandlerStack;
use \GuzzleHttp\Middleware;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

use function PHPSTORM_META\type;

class ChatGPT
{
    private $http;
    private $baseURL;
    private $channelAccessToken;
    private $debug = false;
    private $accessToken;

    public function __construct($config)
    {
        $this->baseURL = 'https://api.openai.com/v1/chat/completions';
        $this->accessToken = $config['GPTToken'];
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
                'Authorization' => "Bearer " . $this->accessToken,
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

    // public function _askChatGPT($question, $message_setting)
    // {
    //     try {

    //         // log_message("info", "message_setting: " . $message_user);
    //         $response = $this->http->post($this->baseURL, [
    //             'headers' => [
    //                 'Authorization' => "Bearer " . $this->accessToken,
    //                 'Content-Type'  => 'application/json',
    //             ],
    //             'json' => [
    //                 'model' => 'gpt-4o',
    //                 'messages' => [
    //                     [
    //                         'role' => 'system',
    //                         'content' => $message_setting
    //                     ],
    //                     [
    //                         'role' => 'user',
    //                         'content' => 'งาน, เป้าหมาย, หรือ Prompt ปัจจุบัน:\n' . $question
    //                     ]
    //                 ]
    //             ]
    //         ]);

    //         $responseBody = json_decode($response->getBody(), true);
    //         return $responseBody['choices'][0]['message']['content'];
    //     } catch (Exception $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }

    public function gennaratePromtChatGPT($question)
    {
        try {

            $response = $this->http->post($this->baseURL, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'คุณคือผู้สร้าง PROMPT จากข้อความของผู้ใช้งานเพื่อนำไปใช้งาน AI ต่อ'
                        ],
                        [
                            'role' => 'user',
                            'content' => 'Task, Goal, or Current Prompt:\n' .  $question
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

    public function gptBuilderChatGPT($question)
    {
        try {

            $response = $this->http->post($this->baseURL, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'คุณคือ GPT Builder ที่คอยรับคำสั่งแล้วสร้าง เอไอ จากผู้ใช้งานแล้วแจ้งผู้ว่าสำเร็จหรือไม่และถามว่าต้องการตั่งค่าเพิ่มเติมไหม'
                        ],
                        [
                            'role' => 'user',
                            'content' => 'Task, Goal, or Current Prompt:\n' . $question
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

    // public function _askChatGPTimg($question,  $message_setting, $file_name)
    // {

    //     $file_data = $this->_updateArrFileLink($file_name);
    //     // log_message("info", "message_data_json_php: " . $file_data);
    //     try {
    //         $response = $this->http->post($this->baseURL, [
    //             'headers' => [
    //                 'Authorization' => "Bearer " . $this->accessToken,
    //                 'Content-Type'  => 'application/json',
    //             ],
    //             'json' => [
    //                 'model' => 'gpt-4o',
    //                 'messages' => [
    //                     [
    //                         'role' => 'system',
    //                         'content' => $message_setting
    //                     ],
    //                     [
    //                         'role' => 'user',
    //                         'content' => [
    //                             [
    //                                 'type' => 'text',
    //                                 'text' => 'งาน, เป้าหมาย, หรือ Prompt ปัจจุบัน:\n' . $question
    //                             ],
    //                             $file_data
    //                         ]
    //                     ]
    //                 ]
    //             ]
    //         ]);

    //         $responseBody = json_decode($response->getBody(), true);
    //         return $responseBody['choices'][0]['message']['content'];
    //     } catch (Exception $e) {
    //         return 'Error: ' . $e->getMessage();
    //     }
    // }

    public function askChatGPTimgTraning($question,  $message_setting, $file_name)
    {

        try {
            $response = $this->http->post($this->baseURL, [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $message_setting
                        ],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => 'งาน, เป้าหมาย, หรือ Prompt ปัจจุบัน:\n' . $question
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => $file_name
                                    ]
                                ]
                            ]
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

    // private function _updateArrFileLink($file_names)
    // {
    //     $file_data = [];

    //     $file_names_splites = explode(',', $file_names);

    //     foreach ($file_names_splites as $file_names_splite) {

    //         $file_data +=  [
    //             'type' => 'image_url',
    //             'image_url' => [
    //                 'url' => $file_names_splite
    //             ]
    //         ];
    //     }

    //     return  $file_data;
    // }

    /*********************************************************************
     * 1. Completions
     */

    private function sendRequest($model, $messages)
    {
        try {

            $response = $this->http->post($this->baseURL, [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'messages' => $messages,
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            return $responseBody['choices'][0]['message']['content'] ?? 'No response';
        } catch (\Exception $e) {
            log_message('error', 'ChatGPT::sendRequest error {message}', ['message' => $e->getMessage()]);
            return 'Error: ' . $e->getMessage();
        }
    }

    public function askChatGPT($roomId, $question, $messageSetting = null, $fileNames = null)
    {
        if (!$messageSetting) $messageSetting = '';

        // เพิ่ม System Prompt เป็นข้อความเริ่มต้น
        $messages = [
            ['role' => 'system', 'content' => $messageSetting]
        ];

        // ดึงประวัติแชทจาก Cache
        $chatHistory = $this->getChatHistory($roomId);

        // แปลงประวัติแชทให้อยู่ในรูปแบบที่ GPT รองรับ
        foreach ($chatHistory as &$msg) {
            // ตรวจสอบว่า content เป็น array หรือ string
            if (is_array($msg['content'])) {
                if (isset($msg['content'][0]['type']) && $msg['content'][0]['type'] === 'text') {
                    $msg['content'] = $msg['content'][0]['text']; // ดึงข้อความออกมา
                } else {
                    $msg['content'] = "[มีไฟล์แนบ]"; // หากเป็นรูปภาพให้ระบุว่าเป็นไฟล์แนบ
                }
            }
        }

        // เพิ่มข้อความของผู้ใช้
        $userContent = [['type' => 'text', 'text' => $question]];

        // ถ้ามีไฟล์ภาพ ให้เพิ่มข้อมูลภาพเข้าไป
        if (!empty($fileNames)) {
            $imageData = $this->formatImageLinks($fileNames);
            $userContent = array_merge($userContent, $imageData);
        }

        // เพิ่มข้อความของผู้ใช้ลงไปในแชท
        $chatHistory[] = [
            'role' => 'user',
            'content' => count($userContent) === 1 ? $userContent[0]['text'] : $userContent
        ];

        // รวมประวัติแชทที่แก้ไขแล้วกับ System Prompt
        $messages = array_merge($messages, $chatHistory);

        // ส่งข้อความไปยัง GPT
        $response = $this->sendRequest('gpt-4o', $messages);

        // เพิ่มข้อความของ AI ลงในประวัติแชท
        $chatHistory[] = [
            'role' => 'assistant',
            'content' => $response
        ];

        // อัปเดตประวัติการสนทนา (เก็บไว้ไม่เกิน 6 ข้อความ)
        $this->saveChatHistory($roomId, $chatHistory);

        return $response;
    }

    private function getChatHistory($roomId)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "chat_history_{$roomId}";

        // ดึงแชทเก่าจาก Cache
        $chatHistory = $cache->get($cacheKey);

        return $chatHistory ?: [];
    }

    private function saveChatHistory($roomId, $chatHistory)
    {
        $cache = \Config\Services::cache();
        $cacheKey = "chat_history_{$roomId}";

        // จำกัดแชทให้เหลือ 15 ข้อความล่าสุด
        $chatHistory = array_slice($chatHistory, -15);

        // บันทึกลง Cache (หมดอายุใน 7วัน)
        $cache->save($cacheKey, $chatHistory, 604800);
    }

    private function formatImageLinks($fileNames)
    {
        return array_map(function ($fileName) {
            return [
                'type' => 'image_url',
                'image_url' => ['url' => trim($fileName)]
            ];
        }, array_filter(explode(',', $fileNames), 'strlen'));
    }
}
