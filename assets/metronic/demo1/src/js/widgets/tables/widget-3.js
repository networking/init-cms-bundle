"use strict";

// Class definition
var KTTablesWidget3 = function () {
    var table;
    var datatable;

    // Private methods
    const initDatatable = () => {
        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            'paging': false,
            'pageLength': false,
        });
    }

    const handleTabStates = () => {
        const tabs = document.querySelector('[data-kt-table-widget-3="tabs_nav"]');
        const tabButtons = tabs.querySelectorAll('[data-kt-table-widget-3="tab"]');
        const tabClasses = ['border-bottom', 'border-3', 'border-primary'];

        tabButtons.forEach(tab => {
            tab.addEventListener('click', e => {
                // Get datatable filter value
                const value = tab.getAttribute('data-kt-table-widget-3-value');
                tabButtons.forEach(t => {
                    t.classList.remove(...tabClasses);
                    t.classList.add('text-muted');
                });

                tab.classList.remove('text-muted');
                tab.classList.add(...tabClasses);

                // Filter datatable
                if (value === 'Show All') {
                    datatable.search('').draw();
                } else {
                    datatable.search(value).draw();
                }
            });
        });
    }

    // Handle status filter dropdown
    const handleStatusFilter = () => {
        const select = document.querySelector('[data-kt-table-widget-3="filter_status"]');

        $(select).on('select2:select', function (e) {
            const value = $(this).val();
            if (value === 'Show All') {
                datatable.search('').draw();
            } else {
                datatable.search(value).draw();
            }
        });
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_widget_table_3');

            if (!table) {
                return;
            }

            initDatatable();
            handleTabStates();
            handleStatusFilter();
        }
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTTablesWidget3;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTTablesWidget3.init();
});
