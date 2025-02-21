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
    private $baseQwenURL;
    private $channelAccessToken;
    private $debug = false;
    private $accessToken;
    private $accessQwenToken;

    public function __construct($config)
    {
        $this->baseURL = 'https://api.openai.com/v1/';
        $this->baseQwenURL = 'https://dashscope-intl.aliyuncs.com/';
        $this->accessToken = $config['GPTToken'];
        $this->accessQwenToken = $config['QWENToken'];
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

    public function runAssistant($thread_id, $assistant_id)
    {
        try {
            //Run the Assistant
            $response_run = $this->http->post($this->baseURL . "threads/$thread_id/runs", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'assistant_id' => $assistant_id,
                ]
            ]);

            $runResponse = json_decode($response_run->getBody(), true);
            $runId = $runResponse['id'] ?? null;

            // var_dump($runId);
            // exit;

            do {
                sleep(5);
                $response =  $this->http->get($this->baseURL . "/threads/$thread_id/runs/$runId", [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                        'OpenAI-Beta' => 'assistants=v2'
                    ],
                ]);

                $runStatus = json_decode($response->getBody(), true);
                $status = $runStatus['status'] ?? 'unknown';

                // echo "Assistant Run Status: $status\n";

                if (in_array($status, ['completed', 'failed', 'cancelled'])) {
                    break;
                }
            } while ($status === 'queued' || $status === 'in_progress');

            if ($status === 'completed') {
                return $status;
            } else {
                return  "Assistant ล้มเหลว: " . ($runStatus['last_error']['message'] ?? 'ไม่ทราบข้อผิดพลาด') . "\n";
            }
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function sendmessagetoThreadId($thread_id, $assistant_id)
    {

        try {
            $response_run_assistant = $this->runAssistant($thread_id, $assistant_id);
            $response_retrieve_assistant = "";
            if ($response_run_assistant == 'completed') {
                $response_retrieve_assistant = $this->retrieveAssistant($thread_id);
            } else {
                $response_retrieve_assistant = $response_run_assistant;
            }

            return $response_retrieve_assistant;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function retrieveAssistant($thread_id)
    {
        try {


            $response =  $this->http->get($this->baseURL . "threads/$thread_id/messages", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'OpenAI-Beta' => 'assistants=v2'
                ],
            ]);

            $messages = json_decode($response->getBody(), true)['data'] ?? [];

            $message_reply = "";

            foreach ($messages as $msg) {
                if ($msg['role'] === 'assistant') {
                    $message_reply .= $msg['content'][0]['text']['value'] . "\n";
                }
            }

            return $message_reply;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function askChatGPTTraininng($question, $message_setting)
    {
        try {

            // log_message("info", "message_setting: " . $message_user);
            $response = $this->http->post($this->baseURL . "chat/completions", [
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
                            'content' => 'งาน, เป้าหมาย, หรือ Prompt ปัจจุบัน:\n' . $question
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

    public function askQwenTraininng($question, $message_setting)
    {
        try {

            // log_message("info", "message_setting: " . $message_user);
            $response = $this->http->post($this->baseQwenURL . "compatible-mode/v1/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessQwenToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'qwen-plus',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $message_setting
                        ],
                        [
                            'role' => 'user',
                            'content' => 'งาน, เป้าหมาย, หรือ Prompt ปัจจุบัน:\n' . $question
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

    public function gennaratePromtChatGPT($question)
    {
        try {

            $response = $this->http->post($this->baseURL . "chat/completions", [
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

            $response = $this->http->post($this->baseURL . "chat/completions", [
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

    public function askChatGPTimgTraining($question,  $message_setting, $file_name)
    {

        try {
            $response = $this->http->post($this->baseURL . "chat/completions", [
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
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function askChatQwenimgTraining($question,  $message_setting, $file_name)
    {

        try {
            $response = $this->http->post($this->baseQwenURL . "compatible-mode/v1/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessQwenToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'model' => 'qwen-plus',
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
        } catch (\Exception $e) {
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

            $response = $this->http->post($this->baseURL . "chat/completions", [
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

    public function saveChatHistory($roomId, $chatHistory)
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

    public function createAssistantsFileSearch($user_id, $message_setting)
    {
        try {
            $response = $this->http->post($this->baseURL . 'assistants', [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'name' => 'assistant_' . $user_id,
                    'instructions' => $message_setting,
                    'tools' => [
                        ['type' => 'file_search']
                    ],
                    'model' => 'gpt-4-turbo'
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            return $responseBody['id'];
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function createVactorStore($user_id)
    {
        try {
            $dataResponse = [];
            //vactor store create
            $response = $this->http->post($this->baseURL . 'vector_stores', [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'name' => 'vactorstore' . $user_id
                ],
            ]);

            $vectorStoreResponse = json_decode($response->getBody(), true);
            $vectorStoreId = $vectorStoreResponse['id'] ?? null;

            if (!$vectorStoreId) {
                die("Failed to create Vector Store.\n");
            }


            $dataResponse = [
                'vactorstore_id' => $vectorStoreId
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function createVactorStoreQwen($user_id)
    {
        try {
            $dataResponse = [];
            //vactor store create
            $response = $this->http->post($this->baseURL . 'vector_stores', [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'name' => 'vactorstore' . $user_id
                ],
            ]);

            $vectorStoreResponse = json_decode($response->getBody(), true);
            $vectorStoreId = $vectorStoreResponse['id'] ?? null;

            if (!$vectorStoreId) {
                die("Failed to create Vector Store.\n");
            }


            $dataResponse = [
                'vactorstore_id' => $vectorStoreId
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function fileUpload($vectorStoreId,  $filePaths)
    {
        try {

            //Upload files and get file IDs
            $fileIds = [];

            foreach ($filePaths as $filePath) {
                $response = $this->http->post($this->baseURL . 'files', [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                    ],
                    'multipart' => [
                        [
                            'name' => 'purpose',
                            'contents' => 'assistants'
                        ],
                        [
                            'name' => 'file',
                            'contents' => fopen($filePath, 'r'),
                            'filename' => basename($filePath)
                        ],
                    ],
                ]);

                $fileResponse = json_decode($response->getBody(), true);
                $fileId = $fileResponse['id'] ?? null;

                if ($fileId) {
                    $fileIds[] = $fileId;
                    echo "File uploaded successfully! File ID: $fileId\n";
                } else {
                    echo "Failed to upload file: $filePath\n";
                }
            }

            if (empty($fileIds)) {
                die("No files were uploaded successfully.\n");
            }

            // Attach Files to Vector Store (Batch Upload)

            $file_id_response = "";

            foreach ($fileIds as $fileId) {
                $response = $this->http->post($this->baseURL . "vector_stores/$vectorStoreId/files", [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                        'Content-Type' => 'application/json',
                        'OpenAI-Beta' => 'assistants=v2'
                    ],
                    'json' => [
                        'file_id' => $fileId
                    ],
                ]);

                $addFileResponse = json_decode($response->getBody(), true);

                if (isset($addFileResponse['id'])) {
                    echo "File $fileId added to Vector Store successfully!\n";
                } else {
                    echo "Failed to add file $fileId to Vector Store.\n";
                }

                $file_id_response .=  $fileId . ",";
            }

            $dataResponse = [
                'file_id' => $file_id_response
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function fileUploadQwen($vectorStoreId,  $filePaths)
    {
        try {

            //Upload files and get file IDs
            $fileIds = [];

            foreach ($filePaths as $filePath) {
                $response = $this->http->post($this->baseURL . 'files', [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                    ],
                    'multipart' => [
                        [
                            'name' => 'purpose',
                            'contents' => 'assistants'
                        ],
                        [
                            'name' => 'file',
                            'contents' => fopen($filePath, 'r'),
                            'filename' => basename($filePath)
                        ],
                    ],
                ]);

                $fileResponse = json_decode($response->getBody(), true);
                $fileId = $fileResponse['id'] ?? null;

                if ($fileId) {
                    $fileIds[] = $fileId;
                    echo "File uploaded successfully! File ID: $fileId\n";
                } else {
                    echo "Failed to upload file: $filePath\n";
                }
            }

            if (empty($fileIds)) {
                die("No files were uploaded successfully.\n");
            }

            // Attach Files to Vector Store (Batch Upload)

            $file_id_response = "";

            foreach ($fileIds as $fileId) {
                $response = $this->http->post($this->baseURL . "vector_stores/$vectorStoreId/files", [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                        'Content-Type' => 'application/json',
                        'OpenAI-Beta' => 'assistants=v2'
                    ],
                    'json' => [
                        'file_id' => $fileId
                    ],
                ]);

                $addFileResponse = json_decode($response->getBody(), true);

                if (isset($addFileResponse['id'])) {
                    echo "File $fileId added to Vector Store successfully!\n";
                } else {
                    echo "Failed to add file $fileId to Vector Store.\n";
                }

                $file_id_response .=  $fileId . ",";
            }

            $dataResponse = [
                'file_id' => $file_id_response
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function addVatorfileToAssistant($assistant_id, $vectorStoreId)
    {
        try {
            $dataResponse = [];
            //vactor store create
            $response = $this->http->post($this->baseURL . "/assistants/$assistant_id", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ],
                'json' => [
                    'tools' => [
                        ['type' => 'file_search']
                    ],
                    'tool_resources' => [
                        'file_search' => [
                            'vector_store_ids' => [$vectorStoreId]
                        ]
                    ]
                ]
            ]);

            $updateResponse  = json_decode($response->getBody(), true);
            $updateResponse_id  = $updateResponse['id'] ?? null;

            if (!$updateResponse_id) {
                die("Failed to update Assistant.\n");
            }

            $dataResponse = [
                'vactorstore_id' => $updateResponse_id
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function removeAssistant($assistant_id)
    {
        try {
            $dataResponse = [];
            //vactor store create
            $response = $this->http->delete($this->baseURL . "/assistants/$assistant_id", [
                'headers' => [
                    'Authorization' => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta' => 'assistants=v2'
                ]
            ]);

            $updateResponse  = $response->getStatusCode();

            if ($updateResponse !== 204) {
                echo  "Failed to delete Assistant.";
            }

            $dataResponse = [
                'status_response' => $updateResponse
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function createthreads($roomId, $fileNames, $question, $assistant_id, $thread_id)
    {
        try {

            if ($question == "") $question = "สิ่งในรูปนี้มีไหม";
            

            $dataResponse = [];
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

            // log_message('info', "link facebook: " . json_encode($userContent));


            if ($thread_id != null) {
                //Create a Thread
                $response = $this->http->post($this->baseURL . "threads", [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                        'Content-Type'  => 'application/json',
                        'OpenAI-Beta' => 'assistants=v2'
                    ],
                    'json' => []
                ]);
                $threadResponse = json_decode($response->getBody(), true);
                $thread_id = $threadResponse['id'] ?? null;
            }


            $response = $this->http->post($this->baseURL . "threads/$thread_id/messages", [
                'headers' => [
                    'Authorization'  => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta'   => 'assistants=v2'
                ],
                'json' => [
                    'role' => 'user',
                    'content' =>
                    $userContent

                ]
            ]);

            $threadmessage = $this->sendmessagetoThreadId($thread_id, $assistant_id);

            // เพิ่มข้อความของ AI ลงในประวัติแชท
            $chatHistory[] = [
                'role' => 'assistant',
                'content' => $threadmessage
            ];

            // อัปเดตประวัติการสนทนา (เก็บไว้ไม่เกิน 6 ข้อความ)
            $this->saveChatHistory($roomId, $chatHistory);

            if ($thread_id == null) {
                echo  "Failed to create thread.";
            }


            $dataResponse = [
                'thread_id' => $thread_id,
                'thread_message' => $threadmessage
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function createthreadsTraining($fileId, $question, $thread_id)
    {
        try {
            $dataResponse = [];
            $messages_context = [];
            if (!empty($fileId)) {
                $message_block = $this->formatImageLinks($fileId);
                $messages_context =
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'งาน, เป้าหมาย, หรือ Prompt ปัจจุบัน:\n' . $question  . '\n ไม่ต้องแสดงลิงก์อ้างอิง'
                            ],
                            $message_block[0]
                        ]
                    ];
            } else {
                $messages_context =
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'งาน, เป้าหมาย, หรือ Prompt ปัจจุบัน:\n' . $question . '\n ไม่ต้องแสดงลิงก์อ้างอิง'
                            ]
                        ]
                    ];
            }
            //  log_message('info', "File S3: " . json_encode($messages_context));
            if ($thread_id == null) {
                //Create a Thread
                $response = $this->http->post($this->baseURL . "threads", [
                    'headers' => [
                        'Authorization' => "Bearer " . $this->accessToken,
                        'Content-Type'  => 'application/json',
                        'OpenAI-Beta' => 'assistants=v2'
                    ],
                    'json' => []
                ]);
                $threadResponse = json_decode($response->getBody(), true);
                $thread_id = $threadResponse['id'] ?? null;
            }

            $response = $this->http->post($this->baseURL . "threads/$thread_id/messages", [
                'headers' => [
                    'Authorization'  => "Bearer " . $this->accessToken,
                    'Content-Type'  => 'application/json',
                    'OpenAI-Beta'   => 'assistants=v2'
                ],
                'json' => $messages_context
            ]);




            if ($thread_id == null) {
                echo  "Failed to delete Assistant.";
            }

            $dataResponse = [
                'thread_id' => $thread_id
            ];

            return $dataResponse;
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }


}
