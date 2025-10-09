// locale persistence into the database
document.querySelectorAll('.language').forEach(el => {
    el.addEventListener('click', function () {
        const locale = this.dataset.locale;
        let route_locale = window.location.pathname.split('/')[1];

        console.log(locale);

        // Fire the AJAX request with keepalive to ensure it completes during page unload
        fetch('/' + route_locale + '/change-locale', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ locale: locale }),
            keepalive: true
        });
    });
});
