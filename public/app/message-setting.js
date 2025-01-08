const notyf_message = new Notyf({
  position: {
    x: "right",
    y: "top",
  },
});

$("#traning-massage-form").on("submit", function (e) {
  e.preventDefault(); // ป้องกันการรีเฟรชหน้าเว็บ

  // ดึงค่าข้อมูลจากฟอร์ม
  const formData = {
    message: $('textarea[name="message-traning"]').val(),
    message_status: "ON",
  };

  // ตรวจสอบข้อมูลก่อนส่ง (เช่น เช็คว่า password กับ confirm_password ตรงกัน)
  if (formData.message == "") {
    notyf_message.error("ไม่อนุญาติให้มีค่าว่าง");
    return;
  }

  // ส่งข้อมูลด้วย AJAX
  $.ajax({
    url: `${serverUrl}/message-traning`,
    type: "POST",
    data: JSON.stringify(formData),
    contentType: "application/json; charset=utf-8",
    success: function (response) {
      if (response.success) {
        notyf_message.success("สำเร็จ");
        document.getElementById("traning-massage-form").reset();
      } else {
        notyf_message.error("ไม่สำเร็จ =>" + response.message);
      }
    },
    error: function (xhr, status, error) {
      const message =
        xhr.responseJSON?.message || "An unexpected error occurred.";
      Swal.fire({
        title: "Error",
        text: message,
        icon: "error",
        confirmButtonText: "OK",
      });
    },
  });
});
