document.getElementById('registerForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    // Create a new FormData object from the form
    let formData = new FormData(this);

    // Make the AJAX request
    fetch('loginregister.php', {
        method: 'POST',
        body: formData  // Send form data in the request body
    })
    .then(response => response.json())  // Parse the JSON response
    .then(data => {
        // Check the response for any error or success message
        if (data.message === 'error') {
            alert(data.error);  // Show error alert
        } else if (data.status === 'success') {
            alert(data.alert);  // Show success alert
            window.location.href = data.redirect;  // Redirect after successful registration
        }
    })
    .catch(error => {
        console.error('Error:', error);  // Log errors if any
    });
});


document.getElementById("loginForm").addEventListener("submit", function (event) {
  event.preventDefault(); // Prevent form from submitting normally

  const formData = new FormData(this);

  fetch("Login.php", {
    method: "POST",
    body: formData
  })
  .then(response => response.text())
  .then(text => {
    try {
      const data = JSON.parse(text); // Manually parse JSON

      if (data.status === "success") {
        alert(`Login successful! Welcome ${data.user.firstname} ${data.user.lastname}`);
        window.location.href = data.redirect; // Redirect after success
      } else {
        alert("Login failed: " + data.message);
      }
    } catch (e) {
      // Catch parse errors
      alert("Unexpected response from server.");
      console.error("Invalid JSON from server:", text);
    }
  })
  .catch(error => {
    alert("Network error. Please try again.");
    console.error("Fetch error:", error);
  });
});

