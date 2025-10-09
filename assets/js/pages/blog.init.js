// form validation has to be loaded again for the dynamically loaded edit blog modal (ajax)
function initializeFormValidation() {
    var forms = document.getElementsByClassName('needs-validation');

    Array.prototype.filter.call(forms, function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                Array.from(form.elements).forEach(function (input) {
                    var grandParentElement = input.parentElement.parentElement;
                    var feedbackElement = grandParentElement.querySelector('.invalid-feedback');
                    if (feedbackElement) {
                        feedbackElement.style.display = 'block'; // Make the feedback element visible
                    }
                });
            }
            form.classList.add('was-validated');
        }, false);
    });
}

// Initial call for existing forms
initializeFormValidation();

// Handling the AJAX request for edit blog modal
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-blog-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const blogId = parseInt(button.getAttribute('data-id'), 10);
            var locale = window.location.pathname.split('/')[1]; // Assuming locale is the first part of the path
            fetch('/' + locale + '/admin/blogs/edit/' + blogId)
                .then(response => response.text())
                .then(html => {

                    // Insert the fetched HTML into the placeholder
                    var dynamicModalPlaceholder = document.getElementById('blogModalPlaceholder');
                    dynamicModalPlaceholder.innerHTML = html;

                    // Initialize and show the modal
                    var modal = new bootstrap.Modal(document.getElementById('EditBlogModal'));
                    modal.show();

                    // Reinitialize form validation for the newly loaded form
                    initializeFormValidation();
                })
                .catch(error => {
                    console.error('Error fetching blog:', error);
                });
        });
    });
});