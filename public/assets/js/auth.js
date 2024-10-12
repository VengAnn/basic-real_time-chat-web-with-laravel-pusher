$(document).ready(function () {
    // Set CSRF token globally for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function register() {
        // using ajax request
        $.ajax({
            url: '/register',
            type: 'POST',
            data: {
                name: $('#name').val(),
                email: $('#registerEmail').val(),
                password: $('#registerPassword').val(),
                confirmPassword: $('#confirmPassword').val()
            },
            success: function (res) {
                if (res.status == 200) {
                    window.location.href = '/';
                }
                else {
                    alert('Registration failed!!');
                }
            },
            error: function (res) {
                alert('Error: ' + JSON.stringify(res));
            }
        });
    }

    $('#id-btn-register').on('click', function (e) {
        e.preventDefault();
        register();
    });

    function login() {
        // using ajax request
        $.ajax({
            url: '/login',
            type: 'POST',
            data: {
                email: $('#email').val(),
                password: $('#password').val()
            },
            success: function (res) {
                if (res.status === 200) {
                    localStorage.removeItem('user_id');
                    localStorage.removeItem('name');

                    localStorage.setItem('user_id', res.user.id);
                    localStorage.setItem('name', res.user.name);
                    alert('login successful');
                    window.location.href = '/chat-page';
                }
                else {
                    alert('Login failed');
                }
            },
            error: function (res) {
                alert('Error: ' + JSON.stringify(res));
            }
        });
    }

    $("#id-btn-login").on('click', function (e) {
        e.preventDefault();
        login();
    });

});