const basicUrl = $('meta[name="base_url"]').attr("content");
const basicInfoUrl = basicUrl + "/instructor/courses/store";
const moreInfoUrl = basicUrl + "/instructor/courses/moreinfo";

var notyf = new Notyf({
    duration: 5000,
    dismissible: true
});

//course tab active
$(".course-tab").on("click", function () {
    let step = $(this).data("step");
    $(".course-form").find("input[name=next_step]").val(step);
    $(".course-form").trigger("submit");
});

$(".basic-info-form").on("submit", function (e) {
    e.preventDefault();
    let formData = new FormData(this);

    $.ajax({
        method: "POST",
        url: basicInfoUrl,
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            // Optionally show a loading spinner or disable the form
        },
        success: function (data) {
            // Handle success response
            if (data.status === "Done") {
                // Redirect or show success message
                window.location.href = data.redirect;
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr);
            let errors = xhr.responseJSON.errors;
            $.each(errors, function (key, value) {
                notyf.error(value[0]);
            })

        },
        complete: function () {
            // Optionally hide the loading spinner or re-enable the form
        },
    });
});

$(".basic-info-update-form").on("submit", function (e) {
    e.preventDefault();
    let formData = new FormData(this);

    $.ajax({
        method: "POST",
        url: moreInfoUrl,
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            // Optionally show a loading spinner or disable the form
        },
        success: function (data) {
            // Handle success response
            if (data.status === "Done") {
                // Redirect or show success message
                window.location.href = data.redirect;
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr);
            let errors = xhr.responseJSON.errors;
            $.each(errors, function (key, value) {
                notyf.error(value[0]);
            })

        },
        complete: function () {
            // Optionally hide the loading spinner or re-enable the form
        },
    });
});

$(".more_info_form").on("submit", function (e) {
    e.preventDefault();
    let form = $(this);
    let formData = new FormData(this);

    $.ajax({
        method: "POST",
        url: moreInfoUrl,
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            form.find(".error-text").remove(); // remove old errors
            form.find(".is-invalid").removeClass("is-invalid");
        },
        success: function (data) {
            if (data.status === "Done") {
                window.location.href = data.redirect;
            }
        },
        error: function (xhr, status, error) {
            console.log(xhr);
            let errors = xhr.responseJSON.errors;
            $.each(errors, function (key, value) {
                notyf.error(value[0]);
            })

        },
    });
});

$(document).ready(function () {
    // show hide path input depending on source
    $(document).on('change', '.storage', function () {
        let value = $(this).val();
        $('.source_input').val('');
        console.log("working");
        if (value == 'upload') {
            $('.upload_source').removeClass('d-none');
            $('.external_source').addClass('d-none');
        } else {
            $('.upload_source').addClass('d-none');
            $('.external_source').removeClass('d-none');
        }
    });
})
