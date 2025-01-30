// สร้างการเชื่อมต่อกับ WebSocket Server
const ws = new WebSocket(wsUrl);
console.log(`WebSocket URL: ${wsUrl}`);

// DOM Elements (ดึง Element ต่าง ๆ จาก DOM)
const chatInput = document.getElementById("chat-input");
const sendBtn = document.getElementById("send-btn");
const messagesDiv = document.getElementById("chat-detail");
const roomsList = document.getElementById("rooms-list");
const roomsListMenu = document.getElementById("rooms-list-menu");
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

// การตั้งค่า DOM สำหรับขนาด Sidebar
const bodyElement = document.body;
const bodySize = bodyElement.getAttribute("data-sidebar-size");
const messagecollapse = document.getElementById("message-collapse");
const chatboxleft = document.getElementById("chat-box-left");

if (bodySize === "collapsed") {
  messagecollapse.style.display = "block";
  chatboxleft.style.display = "none";
} else {
  messagecollapse.style.display = "none";
}

// -----------------------------------------------------------------------------
// การจัดการการเปลี่ยนห้องสนทนาเมื่อคลิกที่รายการห้อง
// -----------------------------------------------------------------------------
roomsList.addEventListener("click", (event) => handleRoomSelection(event));
roomsListMenu.addEventListener("click", (event) => handleRoomSelection(event));

// ฟังก์ชันจัดการการเลือกห้อง
function handleRoomSelection(event) {
  const chatBoxEmpty = document.getElementById("chat-box-emtry");
  const chatBoxRight = document.getElementById("chat-box-right");
  const chatBoxPreloader = document.getElementById("chat-box-preloader");
  const preloader = document.getElementById("preloader");

  chatBoxEmpty.style.display = "none";
  chatBoxRight.style.display = "none";

  const roomItem = event.target.closest(".room-item");
  if (!roomItem) return;

  preloader.style.display = "block";
  chatBoxPreloader.style.display = "block";

  currentRoomId = roomItem.getAttribute("data-room-id");
  currentPlatform = roomItem.getAttribute("data-platform");

  console.log("Debug: ห้องที่กำลังใช้งาน:", currentRoomId);

  document
    .querySelectorAll(".room-item")
    .forEach((item) => item.classList.remove("active"));
  roomItem.classList.add("active");

  loadMessagesForRoom(currentRoomId);
}

// -----------------------------------------------------------------------------
// ฟังก์ชันโหลดข้อความจาก API
// -----------------------------------------------------------------------------
function loadMessagesForRoom(roomId) {
  fetch(`/messages/${roomId}`)
    .then((response) => response.json())
    .then((data) => displayMessages(data))
    .catch((err) => console.error("Error loading messages:", err));
}

// ฟังก์ชันแสดงข้อความใน UI
function displayMessages(data) {
  const { customer, messages, userSocial } = data;

  chatBoxProfile.src =
    customer.profile && customer.profile !== "0"
      ? customer.profile
      : "/assets/images/conX.png";
  chatBoxUsername.innerHTML = customer.name;

  messagesDiv.innerHTML = "";
  messages.forEach((msg) => renderMessage(msg));

  $(".btnAI").toggle(userSocial.ai === "on");

  document.getElementById("preloader").style.display = "none";
  document.getElementById("chat-box-preloader").style.display = "none";
  document.getElementById("chat-box-right").style.display = "block";

  scrollToBottom();
}

// -----------------------------------------------------------------------------
// ฟังก์ชันส่งข้อความใหม่
// -----------------------------------------------------------------------------
function sendMessage() {
  const message = chatInput.value.trim();

  if (!message || !currentRoomId) {
    console.warn("กรุณาใส่ข้อความก่อนส่ง");
    return;
  }

  const data = {
    room_id: currentRoomId,
    message,
    platform: currentPlatform,
  };

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
// การจัดการข้อความใหม่ที่ได้รับผ่าน WebSocket
// -----------------------------------------------------------------------------
ws.onmessage = (event) => {
  console.log("onmessage ข้อความใหม่:", event.data);

  const data = JSON.parse(event.data);

  if (data.userIdLooking.includes(window.userID)) {
    if (data.room_id === currentRoomId) {
      addOrUpdateRoom(data);
      renderMessage(data);
      scrollToBottom();
    } else {
      addOrUpdateRoom(data);
    }
  }
};

// จัดการสถานะ WebSocket
ws.onopen = () => console.log("WebSocket connection opened.");
ws.onclose = () => console.log("WebSocket connection closed.");
ws.onerror = (error) => console.error("WebSocket error:", error);

// -----------------------------------------------------------------------------
// ฟังก์ชันแสดงข้อความบนหน้าจอ
// -----------------------------------------------------------------------------
function renderMessage(msg) {
  const messageTime = formatMessageTime(msg.created_at);

  if (shouldGroupWithPrevious(msg.sender_id, messageTime)) {
    appendMessageToGroup(msg.message, msg.message_type);
  } else {
    createMessageBubble(msg, messageTime);
    updateRoomPreview(msg);
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
function appendMessageToGroup(message, messageType) {
  const userChatDiv = currentChatGroup.querySelector(".user-chat");
  if (userChatDiv) {
    if (messageType === "text") {
      const newMessage = document.createElement("p");
      newMessage.textContent = message;
      userChatDiv.appendChild(newMessage);
    } else if (messageType === "image") {
      const imageUrls = JSON.parse(message);
      imageUrls.forEach((url) => {
        const imgElement = document.createElement("img");
        imgElement.src = url;
        imgElement.classList.add("chat-image");
        userChatDiv.appendChild(imgElement);
      });
    }
  }
}

// ฟังก์ชันสร้างข้อความใหม่ในรูปแบบ Bubble
function createMessageBubble(msg, messageTime) {
  const msgDiv = document.createElement("div");
  msgDiv.classList.add("d-flex");
  const isCustomer = msg.send_by === "Customer";
  msgDiv.classList.toggle("flex-row-reverse", !isCustomer);

  const chatContent = document.createElement("div");
  chatContent.classList.add("chat-box", "w-100", "ms-1");
  if (!isCustomer) {
    chatContent.classList.add("reverse");
  }

  const userChatDiv = document.createElement("div");
  userChatDiv.classList.add("user-chat");

  if (msg.message_type === "text") {
    const textElement = document.createElement("p");
    textElement.textContent = msg.message;
    userChatDiv.appendChild(textElement);
  } else if (msg.message_type === "image") {
    try {
      let imageUrls = [];

      if (typeof msg.message === "string") {
        msg.message = msg.message.trim(); // ลบช่องว่างที่อาจเกิดขึ้น
        if (msg.message.startsWith("[") && msg.message.endsWith("]")) {
          imageUrls = JSON.parse(msg.message);
        } else {
          imageUrls = [msg.message];
        }
      }
      console.log(imageUrls);

      if (Array.isArray(imageUrls)) {
        imageUrls.forEach((url) => {
          const imgContainer = document.createElement("a");
          imgContainer.href = url;
          imgContainer.target = "_blank";

          const imgElement = document.createElement("img");
          imgElement.src = url;
          imgElement.classList.add("img-thumbnail");
          imgElement.style.maxWidth = "200px";
          imgElement.style.height = "auto";

          imgContainer.appendChild(imgElement);
          userChatDiv.appendChild(imgContainer);
        });
      } else {
        console.error("Invalid image data format:", msg.message);
      }
    } catch (error) {
      console.error("Error parsing image message:", error);
    }
  }

  const chatTimeDiv = document.createElement("div");
  chatTimeDiv.classList.add("chat-time");
  chatTimeDiv.textContent = messageTime;

  chatContent.appendChild(userChatDiv);
  chatContent.appendChild(chatTimeDiv);

  msgDiv.innerHTML = `<img src="${getAvatar(
    msg
  )}" alt="user" class="rounded-circle thumb-md">`;
  msgDiv.appendChild(chatContent);

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

  if (messagePreview) {
    const isCustomer = data.send_by === "Customer";
    const prefix = isCustomer ? "" : "คุณ: ";
    messagePreview.textContent = `${prefix}${data.message}`;
  }

  if (timestamp) timestamp.textContent = "Now";
}

// -----------------------------------------------------------------------------
// ฟังก์ชันหลักในการเพิ่มหรืออัปเดตห้อง
// -----------------------------------------------------------------------------
function addOrUpdateRoom(data) {
  if (!data.room_id || !data.message || !data.sender_name) {
    console.warn("ข้อมูลไม่ครบถ้วนสำหรับ addOrUpdateRoom:", data);
    return;
  }

  const roomsList = document.getElementById("rooms-list");
  const roomsListMenu = document.getElementById("rooms-list-menu");

  const existingRoom = roomsList.querySelector(
    `.room-item[data-room-id="${data.room_id}"]`
  );
  const existingRoomMenu = roomsListMenu.querySelector(
    `.room-item[data-room-id="${data.room_id}"]`
  );

  if (existingRoom) {
    updateRoom(existingRoom, data);
    roomsList.prepend(existingRoom);
  }

  if (existingRoomMenu) {
    updateRoom(existingRoomMenu, data);
    roomsListMenu.prepend(existingRoomMenu);
  } else createNewRoom(data);
}

// ฟังก์ชันอัปเดตห้องที่มีอยู่
function updateRoom(roomElement, data) {
  const messagePreview = roomElement.querySelector(".text-primary");
  const timestamp = roomElement.querySelector("small.float-end");

  if (messagePreview) {
    const isCustomer = data.send_by === "Customer";
    const prefix = isCustomer ? "" : "คุณ: ";
    messagePreview.textContent = `${prefix}${data.message}`;
  }

  if (timestamp) timestamp.textContent = "Now";

  console.log("อัปเดตห้อง:", roomElement);
}

// ฟังก์ชันสร้างห้องใหม่
function createNewRoom(data) {
  // ตรวจสอบว่าข้อมูลครบถ้วนก่อนสร้างห้อง
  if (!data.room_id || !data.message || !data.sender_name) {
    console.warn("ข้อมูลไม่เพียงพอสำหรับสร้างห้องใหม่:", data);
    return;
  }

  const isCustomer = data.send_by === "Customer";
  const prefix = isCustomer ? "" : "คุณ: ";
  const avatar = isCustomer
    ? data.sender_avatar
    : data.receiver_avatar || "default-avatar.png";
  const displayName = isCustomer ? data.sender_name : data.receiver_name;

  // สร้างองค์ประกอบ DOM สำหรับห้อง
  const newRoom = document.createElement("div");
  const newRoomList = document.createElement("div");

  // เพิ่มคลาสที่ใช้ร่วมกันให้กับห้อง
  newRoom.classList.add(
    "room-item",
    "p-2",
    "border-dashed",
    "border-theme-color",
    "rounded",
    "mb-2"
  );
  newRoomList.classList.add(
    "room-item",
    "p-2",
    "border-dashed",
    "border-theme-color",
    "rounded",
    "mb-2"
  );

  // ตั้งค่าแอตทริบิวต์
  newRoom.setAttribute("data-room-id", data.room_id);
  newRoom.setAttribute("data-platform", data.platform || "Unknown");

  newRoomList.setAttribute("data-room-id", data.room_id);
  newRoomList.setAttribute("data-platform", data.platform || "Unknown");

  // สร้าง HTML ภายใน
  const platformIcon = getPlatformIcon(data.platform || "Unknown");
  const roomContent = `
    <a href="#" class="d-flex align-items-start">
      <div class="position-relative">
        <img src="${avatar}" alt="user" class="thumb-lg rounded-circle">
        <span class="position-absolute bottom-0 end-0">
          <img src="assets/images/${platformIcon}" width="14">
        </span>
      </div>
      <div class="flex-grow-1 ms-2 text-truncate align-self-center">
        <h6 class="my-0 fw-medium text-dark fs-14">${displayName}
          <small class="float-end text-muted fs-11">Now</small>
        </h6>
        <p class="text-muted mb-0">
          <span class="text-primary">${prefix}${data.message}</span>
        </p>
      </div>
    </a>`;

  // เพิ่ม HTML ให้ทั้งสององค์ประกอบ
  newRoom.innerHTML = roomContent;
  newRoomList.innerHTML = roomContent;

  // เพิ่มห้องใหม่ลงในรายการ
  document.getElementById("rooms-list").prepend(newRoom);
  document.getElementById("rooms-list-menu").prepend(newRoomList);

  console.log("สร้างห้องใหม่สำเร็จ:", newRoom);
}

// -----------------------------------------------------------------------------
// ฟังก์ชันช่วยเหลือทั่วไป
// -----------------------------------------------------------------------------

// ฟังก์ชันแปลงเวลาให้เป็นรูปแบบที่อ่านง่าย
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

// ฟังก์ชันดึง Platform Icon
function getPlatformIcon(platform) {
  switch (platform) {
    case "Facebook":
      return "ic-Facebook.png";
    case "Line":
      return "ic-Line.png";
    case "WhatsApp":
      return "ic-WhatsApp.png";
    default:
      return "unknown-icon.png";
  }
}

// ฟังก์ชันดึง Avatar ของผู้ส่ง
function getAvatar(data) {
  return data.send_by === "Customer"
    ? chatBoxProfile.src
    : "/assets/images/conX.png";
}
