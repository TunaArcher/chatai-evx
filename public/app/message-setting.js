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

// จัดการสถานะ WebSocket
ws.onopen = () => console.log("WebSocket connection opened.");
ws.onclose = () => console.log("WebSocket connection closed.");
ws.onerror = (error) => console.error("WebSocket error:", error);

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
    if (data.value == "" && $("#file_img_ask")[0].files[0] == null) {
      notyf_message.error("ไม่อนุญาติให้มีค่าว่าง");
      return;
    }

    var testing_send = {
      message: data.value,
      // file_IMG: inputImg.files[0]
    };

    var datafile = new FormData();

    datafile.append("message", data.value);
    datafile.append("file_IMG", $("#file_img_ask")[0].files[0]);

    $.ajax({
      url: `${serverUrl}/message-traning-testing`,
      method: "POST",
      async: true,
      data: datafile,
      dataType: "json",
      cache: false,
      contentType: false,
      processData: false,
      beforeSend: function () {
        $("#modal-loading").modal("show", {
          backdrop: "static",
          keyboard: false,
        });
      },
      complete: function (response) {

        $("#chat_test_training").val("");
        $("#modal-loading").modal("hide");

        if (response.responseJSON.img_link == "") {
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
        } else if( testing_send.message == ""){
          $("#chat-detail-training-test").append(
            '<div class="d-flex flex-row-reverse">' +
              '<div class="me-1 chat-box w-100 reverse">' +
              '<div class="user-chat">' +
              '<img src="' +
              response.responseJSON.img_link +
              '" height="90" class="me-3 rounded" alt="..."></img>' +
              "</div>" +
              "</div>" +
              "</div>"
          );
        } else {
          $("#chat-detail-training-test").append(
            '<div class="d-flex flex-row-reverse">' +
              '<div class="me-1 chat-box w-100 reverse">' +
              '<div class="user-chat">' +
              '<img src="' +
              response.responseJSON.img_link +
              '" height="90" class="me-3 rounded" alt="..."></img>' +
              '<p class="">' +
              testing_send.message +
              "</p>" +
              "</div>" +
              "</div>" +
              "</div>"
          );
        }

        $("#chat-detail-training-test").append(
          '<div class="d-flex">' +
            '<div class="ms-1 chat-box w-100">' +
            '<div class="user-chat">' +
            '<p class="">' +
            response.responseJSON.message +
            "</p>" +
            "</div>" +
            "</div>" +
            "</div>"
        );

        resetImgTestAI();
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

function readURLImgTestAI(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function (e) {
      document.querySelector("#img_ai").setAttribute("src", e.target.result);
    };

    reader.readAsDataURL(input.files[0]);
    $("#div_img").show();
  }
}

function resetImgTestAI() {
  $("#file_img_ask").val("");
  $("#div_img").hide();
}
