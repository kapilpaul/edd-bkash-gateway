jQuery(function ($) {
  var form_action_value;
  var payment_mode;
  var script_loaded = false;

  const dc_edd_bkash = {
    purchase_form: "form#edd_purchase_form",
    purchase_form_submit: function (e) {
      if (payment_mode === "dc_bkash") {
        console.log("clicked");
        e.preventDefault();
      }
    },
    set_payment_mode: function (e) {
      //on change set payment method
      $("select#edd-gateway, input.edd-gateway").change(function (e) {
        payment_mode = $(
          "#edd-gateway option:selected, input.edd-gateway:checked"
        ).val();

        if (payment_mode === "dc_bkash") {
          $("#edd_purchase_form").removeAttr("action");
          dc_edd_bkash.load_bkash_script();
        } else {
          $("#edd_purchase_form").attr("action", form_action_value);
        }
      });

      return payment_mode;
    },
    load_bkash_script: function () {
      if (!script_loaded) {
        $.getScript(window.dc_edd_bkash.script_url, function () {
          dc_edd_bkash.create_bkash_button();
          script_loaded = true;
          window.$ = $.noConflict(true);
          dc_edd_bkash.init_bkash();
        });
      }
    },
    create_bkash_button: function () {
      var bkashBtn = document.createElement("button");
      bkashBtn.id = "bKash_button";
      bkashBtn.className = "btn btn-danger";
      bkashBtn.setAttribute("disabled", "disabled");
      bkashBtn.style.display = "none";
      document.body.appendChild(bkashBtn);
    },
    init_bkash: function () {
      let createCheckoutUrl =
        "https://merchantserver.sandbox.bka.sh/api/checkout/v1.2.0-beta/payment/create";
      let executeCheckoutUrl =
        "https://merchantserver.sandbox.bka.sh/api/checkout/v1.2.0-beta/payment/execute";
      let paymentID;

      bKash.init({
        paymentMode: "checkout", // Performs a single checkout.
        paymentRequest: { amount: "85.50", intent: "sale" },

        createRequest: function (request) {
          $.ajax({
            url: createCheckoutUrl,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(request),
            success: function (data) {
              if (data && data.paymentID != null) {
                paymentID = data.paymentID;
                bKash.create().onSuccess(data);
              } else {
                bKash.create().onError(); // Run clean up code
                alert(
                  data.errorMessage +
                    " Tag should be 2 digit, Length should be 2 digit, Value should be number of character mention in Length, ex. MI041234 , supported tags are MI, MW, RF"
                );
              }
            },
            error: function () {
              bKash.create().onError(); // Run clean up code
              alert(data.errorMessage);
            },
          });
        },
        executeRequestOnAuthorization: function () {
          $.ajax({
            url: executeCheckoutUrl,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify({ paymentID: paymentID }),
            success: function (data) {
              if (data && data.paymentID != null) {
                // On success, perform your desired action
                alert("[SUCCESS] data : " + JSON.stringify(data));
                window.location.href = "/success_page.html";
              } else {
                alert("[ERROR] data : " + JSON.stringify(data));
                bKash.execute().onError(); //run clean up code
              }
            },
            error: function () {
              alert("An alert has occurred during execute");
              bKash.execute().onError(); // Run clean up code
            },
          });
        },
        onClose: function () {
          alert("User has clicked the close button");
        },
      });

      $("#bKash_button").removeAttr("disabled");
      $("#bKash_button").click();
    },
    init: function () {
      form_action_value = $("#edd_purchase_form").attr("action");
      dc_edd_bkash.set_payment_mode();
      $(dc_edd_bkash.purchase_form).on(
        "submit",
        dc_edd_bkash.purchase_form_submit
      );
    },
  };

  dc_edd_bkash.init();
});
