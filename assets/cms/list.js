"use strict";

import $ from 'jquery';
import {CMSAdmin} from './cms-admin';
import de from './lang/datables/de.json';
import fr from './lang/datables/fr.json';
import en from './lang/datables/en.json';
import it from './lang/datables/it.json';


let Translator =  await CMSAdmin.getTranslator();


// Class definition
var CMSList = function () {
    // Define shared variables
    var datatable;
    var filterMonth;
    var filterPayment;
    var table
    var listDialog
    var advanceFilters;
    var advanceFilterToggle
    var pageLengthAttr = location.protocol + '//' + location.host + location.pathname + '_page_length'

    // Private functions
    var initList = function () {
        // Set date data order
        const tableRows = table.querySelectorAll('tbody tr');

        let locale =  document.documentElement.lang;
        let language = en
        switch (locale.substring(0, 2)) {
            case 'de':
                language = de;
                break;
            case 'fr':
                language = fr;
                break;
            case 'it':
                language = it;
                break;
            default:
                language = en;
        }

        let lengthMenu = [[20, 50, 100, -1], [20, 50, 100, Translator.trans('all', {}, 'admin')]];

        if (table.dataset.lengthMenu) {
            lengthMenu = JSON.parse(table.dataset.lengthMenu);
        }

        let defaultLength = lengthMenu[0][0];


        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = new DataTable(table,{
            search:false,
            'order': [],
            'columnDefs': [
                {orderable: false, targets: ['.no_sorting']},
                {searchable: false, targets: ['.no_searching']},
            ],
            'classes': {
                lengthSelect: "form-select form-select-sm d-inline-block form-select-solid w-75px" ,
            },
            pageLength: localStorage.getItem(pageLengthAttr) ? parseInt(localStorage.getItem(pageLengthAttr)) : defaultLength,
            language: language,
            lengthMenu: lengthMenu,
            layout: {
                topStart:null,
                topEnd:null,
                bottomStart: ['pageLength','info'],
                bottomEnd: 'paging'
            }
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        datatable.on('draw', function () {
            initToggleToolbar();
            toggleToolbars();
            KTMenu.init(); // reinit KTMenu instances
        });

        datatable.on('length.dt', function () {
            localStorage.setItem(pageLengthAttr, datatable.page.len());
        })
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = () => {
        const filterSearch = document.querySelector('[data-kt-list-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {

            if(table.querySelector('[data-kt-check="true"]')){
                resetCheckboxes();
            }

            datatable.search(e.target.value).draw();

        });
    }

    var resetCheckboxes = () => {
        table.querySelector('[data-kt-check="true"]').checked = false;
        const allCheckboxes = table.querySelectorAll('tbody [type="checkbox"]');
        allCheckboxes.forEach(c => {
            c.checked = false;
        })
    }

    var initBatchDialog = () => {
        let batchLink = document.querySelector('.batch-dialog-link')
        if (!batchLink) {
            return;
        }
        batchLink.addEventListener('click', createBatchDialog);

    };

    var createBatchDialog = (event) => {
        event.preventDefault();
        event.stopPropagation();

        initializeDialog();

        let submit = event.target
        let action = document.querySelector('[data-kt-list-table-select="batch_action"]').value
        let form = document.querySelector('#kt-batch-form');

        let formData = new FormData(form);

        formData.append('action', action);

        fetch(form.getAttribute('action'), {
            method: form.method,
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(response => {
            return response.text()
        }).then(html => {
            document.querySelector('#list_dialog').querySelector('.modal-content').innerHTML = html;
            listDialog.show();
            CMSAdmin.createSelect2();
        })
    }


    var initializeDialog = () => {
        if (!listDialog) {
            listDialog = new bootstrap.Modal(document.querySelector('#list_dialog', { height:'auto', width:650, show:false}))
        }
    }

    // Reset Filter
    var handleResetForm = () => {
        // Select reset button
        const resetButton = document.querySelector('[data-kt-list-table-filter="reset"]');

        // Reset datatable
        resetButton.addEventListener('click', function () {
            // Reset month
            filterMonth.val(null).trigger('change');

            // Reset payment type
            filterPayment[0].checked = true;

            // Reset datatable --- official docs reference: https://datatables.net/reference/api/search()
            datatable.search('').draw();
        });
    }

    // Init toggle toolbar
    var initToggleToolbar = () => {
        // Toggle selected action toolbar
        // Select all checkboxes
        const checkboxes = table.querySelectorAll('[type="checkbox"]');

        // Select elements

        // Toggle delete selected toolbar
        checkboxes.forEach(c => {
            // Checkbox on click event
            c.addEventListener('click', function () {
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        });


    }

    const toggleAdvancedFilters = () => {
        document.querySelectorAll('.advanced-filter').forEach(filter => {
            if(filter.classList.contains('d-none')) {
                return filter.classList.remove('d-none');
            }
            filter.classList.add('d-none');
        });
    }

    // Toggle toolbars
    const toggleToolbars = () => {
        // Define variables
        const toolbarSelected = document.querySelector('[data-kt-list-table-toolbar="selected"]');
        if(!toolbarSelected) {
            return;
        }
        const toolbarBase = document.querySelector('[data-kt-list-table-toolbar="base"]');
        const selectedCount = document.querySelector('[data-kt-list-table-select="selected_count"]');

        // Select refreshed checkbox DOM elements 
        const allCheckboxes = table.querySelectorAll('tbody [type="checkbox"]');

        // Detect checkboxes state & count
        let checkedState = false;
        let count = 0;

        // Count checked boxes
        allCheckboxes.forEach(c => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        // Toggle toolbars
        if (checkedState) {
            selectedCount.innerHTML = count;
            toolbarBase.classList.add('d-none');
            toolbarSelected.classList.remove('d-none');
        } else {
            toolbarBase.classList.remove('d-none');
            toolbarSelected.classList.add('d-none');
        }
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#init_cms_data_table');
            advanceFilterToggle = document.querySelector('#kt_advance_filter_toggle');

            if (advanceFilterToggle) {
                advanceFilterToggle.addEventListener('click', toggleAdvancedFilters);
            }

            if (!table) {
                return;
            }

            initList();
            initToggleToolbar();
            handleSearchDatatable();
            initBatchDialog();


            // handleFilterDatatable();
            // handleDeleteRows();
            // handleResetForm();
        }
    }
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    CMSList.init();
});