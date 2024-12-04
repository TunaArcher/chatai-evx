const wsUrl =
  window.location.hostname === "localhost"
    ? "ws://localhost:3000" // สำหรับการเทสใน Local
    : "wss://websocket.evxcars.com:8080"; // สำหรับ Production

// สร้างการเชื่อมต่อกับ WebSocket Server
const ws = new WebSocket(wsUrl);
console.log(`WebSocket URL: ${wsUrl}`);

// DOM Elements (ดึง Element ต่าง ๆ จาก DOM)
const chatInput = document.getElementById("chat-input");
const sendBtn = document.getElementById("send-btn");
const messagesDiv = document.getElementById("chat-detail");
const roomsList = document.getElementById("rooms-list");
const chatHeader = document.getElementById("chat-header");
const profilePic = document.getElementById("profile-pic");
const chatTitle = document.getElementById("chat-title");

// ตัวแปรสถานะปัจจุบัน
let currentRoomId = null; // ห้องปัจจุบันที่ใช้งาน
let currentPlatform = null; // แพลตฟอร์มปัจจุบัน (Facebook, Line ฯลฯ)
let currentSenderID = null; // ผู้ส่งปัจจุบัน
let currentSentBy = "Admin"; // ค่าเริ่มต้นเป็น Admin

// ตัวแปรสำหรับจัดกลุ่มข้อความ
let previousSenderId = null; // ID ผู้ส่งข้อความก่อนหน้า
let previousTime = null; // เวลาส่งข้อความก่อนหน้า
let currentChatGroup = null; // กลุ่มข้อความปัจจุบัน

// -----------------------------------------------------------------------------
// ฟังก์ชันโหลดข้อความเมื่อเปลี่ยนห้องสนทนา
// -----------------------------------------------------------------------------
roomsList.addEventListener("click", (event) => {
  const roomItem = event.target.closest(".room-item");
  if (!roomItem) return; // หากไม่ได้คลิกที่รายการห้องให้หยุดทำงาน

  // อัปเดตสถานะห้องปัจจุบัน
  currentRoomId = roomItem.getAttribute("data-room-id");
  currentPlatform = roomItem.getAttribute("data-platform");

  console.log("Debug: ห้องที่กำลังใช้งาน:", currentRoomId);

  // เน้นรายการห้องที่ถูกเลือก
  document
    .querySelectorAll(".room-item")
    .forEach((item) => item.classList.remove("active"));
  roomItem.classList.add("active");

  // ดึงข้อความของห้องสนทนาจาก API
  fetch(`/messages/${currentRoomId}`)
    .then((response) => response.json())
    .then((messages) => {
      // เคลียร์ข้อความเก่าในหน้าจอ
      messagesDiv.innerHTML = "";

      // วนลูปข้อความและเพิ่มลงในหน้าจอ
      messages.forEach((msg) => renderMessage(msg));
      scrollToBottom();
    })
    .catch((err) => console.error("Error loading messages:", err));
});

// -----------------------------------------------------------------------------
// ฟังก์ชันแสดงข้อความบนหน้าจอ
// -----------------------------------------------------------------------------
function renderMessage(msg) {
  const messageTime = new Date(msg.created_at).toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });

  // ตรวจสอบว่าข้อความสามารถรวมกับข้อความก่อนหน้าได้หรือไม่
  const canGroupWithPrevious =
    msg.sender_id === previousSenderId && previousTime === messageTime;

  if (canGroupWithPrevious && currentChatGroup !== null) {
    // เพิ่มข้อความใหม่ในกลุ่มเดิม
    const userChatDiv = currentChatGroup.querySelector(".user-chat");
    if (userChatDiv) {
      const newMessage = document.createElement("p");
      newMessage.textContent = msg.message;
      userChatDiv.appendChild(newMessage);
    }
  } else {
    // สร้างกล่องข้อความใหม่
    const msgDiv = document.createElement("div");
    msgDiv.classList.add("d-flex");

    // ระบุฝั่งผู้ส่ง (ลูกค้าหรือแอดมิน)
    if (msg.send_by === "Customer") {
      msgDiv.innerHTML = `
                <img src="${msg.sender_avatar}" alt="user" class="rounded-circle thumb-md">
                <div class="ms-1 chat-box w-100">
                    <div class="user-chat">
                        <p>${msg.message}</p>
                    </div>
                    <div class="chat-time">${messageTime}</div>
                </div>`;
    } else if (msg.send_by === "Admin") {
      msgDiv.classList.add("flex-row-reverse");
      msgDiv.innerHTML = `
                <img src="${msg.sender_avatar}" alt="user" class="rounded-circle thumb-md">
                <div class="me-1 chat-box w-100 reverse">
                    <div class="user-chat">
                        <p>${msg.message}</p>
                    </div>
                    <div class="chat-time">${messageTime}</div>
                </div>`;
    }

    // เพิ่มข้อความใหม่ลงในหน้าจอ
    messagesDiv.appendChild(msgDiv);

    // อัปเดตตัวแปรสถานะสำหรับจัดกลุ่มข้อความ
    currentChatGroup = msgDiv;
    previousSenderId = msg.sender_id;
    previousTime = messageTime;
  }
}

// -----------------------------------------------------------------------------
// ฟังก์ชันส่งข้อความใหม่
// -----------------------------------------------------------------------------
// sendBtn.addEventListener("click", () => {
//   const message = chatInput.value.trim(); // ตัดช่องว่างด้านหน้าและท้ายออก

//   if (message !== "" && currentRoomId) {
//     const data = {
//       room_id: currentRoomId,
//       message: message,
//       platform: currentPlatform,
//     };

//     console.log("กำลังส่งข้อมูลไปยังเซิร์ฟเวอร์:", data);

//     // ส่งข้อความไปยัง API
//     fetch("/send-message", {
//       method: "POST",
//       headers: { "Content-Type": "application/json" },
//       body: JSON.stringify(data),
//     })
//       .then((response) => {
//         if (!response.ok) throw new Error("HTTP error " + response.status);
//         return response.json();
//       })
//       .then((result) => console.log("ส่งข้อความสำเร็จ:", result))
//       .catch((err) => console.error("Error sending message:", err));

//     // ล้างข้อความในช่องกรอก
//     chatInput.value = "";
//   } else {
//     console.warn("กรุณาใส่ข้อความก่อนส่ง");
//   }
// });

// ฟังก์ชันเลื่อนหน้าจอไปยังข้อความล่าสุด
function scrollToBottom() {
  const chatBody = document.querySelector(".chat-body"); // Container ของ SimpleBar
  if (chatBody) {
    const scrollElement = chatBody.querySelector(".simplebar-content-wrapper"); // Scroll Element ของ SimpleBar
    if (scrollElement) {
      scrollElement.scrollTo({
        top: scrollElement.scrollHeight,
        behavior: "smooth",
      });
      console.log("เลื่อนหน้าจอไปที่ข้อความล่าสุด (SimpleBar)");
    }
  }
}

// ฟังก์ชันส่งข้อความ
function sendMessage() {
  const message = chatInput.value.trim(); // ตัดช่องว่างด้านหน้าและท้ายออก

  if (message !== "" && currentRoomId) {
    const data = {
      room_id: currentRoomId,
      message: message,
      platform: currentPlatform,
    };

    console.log("กำลังส่งข้อมูลไปยังเซิร์ฟเวอร์:", data);

    // ส่งข้อความไปยัง API
    fetch("/send-message", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    })
      .then((response) => {
        if (!response.ok) throw new Error("HTTP error " + response.status);
        return response.json();
      })
      .then((result) => {
        console.log("ส่งข้อความสำเร็จ:", result);
        scrollToBottom(); // เลื่อนหน้าจอไปยังข้อความล่าสุด
      })
      .catch((err) => console.error("Error sending message:", err));

    // ล้างข้อความในช่องกรอก
    chatInput.value = "";
  } else {
    console.warn("กรุณาใส่ข้อความก่อนส่ง");
  }
}

// ตรวจจับการคลิกปุ่มส่งข้อความ
sendBtn.addEventListener("click", sendMessage);

// ตรวจจับการกดปุ่ม Enter
chatInput.addEventListener("keypress", (event) => {
  if (event.key === "Enter") {
    // ตรวจสอบว่าปุ่มที่กดคือ Enter
    event.preventDefault(); // ป้องกันการส่งฟอร์มหรือเหตุการณ์เริ่มต้น
    sendMessage(); // เรียกใช้ฟังก์ชันส่งข้อความ
  }
});

// -----------------------------------------------------------------------------
// ฟังก์ชันจัดการข้อความใหม่ที่ได้รับผ่าน WebSocket
// -----------------------------------------------------------------------------
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  console.log("ข้อความใหม่:", data);

  if (data.room_id === currentRoomId) {
    renderMessage(data); // แสดงข้อความในห้องปัจจุบัน
    scrollToBottom(); // เลื่อนหน้าจอไปยังข้อความล่าสุด
  } else {
    // เพิ่มห้องใหม่ถ้าห้องนั้นยังไม่มีใน rooms-list
    addNewRoom(data);
  }
};

// -----------------------------------------------------------------------------
// ฟังก์ชันเพิ่มห้องใหม่ใน rooms-list
// -----------------------------------------------------------------------------
function addNewRoom(data) {
  const existingRoom = document.querySelector(
    `.room-item[data-room-id="${data.room_id}"]`
  );

  if (!existingRoom) {
    const newRoom = document.createElement("div");
    newRoom.classList.add(
      "room-item",
      "p-2",
      "border-dashed",
      "border-theme-color",
      "rounded",
      "mb-2"
    );
    newRoom.setAttribute("data-room-id", data.room_id);
    newRoom.setAttribute("data-platform", data.platform);

    newRoom.innerHTML = `
            <a href="#" class="">
                <div class="d-flex align-items-start">
                    <div class="position-relative">
                        <img src="${data.sender_avatar}" alt="" class="thumb-lg rounded-circle">
                        <span class="position-absolute bottom-0 end-0">
                            <img src="assets/images/${getPlatformIcon(data.platform)}" width="14">
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                        <h6 class="my-0 fw-medium text-dark fs-14">${data.sender_name}
                            <small class="float-end text-muted fs-11">Now</small>
                        </h6>
                        <p class="text-muted mb-0"><span class="text-primary">${data.message}</span></p>
                    </div>
                </div>
            </a>`;

    roomsList.prepend(newRoom);
    console.log("เพิ่มห้องใหม่:", newRoom);
  }
}

// จัดการสถานะ WebSocket
ws.onopen = () => console.log("WebSocket connection opened.");
ws.onclose = () => console.log("WebSocket connection closed.");
ws.onerror = (error) => console.error("WebSocket error:", error);

// -----------------------------------------------------------------------------
// อื่น ๆ แปะไปก่อน
// -----------------------------------------------------------------------------

function getPlatformIcon(platform) {
  switch (platform) {
    case "Facebook":
      return "ic-Facebook.svg";
    case "Line":
      return "ic-Line.png";
    case "WhatsApp":
      return "ic-WhatsApp.png";
    default:
      return "unknown-icon.png"; // ค่าเริ่มต้นกรณีไม่ตรงกับเงื่อนไขใด
  }
}

