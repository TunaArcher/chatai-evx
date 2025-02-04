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

const multiSelectSocial = new Selectr("#multiSelectSocial", {
  multiple: true,
  renderOption: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    if (text == "--- คุณยังไม่มี Connect กรุณาไปเพิ่ม ---") {
      return `
            <div style="display: flex; align-items: center;">
                <span>${text}</span>
            </div>
        `;
    }

    return `
        <div style="display: flex; align-items: center;">
          <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
          <span>${text}</span>
        </div>
    `;
  },
  renderSelection: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    // สร้าง HTML สำหรับแสดงผลในส่วนที่เลือก
    return `
            <div style="display: flex; align-items: center;">
                <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
                <span>${text}</span>
            </div>
        `;
  },
});

const multiSelectMember = new Selectr("#multiSelectMember", {
  multiple: true,
  renderOption: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    if (text == "--- คุณยังไม่มีสมาชิก ---") {
      return `
            <div style="display: flex; align-items: center;">
                <span>${text}</span>
            </div>
        `;
    }

    return `
            <div style="display: flex; align-items: center;">
                <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
                <span>${text}</span>
            </div>
        `;
  },
  renderSelection: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    // สร้าง HTML สำหรับแสดงผลในตัวเลือกที่เลือก
    return `
            <div style="display: flex; align-items: center;">
                <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
                <span>${text}</span>
            </div>
        `;
  },
});

const editMultiSelectSocial = new Selectr("#editMultiSelectSocial", {
  multiple: true,
  renderOption: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    // สร้าง HTML สำหรับแสดงผลรูปภาพพร้อมข้อความ
    return `
            <div style="display: flex; align-items: center;">
                <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
                <span>${text}</span>
            </div>
        `;
  },
  renderSelection: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    // สร้าง HTML สำหรับแสดงผลในส่วนที่เลือก
    return `
            <div style="display: flex; align-items: center;">
                <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
                <span>${text}</span>
            </div>
        `;
  },
});

const editMultiSelectMember = new Selectr("#editMultiSelectMember", {
  multiple: true,
  renderOption: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    // สร้าง HTML สำหรับแสดงผลรูปภาพพร้อมข้อความในรายการตัวเลือก
    return `
            <div style="display: flex; align-items: center;">
                <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
                <span>${text}</span>
            </div>
        `;
  },
  renderSelection: function (option) {
    const imgSrc = option.dataset.image;
    const text = option.textContent;

    // สร้าง HTML สำหรับแสดงผลในตัวเลือกที่เลือก
    return `
            <div style="display: flex; align-items: center;">
                <img src="${imgSrc}" alt="${text}" style="width: 20px; height: 20px; margin-right: 5px; border-radius: 50%;">
                <span>${text}</span>
            </div>
        `;
  },
});

const teamLogos = [
  "assets/images/logos/lang-logo/nextjs.png",
  "assets/images/logos/lang-logo/reactjs.png",
  "assets/images/logos/lang-logo/svelte.png",
  "assets/images/logos/lang-logo/vue.png",
  "assets/images/logos/lang-logo/symfony.png",
  "assets/images/logos/lang-logo/nodejs.png",
];

document
  .getElementById("randomLogoButton")
  .addEventListener("click", function () {
    // สุ่ม index ใน Array
    const randomIndex = Math.floor(Math.random() * teamLogos.length);
    const randomLogo = teamLogos[randomIndex];

    // เปลี่ยน src ของรูป
    document.getElementById("teamLogo").src = randomLogo;
  });

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

$(document).ready(function () {
  // -----------------------------------------------------------------------------
  // Eevent
  // -----------------------------------------------------------------------------

  // เชิญเข้าทีม
  $("#btnSendInviteToTeamMember").on("click", function () {
    let $me = $(this);

    // ดึงค่าจากช่องป้อนข้อมูล
    const email = $("#emailInput").val();

    // ตรวจสอบว่ากรอกอีเมลหรือไม่
    if (!email) {
      Swal.fire({
        title: "ระบุข้อมูลให้ครบถ้วน",
        text: "กรุณากรอกอีเมล",
        icon: "warning",
      });

      return;
    } else {
      $me.prop("disabled", true);

      $.ajax({
        url: `${serverUrl}/team/invite-to-member`,
        type: "POST",
        data: JSON.stringify({ email: email }),
        contentType: "application/json",
        success: function (response) {
          if (response.success) {
            notyf(`ส่งคำเชิญไปที่ ${email} สำเร็จ`, "success");
            $("#inviteToTeamMember").modal("hide");
            location.reload();
          } else {
            notyf(`${response.message}`, "error");
          }

          $me.prop("disabled", false);
        },
        error: function (xhr, status, err) {
          const message =
            xhr.responseJSON?.message || // ใช้ responseJSON.message เพื่อดึงข้อความที่ส่งมาจากเซิร์ฟเวอร์
            "ไม่สามารถอัพเดทได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ให้บริการ";

          Swal.fire({
            title: message, // แสดงข้อความที่ดึงมา
            text: "Redirecting...",
            icon: "warning",
            timer: 2000,
            showConfirmButton: false,
          });

          $me.prop("disabled", false);
        },
      });
    }
  });

  // สร้างทีม
  $("#btnSaveTeam").on("click", function () {
    let $me = $(this);

    // Gather data from the form
    const teamLogo = $("#teamLogo").attr("src");
    const teamName = $('input[placeholder="ชื่อทีม *"]').val();
    const teamNote = $('input[placeholder="Note"]').val();
    const connectIds = $("#multiSelectSocial").val();
    const memberIds = $("#multiSelectMember").val();

    // Validate required fields
    if (!teamName || !connectIds || !memberIds) {
      Swal.fire({
        title: "ระบุข้อมูลให้ครบถ้วน",
        text: "กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน",
        icon: "warning",
      });

      return;
    } else {
      $("#wrapper-form-add-team-preloader").show();
      $("#form-add-team").hide();

      $me.prop("disabled", true);

      $.ajax({
        url: `${serverUrl}/team/create`,
        type: "POST",
        data: JSON.stringify({
          team_logo: teamLogo,
          team_name: teamName,
          team_note: teamNote,
          connect_ids: connectIds,
          member_ids: memberIds,
        }),
        contentType: "application/json",
        success: function (response) {
          if (response.success) {
            Swal.fire({
              title: "สำเร็จ",
              icon: "success",
              timer: 2000,
              showConfirmButton: false,
            });

            $me.prop("disabled", false);

            location.reload();
          } else {
            $("#wrapper-form-add-team-preloader").hide();
            $("#form-add-team").show();

            $me.prop("disabled", false);

            Swal.fire({
              title: response.message,
              text: "Redirecting...",
              icon: "warning",
              timer: 2000,
              showConfirmButton: false,
            });
          }
        },
        error: function (xhr, status, err) {
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
        },
      });
    }
  });

  // แก้ไขทีม
  $(".btn-edit-team").on("click", function () {
    const teamId = $(this).data("team-id"); // ดึง ID ทีมจากปุ่ม

    $("#wrapper-form-edit-team-preloader").show();
    $("#form-edit-team").hide();

    // ดึงข้อมูลทีมจากเซิร์ฟเวอร์
    $.ajax({
      url: `${serverUrl}/team/getTeam/${teamId}`, // Endpoint สำหรับดึงข้อมูล
      method: "GET",
      success: function (response) {
        let $data = response.data;

        if (response.success) {
          // กรอกข้อมูลลงใน Modal
          $("#editTeamLogo").attr("src", $data.icon);
          $("#editTeamName").val($data.name);
          $("#editTeamNote").val($data.note);
          $("#editTeamID").val($data.id);

          // ดึง connect_ids และ member_ids จาก response
          const socialIds = $data.socials || []; // Array ของ Connect IDs
          const memberIds = $data.members || []; // Array ของ Member IDs

          console.log(socialIds);

          // ล้างค่าที่เลือกก่อน
          editMultiSelectSocial.clear();
          editMultiSelectMember.clear();

          if (editMultiSelectSocial && editMultiSelectMember) {
            // ตั้งค่า selected ใน Selectr โดยใช้ค่าที่มีอยู่ใน HTML
            editMultiSelectSocial.setValue(socialIds);
            editMultiSelectMember.setValue(memberIds);
          } else {
            console.error("Selectr instance is not available.");
          }

          $("#wrapper-form-edit-team-preloader").hide();
          $("#form-edit-team").show();
        }
      },
      error: function (err) {
        console.error("Error loading team data:", err);
      },
    });
  });

  // อัพเดททีม
  $("#btnUpdateTeam").on("click", function () {
    let $me = $(this);

    // รวบรวมข้อมูลจากฟอร์ม
    const data = {
      teamLogo: $("#editTeamLogo").attr("src"),
      team_id: $("#editTeamID").val(),
      // name: $("#editTeamName").val(),
      team_note: $("#editTeamNote").val(),
      connect_ids: $("#editMultiSelectSocial").val(), // รับค่าจาก multiselect
      member_ids: $("#editMultiSelectMember").val(), // รับค่าจาก multiselect
    };

    $me.prop("disabled", true);

    $("#wrapper-form-edit-team-preloader").show();
    $("#form-edit-team").hide();

    // ส่งข้อมูลไปที่เซิร์ฟเวอร์ผ่าน AJAX
    $.ajax({
      url: `${serverUrl}/team/update`,
      method: "POST",
      data: JSON.stringify(data),
      contentType: "application/json",
      success: function (response) {
        if (response.success) {
          $("#editTeam").modal("hide"); // ปิด Modal

          Swal.fire({
            title: "ข้อมูลถูกอัพเดทเรียบร้อยแล้ว",
            icon: "success",
            timer: 2000,
            showConfirmButton: false,
          });

          $me.prop("disabled", false);

          location.reload(); // โหลดหน้าใหม่เพื่อรีเฟรชข้อมูล (หรืออัปเดตตารางด้วย AJAX)
        } else {
          $me.prop("disabled", false);

          $("#wrapper-form-edit-team-preloader").show();
          $("#form-edit-team").hide();

          Swal.fire({
            title: response.message,
            text: "Redirecting...",
            icon: "warning",
            timer: 2000,
            showConfirmButton: false,
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", error);
        alert("ไม่สามารถบันทึกข้อมูลได้");
      },
    });
  });

  // ลบทีม
  $(".btnRemoveTeam").on("click", function () {
    let $me = $(this);

    // รวบรวมข้อมูลจากฟอร์ม
    const data = {
      teamID: $me.data("team-id"),
    };

    $me.prop("disabled", true);

    Swal.fire({
      text: `คุณต้องการลบ`,
      icon: "warning",
      buttonsStyling: false,
      confirmButtonText: "ตกลง",
      showCloseButton: true,
      showCancelButton: true,
      customClass: {
        confirmButton: "btn btn-primary",
        cancelButton: "btn btn-secoundary",
      },
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          url: `${serverUrl}/team/destroy`,
          method: "POST",
          data: JSON.stringify(data),
          contentType: "application/json",
          success: function (res) {
            if (res.success) {
              Swal.fire({
                icon: "success",
                text: `${res.message}`,
                timer: "2000",
                heightAuto: false,
              });

              setTimeout(function () {
                window.location.href = `${serverUrl}/team`;
              }, 1 * 1500);
            }
          },
          error: function (res) {
            Swal.fire({
              icon: "error",
              text: `ไม่สามารถอัพเดทได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ให้บริการ`,
              timer: "2000",
              heightAuto: false,
            });
          },
        });
      }
    });
  });
});
