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
  
$(document).ready(function () {
  // -----------------------------------------------------------------------------
  // Eevent
  // -----------------------------------------------------------------------------

  $('.btnHandlePlan').click(function() {

    let $me = $(this)

    $me.prop("disabled", true);

    // Send selected plan to the server via AJAX
    $.ajax({
        url: `${serverUrl}/subscription/handlePlan`, // Replace with your server endpoint
        type: 'POST',
        data: JSON.stringify({
            userID: `${window.userID}`,
            // planID: selectedPlan.id,
            // planName: selectedPlan.name,
            // planPrice: selectedPlan.price
        }),
        contentType: "application/json; charset=utf-8",
        success: function(response) {
            $me.prop("disabled", false);
            location.href = response.url
        },
        error: function(xhr, status, error) {
            console.error('Payment Error:', error);
            alert('เกิดข้อผิดพลาดในการชำระเงิน กรุณาลองอีกครั้ง');
        }
    });
});
});
