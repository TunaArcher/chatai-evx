<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <script src="/app/chat.js" defer></script>
    <title>Real-Time Chat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }

        #chat-app {
            display: flex;
            width: 100%;
        }

        #sidebar {
            width: 25%;
            background: #f4f4f4;
            padding: 20px;
            border-right: 1px solid #ddd;
            overflow-y: auto;
        }

        #sidebar h2 {
            margin: 0 0 10px;
        }

        #rooms-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .room-item {
            padding: 10px;
            cursor: pointer;
            border: 1px solid #ddd;
            margin-bottom: 5px;
            border-radius: 5px;
            background: #fff;
            transition: background 0.3s;
        }

        .room-item.active {
            background: #007bff;
            color: #fff;
        }

        #chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        #chat-header {
            padding: 10px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #ddd;
        }

        #chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        #chat-title {
            margin: 0;
        }

        #messages {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .message {
            margin-bottom: 10px;
        }

        .message-sender {
            font-weight: bold;
            margin-right: 5px;
        }

        #chat-footer {
            padding: 10px;
            border-top: 1px solid #ddd;
            display: flex;
            align-items: center;
        }

        #chat-input {
            flex: 1;
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        #send-btn {
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        #send-btn:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <div id="chat-app">
        <aside id="sidebar">
            <h2>Chat Rooms</h2>
            <ul id="rooms-list">
                <?php foreach ($rooms as $room): ?>
                    <li data-room-id="<?= $room['id'] ?>" class="room-item">
                        <span class="room-name">Room <?= $room['id'] ?> (<?= $room['platform'] ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <main id="chat-main">
            <header id="chat-header">
                <img id="profile-pic" src="" alt="Profile Picture" />
                <h3 id="chat-title">Select a Room</h3>
            </header>
            <div id="messages"></div>
            <footer id="chat-footer">
                <input type="text" id="chat-input" placeholder="Type a message...">
                <button id="send-btn">Send</button>
            </footer>
        </main>
    </div>
</body>

</html>