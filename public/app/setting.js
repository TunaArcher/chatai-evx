// Collect DOM elements
const steps = {
  step1: { tab: $("#step1-tab"), content: $("#step1"), next: $("#step1Next") },
  step2: {
    tab: $("#step2-tab"),
    content: $("#step2"),
    next: $("#step2Next"),
    prev: $("#step2Prev"),
    wrappers: {
      Facebook: $(".step2-facebook-wrapper"),
      Line: $(".step2-line-wrapper"),
      WhatsApp: $(".step2-whatsapp-wrapper"),
    },
  },
  step3: {
    tab: $("#step3-tab"),
    content: $("#step3"),
    prev: $("#step3Prev"),
    finish: $("#step3Finish"),
    wrappers: {
      Facebook: $(".step3-facebook-wrapper"),
      Line: $(".step3-line-wrapper"),
      WhatsApp: $(".step3-whatsapp-wrapper"),
    },
  },
};

let selectedPlatform = "";

// Utility Functions
function activateStep(fromStep, toStep) {
  fromStep.tab.removeClass("active");
  fromStep.content.removeClass("active");
  toStep.tab.addClass("active");
  toStep.content.addClass("active");
}

function setPlatformWrappers(wrappers, platform) {
  Object.entries(wrappers).forEach(([key, element]) => {
    element.toggle(key === platform);
  });
}

function disableTab(tab, isDisabled) {
  tab.toggleClass("disabled", isDisabled);
}

// Initialization
function initialize() {
  disableTab(steps.step3.tab, true); // Disable step3 tab at start
  setPlatformWrappers(steps.step2.wrappers, null); // Hide all wrappers in step2
  setPlatformWrappers(steps.step3.wrappers, null); // Hide all wrappers in step3
}

// Event Handlers
steps.step1.next.on("click", function () {
  selectedPlatform = $("input[name=btnradio]:checked", "#custom-step").val();
  console.log("คุณเลือก " + selectedPlatform);

  activateStep(steps.step1, steps.step2);
  setPlatformWrappers(steps.step2.wrappers, selectedPlatform);
  disableTab(steps.step3.tab, false); // Enable step3 tab
});

steps.step2.prev.on("click", function () {
  activateStep(steps.step2, steps.step1);
  disableTab(steps.step3.tab, true); // Disable step3 tab
});

steps.step2.next.on("click", function () {
  activateStep(steps.step2, steps.step3);
  setPlatformWrappers(steps.step3.wrappers, selectedPlatform);
});

steps.step3.prev.on("click", function () {
  activateStep(steps.step3, steps.step2);
  setPlatformWrappers(steps.step2.wrappers, selectedPlatform);
});

steps.step3.finish.on("click", function () {
  const $me = $(this);
  const formData = $("#custom-step").serialize(); // ดึงข้อมูลจากฟอร์มในรูปแบบ URL-encoded

  $me.prop("disabled", true);

  // ตรวจสอบข้อมูลตามแพลตฟอร์ม
  if (!validatePlatformInputs(selectedPlatform)) {
    $me.prop("disabled", false);
    return false;
  }

  // ส่งข้อมูลไปยังเซิร์ฟเวอร์
  $.ajax({
    url: `${serverUrl}/setting`,
    type: "POST",
    data: formData,
    success: function (response) {
      console.log("ข้อมูลถูกส่งเรียบร้อย:", response);

      let $data = response;

      if ($data.success == 1) {
        Swal.fire("ข้อมูลถูกส่งเรียบร้อย").then(() => {
          ajaxCheckConnect($data.platform, $data.userSocialID);
        });
      } else {
        Swal.fire({
          title: "เกิดข้อผิดพลาด!",
          text: response.message,
          icon: "error",
          confirmButtonText: "ตกลง",
        }).then((result) => {
          if (result.isConfirmed) {
            // location.reload(); // รีโหลดหน้าเว็บ
          }
        });
      }
    },
    error: function (xhr, status, error) {
      console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);
      alert("เกิดข้อผิดพลาดในการส่งข้อมูล กรุณาลองอีกครั้ง");
      $me.prop("disabled", false);
    },
  });
});

$(".btnCheckConnect").on("click", function () {
  let $me = $(this);

  let $platform = $me.data("platform"),
    $userSocialID = $me.data("user-social-id");

  ajaxCheckConnect($platform, $userSocialID, $me);
});

$(".btnDelete").on("click", function () {
  let $me = $(this);

  $me.prop("disabled", true);

  let dataObj = {};

  let $platform = $me.data("platform"),
    $userSocialID = $me.data("user-social-id");

  $me.attr("disabled", true);

  Swal.fire({
    title: "คุณต้องการลบ ?",
    text: "กรุณาระบุเหตุผล",
    icon: "warning",
    input: "text",
    inputPlaceholder: "กรุณาระบุเหตุผลที่ต้องการยกเลิก",
    showCancelButton: true,
    confirmButtonText: "ตกลง",
    cancelButtonText: "ยกเลิก",
    dangerMode: true,
  }).then(async (result) => {
    if (result.isConfirmed) {
      dataObj = {
        platform: $platform,
        userSocialID: $userSocialID,
        description: result.value,
      };

      $.ajax({
        type: "POST",
        url: `${serverUrl}/remove-social`,
        data: JSON.stringify(dataObj),
        contentType: "application/json; charset=utf-8",
      })
        .done(function (res) {
          if (res.success) {
            Swal.fire({
              title: "สำเร็จ",
              icon: "success",
              timer: 2000,
              showConfirmButton: false,
            });

            location.reload(); // รีโหลดหน้าเว็บ
          } else {
            Swal.fire({
              title: res.messages,
              text: "Redirecting...",
              icon: "warning",
              timer: 2000,
              showConfirmButton: false,
            });
          }
        })
        .fail(function (err) {
          const message =
            err.responseJSON?.messages ||
            "ไม่สามารถอัพเดทได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ให้บริการ";
          Swal.fire({
            title: message,
            text: "Redirecting...",
            icon: "warning",
            timer: 2000,
            showConfirmButton: false,
          });
        });
    } else {
      $me.attr("disabled", false);
    }
  });
});

function validatePlatformInputs(platform) {
  const platformValidators = {
    Facebook: () => {
      return validateField(
        'input[name="facebook_social_name"]',
        "กรุณาใส่ชื่อ"
      );
      // validateField('input[name="fb_token"]', "กรุณาใส่ Token")
    },
    Line: () => {
      return (
        validateField('input[name="line_social_name"]', "กรุณาใส่ชื่อ") &&
        validateField('input[name="line_channel_id"]', "กรุณาใส่ Channel ID") &&
        validateField(
          'input[name="line_channel_secret"]',
          "กรุณาใส่ Channel Secret"
        )
      );
    },
    WhatsApp: () => {
      return (
        validateField('input[name="whatsapp_social_name"]', "กรุณาใส่ชื่อ") &&
        validateField('input[name="whatsapp_token"]', "กรุณาใส่ Token") &&
        validateField(
          'input[name="whatsapp_phone_number_id"]',
          "กรุณาใส่ Phone Number ID"
        )
      );
    },
    Instagram: () => true, // ไม่มีฟิลด์ต้องตรวจสอบสำหรับ Instagram
    Tiktok: () => true, // ไม่มีฟิลด์ต้องตรวจสอบสำหรับ Tiktok
  };

  // เรียกฟังก์ชันตรวจสอบข้อมูลตามแพลตฟอร์ม
  return platformValidators[platform] ? platformValidators[platform]() : true;
}

function validateField(selector, errorMessage) {
  const $field = $(selector);
  if ($field.val().trim() === "") {
    alert(errorMessage);
    return false;
  }
  return true;
}

function ajaxCheckConnect($platform, $userSocialID, actionBy = null) {
  if (actionBy != null) {
    actionBy.prop("disabled", true);
  }

  $.ajax({
    url: `${serverUrl}/check/connection`,
    type: "POST",
    data: {
      platform: $platform,
      userSocialID: $userSocialID,
    },
    success: function (response) {
      let $data = response;

      if (response.success == 1) {
        if (actionBy != null) {
          let $wrapper = $("#userSocialWrapper-" + $userSocialID);

          // เปิดสถานะได้
          if ($data.data == "1") {
            actionBy.prop("disabled", false);
            $wrapper
              .find(".userSocialStatus")
              .html(
                '<span class="badge rounded text-success bg-transparent border border-primary ms-1 p-1">เชื่อมต่อแล้ว</span>'
              );
          }

          // เชื่อมต่อไม่ติด
          else {
            console.log("เชื่อมต่อไม่ติด");
            actionBy.prop("disabled", false);
            $wrapper
              .find(".userSocialStatus")
              .html(
                '<span class="badge rounded text-danger bg-transparent border border-danger ms-1 p-1">หลุดการเชื่อมต่อ</span>'
              );

            Swal.fire({
              title: "เกิดข้อผิดพลาด!",
              text: response.message,
              icon: "error",
              confirmButtonText: "ตกลง",
            }).then((result) => {
              if (result.isConfirmed) {
                // location.reload(); // รีโหลดหน้าเว็บ
              }
            });
          }
        } else {
          location.reload(); // รีโหลดหน้าเมื่อผู้ใช้ปิดข้อความแจ้งเตือน
        }
      }
    },
    error: function (xhr, status, error) {
      console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);
    },
  });
}

// Tab click prevention for disabled tabs
[steps.step1.tab, steps.step2.tab, steps.step3.tab].forEach((tab) => {
  tab.on("click", function (e) {
    if (tab.hasClass("disabled")) {
      e.preventDefault();
      console.log("Tab is disabled");
    }
  });
});

// Optional tab click handlers
steps.step1.tab.on("click", function (e) {
  e.preventDefault();
  activateStep(steps.step2, steps.step1);
  disableTab(steps.step3.tab, true);
});

steps.step2.tab.on("click", function (e) {
  e.preventDefault();
  selectedPlatform = $("input[name=btnradio]:checked", "#custom-step").val();
  console.log("คุณเลือก " + selectedPlatform);

  activateStep(steps.step1, steps.step2);
  setPlatformWrappers(steps.step2.wrappers, selectedPlatform);
  disableTab(steps.step3.tab, false);
});

steps.step3.tab.on("click", function (e) {
  e.preventDefault();
  activateStep(steps.step2, steps.step3);
  setPlatformWrappers(steps.step3.wrappers, selectedPlatform);
});

$(".btnInputToken").on("click", function () {
  let $me = $(this);

  let $userSocialID = $me.data("user-social-id");

  let $form = $("#form-fb-token");

  $inputUserSocialID = $form.find('input[name="user_social_id"]');
  $inputUserSocialID.val($userSocialID);
});

$("#btnSaveFbToken").on("click", function () {
  let $me = $(this);

  let $userSocialID = $('input[name="user_social_id"]').val(),
    $fbToken = $('input[name="fb_token"]').val();

  dataObj = {
    userSocialID: $userSocialID,
    fbToken: $fbToken,
  };

  $me.prop("disabled", true);

  $.ajax({
    type: "POST",
    url: `${serverUrl}/setting/save-token`,
    data: JSON.stringify(dataObj),
    contentType: "application/json; charset=utf-8",
  })
    .done(function (res) {
      if (res.success) {
        Swal.fire({
          title: "สำเร็จ",
          icon: "success",
          timer: 2000,
          showConfirmButton: false,
        });

        $me.prop("disabled", false);

        $btn = $("#userSocialWrapper-" + $userSocialID);

        ajaxCheckConnect(
          "Facebook",
          $userSocialID,
          $btn.find(".btnCheckConnect")
        );

        $("#formModalDefault").modal("hide");
      } else {
        Swal.fire({
          title: res.messages,
          text: "Redirecting...",
          icon: "warning",
          timer: 2000,
          showConfirmButton: false,
        });
      }
    })
    .fail(function (err) {
      const message =
        err.responseJSON?.messages ||
        "ไม่สามารถอัพเดทได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ให้บริการ";
      Swal.fire({
        title: message,
        text: "Redirecting...",
        icon: "warning",
        timer: 2000,
        showConfirmButton: false,
      });
    });
});

// Run initialization
initialize();
