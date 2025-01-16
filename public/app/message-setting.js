$(document).ready(function () {
  loadMessageTraning();
});

const notyf_message = new Notyf({
  position: {
    x: "right",
    y: "top",
  },
});

// $("#traning-massage-form").on("submit", function (e) {
//   e.preventDefault(); // ป้องกันการรีเฟรชหน้าเว็บ

//   // ดึงค่าข้อมูลจากฟอร์ม
//   const formData = {
//     message: $('textarea[name="message-traning"]').val(),
//     message_status: "ON",
//   };

//   // ตรวจสอบข้อมูลก่อนส่ง (เช่น เช็คว่า password กับ confirm_password ตรงกัน)
//   if (formData.message == "") {
//     notyf_message.error("ไม่อนุญาติให้มีค่าว่าง");
//     return;
//   }

//   // ส่งข้อมูลด้วย AJAX
//   $.ajax({
//     url: `${serverUrl}/message-traning`,
//     type: "POST",
//     data: JSON.stringify(formData),
//     contentType: "application/json; charset=utf-8",
//     success: function (response) {
//       if (response.success) {
//         notyf_message.success("สำเร็จ");
//         // document.getElementById("traning-massage-form").reset();
//       } else {
//         notyf_message.error("ไม่สำเร็จ =>" + response.message);
//       }
//     },
//     error: function (xhr, status, error) {
//       const message =
//         xhr.responseJSON?.message || "An unexpected error occurred.";
//       Swal.fire({
//         title: "Error",
//         text: message,
//         icon: "error",
//         confirmButtonText: "OK",
//       });
//     },
//   });
// });

function sendTraining(data) {
  if (event.key === "Enter") {
    if (data.value == "") {
      notyf_message.error("ไม่อนุญาติให้มีค่าว่าง");
      return;
    }

    var training_send = {
      message: data.value,
      message_status: "Q",
    };

    $.ajax({
      url: `${serverUrl}/message-traning`,
      method: "POST",
      async: true,
      data: JSON.stringify(training_send),
      contentType: "application/json; charset=utf-8",
      beforeSend: function () {
        $("#modal-loading").modal("show", {
          backdrop: "static",
          keyboard: false,
        });
      },
      complete: function () {
        loadMessageTraning();
        $("#chat_training").val("");
        $("#modal-loading").modal("hide");
      },
      success: function (response) {},
    });
  }

  // if (ask == "") {
  //   notyf_message.error("ไม่อนุญาติให้มีค่าว่าง");
  //   return;
  // }
}

function loadMessageTraning() {
  $.ajax({
    url: `${serverUrl}/message-traning-load/${userID}`,
    method: "get",
    async: false,
    success: function (response) {
      var result = response;
      var htmlBox = "<div class='chat-detail' id='chat-detail-training'>";
      for (let index_ = 0; index_ < result.length; index_++) {
        if (result.length > 0) {
          if (result[index_].message_state == "Q") {
            htmlBox +=
              '<div class="d-flex flex-row-reverse">' +
              '<div class="me-1 chat-box w-100 reverse">' +
              '<div class="user-chat">' +
              '<p class="">' +
              result[index_].message_training +
              "</p>" +
              "</div>" +
              "</div>" +
              "</div>";
          } else {
            htmlBox +=
              '<div class="d-flex">' +
              '<div class="ms-1 chat-box w-100">' +
              '<div class="user-chat">' +
              '<p class="">' +
              result[index_].message_training +
              "</p>" +
              "</div>" +
              "</div>" +
              "</div>";
          }
        }
        htmlBox += "</div>";

        $("#chat-detail-training").html(htmlBox);
      }
    },
  });
}

function sendTestTraning(data) {
  if (event.key === "Enter") {
    if (data.value == "") {
      notyf_message.error("ไม่อนุญาติให้มีค่าว่าง");
      return;
    }

    var testing_send = {
      message: data.value,
    };

    $.ajax({
      url: `${serverUrl}/message-traning-testing`,
      method: "POST",
      async: true,
      data: JSON.stringify(testing_send),
      beforeSend: function () {
        $("#modal-loading").modal("show", {
          backdrop: "static",
          keyboard: false,
        });
      },
      complete: function (response) {
        $("#chat_test_training").val("");
        $("#modal-loading").modal("hide");
        // console.log(response.responseText);
        $("#chat-detail-training-test").append(
          '<div class="d-flex flex-row-reverse">' +
            '<div class="me-1 chat-box w-100 reverse">' +
            '<div class="user-chat">' +
            '<p class="">' +
            testing_send.message +
            "</p>" +
            "</div>" +
            "</div>" +
            "</div>"
        );
        $("#chat-detail-training-test").append(
          '<div class="d-flex">' +
            '<div class="ms-1 chat-box w-100">' +
            '<div class="user-chat">' +
            '<p class="">' +
            response.responseText +
            "</p>" +
            "</div>" +
            "</div>" +
            "</div>"
        );
      },
      success: function (response) {},
    });
  }
}

function clearTraning() {
  var userid_send = {
    user_id: userID,
  };

  $.ajax({
    url: `${serverUrl}/message-traning-clears`,
    method: "POST",
    async: true,
    data: JSON.stringify(userid_send),
    beforeSend: function () {
      $("#modal-loading").modal("show", {
        backdrop: "static",
        keyboard: false,
      });
    },
    complete: function (response) {
      $("#chat_test_training").val("");
      $("#modal-loading").modal("hide");
      $("#chat-detail-training").html("");
    },
    success: function (response) {},
  });
}
