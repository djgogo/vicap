/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Profile-setting init js
*/

// Profile Foreground Img
if (document.querySelector("#profile-foreground-img-file-input")) {
    document.querySelector("#profile-foreground-img-file-input").addEventListener("change", function () {
        var preview = document.querySelector(".profile-wid-img");
        var file = document.querySelector(".profile-foreground-img-file-input")
            .files[0];
        var reader = new FileReader();
        reader.addEventListener(
            "load",
            function () {
                preview.src = reader.result;
            },
            false
        );
        if (file) {
            reader.readAsDataURL(file);
        }
    });
}

// Profile Img AJAX Upload
document.querySelector("#profile-img-file-input").addEventListener("change", function () {
    var preview = document.querySelector(".user-profile-image");
    var file = this.files[0];
    var userId = parseInt(this.getAttribute('data-id'), 10);
    var locale = window.location.pathname.split('/')[1]; // get the locale from the path
    var reader = new FileReader();

    var formData = new FormData();
    formData.append('profileImage', file);

    fetch('/' + locale + '/upload/profile-image/' + userId, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            // Handle response here, update UI as necessary
            console.log(data);

            // display new profile image
            reader.addEventListener(
                "load",
                function () {
                    preview.src = reader.result;
                },
                false
            );
            if (file) {
                reader.readAsDataURL(file);
            }

            // Reload the page to show the success message
            window.location.reload();
        })
    .catch(error => console.error('Error:', error));
    // If there's an error, reload the page to show the error message
    window.location.reload();
});

/**
 * notification options ajax handler in the admin user profile
 */
document.querySelectorAll('#options .form-check-input[type="checkbox"]').forEach((checkbox) => {
    checkbox.addEventListener('change', function () {

        function showToast(message, type) {
            let backgroundColor;
            switch (type) {
                case 'success':
                case 'info':
                    backgroundColor = "linear-gradient(to right, #00b09b, #96c93d)"; // Green
                    break;
                case 'error':
                case 'warning':
                    backgroundColor = "linear-gradient(to right, #ff5f6d, #ffc371)"; // Red
                    break;
                default:
                    backgroundColor = "linear-gradient(to right, #1a73e8, #66a6ff)"; // Default color (blue)
            }
            Toastify({
                text: message,
                duration: 5000,
                close: true,
                gravity: "top",
                position: 'right',
                style: {
                    background: backgroundColor,
                },
            }).showToast();
        }

        const endpoint = this.dataset.endpoint;
        const newValue = this.checked ? 1 : 0;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ value: newValue })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, "success");
                } else if (data.errors) {
                    data.errors.forEach(error => {
                        showToast(error, "error");
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.errors) {
                    error.errors.forEach(err => {
                        showToast(err, "error");
                    });
                } else if (error.error) {
                    showToast(error.error, "error");
                } else {
                    showToast("An unexpected error occurred.", "error");
                }
            });
    });
});
