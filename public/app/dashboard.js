ws.onmessage = (event) => {
  let data = JSON.parse(event.data);
  if (data.receiver_id === window.userID) {
      ntf = new Notyf({
          position: {
              x: "right",
              y: "bottom",
          },
          types: [{
              type: "message",
              background: "rgba(0,0,0,.7)",
              color: "#000",
              icon: `<img width="24" src="${data.sender_avatar}">`,
          }, ],
      });

      ntf.open({
          type: "message",
          message: `ส่งข้อความใหม่: ${data.message}`,
      });
  }
};

// รอให้ DOM โหลดเสร็จ
document.addEventListener("DOMContentLoaded", function () {
  // ดึงค่า username จาก data-attribute
  var username = document
    .getElementById("typed-text-container")
    .getAttribute("data-username");

  // ตั้งค่า Typed.js
  var options = {
    strings: [
      "สวัสดี, " + username,
      "ยินดีต้อนรับสู่ AutoConX",
      "เริ่มสำรวจฟีเจอร์ต่าง ๆ ได้เลย!",
    ], // ข้อความที่พิมพ์ทีละข้อความ
    typeSpeed: 50, // ความเร็วในการพิมพ์ (มิลลิวินาที)
    backSpeed: 25, // ความเร็วในการลบข้อความ (มิลลิวินาที)
    loop: true, // ให้ข้อความวนซ้ำ
    smartBackspace: true, // ลบข้อความเฉพาะเมื่อจำเป็น
    showCursor: true, // แสดงเคอร์เซอร์กระพริบ
    cursorChar: "|", // รูปแบบของเคอร์เซอร์
  };

  var typed = new Typed("#typed-text", options);
});

document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll(".card-dashboard-animate");

  // สร้าง IntersectionObserver สำหรับ Scroll Animation
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("visible");
        }
      });
    },
    { threshold: 0.2 }
  );

  // ใช้ Observer กับแต่ละการ์ด
  cards.forEach((card) => {
    observer.observe(card);
  });
});
