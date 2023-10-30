"use strict";
import SearchModule from './search';

// Class definition
var KTLayoutSearch = function() {
    // Private variables
    let element;
    let formElement;
    let mainElement;
    let resultsElement;
    let wrapperElement;
    let emptyElement;

    let preferencesElement;
    let preferencesShowElement;
    let preferencesDismissElement;
    let preferencesSaveElement;

    let advancedOptionsFormElement;
    let advancedOptionsFormShowElement;
    let advancedOptionsFormCancelElement;
    let advancedOptionsFormSearchElement;

    let adminGroups = [];
    let admins = new Map();

    let tryAdmins = [];

    let searchObject;

    let processsAjax = (search) => {

        setTryAdmins();

        // Learn more: https://axios-http.com/docs/intro
        axios.get('/admin/search',{
                ...axiosConfig,
                params: {
                    q: search.getQuery(),
                    admins: tryAdmins
                }
            })
            .then(function (response) {

                // Populate results
                resultsElement.innerHTML = response.data;
                // Show results
                resultsElement.classList.remove('d-none');
                // Hide empty message
                emptyElement.classList.add('d-none');

                // Complete search
                search.complete();
            })
            .catch(function (error) {
                // Hide results
                resultsElement.classList.add('d-none');
                // Show empty message
                emptyElement.classList.remove('d-none');

                // Complete search
                search.complete();
            });
    }

    let clear = (search) => {
        // Show recently viewed
        // mainElement.classList.remove('d-none');
        // Hide results
        resultsElement.classList.add('d-none');
        // // Hide empty message
        emptyElement.classList.add('d-none');
    }

    let handlePreferences = () => {

        adminGroups = JSON.parse(preferencesElement.dataset.cmsAdminGroups);
        adminGroups.forEach((admin) => {
            if(localStorage.getItem(admin.code) === null){
                localStorage.setItem(admin.code, 'true')
            }
            admins.set(admin.code, localStorage.getItem(admin.code) === 'true')
        });

        let checkboxes = preferencesElement.querySelectorAll('input[type="checkbox"]');

        checkboxes.forEach((checkbox) => {
            let isChecked = admins.get(checkbox.dataset.adminCode);
            checkbox.checked = isChecked;

            checkbox.addEventListener('change', () => {
                admins.set(checkbox.dataset.adminCode, checkbox.checked);
                localStorage.setItem(checkbox.dataset.adminCode, checkbox.checked)
            })
        })


        setTryAdmins();

        // Preference show handler
        if (preferencesShowElement) {
            preferencesShowElement.addEventListener('click', function() {
                wrapperElement.classList.add('d-none');
                preferencesElement.classList.remove('d-none');
            });
        }

        // Preference dismiss handler
        if (preferencesDismissElement) {
            preferencesDismissElement.addEventListener('click', function() {
                wrapperElement.classList.remove('d-none');
                preferencesElement.classList.add('d-none');
                searchObject.focus()
            });
        }

        // Preference dismiss handler
        if (preferencesSaveElement) {
            preferencesSaveElement.addEventListener('click', function(e) {
                e.preventDefault();
                setTryAdmins();
                searchObject.search();
                wrapperElement.classList.remove('d-none');
                preferencesElement.classList.add('d-none');
            });
        }

    }

    let setTryAdmins = () => {
        tryAdmins = Array.from(admins.entries()).filter((v) => {
            return v[1];
        }).map((v) => {
            return v[0];
        })
        document.querySelector('input[name="admins"]').value = JSON.stringify(tryAdmins);
    }

    // Public methods
    return {
        init: function() {
            // Elements
            element = document.querySelector('#kt_cms_search');

            if (!element) {
                return;
            }

            wrapperElement = element.querySelector('[data-kt-search-element="wrapper"]');
            formElement = element.querySelector('[data-kt-search-element="form"]');
            resultsElement = element.querySelector('[data-kt-search-element="results"]');
            emptyElement = element.querySelector('[data-kt-search-element="empty"]');

            preferencesElement = element.querySelector('[data-kt-search-element="preferences"]');
            preferencesShowElement = element.querySelector('[data-kt-search-element="preferences-show"]');
            preferencesDismissElement = element.querySelector('[data-kt-search-element="preferences-dismiss"]');
            preferencesSaveElement = element.querySelector('[data-kt-search-element="preferences-save"]');

            // Initialize search handler
            searchObject = new SearchModule(element)

            // Demo search handler
            searchObject.on('kt.search.process', processsAjax);

            // Clear handler
            searchObject.on('kt.search.clear', clear);

            // Custom handlers
            if (preferencesElement) {
                handlePreferences();

            }
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTLayoutSearch.init();
});