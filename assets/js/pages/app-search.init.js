// Debounce function to limit rapid requests
function debounce(fn, delay) {
    let timeoutID;
    return function(...args) {
        clearTimeout(timeoutID);
        timeoutID = setTimeout(() => fn.apply(this, args), delay);
    };
}

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-options");
    const dropdown = document.getElementById("search-dropdown");
    const searchClose = document.getElementById("search-close-options");
    const resultsContainer = document.getElementById("search-results-container");
    const viewAllLink = document.getElementById("view-all-results");
    let locale = window.location.pathname.split('/')[1];

    // Early return if search elements don't exist (non-admin users)
    if (!searchInput || !dropdown || !searchClose || !resultsContainer || !viewAllLink) {
        return;
    }

    function fetchSearchResults(query) {
        fetch('/' + locale + `/admin/search?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                return response.json();
            })
            .then(data => {
                // Clear existing results
                resultsContainer.innerHTML = "";

                if (data.length === 0) {
                    resultsContainer.innerHTML = "<div class='dropdown-item text-muted'>No results found</div>";
                } else {
                    // Group results by the "type" property
                    const groups = {};
                    data.forEach(item => {
                        const type = item.type;
                        if (!groups[type]) {
                            groups[type] = [];
                        }
                        groups[type].push(item);
                    });

                    // For each group, add a header and then the group items
                    Object.keys(groups).forEach(type => {
                        // Determine the display title based on type
                        let displayTitle = "";
                        switch (type.toLowerCase()) {
                            case "candidate":
                                displayTitle = "Candidates";
                                break;
                            case "client":
                                displayTitle = "Clients";
                                break;
                            case "course":
                                displayTitle = "Courses";
                                break;
                            case "job":
                                displayTitle = "Jobs";
                                break;
                            default:
                                displayTitle = type;
                        }

                        // Create and append the header element
                        const header = document.createElement("div");
                        header.className = "dropdown-header mt-2";
                        header.innerHTML = `<h6 class="text-overflow text-muted mb-2 text-uppercase">${displayTitle}</h6>`;
                        resultsContainer.appendChild(header);

                        // Append each result in the current group
                        groups[type].forEach(item => {
                            const a = document.createElement("a");
                            a.href = item.url;
                            a.className = "dropdown-item notify-item";

                            // Determine the appropriate icon based on the entity type
                            let iconHtml = "";
                            switch (item.type.toLowerCase()) {
                                case "candidate":
                                    iconHtml = '<i class="ri-user-settings-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                case "client":
                                    iconHtml = '<i class="ri-building-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                case "course":
                                    iconHtml = '<i class="ri-book-open-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                case "job":
                                    iconHtml = '<i class="ri-briefcase-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                default:
                                    iconHtml = '';
                            }

                            a.innerHTML = `${iconHtml}<span>${item.label}</span>`;
                            resultsContainer.appendChild(a);
                        });
                    });
                }
                // Show the dropdown
                dropdown.classList.add("show");
                searchClose.classList.remove("d-none");
            })
            .catch(error => {
                console.error('Search error:', error);
            });
    }

    const debouncedFetch = debounce(function () {
        const query = searchInput.value.trim();
        if (query.length > 0) {
            // Update the "View All Results" link dynamically:
            viewAllLink.href = '/' + locale + `/admin/search/search-results/${encodeURIComponent(query)}`;
            // Fetch and display search results via AJAX
            fetchSearchResults(query);
        } else {
            dropdown.classList.remove("show");
            searchClose.classList.add("d-none");
            viewAllLink.href = "#"; // reset link if no query
        }
    }, 300); // adjust delay as needed

    searchInput.addEventListener("keyup", debouncedFetch);

    searchInput.addEventListener("focus", function () {
        if (searchInput.value.trim().length > 0) {
            dropdown.classList.add("show");
            searchClose.classList.remove("d-none");
        }
    });

    searchClose.addEventListener("click", function () {
        searchInput.value = "";
        dropdown.classList.remove("show");
        searchClose.classList.add("d-none");
        viewAllLink.href = "#"; // reset link on close
    });

    // Hide dropdown if clicking outside the search box
    document.body.addEventListener("click", function (e) {
        if (e.target.getAttribute("id") !== "search-options") {
            dropdown.classList.remove("show");
            searchClose.classList.add("d-none");
        }
    });
});

// mobile ajax search version
document.addEventListener("DOMContentLoaded", function () {
    // Define mobile-specific DOM elements
    const mobileSearchForm = document.getElementById("mobile-search-form");
    const mobileSearchInput = document.getElementById("mobile-search-input");
    const mobileResultsContainer = document.getElementById("mobile-search-results-container");
    const mobileViewAllLink = document.getElementById("mobile-view-all-results");
    let locale = window.location.pathname.split('/')[1];

    // Early return if mobile search elements don't exist (non-admin users)
    if (!mobileSearchForm || !mobileSearchInput || !mobileResultsContainer || !mobileViewAllLink) {
        return;
    }

    // Function to fetch search results (reuse logic from desktop)
    function fetchMobileSearchResults(query) {
        fetch('/' + locale + `/admin/search?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Response is not JSON');
                }
                return response.json();
            })
            .then(data => {
                // Clear existing mobile results
                mobileResultsContainer.innerHTML = "";

                // Reference to the "View All" container
                const viewAllContainer = document.getElementById("mobile-view-all-container");

                if (data.length === 0) {
                    mobileResultsContainer.innerHTML = "<div class='dropdown-item text-muted'>No results found</div>";
                    // Hide the view all button if no results
                    viewAllContainer.style.display = "none";
                } else {
                    // Group results by the "type" property
                    const groups = {};
                    data.forEach(item => {
                        const type = item.type;
                        if (!groups[type]) {
                            groups[type] = [];
                        }
                        groups[type].push(item);
                    });

                    // For each group, add a header and then the group items
                    Object.keys(groups).forEach(type => {
                        // Determine the display title based on type
                        let displayTitle = "";
                        switch (type.toLowerCase()) {
                            case "candidate":
                                displayTitle = "Candidates";
                                break;
                            case "client":
                                displayTitle = "Clients";
                                break;
                            case "course":
                                displayTitle = "Courses";
                                break;
                            case "job":
                                displayTitle = "Jobs";
                                break;
                            default:
                                displayTitle = type;
                        }

                        // Create and append the header element
                        const header = document.createElement("div");
                        header.className = "dropdown-header mt-2";
                        header.innerHTML = `<h6 class="text-overflow text-muted mb-2 text-uppercase">${displayTitle}</h6>`;
                        mobileResultsContainer.appendChild(header);

                        // Append each result in the current group
                        groups[type].forEach(item => {
                            const a = document.createElement("a");
                            a.href = item.url;
                            a.className = "dropdown-item notify-item";

                            // Determine the appropriate icon based on the entity type
                            let iconHtml = "";
                            switch (item.type.toLowerCase()) {
                                case "candidate":
                                    iconHtml = '<i class="ri-user-settings-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                case "client":
                                    iconHtml = '<i class="ri-building-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                case "course":
                                    iconHtml = '<i class="ri-book-open-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                case "job":
                                    iconHtml = '<i class="ri-briefcase-line align-middle fs-18 text-muted me-2"></i>';
                                    break;
                                default:
                                    iconHtml = '';
                            }

                            a.innerHTML = `${iconHtml}<span>${item.label}</span>`;
                            mobileResultsContainer.appendChild(a);
                        });
                    });
                    // Update the "View All" link if needed
                    const mobileViewAllLink = document.getElementById("mobile-view-all-results");
                    mobileViewAllLink.href = '/' + locale + `/admin/search/search-results/${encodeURIComponent(query)}`;

                    // Show the "View All" button now that results exist
                    viewAllContainer.style.display = "block";
                }
            })
            .catch(error => {
                console.error('Mobile search error:', error);
            });
    }

    // Attach submit event on mobile form (instead of keyup)
    mobileSearchForm.addEventListener("submit", function (e) {
        e.preventDefault();
        const query = mobileSearchInput.value.trim();
        if (query.length > 0) {
            // Force the dropdown to open using Bootstrap's Dropdown API
            const dropdownToggleEl = document.getElementById('page-header-search-dropdown');
            let dropdownInstance = bootstrap.Dropdown.getInstance(dropdownToggleEl);
            if (!dropdownInstance) {
                dropdownInstance = new bootstrap.Dropdown(dropdownToggleEl);
            }
            dropdownInstance.show();

            // update the "View All" link
            mobileViewAllLink.href = '/' + locale + `/admin/search/search-results/${encodeURIComponent(query)}`;

            // Fetch and display the mobile search results
            fetchMobileSearchResults(query);
        }
    });
});
