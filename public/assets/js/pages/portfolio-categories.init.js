// form validation has to be loaded again for the dynamically loaded edit portfolioCategory modal (ajax)
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
    document.querySelectorAll('.edit-portfolioCategory-card').forEach(function (card) {
        card.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default link behavior
            const portfolioCategoryId = parseInt(card.getAttribute('data-id'), 10);
            var locale = window.location.pathname.split('/')[1]; // Assuming locale is the first part of the path
            fetch('/' + locale + '/admin/portfolios/categories/edit/' + portfolioCategoryId)
                .then(response => response.text())
                .then(html => {
                    // Insert the fetched HTML into the placeholder
                    var dynamicModalPlaceholder = document.getElementById('portfolioCategoryModalPlaceholder');
                    dynamicModalPlaceholder.innerHTML = html;

                    // Initialize and show the modal
                    var modal = new bootstrap.Modal(document.getElementById('EditPortfolioCategoryModal'));
                    modal.show();

                    // Reinitialize form validation for the newly loaded form
                    initializeFormValidation();
                })
                .catch(error => {
                    console.error('Error fetching portfolio:', error);
                });
        });
    });
});

// delete modal
document.querySelectorAll('.remove-item-btn').forEach(function(button) {
    button.addEventListener('click', function() {
        var portfolioCategoryId = button.getAttribute('data-id');
        var locale = window.location.pathname.split('/')[1];
        var form = document.getElementById('deleteCategoryForm');
        form.action = '/' + locale + '/admin/portfolios/categories/delete/' + portfolioCategoryId; // Update the form action URL
    });
});
