// form validation has to be loaded again for the dynamically loaded edit blogCategory modal (ajax)
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

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-blogCategory-card').forEach(function (card) {
        card.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default link behavior
            const blogCategoryId = parseInt(card.getAttribute('data-id'), 10);
            var locale = window.location.pathname.split('/')[1]; // Assuming locale is the first part of the path
            fetch('/' + locale + '/admin/blogs/categories/edit/' + blogCategoryId)
                .then(response => response.text())
                .then(html => {
                    // Insert the fetched HTML into the placeholder
                    var dynamicModalPlaceholder = document.getElementById('blogCategoryModalPlaceholder');
                    dynamicModalPlaceholder.innerHTML = html;

                    // Initialize and show the modal
                    var modal = new bootstrap.Modal(document.getElementById('EditBlogCategoryModal'));
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

// delete modal
document.querySelectorAll('.remove-item-btn').forEach(function(button) {
    button.addEventListener('click', function() {
        var blogCategoryId = button.getAttribute('data-id');
        var locale = window.location.pathname.split('/')[1];
        var form = document.getElementById('deleteCategoryForm');
        form.action = '/' + locale + '/admin/blogs/categories/delete/' + blogCategoryId; // Update the form action URL
    });
});
