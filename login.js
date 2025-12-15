$(document).ready(function() {
    $('#loginForm').submit(function(event) {
        event.preventDefault();

        var loginData = {
            idLogin: $('#idLogin').val(),
            idPassword: $('#idPassword').val()
        };

        $.ajax({
            url: 'Login.php',
            type: 'POST',
            data: loginData,
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if (response.status === 'success') {
                    // Replace Swal.fire with alert
                    alert('Login Successful! You will be redirected shortly.');

                    $('#idLogin').val("");
                    $('#idPassword').val("");

                    // Fetch user first name and last name using the login email
                    var loginEmail = $('#idLogin').val(); // Capture the email used to log in

                    $.ajax({
                        url: 'fetch_userName.php',
                        type: 'POST',
                        data: { idLogin: loginEmail },
                        dataType: 'json',
                        success: function(userResponse) {
                            if (userResponse.error) {
                                alert('Error! ' + userResponse.error);
                            } else {
                                // Handle user data (first name, last name)
                                const firstname = userResponse.firstname;
                                const lastname = userResponse.lastname;

                                // Example: Show first name and last name on the page
                                $('#st-name').text(firstname + " " + lastname);
                            }
                        },
                        error: function() {
                            alert('Error! There was an error fetching your user details.');
                        }
                    });

                    // Redirect to the provided location after successful login
                    window.location.href = response.redirect;
                } else {
                    alert('Error! ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error! An error occurred. Please try again later.');
            }
        });
    });
});
