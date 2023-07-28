"use strict";

// Class definition
var KTLayoutSearch = function() {
    // Private variables
    var element;
    var formElement;
    var mainElement;
    var resultsElement;
    var wrapperElement;
    var emptyElement;

    var preferencesElement;
    var preferencesShowElement;
    var preferencesDismissElement;
    
    var advancedOptionsFormElement;
    var advancedOptionsFormShowElement;
    var advancedOptionsFormCancelElement;
    var advancedOptionsFormSearchElement;
    
    var searchObject;

    // Private functions
    var processs = function(search) {
        var timeout = setTimeout(function() {
            var number = KTUtil.getRandomInt(1, 3);

            // Hide recently viewed
            mainElement.classList.add('d-none');

            if (number === 3) {
                // Hide results
                resultsElement.classList.add('d-none');
                // Show empty message 
                emptyElement.classList.remove('d-none');
            } else {
                // Show results
                resultsElement.classList.remove('d-none');
                // Hide empty message 
                emptyElement.classList.add('d-none');
            }                  

            // Complete search
            search.complete();
        }, 1500);
    }

    var processsAjax = function(search) {
        // Hide recently viewed
        mainElement.classList.add('d-none');

        // Learn more: https://axios-http.com/docs/intro
        axios.post('/search.php', {
            query: searchObject.getQuery()
        })
        .then(function (response) {
            // Populate results
            resultsElement.innerHTML = response;
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

    var clear = function(search) {
        // Show recently viewed
        mainElement.classList.remove('d-none');
        // Hide results
        resultsElement.classList.add('d-none');
        // Hide empty message 
        emptyElement.classList.add('d-none');
    }    

    var handlePreferences = function() {
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
            });
        }
    }

    var handleAdvancedOptionsForm = function() {
        // Show
        if (advancedOptionsFormShowElement) {            
            advancedOptionsFormShowElement.addEventListener('click', function() {
                wrapperElement.classList.add('d-none');
                advancedOptionsFormElement.classList.remove('d-none');
            });
        }        

        // Cancel
        if (advancedOptionsFormCancelElement) {           
            advancedOptionsFormCancelElement.addEventListener('click', function() {
                wrapperElement.classList.remove('d-none');
                advancedOptionsFormElement.classList.add('d-none');
            });
        }
    }

    // Public methods
	return {
		init: function() {
            // Elements
            element = document.querySelector('#kt_header_search');

            if (!element) {
                return;
            }

            wrapperElement = element.querySelector('[data-kt-search-element="wrapper"]');
            formElement = element.querySelector('[data-kt-search-element="form"]');
            mainElement = element.querySelector('[data-kt-search-element="main"]');
            resultsElement = element.querySelector('[data-kt-search-element="results"]');
            emptyElement = element.querySelector('[data-kt-search-element="empty"]');

            preferencesElement = element.querySelector('[data-kt-search-element="preferences"]');
            preferencesShowElement = element.querySelector('[data-kt-search-element="preferences-show"]');
            preferencesDismissElement = element.querySelector('[data-kt-search-element="preferences-dismiss"]');

            advancedOptionsFormElement = element.querySelector('[data-kt-search-element="advanced-options-form"]');
            advancedOptionsFormShowElement = element.querySelector('[data-kt-search-element="advanced-options-form-show"]');
            advancedOptionsFormCancelElement = element.querySelector('[data-kt-search-element="advanced-options-form-cancel"]');
            advancedOptionsFormSearchElement = element.querySelector('[data-kt-search-element="advanced-options-form-search"]');
            
            // Initialize search handler
            searchObject = new KTSearch(element);

            // Demo search handler
            searchObject.on('kt.search.process', processs);

            // Ajax search handler
            //searchObject.on('kt.search.process', processsAjax);

            // Clear handler
            searchObject.on('kt.search.clear', clear);

            // Custom handlers
            if (preferencesElement) {
                handlePreferences();
            }            

            if (advancedOptionsFormElement) {
                handleAdvancedOptionsForm();
            }                        
		}
	};
}();

// On document ready
KTUtil.onDOMContentLoaded(function() {
    KTLayoutSearch.init();
});