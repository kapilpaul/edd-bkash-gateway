jQuery(function ($) {
  var form_action_value;
  var payment_mode;
  var payment_id;
  var script_loaded = false;
  var loader;

  const edd_bkash = window.dc_edd_bkash;

  const dc_edd_bkash = {
    purchase_form: "form#edd_purchase_form",
    purchase_form_submit: function (e) {
      if (payment_mode === "dc_bkash") {
        e.preventDefault();

        $.ajax({
          url: edd_bkash.ajaxurl,
          method: "POST",
          data: $(dc_edd_bkash.purchase_form).serialize(),
          success: function(data) {
            if(data.success) {
              data = data.data;
              dc_edd_bkash.init_bkash(data.payment, data.amount);
            }
          },
          error: function(errorMessage) {
          }
        });
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
          dc_edd_bkash.create_bkash_loader();
          dc_edd_bkash.load_bkash_script();
        } else {
          $("#edd_purchase_form").attr("action", form_action_value);
        }
      });

      return payment_mode;
    },
    load_bkash_script: function () {
      if (!script_loaded) {
        loader.style.display = "block";

        $.getScript(edd_bkash.script_url, function () {
          loader.style.display = "none";
          dc_edd_bkash.create_bkash_button();
          script_loaded = true;
          window.$ = $.noConflict(true);
        });
      }
    },
    create_bkash_loader: function() {
      var elem = document.createElement("div");
      elem.className = "bkash-loader";
      elem.id = "bkash-loader";
      document.body.appendChild(elem);
      loader = document.getElementById("bkash-loader");
    },
    create_bkash_button: function () {
      var bkashBtn = document.createElement("button");
      bkashBtn.id = "bKash_button";
      bkashBtn.className = "btn btn-danger";
      bkashBtn.setAttribute("disabled", "disabled");
      bkashBtn.style.display = "none";
      document.body.appendChild(bkashBtn);
    },
    create_bkash_request: function(order_number) {
      let create_payment_data = {
        order_number: order_number,
        action: "dc-edd-bkash-create-payment-request",
        _ajax_nonce: edd_bkash.nonce
      };

      $.ajax({
        url: edd_bkash.ajaxurl,
        method: "POST",
        data: create_payment_data,
        success: function(data) {
          console.log(data);
          if (data.success && data.data.paymentID != null) {
            data = data.data;
            payment_id = data.paymentID;
            bKash.create().onSuccess(data);
          } else {
            bKash.create().onError();
          }
        },
        error: function(errorMessage) {
          bKash.create().onError();
        }
      });
    },
    execute_bkash_request: function(order_number) {
      let execute_payment_data = {
        payment_id: payment_id,
        order_number: order_number,
        action: "dc-edd-bkash-execute-payment-request",
        _ajax_nonce: edd_bkash.nonce
      };

      $.ajax({
        url: edd_bkash.ajaxurl,
        method: "POST",
        data: execute_payment_data,
        success: function(response) {
          if (response.success && response.data.paymentID != null) {
            let data = response.data;
            window.location.href = data.order_success_url;
          } else {
            bKash.execute().onError(); //run clean up code
          }
        },
        error: function() {
          bKash.execute().onError(); // Run clean up code
        }
      });
    },
    init_bkash: function (order_number, amount) {
      loader.style.display = "block";
      let payment_request = {
        amount: amount,
        intent: "sale",
        currency: "BDT",
        merchantInvoiceNumber: order_number
      };

      bKash.init({
        paymentMode: "checkout",
        paymentRequest: payment_request,
        createRequest: function () {dc_edd_bkash.create_bkash_request(order_number)},
        executeRequestOnAuthorization: function () {dc_edd_bkash.execute_bkash_request(order_number)},
        onClose: function() {
          loader.style.display = "none";
        }
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
