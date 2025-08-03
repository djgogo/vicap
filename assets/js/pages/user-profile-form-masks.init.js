/*
Template Name: Velzon - Admin & Dashboard Template
Author: Themesbrand
Website: https://Themesbrand.com/
Contact: Themesbrand@gmail.com
File: Form masks Js File
*/

if (document.querySelector("#cleave-date")) {
    var cleaveDate = new Cleave('#cleave-date', {
        date: true,
        delimiter: '-',
        datePattern: ['d', 'm', 'Y']
    });
}

if (document.querySelector("#cleave-date-format")) {
    var cleaveDateFormat = new Cleave('#cleave-date-format', {
        date: true,
        datePattern: ['m', 'y']
    });
}

if (document.querySelector("#cleave-time")) {
    var cleaveTime = new Cleave('#cleave-time', {
        time: true,
        timePattern: ['h', 'm', 's']
    });
}

if (document.querySelector("#cleave-time-format")) {
    var cleaveTimeFormat = new Cleave('#cleave-time-format', {
        time: true,
        timePattern: ['h', 'm']
    });
}

if (document.querySelector("#cleave-numeral")) {
    var cleaveNumeral = new Cleave('#cleave-numeral', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand'
    });
}

if (document.querySelector("#cleave-ccard")) {
    var cleaveBlocks = new Cleave('#cleave-ccard', {
        blocks: [4, 4, 4, 4],
        uppercase: true
    });
}

if (document.querySelector("#cleave-delimiter")) {
    var cleaveDelimiter = new Cleave('#cleave-delimiter', {
        delimiter: '·',
        blocks: [3, 3, 3],
        uppercase: true
    });
}

if (document.querySelector("#cleave-delimiters")) {
    var cleaveDelimiters = new Cleave('#cleave-delimiters', {
        delimiters: ['.', '.', '-'],
        blocks: [3, 3, 3, 2],
        uppercase: true
    });
}

if (document.querySelector("#cleave-prefix")) {
    var cleavePrefix = new Cleave('#cleave-prefix', {
        prefix: 'PREFIX',
        delimiter: '-',
        blocks: [6, 4, 4, 4],
        uppercase: true
    });
}

if (document.querySelector("#cleave-mobile-phone")) {
    var cleaveMobilePhone = new Cleave('#cleave-mobile-phone', {
        delimiters: [' '],
        blocks: [3, 3, 2, 2]
    });
}

/**
 * custom implementation for the international cleave mask after user selects the country
 */
document.addEventListener('DOMContentLoaded', function() {
    let cleavePhone = null;
    const countrySelect = document.getElementById('user_form_country');
    const phoneInput = document.getElementById('cleave-phone');

    /**
     * (Re)initialize Cleave with a custom prefix/blocks
     * depending on the isoCode.
     */
    function initCleave(isoCode) {
        // Destroy old instance if it exists
        if (cleavePhone) {
            cleavePhone.destroy();
            phoneInput.value = ""; // optionally clear the field
        }

        // Decide mask based on isoCode
        switch (isoCode) {
            case 'CH': // Switzerland
                cleavePhone = new Cleave(phoneInput, {
                    prefix: '+41',
                    delimiters: [' '],
                    blocks: [3, 2, 3, 2, 2],
                    numericOnly: true
                });
                break;

            case 'DE': // Germany
                cleavePhone = new Cleave(phoneInput, {
                    prefix: '+49',
                    delimiters: [' '],
                    blocks: [3, 3, 4, 4],
                    numericOnly: true
                });
                break;

            case 'IT': // Italy
                cleavePhone = new Cleave(phoneInput, {
                    prefix: '+39',
                    delimiters: [' '],
                    blocks: [3, 3, 4, 3, 3],
                    numericOnly: true
                });
                break;

            case 'FR': // France
                cleavePhone = new Cleave(phoneInput, {
                    prefix: '+33',
                    delimiters: [' '],
                    blocks: [3, 3, 4, 3, 3],
                    numericOnly: true
                });
                break;

            case 'AT': // Austria
                cleavePhone = new Cleave(phoneInput, {
                    prefix: '+43',
                    delimiters: [' '],
                    blocks: [3, 3, 4, 3, 3],
                    numericOnly: true
                });
                break;

            default:
                // No Cleave setup or fallback logic
                // You could also create a generic mask if you like
                break;
        }
    }

    /**
     * Read the data-iso-code from the currently selected <option>.
     */
    function getSelectedIsoCode() {
        const selectedOption = countrySelect.options[countrySelect.selectedIndex];
        return selectedOption ? selectedOption.dataset.isoCode : null;
    }

    // 1) On page load, if there's a pre-selected country
    let initialIso = getSelectedIsoCode();
    if (initialIso) {
        initCleave(initialIso);
    }

    // 2) When user changes country, re-init Cleave
    countrySelect.addEventListener('change', function() {
        let newIso = getSelectedIsoCode();
        initCleave(newIso);
    });
});