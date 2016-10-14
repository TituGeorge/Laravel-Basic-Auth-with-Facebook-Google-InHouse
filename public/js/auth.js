$(document).ready(function() {
    $('body').on('click', '#signup_btn_click', function(event) {
        event.preventDefault();
        $("#frm_login").hide("slow");
        $("#frm_signup").show("slow");
        clearSignupErr();
    });

    $('body').on('click', '#login_btn_click', function(event) {
        event.preventDefault();
        $("#frm_signup").hide("slow");
        $("#frm_login").show("slow");
        $("#login_err").html("");
    });

    $("body").on('submit', "#frm_login", function (event) {
        event.preventDefault();

        $.ajax({
            url:"login",
            type: "POST",
            data: $("#frm_login").serialize(),
            dataType: 'json',
            beforeSend: function () {
                $("#login_loader").show();
                $("#login_err").html("");
            },
            complete: function() {
                $("#login_loader").hide();
            },
            success:function(response) {

                if(response.status == "Success") {
                    window.location.href = "dashboard";
                }
                if(response.status == "Failure") {
                    $("#login_err").html(response.data);
                }

            },
            error: function (response) {
                $("#login_err").html(response);
            }

        });

    });

 $("body").on('submit', "#frm_signup", function (event) {
        event.preventDefault();

        clearSignupErr();

        $.ajax({
            url:"signup",
            type: "POST",
            data: $("#frm_signup").serialize(),
            dataType: 'json',
            beforeSend: function () {
                $("#signup_loader").show();
            },
            complete: function() {
                $("#signup_loader").hide();
            },
            success:function(response) {

                if(response.status == "Success") {
                    $("#frm_signup")[0].reset();
                    $("#signup_Success").html("Successfully registered, Please login.");

                }
                if(response.status == "Failure") {
                    if(response.data.email){
                        $("#email_err").html(response.data.email[0]);
                    }else if(response.data.password){
                        $("#password_err").html(response.data.password[0]);
                    }
                }

            },
            error: function (response) {
                $("#signup_err").html(response);
            }

        });

    });

    function clearSignupErr()
    {
        $("#email_err").html("");
        $("#password_err").html("");
        $("#signup_err").html("");
        $("#signup_Success").html("");
    }

});