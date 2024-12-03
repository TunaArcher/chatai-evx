// สร้างการเชื่อมต่อกับ WebSocket Server
const ws = new WebSocket("ws://localhost:8080");

// DOM Elements
const chatInput = document.getElementById("chat-input");
const sendBtn = document.getElementById("send-btn");
const messagesDiv = document.getElementById("chat-detail");
const roomsList = document.getElementById("rooms-list");
const chatHeader = document.getElementById("chat-header");
const profilePic = document.getElementById("profile-pic");
const chatTitle = document.getElementById("chat-title");

let currentRoomId = null,
  currentPlatform = null,
  currentSenderID = null,
  currentSentBy = "Admin";

let previousSenderId = null;
let previousTime = null;
let currentChatGroup = null;

// โหลดข้อความเมื่อเปลี่ยนห้องสนทนา
roomsList.addEventListener("click", (event) => {
  const roomItem = event.target.closest(".room-item");
  if (!roomItem) return;

  // กำหนดห้องที่เลือกปัจจุบัน
  currentRoomId = roomItem.getAttribute("data-room-id");
  console.log("Debug: คุณกำลังใช้งานห้อง: " + currentRoomId);

  currentPlatform = roomItem.getAttribute("data-platform");

  // เน้นรายการห้องที่ถูกเลือก
  document
    .querySelectorAll(".room-item")
    .forEach((item) => item.classList.remove("active"));
  roomItem.classList.add("active");

  // ดึงข้อมูลโปรไฟล์ห้อง
  // chatTitle.textContent = `Room ${currentRoomId}`;

  // ดึงข้อความของห้องสนทนา
  fetch(`/messages/${currentRoomId}`)
    .then((response) => response.json())
    .then((messages) => {
      // เคลียร์ข้อความเก่าในหน้าจอ
      messagesDiv.innerHTML = "";
      messages.forEach((msg) => {
        const messageTime = new Date(msg.created_at).toLocaleTimeString([], {
          hour: "2-digit",
          minute: "2-digit",
          hour12: true,
        });

        // ตรวจสอบว่าข้อความปัจจุบันสามารถรวมกับข้อความก่อนหน้าได้หรือไม่
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
                    </div>
                `;
          } else if (msg.send_by === "Admin") {
            msgDiv.classList.add("flex-row-reverse");
            msgDiv.innerHTML = `
                    <img src="${msg.sender_avatar}" alt="user" class="rounded-circle thumb-md">
                    <div class="me-1 chat-box w-100 reverse">
                        <div class="user-chat">
                            <p>${msg.message}</p>
                        </div>
                        <div class="chat-time">${messageTime}</div>
                    </div>
                `;
          }

          // เพิ่มกล่องข้อความใหม่ลงใน container
          messagesDiv.appendChild(msgDiv);

          console.log(msgDiv);
          // อัปเดตตัวแปรสำหรับการจัดกลุ่มข้อความ
          currentChatGroup = msgDiv;
          previousSenderId = msg.sender_id;
          previousTime = messageTime;
        }
      });
    })
    .catch((err) => console.error("Error loading messages:", err));
});

// ฟังก์ชันส่งข้อความใหม่
// sendBtn.addEventListener("click", () => {
//   const message = chatInput.value;

//   // ตรวจสอบว่ามีข้อความและห้องปัจจุบัน
//   if (message.trim() !== "" && currentRoomId) {
//     const data = {
//       room_id: currentRoomId,
//       message: message,
//       platform: currentPlatform,
//     };

//     // ส่งข้อความไปยัง API
//     fetch("/send-message", {
//       method: "POST",
//       headers: { "Content-Type": "application/json" },
//       body: JSON.stringify(data),
//     })
//       .then((response) => response.json())
//       .then((result) => {
//         console.log("Message sent:", result);
//       })
//       .catch((err) => console.error("Error sending message:", err));

//     // ล้างข้อความในช่องกรอก
//     chatInput.value = "";
//   }
// });
sendBtn.addEventListener("click", () => {
    const message = chatInput.value.trim(); // ตัดช่องว่างด้านหน้า/ท้ายออก

    // ตรวจสอบว่ามีข้อความและห้องปัจจุบัน
    if (message !== "" && currentRoomId) {
        const data = {
            room_id: currentRoomId,
            message: message, // ข้อความที่ส่ง
            platform: currentPlatform,
        };

        console.log("กำลังส่งข้อมูลไปยังเซิร์ฟเวอร์:", data);

        // ส่งข้อความไปยัง API
        fetch("/send-message", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data), // แปลงข้อมูลเป็น JSON
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("HTTP error " + response.status);
                }
                return response.json();
            })
            .then((result) => {
                console.log("Message sent successfully:", result);
            })
            .catch((err) => {
                console.error("Error sending message:", err);
            });

        // ล้างข้อความในช่องกรอก
        chatInput.value = "";
    } else {
        console.warn("กรุณาใส่ข้อความก่อนส่ง");
    }
});


// ฟังก์ชันรับข้อความใหม่ผ่าน WebSocket
// ws.onmessage = (event) => {
//   const data = JSON.parse(event.data);

//   console.log(data);

//   // ตรวจสอบว่าเป็นข้อความสำหรับห้องปัจจุบัน
//   if (data.room_id === currentRoomId) {
//     const messageTime = new Date(data.created_at).toLocaleTimeString([], {
//       hour: "2-digit",
//       minute: "2-digit",
//       hour12: true,
//     });

//     // ตรวจสอบว่าข้อความใหม่สามารถรวมกลุ่มกับข้อความก่อนหน้าได้หรือไม่
//     const canGroupWithPrevious =
//       data.sender_id === previousSenderId && previousTime === messageTime;

//     if (canGroupWithPrevious && currentChatGroup !== null) {
//       // เพิ่มข้อความใหม่ในกลุ่มเดิม
//       const userChatDiv = currentChatGroup.querySelector(".user-chat");
//       if (userChatDiv) {
//         const newMessage = document.createElement("p");
//         newMessage.textContent = data.message;
//         userChatDiv.appendChild(newMessage);
//       }
//     } else {
//       // สร้างกล่องข้อความใหม่
//       const msgDiv = document.createElement("div");
//       msgDiv.classList.add("d-flex");

//       // ระบุฝั่งผู้ส่ง (ลูกค้าหรือแอดมิน)
//       if (data.send_by === "Customer") {
//         msgDiv.innerHTML = `
//                   <img src="${data.sender_avatar}" alt="user" class="rounded-circle thumb-md">
//                   <div class="ms-1 chat-box w-100">
//                       <div class="user-chat">
//                           <p>${data.message}</p>
//                       </div>
//                       <div class="chat-time">${messageTime}</div>
//                   </div>
//               `;
//       } else if (data.send_by === "Admin") {
//         msgDiv.classList.add("flex-row-reverse");
//         msgDiv.innerHTML = `
//                   <img src="${data.sender_avatar}" alt="user" class="rounded-circle thumb-md">
//                   <div class="me-1 chat-box w-100 reverse">
//                       <div class="user-chat">
//                           <p>${data.message}</p>
//                       </div>
//                       <div class="chat-time">${messageTime}</div>
//                   </div>
//               `;
//       }

//       // เพิ่มกล่องข้อความใหม่ลงใน container
//       messagesDiv.appendChild(msgDiv);

//       // อัปเดตตัวแปรสำหรับการจัดกลุ่มข้อความ
//       currentChatGroup = msgDiv;
//       previousSenderId = data.sender_id;
//       previousTime = messageTime;
//     }

//     // เลื่อนลงอัตโนมัติไปยังข้อความล่าสุด
//     messagesDiv.scrollTop = messagesDiv.scrollHeight;
//   }
// };
ws.onmessage = (event) => {
  const data = JSON.parse(event.data);

  console.log("ข้อความใหม่:", data);

  // ตรวจสอบว่าเป็นข้อความสำหรับห้องปัจจุบัน
  if (data.room_id === currentRoomId) {
    const messageTime = new Date(data.created_at).toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
      hour12: true,
    });

    console.log(data.created_at)
    console.log(messageTime)

    // ตรวจสอบว่าข้อความใหม่สามารถรวมกลุ่มกับข้อความก่อนหน้าได้หรือไม่
    const canGroupWithPrevious =
      data.sender_id === previousSenderId && previousTime === messageTime;

    if (canGroupWithPrevious && currentChatGroup !== null) {
      // เพิ่มข้อความใหม่ในกลุ่มเดิม
      const userChatDiv = currentChatGroup.querySelector(".user-chat");
      if (userChatDiv) {
        const newMessage = document.createElement("p");
        newMessage.textContent = data.message;
        userChatDiv.appendChild(newMessage);
      }
    } else {
      // สร้างกล่องข้อความใหม่
      const msgDiv = document.createElement("div");
      msgDiv.classList.add("d-flex");

      // ระบุฝั่งผู้ส่ง (ลูกค้าหรือแอดมิน)
      if (data.send_by === "Customer") {
        msgDiv.innerHTML = `
                  <img src="${data.sender_avatar}" alt="user" class="rounded-circle thumb-md">
                  <div class="ms-1 chat-box w-100">
                      <div class="user-chat">
                          <p>${data.message}</p>
                      </div>
                      <div class="chat-time">${messageTime}</div>
                  </div>
              `;
      } else if (data.send_by === "Admin") {
        msgDiv.classList.add("flex-row-reverse");
        msgDiv.innerHTML = `
                  <img src="${data.sender_avatar}" alt="user" class="rounded-circle thumb-md">
                  <div class="me-1 chat-box w-100 reverse">
                      <div class="user-chat">
                          <p>${data.message}</p>
                      </div>
                      <div class="chat-time">${messageTime}</div>
                  </div>
              `;
      }

      // เพิ่มกล่องข้อความใหม่ลงใน container
      messagesDiv.appendChild(msgDiv);

      // อัปเดตตัวแปรสำหรับการจัดกลุ่มข้อความ
      currentChatGroup = msgDiv;
      previousSenderId = data.sender_id;
      previousTime = messageTime;
    }

    // เลื่อนลงอัตโนมัติไปยังข้อความล่าสุด
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  } else {
    // หาก room_id ยังไม่มีใน rooms-list
    const existingRoom = document.querySelector(
      `.room-item[data-room-id="${data.room_id}"]`
    );

    if (!existingRoom) {
      // สร้างห้องใหม่ใน rooms-list
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
                              <img src="assets/images/${data.platform}.png" width="14">
                          </span>
                      </div>
                      <div class="flex-grow-1 ms-2 text-truncate align-self-center">
                          <h6 class="my-0 fw-medium text-dark fs-14">${data.sender_name}
                              <small class="float-end text-muted fs-11">Now</small>
                          </h6>
                          <p class="text-muted mb-0"><span class="text-primary">${data.message}</span>
                          </p>
                      </div>
                  </div>
              </a>
          `;

      // เพิ่มห้องใหม่ใน rooms-list
      roomsList.prepend(newRoom);

      console.log("เพิ่มห้องใหม่:", newRoom);
    }
  }
};

// จัดการสถานะ WebSocket
ws.onopen = () => {
  console.log("WebSocket connection opened.");
};

ws.onclose = () => {
  console.log("WebSocket connection closed.");
};

ws.onerror = (error) => {
  console.error("WebSocket error:", error);
};
