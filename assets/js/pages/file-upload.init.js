document.addEventListener("DOMContentLoaded", function () {
    /**
     * Displays a toast notification using Toastify.js
     * @param {string} message - The message to display
     * @param {string} type - The type of message: 'success', 'error', 'warning', 'info'
     */
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

        // Show Toastify notification
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

    /**
     * Represents the button element on the web page used for file uploads.
     * This variable is initialized by referencing the DOM element with the ID "uploadFileButton".
     * It is typically utilized to trigger an upload operation or open a file selection dialog when clicked.
     */
    const uploadButton = document.getElementById("uploadFileButton");
    const mediaFileInput = document.getElementById("mediaFile");
    const uploadUrl = uploadButton.getAttribute("data-upload-url");
    const context = uploadButton.getAttribute("data-context");
    let mapping = 'media_files'
    if (context === 'project') {
        mapping = 'project_files'
    }

    uploadButton.addEventListener("click", function () {
        mediaFileInput.click();
    });

    mediaFileInput.addEventListener("change", function () {
        const file = this.files[0];

        if (!file) {
            showToast('No file selected.', 'error');
            return;
        }

        // Client-side validation
        const allowedTypes = [
            // images
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            // documents
            'application/pdf',
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',  // .docx
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',        // .xlsx
            // plain text
            'text/plain' // .txt
        ];
        if (!allowedTypes.includes(file.type)) {
            showToast("Invalid file type. Please upload a valid file type.", "error");
            return;
        }

        if (file.size > 10 * 1024 * 1024) { // 10MB
            showToast("File size exceeds the 10MB limit.", "error");
            return;
        }

        const formData = new FormData();
        formData.append('mediaFile', file);
        // If CSRF token is required, append it here
        // formData.append('_csrf_token', '{{ csrf_token('upload_file') }}');

        fetch(uploadUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => { throw errData; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message, "success");
                    // Remove "No documents found." row if it exists
                    const noFilesRow = document.getElementById("noFilesRow");
                    if (noFilesRow) {
                        noFilesRow.remove();
                    }

                    // Create a new table row for the uploaded file
                    const tbody = document.getElementById("mediaTableBody");
                    const newRow = document.createElement("tr");
                    newRow.setAttribute("id", `fileRow-${data.file.id}`);

                    newRow.innerHTML = `
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm">
                                    <div class="avatar-title ${data.file.bgClass} ${data.file.colorClass} rounded fs-20 shadow">
                                        <i class="${data.file.iconClass}" aria-label="${data.file.fileType.toUpperCase()} file"></i>
                                    </div>
                                </div>
                                <div class="ms-3 flex-grow-1">
                                    <h6 class="fs-15 mb-0">
                                        <a href="/media/${mapping}/${data.file.filePath}" target="_blank">
                                            ${data.file.fileName}
                                        </a>
                                    </h6>
                                </div>
                            </div>
                        </td>
                        <td>${data.file.fileType.toUpperCase()}</td>
                        <td>${(data.file.fileSize / 1024).toFixed(2)} KB</td>
                        <td>${data.file.created}</td>
                        <td>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-light btn-icon" id="dropdownMenuLink${data.file.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-equalizer-fill"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink${data.file.id}">
                                    <li><a class="dropdown-item" href="/media/uploads/${data.file.filePath}" target="_blank"><i class="ri-eye-fill me-2 align-middle text-muted"></i>View</a></li>
                                    <li><a class="dropdown-item" href="/media/uploads/${data.file.filePath}" download><i class="ri-download-2-fill me-2 align-middle text-muted"></i>Download</a></li>
                                    <li class="dropdown-divider"></li>
                                    <li><a class="dropdown-item delete-file" href="javascript:void(0);" data-id="${data.file.id}" data-delete-url="${data.file.deleteUrl}"><i class="ri-delete-bin-5-line me-2 align-middle text-muted"></i>Delete</a></li>
                                </ul>
                            </div>
                        </td>
                    `;

                    tbody.appendChild(newRow);

                    // Attach event listener to the new delete link
                    const newDeleteLink = newRow.querySelector('.delete-file');
                    newDeleteLink.addEventListener('click', function () {
                        const fileId = this.getAttribute('data-id');
                        const deleteUrl = this.getAttribute('data-delete-url');
                        if (fileId && deleteUrl) {
                            deleteFileIdInput.value = fileId;
                            deleteFileModalElement.setAttribute('data-delete-url', deleteUrl);
                            deleteFileModal.show();
                        } else {
                            showToast('Invalid file ID or delete URL.', 'error');
                        }
                    });
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
            })
            .finally(() => {
                // Reset the file input
                this.value = '';
            });
    });

    /**
     * Represents the DOM element associated with the modal for deleting files.
     *
     * This variable holds a reference to the HTML element with the ID 'deleteFileModal'.
     * It is used for interacting with or manipulating the delete file modal in the user interface.
     */
    const deleteFileModalElement = document.getElementById('deleteFileModal');
    const deleteFileModal = new bootstrap.Modal(deleteFileModalElement);
    const deleteFileIdInput = document.getElementById('deleteFileId');
    const csrfTokenDeleteFileInput = document.getElementById('csrfTokenDeleteFile');
    const confirmDeleteFileBtn = document.getElementById('confirmDeleteFileBtn');

    // Function to attach delete event listeners to all existing delete links
    function attachDeleteEventListeners() {
        const deleteFileLinks = document.querySelectorAll('.delete-file');
        deleteFileLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                const fileId = this.getAttribute('data-id');
                const deleteUrl = this.getAttribute('data-delete-url');
                if (fileId && deleteUrl) {
                    deleteFileIdInput.value = fileId;
                    deleteFileModalElement.setAttribute('data-delete-url', deleteUrl);
                    deleteFileModal.show();
                } else {
                    showToast('Invalid file ID or delete URL.', 'error');
                }
            });
        });
    }

    // Initial attachment for existing delete links
    attachDeleteEventListeners();

    // Handle the confirmation of file deletion
    confirmDeleteFileBtn.addEventListener('click', function () {
        const fileId = deleteFileIdInput.value;
        const csrfToken = csrfTokenDeleteFileInput.value;
        const deleteUrl = deleteFileModalElement.getAttribute('data-delete-url');

        if (!fileId || !deleteUrl) {
            showToast('No file selected for deletion.', 'error');
            deleteFileModal.hide();
            return;
        }

        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                _token: csrfToken
            })
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(errData => { throw errData; });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');

                    // Remove the corresponding table row
                    const fileRow = document.getElementById(`fileRow-${fileId}`);
                    if (fileRow) {
                        fileRow.remove();

                        // Optionally, display "No media file found." if no files remain
                        const tbody = document.getElementById("mediaTableBody");
                        if (tbody.children.length === 0) {
                            const noFilesRow = document.createElement("tr");
                            noFilesRow.setAttribute("id", "noFilesRow");
                            noFilesRow.innerHTML = `
                                <td colspan="5" class="text-center">No media file found.</td>
                            `;
                            tbody.appendChild(noFilesRow);
                        }
                    }
                } else {
                    // Handle validation errors or other server-side errors
                    if (data.errors) {
                        data.errors.forEach(error => {
                            showToast(error, 'error');
                        });
                    } else if (data.error) {
                        showToast(data.error, 'error');
                    } else {
                        showToast('An unexpected error occurred.', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.errors) {
                    error.errors.forEach(err => {
                        showToast(err, 'error');
                    });
                } else if (error.error) {
                    showToast(error.error, 'error');
                } else {
                    showToast('An unexpected error occurred.', 'error');
                }
            })
            .finally(() => {
                // Reset the modal state
                deleteFileModal.hide();
                deleteFileIdInput.value = '';
                deleteFileModalElement.removeAttribute('data-delete-url');
            });
    });
});
