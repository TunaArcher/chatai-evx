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
      Instagram: $(".step2-instagram-wrapper"),
      Tiktok: $(".step2-tiktok-wrapper"),
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
      Instagram: $(".step3-instagram-wrapper"),
      Tiktok: $(".step3-tiktok-wrapper"),
    },
  },

  facebookStep2: {
    tab: $("#facebook-step2-tab"),
    content: $("#facebook-step2"),
    prev: $("#facebookStep2Prev"),
    wrappers: {
      Facebook: $(".step2-facebook-wrapper"),
    },
  },

  instagramStep2: {
    tab: $("#instagram-step2-tab"),
    content: $("#instagram-step2"),
    prev: $("#instagramStep2Prev"),
    wrappers: {
      Instagram: $(".step2-instagram-wrapper"),
    },
  },

  whatsAppStep2: {
    tab: $("#whatsapp-step2-tab"),
    content: $("#whatsapp-step2"),
    prev: $("#whatsAppStep2Prev"),
    wrappers: {
      WhatsApp: $(".step2-whatsapp-wrapper"),
    },
  },
};

let selectedPlatform = "";

// Utility Functions
function copyToClipboard(url) {
  // สร้าง Element ชั่วคราวสำหรับคัดลอก
  const tempInput = document.createElement("input");
  tempInput.value = url;
  document.body.appendChild(tempInput);

  // เลือกและคัดลอกข้อความ
  tempInput.select();
  tempInput.setSelectionRange(0, 99999); // รองรับบนมือถือ
  document.execCommand("copy");

  // ลบ Element ชั่วคราว
  document.body.removeChild(tempInput);

  // แสดงข้อความแจ้งเตือน
  notyf("คัดลอกแล้ว", "success");
}

function notyf(message, type) {
  const notyf = new Notyf({
    position: {
      x: "right",
      y: "top",
    },
  });

  if (type == "success") {
    const notification = notyf.success(message);
    // notyf.dismiss(notification);
  }

  if (type == "error") {
    const notification = notyf.error(message);
    // notyf.dismiss(notification);
  }
}

function generateRandomState() {
  const array = new Uint8Array(16);
  crypto.getRandomValues(array); // ใช้ API สำหรับสร้างตัวเลขสุ่มที่ปลอดภัย
  return Array.from(array, (byte) => byte.toString(16).padStart(2, "0")).join(
    ""
  );
}

function disableTab(tab, isDisabled) {
  tab.toggleClass("disabled", isDisabled);
}

// Main Functions
function openOAuthInstagramPopup() {
  // สร้างค่า state แบบสุ่ม
  const state = generateRandomState();
  localStorage.setItem("oauth_state", state); // บันทึก state ใน localStorage

  // let $scope =
  //   "instagram_business_basic,instagram_business_content_publish,instagram_business_manage_messages,instagram_business_manage_comments";
  let $scope = "instagram_business_basic,instagram_business_manage_messages";

  const oauthUrl =
    "https://www.instagram.com/oauth/authorize?" +
    new URLSearchParams({
      client_id: `${window.IG_APP_ID}`,
      redirect_uri: `${serverUrl}/callback/instagram`,
      scope: $scope,
      response_type: "code",
      state: state,
    });

  const popupWidth = 800;
  const popupHeight = 700;
  const screenX = window.screenX ?? window.screenLeft;
  const screenY = window.screenY ?? window.screenTop;
  const screenWidth = window.innerWidth ?? document.documentElement.clientWidth;
  const screenHeight =
    window.innerHeight ?? document.documentElement.clientHeight;

  const left = screenX + (screenWidth - popupWidth) / 2;
  const top = screenY + (screenHeight - popupHeight) / 2;

  const popup = window.open(
    oauthUrl,
    "oauthPopup",
    `width=${popupWidth},height=${popupHeight},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`
  );

  // ตรวจสอบว่า popup ถูกปิดหรือยัง
  const popupInterval = setInterval(() => {
    if (popup.closed) {
      clearInterval(popupInterval);

      Swal.fire({
        title: "สำเร็จ",
        icon: "success",
        timer: 2000,
        showConfirmButton: false,
      });

      location.reload(); // รีโหลดหน้าเว็บ
    }
  }, 500);
}

function openOAuthWhatsAppPopup() {
  // สร้างค่า state แบบสุ่ม
  const state = generateRandomState();
  localStorage.setItem("oauth_state", state); // บันทึก state ใน localStorage

  // const $scope = "whatsapp_business_management,business_management,whatsapp_business_messaging";
  const $scope = "whatsapp_business_management,whatsapp_business_messaging";

  const oauthUrl =
    "https://www.facebook.com/v21.0/dialog/oauth?" +
    new URLSearchParams({
      client_id: `${window.APP_ID}`,
      redirect_uri: `${serverUrl}/callback/whatsapp`,
      scope: $scope,
      response_type: "code",
      state: state,
    });

  const popupWidth = 800;
  const popupHeight = 700;
  const screenX = window.screenX ?? window.screenLeft;
  const screenY = window.screenY ?? window.screenTop;
  const screenWidth = window.innerWidth ?? document.documentElement.clientWidth;
  const screenHeight =
    window.innerHeight ?? document.documentElement.clientHeight;

  const left = screenX + (screenWidth - popupWidth) / 2;
  const top = screenY + (screenHeight - popupHeight) / 2;

  const popup = window.open(
    oauthUrl,
    "oauthPopup",
    `width=${popupWidth},height=${popupHeight},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`
  );

  // ตรวจสอบว่า popup ถูกปิดหรือยัง
  const popupInterval = setInterval(() => {
    if (popup.closed) {
      clearInterval(popupInterval);
      // alert("Login completed! Please check your session or token.");

      WABListBusinessAccounts();
    }
  }, 500);
}

function openOAuthFacebookPopup() {
  // สร้างค่า state แบบสุ่ม
  const state = generateRandomState();
  localStorage.setItem("oauth_state", state); // บันทึก state ใน localStorage

  // const $scope =
  //   "pages_messaging pages_manage_metadata pages_read_engagement pages_read_user_content pages_read_engagement";
  const $scope = "pages_messaging pages_manage_metadata";

  const oauthUrl =
    "https://www.facebook.com/v21.0/dialog/oauth?" +
    new URLSearchParams({
      client_id: `${window.APP_ID}`,
      redirect_uri: `${serverUrl}/callback/facebook`,
      scope: $scope,
      response_type: "code",
      state: state,
    });

  console.log(oauthUrl);

  const popupWidth = 800;
  const popupHeight = 700;
  const screenX = window.screenX ?? window.screenLeft;
  const screenY = window.screenY ?? window.screenTop;
  const screenWidth = window.innerWidth ?? document.documentElement.clientWidth;
  const screenHeight =
    window.innerHeight ?? document.documentElement.clientHeight;

  const left = screenX + (screenWidth - popupWidth) / 2;
  const top = screenY + (screenHeight - popupHeight) / 2;

  const popup = window.open(
    oauthUrl,
    "oauthPopup",
    `width=${popupWidth},height=${popupHeight},left=${left},top=${top},resizable=yes,scrollbars=yes,status=yes`
  );

  // ตรวจสอบว่า popup ถูกปิดหรือยัง
  const popupInterval = setInterval(() => {
    if (popup.closed) {
      clearInterval(popupInterval);
      // alert("Login completed! Please check your session or token.");

      FbPagesList();
    }
  }, 500);
}

function FbPagesList() {
  $("#chat-box-preloader").show();

  $.ajax({
    type: "GET",
    url: `${serverUrl}/auth/FbPagesList`,
  })
    .done(function (res) {
      let $pages = res.data.pages; // ข้อมูลเพจจาก JSON
      let $wrapper = $(".step2-facebook-wrapper"); // div ที่เราจะใส่ข้อมูล

      // เคลียร์ HTML เดิมใน wrapper
      $wrapper.empty();

      // วนลูปข้อมูลเพจ
      $pages.forEach((page) => {
        let $btnConnect = `<button type="button" class="btnConnectToApp btn btn-primary btn-sm px-2" data-platform="Facebook" data-page-id="${page.id}" data-src="${page.ava}">เชื่อมต่อ</button>`;
        if (page.status == "connected") {
          $btnConnect = `<button type="button" class="btnConnectToApp btn btn-primary btn-sm px-2 disabled" data-platform="Facebook" data-page-id="${page.id}" data-src="${page.ava}">เชื่อมต่อแล้ว</button>`;
        }
        let pageHtml = `
              <div class="card">
                <div class="card-body py-0">
                    <div class="row">
                        <div class="col-md-10">
                            <a href="#" class="">                                               
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="${page.ava}" alt="" class="thumb-lg rounded-circle">
                                    </div>
                                    <div class="flex-grow-1 ms-2 text-truncate">
                                        <h6 class="my-1 fw-medium text-dark fs-14">${page.name}</h6>
                                    </div><!--end media-body-->
                                </div><!--end media-->
                            </a>
                        </div> <!--end col--> 
                        <div class="col-md-2 text-end align-self-center mt-sm-2 mt-lg-0">
                            ${$btnConnect}
                        </div> <!--end col-->                                                      
                    </div><!--end row-->         
                </div><!--end card-body--> 
            </div>
            <hr>
          `;

        // ใส่ HTML ที่สร้างลงใน wrapper
        $wrapper.append(pageHtml);
      });

      $("#chat-box-preloader").hide();
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
}

function WABListBusinessAccounts() {
  $.ajax({
    type: "GET",
    url: `${serverUrl}/auth/WABListBusinessAccounts`,
  })
    .done(function (res) {
      let $pages = res.data.pages; // ข้อมูลเพจจาก JSON
      let $wrapper = $(".step2-whatsapp-wrapper"); // div ที่เราจะใส่ข้อมูล

      // เคลียร์ HTML เดิมใน wrapper
      $wrapper.empty();

      // วนลูปข้อมูลเพจ
      $pages.forEach((page) => {
        let $btnConnect = `<button type="button" class="btnConnectToApp btn btn-primary btn-sm px-2" data-platform="WhatsApp" data-page-id="${page.id}" data-page-name="${page.name}">เชื่อมต่อ</button>`;
        if (page.status == "connected") {
          $btnConnect = `<button type="button" class="btnConnectToApp btn btn-primary btn-sm px-2 disabled" data-platform="WhatsApp" data-page-id="${page.id}" data-page-name="${page.name}">เชื่อมต่อแล้ว</button>`;
        }
        let pageHtml = `
              <div class="card">
                <div class="card-body py-0">
                    <div class="row">
                        <div class="col-md-10">
                            <a href="#" class="">                                               
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6b/WhatsApp.svg/800px-WhatsApp.svg.png" alt="" class="thumb-lg rounded-circle">
                                    </div>
                                    <div class="flex-grow-1 ms-2 text-truncate">
                                        <h6 class="my-1 fw-medium text-dark fs-14">${page.name}</h6>
                                    </div><!--end media-body-->
                                </div><!--end media-->
                            </a>
                        </div> <!--end col--> 
                        <div class="col-md-2 text-end align-self-center mt-sm-2 mt-lg-0">
                            ${$btnConnect}
                        </div> <!--end col-->                                                      
                    </div><!--end row-->         
                </div><!--end card-body--> 
            </div>
            <hr>
          `;

        // ใส่ HTML ที่สร้างลงใน wrapper
        $wrapper.append(pageHtml);
      });
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
}

// function IGListBusinessAccounts() {
//   $.ajax({
//     type: "GET",
//     url: `${serverUrl}/auth/IGListBusinessAccounts`,
//   })
//     .done(function (res) {
//       let $pages = res.data.pages; // ข้อมูลเพจจาก JSON
//       let $wrapper = $(".step2-instagram-wrapper"); // div ที่เราจะใส่ข้อมูล

//       // เคลียร์ HTML เดิมใน wrapper
//       $wrapper.empty();

//       // วนลูปข้อมูลเพจ
//       $pages.forEach((page) => {
//         let $btnConnect = `<button type="button" class="btnConnectToApp btn btn-primary btn-sm px-2" data-platform="Instagram" data-page-id="${page.id}" data-page-name="${page.name}">เชื่อมต่อ</button>`;
//         if (page.status == "connected") {
//           $btnConnect = `<button type="button" class="btnConnectToApp btn btn-primary btn-sm px-2 disabled" data-platform="Instagram" data-page-id="${page.id}" data-page-name="${page.name}">เชื่อมต่อแล้ว</button>`;
//         }
//         let pageHtml = `
//               <div class="card">
//                 <div class="card-body py-0">
//                     <div class="row">
//                         <div class="col-md-10">
//                             <a href="#" class="">
//                                 <div class="d-flex align-items-center">
//                                     <div class="flex-shrink-0">
//                                         <img src="${page.ava}" alt="" class="thumb-lg rounded-circle">
//                                     </div>
//                                     <div class="flex-grow-1 ms-2 text-truncate">
//                                         <h6 class="my-1 fw-medium text-dark fs-14">${page.name}</h6>
//                                     </div><!--end media-body-->
//                                 </div><!--end media-->
//                             </a>
//                         </div> <!--end col-->
//                         <div class="col-md-2 text-end align-self-center mt-sm-2 mt-lg-0">
//                             ${$btnConnect}
//                         </div> <!--end col-->
//                     </div><!--end row-->
//                 </div><!--end card-body-->
//             </div>
//             <hr>
//           `;

//         // ใส่ HTML ที่สร้างลงใน wrapper
//         $wrapper.append(pageHtml);
//       });
//     })
//     .fail(function (err) {
//       const message =
//         err.responseJSON?.messages ||
//         "ไม่สามารถอัพเดทได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ให้บริการ";
//       Swal.fire({
//         title: message,
//         text: "Redirecting...",
//         icon: "warning",
//         timer: 2000,
//         showConfirmButton: false,
//       });
//     });
// }

function ajaxCheckConnect($platform, $userSocialID, actionBy = null) {
  if (actionBy != null) actionBy.prop("disabled", true);

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

            notyf("เชื่อมต่อสำเร็จ", "success");
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

            notyf("Token หรือ API มีปัญหา กรุณาติดต่อทีมงาน", "error");
          }

          actionBy.prop("disabled", false);
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
        validateField('input[name="whatsapp_token"]', "กรุณาใส่ Token")
        // validateField(
        //   'input[name="whatsapp_phone_number_id"]',
        //   "กรุณาใส่ Phone Number ID"
        // )
      );
    },
    Instagram: () => {
      return (
        validateField('input[name="instagram_social_name"]', "กรุณาใส่ชื่อ") &&
        validateField('input[name="instagram_token"]', "กรุณาใส่ Token")
      );
    }, // ไม่มีฟิลด์ต้องตรวจสอบสำหรับ Instagram
    Tiktok: () => {
      return (
        validateField('input[name="tiktok_social_name"]', "กรุณาใส่ชื่อ") &&
        validateField('input[name="tiktok_token"]', "กรุณาใส่ Token")
      );
    }, // ไม่มีฟิลด์ต้องตรวจสอบสำหรับ Tiktok
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

// Initialization
function initialize() {
  disableTab(steps.step3.tab, true); // Disable step3 tab at start
  setPlatformWrappers(steps.step2.wrappers, null); // Hide all wrappers in step2
  setPlatformWrappers(steps.step3.wrappers, null); // Hide all wrappers in step3
}

// Event Handlers
steps.step1.next.on("click", function () {
  // selectedPlatform = $("input[name=btnradio]:checked", "#custom-step").val();
  console.log("คุณเลือก " + selectedPlatform);

  if (!selectedPlatform) {
    alert("เลือก Social ที่จะเชื่อมต่อ");
    return false;
  }

  switch (selectedPlatform) {
    case "Facebook":
      $(".step2-facebook-wrapper").html("");

      $.ajax({
        type: "GET",
        url: `${serverUrl}/check/token/Facebook`,
      })
        .done(function (res) {
          let $data = res.data;
          console.log($data);
          if ($data == "NO TOKEN") {
            openOAuthFacebookPopup();
          } else {
            FbPagesList();
          }
        })
        .fail(function (err) {
          console.log(err);
        });

      activateStep(steps.step1, steps.facebookStep2);
      setPlatformWrappers(steps.facebookStep2.wrappers, selectedPlatform);
      break;

    case "Instagram":
      $(".step2-instagram-wrapper").html("");

      $.ajax({
        type: "GET",
        url: `${serverUrl}/check/token/Instagram`,
      })
        .done(function (res) {
          let $data = res.data;
          if ($data == "NO TOKEN") {
            openOAuthInstagramPopup();
          } else {
            alert("คุณเชื่อมต่อแล้ว");
          }
        })
        .fail(function (err) {
          console.log(err);
        });

      // activateStep(steps.step1, steps.instagramStep2);
      // setPlatformWrappers(steps.instagramStep2.wrappers, selectedPlatform);
      break;

    case "WhatsApp":
      $(".step2-whatsapp-wrapper").html("");

      $.ajax({
        type: "GET",
        url: `${serverUrl}/check/token/WhatsApp`,
      })
        .done(function (res) {
          let $data = res.data;
          if ($data == "NO TOKEN") {
            openOAuthWhatsAppPopup();
          } else {
            WABListBusinessAccounts();
          }
        })
        .fail(function (err) {
          console.log(err);
        });

      activateStep(steps.step1, steps.whatsAppStep2);
      setPlatformWrappers(steps.whatsAppStep2.wrappers, selectedPlatform);
      break;

    default:
      activateStep(steps.step1, steps.step2);
      setPlatformWrappers(steps.step2.wrappers, selectedPlatform);
      disableTab(steps.step3.tab, false); // Enable step3 tab
  }
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
  // const formData = $("#custom-step").serialize();
  let formData = new FormData($("#custom-step")[0]);
  formData.append("platform", selectedPlatform); // เพิ่มข้อมูลแบบ Dynamic

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
    processData: false,
    contentType: false,
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

        $me.prop("disabled", false);
      }
    },
    error: function (xhr, status, error) {
      console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);

      // ดึงข้อความจาก response JSON
      const errorMessage =
        xhr.responseJSON?.message || "เกิดข้อผิดพลาดบางอย่าง";

      Swal.fire({
        title: "เกิดข้อผิดพลาด!",
        text: errorMessage,
        icon: "error",
        confirmButtonText: "ตกลง",
      }).then((result) => {
        if (result.isConfirmed) {
          // location.reload(); // รีโหลดหน้าเว็บ
        }
      });

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

  switch (selectedPlatform) {
    case "Facebook":
      $("#facebook-step2-tab").show();
      $("#instagram-step2-tab").hide();
      $("#whatsapp-step2-tab").hide();
      $("#step2-tab").hide();
      $("#step3-tab").hide();
      break;

    case "Instagram":
      $("#instagram-step2-tab").show();
      $("#facebook-step2-tab").hide();
      $("#whatsapp-step2-tab").hide();
      $("#step2-tab").hide();
      $("#step3-tab").hide();
      break;

    case "WhatsApp":
      $("#whatsapp-step2-tab").show();
      $("#facebook-step2-tab").hide();
      $("#instagram-step2-tab").hide();
      $("#step2-tab").hide();
      $("#step3-tab").hide();
      break;

    default:
      $("#facebook-step2-tab").hide();
      $("#instagram-step2-tab").hide();
      $("#whatsapp-step2-tab").hide();
      $("#step2-tab").show();
      $("#step3-tab").show();
  }
});

steps.step2.tab.on("click", function (e) {
  e.preventDefault();

  console.log("คุณเลือก " + selectedPlatform);

  if (!selectedPlatform) {
    alert("เลือก Social ที่จะเชื่อมต่อ");
    activateStep(steps.step2, steps.step1);
    disableTab(steps.step3.tab, true);
  } else {
    activateStep(steps.step1, steps.step2);
    setPlatformWrappers(steps.step2.wrappers, selectedPlatform);
    disableTab(steps.step3.tab, false);
  }

  if (selectedPlatform == "Facebook") {
    $("#facebook-step2-tab").show();
    $("#step2-tab").hide();
    $("#step3-tab").hide();
  } else {
    $("#facebook-step2-tab").hide();
    $("#step2-tab").show();
    $("#step3-tab").show();
  }
});

steps.step3.tab.on("click", function (e) {
  e.preventDefault();
  activateStep(steps.step2, steps.step3);
  setPlatformWrappers(steps.step3.wrappers, selectedPlatform);
});

steps.facebookStep2.tab.on("click", function (e) {
  e.preventDefault();

  console.log("คุณเลือก " + selectedPlatform);

  $(".step2-facebook-wrapper").html("");

  $.ajax({
    type: "GET",
    url: `${serverUrl}/check/token/Facebook`,
  })
    .done(function (res) {
      let $data = res.data;
      if ($data == "NO TOKEN") {
        openOAuthFacebookPopup();
      } else {
        FbPagesList();
      }
    })
    .fail(function (err) {
      console.log(err);
    });

  activateStep(steps.step1, steps.facebookStep2);
  setPlatformWrappers(steps.facebookStep2.wrappers, selectedPlatform);
});

steps.instagramStep2.tab.on("click", function (e) {});

steps.whatsAppStep2.tab.on("click", function (e) {
  e.preventDefault();

  console.log("คุณเลือก " + selectedPlatform);

  $(".step2-whatsapp-wrapper").html("");

  $.ajax({
    type: "GET",
    url: `${serverUrl}/check/token/WhatsApp`,
  })
    .done(function (res) {
      let $data = res.data;
      if ($data == "NO TOKEN") {
        openOAuthWhatsAppPopup();
      } else {
        WABListBusinessAccounts();
      }
    })
    .fail(function (err) {
      console.log(err);
    });

  activateStep(steps.step1, steps.whatsAppStep2);
  setPlatformWrappers(steps.whatsAppStep2.wrappers, selectedPlatform);
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
          // icon: "success",
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

$(".btnAI").on("click", function () {
  let $me = $(this);

  let $platform = $me.data("platform"),
    $userSocialID = $me.data("user-social-id");

  dataObj = {
    platform: $platform,
    userSocialID: $userSocialID,
  };

  $me.prop("disabled", true);

  $.ajax({
    type: "POST",
    url: `${serverUrl}/setting/ai`,
    data: JSON.stringify(dataObj),
    contentType: "application/json; charset=utf-8",
  })
    .done(function (res) {
      if (res.success) {
        Swal.fire({
          title: "สำเร็จ",
          // icon: "success",
          timer: 2000,
          showConfirmButton: false,
        });

        if (res.data.newStatus == "on") {
          $me.html(`<i class="fas fa-robot me-1"></i> กำลังใช้งาน AI`);
          $me.prop("disabled", false);
        } else {
          $me.html(`<i class="fas fa-robot me-1"></i> เปิดใช้ AI`);
          $me.prop("disabled", false);
        }

        // TODO:: HANDLE
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

$(".step2-facebook-wrapper").on("click", ".btnConnectToApp", function () {
  let $me = $(this);

  let $platform = $me.data("platform");
  let $pageID = $me.data("page-id");
  let $src = $me.data("src");

  dataObj = {
    platform: $platform,
    pageID: $pageID,
    src: $src,
  };

  $me.prop("disabled", true);

  $.ajax({
    url: `${serverUrl}/connect/connectToApp`,
    type: "POST",
    data: JSON.stringify(dataObj),
    contentType: "application/json; charset=utf-8",
    success: function (response) {
      if (response.success) {
        $me.html("เชื่อมต่อแล้ว");

        Swal.fire({
          title: "สำเร็จ",
          icon: "success",
          timer: 2000,
          showConfirmButton: false,
        });

        location.reload(); // รีโหลดหน้าเว็บ
      } else {
        $me.prop("disabled", false);
      }
    },
    error: function (xhr, status, error) {
      console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);
      alert("เกิดข้อผิดพลาดในการส่งข้อมูล กรุณาลองอีกครั้ง");
      $me.prop("disabled", false);
    },
  });
});

$(".step2-instagram-wrapper").on("click", ".btnConnectToApp", function () {
  let $me = $(this);

  let $platform = $me.data("platform");
  let $pageID = $me.data("page-id");
  let $pageName = $me.data("page-name");

  dataObj = {
    platform: $platform,
    pageID: $pageID,
    pageName: $pageName,
  };

  $me.prop("disabled", true);

  $.ajax({
    url: `${serverUrl}/connect/connectToApp`,
    type: "POST",
    data: JSON.stringify(dataObj),
    contentType: "application/json; charset=utf-8",
    success: function (response) {
      if (response.success) {
        $me.html("เชื่อมต่อแล้ว");

        Swal.fire({
          title: "สำเร็จ",
          icon: "success",
          timer: 2000,
          showConfirmButton: false,
        });

        location.reload(); // รีโหลดหน้าเว็บ
      } else {
        $me.prop("disabled", false);
      }
    },
    error: function (xhr, status, error) {
      console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);
      alert("เกิดข้อผิดพลาดในการส่งข้อมูล กรุณาลองอีกครั้ง");
      $me.prop("disabled", false);
    },
  });
});

$(".step2-whatsapp-wrapper").on("click", ".btnConnectToApp", function () {
  let $me = $(this);

  let $platform = $me.data("platform");
  let $pageID = $me.data("page-id");
  let $pageName = $me.data("page-name");

  dataObj = {
    platform: $platform,
    pageID: $pageID,
    pageName: $pageName,
  };

  $me.prop("disabled", true);

  $.ajax({
    url: `${serverUrl}/connect/connectToApp`,
    type: "POST",
    data: JSON.stringify(dataObj),
    contentType: "application/json; charset=utf-8",
    success: function (response) {
      if (response.success) {
        $me.html("เชื่อมต่อแล้ว");

        Swal.fire({
          title: "สำเร็จ",
          icon: "success",
          timer: 2000,
          showConfirmButton: false,
        });

        location.reload(); // รีโหลดหน้าเว็บ
      } else {
        $me.prop("disabled", false);
      }
    },
    error: function (xhr, status, error) {
      console.error("เกิดข้อผิดพลาดในการส่งข้อมูล:", error);
      alert("เกิดข้อผิดพลาดในการส่งข้อมูล กรุณาลองอีกครั้ง");
      $me.prop("disabled", false);
    },
  });
});

$(".radio-item").click(function () {
  // ลบ class 'selected' ออกจากไอคอนอื่น ๆ
  $(".radio-icon").removeClass("selected");
  // เพิ่ม class 'selected' ในไอคอนที่คลิก
  $(this).find(".radio-icon").addClass("selected");
  // ดึงค่าที่เลือก (value)
  selectedPlatform = $(this).data("value");

  console.log("Selected:", selectedPlatform);

  switch (selectedPlatform) {
    case "Facebook":
      $("#facebook-step2-tab").show();
      $("#instagram-step2-tab").hide();
      $("#whatsapp-step2-tab").hide();
      $("#step2-tab").hide();
      $("#step3-tab").hide();
      break;

    case "Instagram":
      $("#instagram-step2-tab").show();
      $("#facebook-step2-tab").hide();
      $("#whatsapp-step2-tab").hide();
      $("#step2-tab").hide();
      $("#step3-tab").hide();
      break;

    case "WhatsApp":
      $("#whatsapp-step2-tab").show();
      $("#facebook-step2-tab").hide();
      $("#instagram-step2-tab").hide();
      $("#step2-tab").hide();
      $("#step3-tab").hide();
      break;

    default:
      $("#facebook-step2-tab").hide();
      $("#instagram-step2-tab").hide();
      $("#whatsapp-step2-tab").hide();
      $("#step2-tab").show();
      $("#step3-tab").show();
  }
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

// Run initialization
initialize();
