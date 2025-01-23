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
