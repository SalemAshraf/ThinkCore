import $ from 'jquery';


$('.toggle-password').on('click', function(e) {
    e.preventDefault();
    var input = $(this).closest('.input-group').find('input');
    var icon = $(this).find('svg');

    if (input.attr('type') === 'password') {
        input.attr('type', 'text');
        icon.attr('data-icon', 'eye-off');
    } else {
        input.attr('type', 'password');
        icon.attr('data-icon', 'eye');
    }
});
