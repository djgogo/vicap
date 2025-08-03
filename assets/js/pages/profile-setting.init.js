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
 * notification options ajax handler in the user profile
 */
document.querySelectorAll('.form-check-input[type="checkbox"]').forEach((checkbox) => {
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
                // If you use CSRF in your AJAX calls, add your CSRF header or token here.
                // 'X-CSRF-Token': '{{ csrf_token('toggle_user_option') }}'
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

/**
 * check password before Account Deletion
 */
document.addEventListener('DOMContentLoaded', function() {
    const checkPasswordBtn = document.getElementById('checkPasswordBtn');
    if (!checkPasswordBtn) return;

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

    checkPasswordBtn.addEventListener('click', function(event) {
        event.preventDefault();
        const password = document.getElementById('passwordInput').value;
        let locale = window.location.pathname.split('/')[1];
        let route = checkPasswordBtn.getAttribute("data-route");

        fetch('/' + locale + '/' + route + '/profile/verify-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ password: password })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, "success");
                    // Password is correct: display the confirmation modal
                    const modalEl = document.getElementById(`deleteAccountModal`);
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
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
