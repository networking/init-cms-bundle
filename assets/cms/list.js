"use strict";

import $ from 'jquery';
import {CMSAdmin, Routing} from './cms-admin';
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

    // Private functions
    var initCustomerList = function () {
        // Set date data order
        const tableRows = table.querySelectorAll('tbody tr');

        tableRows.forEach(row => {
            // const dateRow = row.querySelectorAll('td');
            // const realDate = moment(dateRow[5].innerHTML, "DD MMM YYYY, LT").format(); // select date from 5th column in table
            // dateRow[5].setAttribute('data-order', realDate);
        });

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = new DataTable(table,{
            'order': [],
            'columnDefs': [
                { orderable: false, targets: [0,'no_sorting'] },
            ],
            "lengthMenu": [[20, 50, 100, -1], [20, 50, 100, "All"]],
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        datatable.on('draw', function () {
            initToggleToolbar();
            handleDeleteRows();
            toggleToolbars();
            KTMenu.init(); // reinit KTMenu instances 
        });
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = () => {
        const filterSearch = document.querySelector('[data-kt-list-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            table.querySelector('[data-kt-check="true"]').checked = false;
            const allCheckboxes = table.querySelectorAll('tbody [type="checkbox"]');
            allCheckboxes.forEach(c => {
                c.checked = false;
            })
            datatable.search(e.target.value).draw();

        });
    }

    // Filter Datatable
    // var handleFilterDatatable = () => {
    //     // Select filter options
    //     filterMonth = $('[data-kt-list-table-filter="month"]');
    //     filterPayment = document.querySelectorAll('[data-kt-list-table-filter="payment_type"] [name="payment_type"]');
    //     const filterButton = document.querySelector('[data-kt-list-table-filter="filter"]');
    //
    //     // Filter datatable on submit
    //     filterButton.addEventListener('click', function () {
    //         // Get filter values
    //         const monthValue = filterMonth.val();
    //         let paymentValue = '';
    //
    //         // Get payment value
    //         filterPayment.forEach(r => {
    //             if (r.checked) {
    //                 paymentValue = r.value;
    //             }
    //
    //             // Reset payment value if "All" is selected
    //             if (paymentValue === 'all') {
    //                 paymentValue = '';
    //             }
    //         });
    //
    //         // Build filter string from filter options
    //         const filterString = monthValue + ' ' + paymentValue;
    //
    //         // Filter datatable --- official docs reference: https://datatables.net/reference/api/search()
    //         datatable.search(filterString).draw();
    //     });
    // }

    // Delete customer
    var handleDeleteRows = () => {
        // Select all delete buttons
        const deleteButtons = table.querySelectorAll('[data-kt-list-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');

                // Get customer name
                const customerName = parent.querySelectorAll('td')[1].innerText;

                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "Are you sure you want to delete " + customerName + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        Swal.fire({
                            text: "You have deleted " + customerName + "!.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        }).then(function () {
                            // Remove current row
                            datatable.row($(parent)).remove().draw();
                        });
                    } else if (result.dismiss === 'cancel') {
                        Swal.fire({
                            text: customerName + " was not deleted.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    }
                });
            })
        });
    }

    var initBatchDialog = () => {
        document.querySelector('.batch-dialog-link').addEventListener('click', createBatchDialog);

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
        const toolbarBase = document.querySelector('[data-kt-list-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-list-table-toolbar="selected"]');
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

            initCustomerList();
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