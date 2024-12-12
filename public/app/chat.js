const wsUrl =
  window.location.hostname === "localhost"
    ? "ws://localhost:3000"
    : "wss://websocket.evxcars.com:8080";

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
const chatBoxProfile = document.getElementById("chat-box-profile");
const chatBoxUsername = document.getElementById("chat-box-username");

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
  const chatBoxEmpty = document.getElementById("chat-box-emtry");
  const chatBoxRight = document.getElementById("chat-box-right");
  const chatBoxPreloader = document.getElementById("chat-box-preloader");
  const preloader = document.getElementById("preloader"); // เพิ่ม preloader (สร้าง element นี้ใน HTML)

  // ซ่อน chat-box-emtry
  chatBoxEmpty.style.display = "none";
  chatBoxRight.style.display = "none";

  const roomItem = event.target.closest(".room-item");
  if (!roomItem) return; // หากไม่ได้คลิกที่รายการห้องให้หยุดทำงาน

  // แสดง preloader
  preloader.style.display = "block";
  chatBoxPreloader.style.display = "block";

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
    .then((data) => {
      let customer = data.customer;
      let messages = data.messages;

      // จัดการกล่องแชท
      chatBoxProfile.src = customer.profile;
      chatBoxUsername.innerHTML = customer.name;

      // เคลียร์ข้อความเก่าในหน้าจอ
      messagesDiv.innerHTML = "";

      // วนลูปข้อความและเพิ่มลงในหน้าจอ
      messages.forEach((msg) => renderMessage(msg));

      // จัดการปุ่ม AI
      if (data.userSocial.ai == "on") $(".btnAI").show();
      else $(".btnAI").hide();

      // ซ่อน preloader เมื่อโหลดเสร็จ
      preloader.style.display = "none";
      chatBoxPreloader.style.display = "none";

      // แสดง chat-box-right
      chatBoxRight.style.display = "block";

      scrollToBottom();
    })
    .catch((err) => console.error("Error loading messages:", err));
});

// -----------------------------------------------------------------------------
// ฟังก์ชันแสดงข้อความบนหน้าจอ
// -----------------------------------------------------------------------------
function renderMessage(msg) {
  const messageTime = formatMessageTime(msg.created_at);

  // กรณีเป็นข้อความในนาทีเดียวกัน ให้ Group
  if (shouldGroupWithPrevious(msg.sender_id, messageTime)) {
    appendMessageToGroup(msg.message);
  }

  // กรณีแยกเป็นแต่ละข้อความ
  else {
    createMessageBubble(msg, messageTime);
    updateRoomPreview(msg);
  }
}

// -----------------------------------------------------------------------------
// ฟังก์ชันส่งข้อความใหม่
// -----------------------------------------------------------------------------

// ฟังก์ชันส่งข้อความ
function sendMessage() {
  const message = chatInput.value.trim();

  if (!message || !currentRoomId) {
    console.warn("กรุณาใส่ข้อความก่อนส่ง");
    return;
  }

  const data = { room_id: currentRoomId, message, platform: currentPlatform };

  console.log("กำลังส่งข้อมูลไปยังเซิร์ฟเวอร์:", data);

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
      addOrUpdateRoom({
        room_id: currentRoomId,
        message,
        platform: currentPlatform,
        sender_name: "Admin",
        sender_avatar: chatBoxProfile.src,
      });
      scrollToBottom();
    })
    .catch((err) => console.error("Error sending message:", err));

  chatInput.value = "";
}

// ตรวจจับการคลิกปุ่มส่งข้อความ
sendBtn.addEventListener("click", sendMessage);

// ตรวจจับการกดปุ่ม Enter
chatInput.addEventListener("keypress", (event) => {
  if (event.key === "Enter") {
    event.preventDefault();
    sendMessage();
  }
});

// -----------------------------------------------------------------------------
// ฟังก์ชันจัดการข้อความใหม่ที่ได้รับผ่าน WebSocket
// -----------------------------------------------------------------------------
// จัดการข้อความใหม่ที่ได้รับผ่าน WebSocket
ws.onmessage = (event) => {
  console.log("onmessage ข้อความใหม่:", event.data);
  let data = JSON.parse(event.data);
  if (data.room_id === currentRoomId) {
    renderMessage(data);
    scrollToBottom();
  } else {
    addOrUpdateRoom(data);
  }
};
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
      return "ic-Facebook.png";
    case "Line":
      return "ic-Line.png";
    case "WhatsApp":
      return "ic-WhatsApp.png";
    default:
      return "unknown-icon.png"; // ค่าเริ่มต้นกรณีไม่ตรงกับเงื่อนไขใด
  }
}

function getAvatar(data) {
  switch (data.send_by) {
    case "Customer":
      return chatBoxProfile.src;
    case "Admin":
      return "";
    default:
      return "unknown-icon.png"; // ค่าเริ่มต้นกรณีไม่ตรงกับเงื่อนไขใด
  }
}

// ฟังก์ชันแปลงเวลา
function formatMessageTime(createdAt) {
  return new Date(createdAt).toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit",
    hour12: true,
  });
}

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

// ฟังก์ชันตรวจสอบว่าควรรวมข้อความใหม่กับข้อความเดิมหรือไม่
function shouldGroupWithPrevious(senderId, messageTime) {
  return (
    senderId === previousSenderId &&
    messageTime === previousTime &&
    currentChatGroup !== null
  );
}

// ฟังก์ชันเพิ่มข้อความใหม่ในกลุ่มเดิม
function appendMessageToGroup(message) {
  const userChatDiv = currentChatGroup.querySelector(".user-chat");
  if (userChatDiv) {
    const newMessage = document.createElement("p");
    newMessage.textContent = message;
    userChatDiv.appendChild(newMessage);
  }
}

// ฟังก์ชันสร้างข้อความใหม่ในรูปแบบ Bubble
function createMessageBubble(msg, messageTime) {
  const msgDiv = document.createElement("div");
  msgDiv.classList.add("d-flex");
  const isCustomer = msg.send_by === "Customer";
  msgDiv.classList.toggle("flex-row-reverse", !isCustomer);

  msgDiv.innerHTML = `
    <img src="${getAvatar(msg)}" alt="user" class="rounded-circle thumb-md">
    <div class="${isCustomer ? "ms-1" : "me-1"} chat-box w-100 ${
    isCustomer ? "" : "reverse"
  }">
      <div class="user-chat">
        <p>${msg.message}</p>
      </div>
      <div class="chat-time">${messageTime}</div>
    </div>
  `;

  messagesDiv.appendChild(msgDiv);

  currentChatGroup = msgDiv;
  previousSenderId = msg.sender_id;
  previousTime = messageTime;
}

// ฟังก์ชันอัปเดต Preview ข้อความล่าสุดในห้อง
function updateRoomPreview(data) {
  const existingRoom = document.querySelector(
    `.room-item[data-room-id="${data.room_id}"]`
  );
  if (!existingRoom) return;

  const messagePreview = existingRoom.querySelector(".text-primary");
  const timestamp = existingRoom.querySelector("small.float-end");

  // อัปเดตข้อความพร้อมตรวจสอบผู้ส่ง
  if (messagePreview) {
    const prefix = data.send_by === "Admin" ? "คุณ: " : "";
    messagePreview.textContent = `${prefix}${data.message}`;
  }

  // if (timestamp) timestamp.textContent = "Now";
  if (timestamp) timestamp.textContent = timestamp.innerHTML;
}

// ฟังก์ชั่นหลักในการเพิ่มห้องหรืออัพเดท
function addOrUpdateRoom(data) {
  if (!data.room_id || !data.message || !data.sender_name) {
    console.warn("ข้อมูลไม่ครบถ้วนสำหรับ addOrUpdateRoom:", data);
    return;
  }

  const roomsList = document.getElementById("rooms-list");
  const existingRoom = roomsList.querySelector(
    `.room-item[data-room-id="${data.room_id}"]`
  );

  // ถ้ามีห้องแล้ว
  if (existingRoom) {
    updateRoom(existingRoom, data);
    roomsList.prepend(existingRoom);
  }

  // ถ้ายังไม่มี
  else createNewRoom(data);

  console.log(data);
}

// ฟังก์ชันอัปเดตห้องที่มีอยู่
function updateRoom(roomElement, data) {
  const messagePreview = roomElement.querySelector(".text-primary");
  const timestamp = roomElement.querySelector("small.float-end");

  // อัปเดตข้อความพร้อมตรวจสอบผู้ส่ง
  if (messagePreview) {
    const prefix = data.sender_name === "Admin" ? "คุณ: " : "";
    messagePreview.textContent = `${prefix}${data.message}`;
  }

  if (timestamp) timestamp.textContent = "Now";

  console.log("อัปเดตห้อง:", roomElement);
}

// ฟังก์ชันสร้างห้องใหม่
function createNewRoom(data) {
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
  newRoom.setAttribute("data-platform", data.platform || "Unknown");

  newRoom.innerHTML = `
    <a href="#" class="">
      <div class="d-flex align-items-start">
        <div class="position-relative">
          <img src="${
            data.sender_avatar || "default-avatar.png"
          }" alt="user" class="thumb-lg rounded-circle">
          <span class="position-absolute bottom-0 end-0">
            <img src="assets/images/${getPlatformIcon(
              data.platform || "Unknown"
            )}" width="14">
          </span>
        </div>
        <div class="flex-grow-1 ms-2 text-truncate align-self-center">
          <h6 class="my-0 fw-medium text-dark fs-14">${data.sender_name}
            <small class="float-end text-muted fs-11">Now</small>
          </h6>
          <p class="text-muted mb-0">
            <span class="text-primary">${data.message}</span>
          </p>
        </div>
      </div>
    </a>`;
  document.getElementById("rooms-list").prepend(newRoom);
  console.log("เพิ่มห้องใหม่:", newRoom);
}
