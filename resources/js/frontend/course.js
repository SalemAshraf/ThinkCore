
const basicUrl = $('meta[name="basic_url"]').attr('content');
const basicInfoUrl = basicUrl + '/instructor/courses/store';
const moreInfoUrl = basicUrl + '/instructor/courses/moreinfo';


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

        },
        complete: function () {
            // Optionally hide the loading spinner or re-enable the form
        }
    });
});

$(".more_info_form").on("submit", function (e) {
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

        },
        complete: function () {
            // Optionally hide the loading spinner or re-enable the form
        }
    });
});
