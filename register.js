$(document).ready(function () {

});

$('#signupBtn').click(function(event){
        event.preventDefault(); 
        
        if (!validateForm()) return;
    
        $.ajax({
            url: 'loginregister.php',
            type: 'POST',
            data: $('#register-form').serialize(),
            dataType: 'json', // Make sure jQuery treats the response as JSON
            success: function(response) {
                // Log the entire response to the console for debugging
                console.log(response);
                
                if (response.error) {
                    Swal.fire({
                        title: 'Oops, something went wrong!',
                        text: response.error,
                        icon: 'error'
                    });
                } else {
                    Swal.fire({
                        title: 'Success! You\'re all set!',
                        text: 'Welcome to Jot Well!',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        if (response.redirectUrl) {
                            window.location.href = response.redirectUrl; // Redirect to the URL returned by the server
                        } else {
                            console.error("Redirect URL is missing from response.");
                        }
                    });
        
                    resetFormFeedback();
                }
            },
            error: function(xhr) {
                // Handle error in case AJAX request fails
                console.error(xhr.responseText);
                Swal.fire({
                    title: 'Oops!',
                    text: 'An error occurred while processing your request. Please try again later.',
                    icon: 'error'
                });
            }
        });
        
});

    function validateForm() {
        let isValid = true;

        if ($('#firstname').val() === "") {
            showFeedback('#fnameFeedback', false, 'Please fill up the Firstname');
            $('#firstname').val("");
            isValid = false;
        } else if (/[^a-zA-Z ]/.test($('#firstname').val())) {
            showFeedback('#fnameFeedback', false, 'Please use only letters for Firstname');
            $('#firstname').val("");
            isValid = false;
        } else {
            showFeedback('#fnameFeedback', true);
        }

        if ($('#lastname').val() === "") {
            showFeedback('#lnameFeedback', false, 'Please fill up the Lastname');
            $('#lastname').val("");
            isValid = false;
        } else if (/[^a-zA-Z ]/.test($('#lastname').val())) {
            showFeedback('#lnameFeedback', false, 'Please use only letters for Lastname');
            $('#lastname').val("");
            isValid = false;
        } else {
            showFeedback('#lnameFeedback', true);
        }

        const email = $('#email').val();
        const emailValidation = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        if (email === "" || !emailValidation.test(email)) {
            showFeedback('#emailFeedback', false, 'Please enter a valid Email');
            isValid = false;
        } else {
            showFeedback('#emailFeedback', true);
        }

        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();

        if (password === "") {
            showFeedback('#passwordFeedback', false, 'Please enter a password');
            isValid = false;
        } else {
            showFeedback('#passwordFeedback', true);
        }

        if (confirmPassword === "") {
            showFeedback('#confirmPasswordFeedback', false, 'Please confirm your password');
            isValid = false;
        } else if (password !== confirmPassword) {
            showFeedback('#confirmPasswordFeedback', false, 'Passwords do not match');
            isValid = false;
        } else {
            showFeedback('#confirmPasswordFeedback', true);
        }

        return isValid;
    }

    function showFeedback(feedbackElement, isValid, message = '') {
        if (isValid) {
            $(feedbackElement).removeClass('invalid-feedback').addClass('valid-feedback').html('');
        } else {
            $(feedbackElement).removeClass('valid-feedback').addClass('invalid-feedback').html(message);
        }
    }

    function resetFormFeedback() {
        $('#fnameFeedback, #lnameFeedback, #emailFeedback, #passwordFeedback, #confirmPasswordFeedback').removeClass('invalid-feedback valid-feedback').html('');
    }

