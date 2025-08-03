/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Job application init Js File
*/

var checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.onclick = function () {
        var checkboxes = document.querySelectorAll('.form-check-all input[type="checkbox"]');
        var checkedCount = document.querySelectorAll('.form-check-all input[type="checkbox"]:checked').length;
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = this.checked;
            if (checkboxes[i].checked) {
                checkboxes[i].closest("tr").classList.add("table-active");
            } else {
                checkboxes[i].closest("tr").classList.remove("table-active");
            }
        }

        (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'none' : document.getElementById("remove-actions").style.display = 'block';
    };
}

document.querySelector("#usersList").addEventListener("click", function () {
    ischeckboxcheck();
});

var table = document.getElementById("userListTable");
// save all tr
var tr = table.getElementsByTagName("tr");
var trlist = table.querySelectorAll(".list tr");

function ischeckboxcheck() {
    Array.from(document.getElementsByName("checkAll")).forEach(function (x) {
        x.addEventListener("change", function (e) {
            if (x.checked == true) {
                e.target.closest("tr").classList.add("table-active");
            } else {
                e.target.closest("tr").classList.remove("table-active");
            }

            var checkedCount = document.querySelectorAll('[name="checkAll"]:checked').length;
            if (e.target.closest("tr").classList.contains("table-active")) {
                (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'block': document.getElementById("remove-actions").style.display = 'none';
            } else {
                (checkedCount > 0) ? document.getElementById("remove-actions").style.display = 'block': document.getElementById("remove-actions").style.display = 'none';
            }
        });
    });
}

function deleteMultiple(){
    // get the locale
    var locale = window.location.pathname.split('/')[1];

    // Array to hold objects containing user id and the corresponding table row
    let ids_array = [];

    // Get all checkboxes (adjust the selector if needed)
    const items = document.querySelectorAll('.form-check [value=option1]');

    // Loop through the checkboxes to collect checked items
    items.forEach(item => {
        if(item.checked) {
            // Using closest('tr') for a robust way to get the table row
            let trNode = item.closest('tr');
            // Assuming the first <a> in the row contains the user id
            let id = trNode.querySelector("td a").textContent.trim();
            ids_array.push({ id: id, row: trNode });
        }
    });

    const swalert = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-primary w-xs me-2 mt-2",
            cancelButton: "btn btn-danger w-xs mt-2"
        },
        buttonsStyling: false
    });
    if (ids_array.length > 0) {
        swalert.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            showCloseButton: true
        }).then(result => {
            if (result.value) {
                // Create an array of ids only for sending in the payload
                let idPayload = ids_array.map(item => item.id);

                // Make the AJAX request using fetch
                fetch('/' + locale + '/admin/users/delete-multiple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ ids: idPayload })
                })
                    .then(response => {
                        if(response.ok){
                            // Remove the deleted rows from the DOM
                            ids_array.forEach(item => {
                                item.row.remove();
                            });
                            // Optionally update any UI elements (like unchecking a "select all" checkbox)
                            document.getElementById("remove-actions").style.display = 'none';
                            document.getElementById("checkAll").checked = false;

                            swalert.fire({
                                title: 'Deleted!',
                                text: 'Your data has been deleted.',
                                icon: 'success',
                            });
                        } else {
                            swalert.fire({
                                title: 'Error!',
                                text: 'Deletion failed.',
                                icon: 'error',
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        swalert.fire({
                            title: 'Error!',
                            text: 'There was a problem processing your request.',
                            icon: 'error',
                        });
                    });
            }
        });
    } else {
        swalert.fire({
            title: 'Please select at least one checkbox',
            showCloseButton: true
        });
    }
}
