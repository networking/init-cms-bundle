"use strict";

// Class definition
var KTTablesWidget4 = function () {
    var table;
    var datatable;
    var template;

    // Private methods
    const initDatatable = () => {
        // Get subtable template
        const subtable = document.querySelector('[data-kt-table-widget-4="subtable_template"]');
        template = subtable.cloneNode(true);
        template.classList.remove('d-none');

        // Remove subtable template
        subtable.parentNode.removeChild(subtable);

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            "lengthChange": false,
            'pageLength': 6,
            'ordering': false,
            'paging': false,
            'columnDefs': [
                { orderable: false, targets: 0 }, // Disable ordering on column 0 (checkbox)
                { orderable: false, targets: 6 }, // Disable ordering on column 6 (actions)
            ]
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        datatable.on('draw', function () {
            resetSubtable();
            handleActionButton();
        });
    }

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = () => {
        const filterSearch = document.querySelector('[data-kt-table-widget-4="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Handle status filter
    const handleStatusFilter = () => {
        const select = document.querySelector('[data-kt-table-widget-4="filter_status"]');

        $(select).on('select2:select', function (e) {
            const value = $(this).val();
            if (value === 'Show All') {
                datatable.search('').draw();
            } else {
                datatable.search(value).draw();
            }
        });
    }

    // Subtable data sample
    const data = [
        {
            image: '76',
            name: 'Go Pro 8',
            description: 'Latest  version of Go Pro.',
            cost: '500.00',
            qty: '1',
            total: '500.00',
            stock: '12'
        },
        {
            image: '60',
            name: 'Bose Earbuds',
            description: 'Top quality earbuds from Bose.',
            cost: '300.00',
            qty: '1',
            total: '300.00',
            stock: '8'
        },
        {
            image: '211',
            name: 'Dry-fit Sports T-shirt',
            description: 'Comfortable sportswear.',
            cost: '89.00',
            qty: '1',
            total: '89.00',
            stock: '18'
        },
        {
            image: '21',
            name: 'Apple Airpod 3',
            description: 'Apple\'s latest earbuds.',
            cost: '200.00',
            qty: '2',
            total: '400.00',
            stock: '32'
        },
        {
            image: '83',
            name: 'Nike Pumps',
            description: 'Apple\'s latest headphones.',
            cost: '200.00',
            qty: '1',
            total: '200.00',
            stock: '8'
        }
    ];

    // Handle action button
    const handleActionButton = () => {
        const buttons = document.querySelectorAll('[data-kt-table-widget-4="expand_row"]');

        // Sample row items counter --- for demo purpose only, remove this variable in your project
        const rowItems = [3, 1, 3, 1, 2, 1];

        buttons.forEach((button, index) => {
            button.addEventListener('click', e => {
                e.stopImmediatePropagation();
                e.preventDefault();

                const row = button.closest('tr');
                const rowClasses = ['isOpen', 'border-bottom-0'];

                // Get total number of items to generate --- for demo purpose only, remove this code snippet in your project
                const demoData = [];
                for (var j = 0; j < rowItems[index]; j++) {
                    demoData.push(data[j]);
                }
                // End of generating demo data

                // Handle subtable expanded state
                if (row.classList.contains('isOpen')) {
                    // Remove all subtables from current order row
                    while (row.nextSibling && row.nextSibling.getAttribute('data-kt-table-widget-4') === 'subtable_template') {
                        row.nextSibling.parentNode.removeChild(row.nextSibling);
                    }
                    row.classList.remove(...rowClasses);
                    button.classList.remove('active');
                } else {
                    populateTemplate(demoData, row);
                    row.classList.add(...rowClasses);
                    button.classList.add('active');
                }
            });
        });
    }

    // Populate template with content/data -- content/data can be replaced with relevant data from database or API
    const populateTemplate = (data, target) => {
        data.forEach((d, index) => {
            // Clone template node
            const newTemplate = template.cloneNode(true);

            // Stock badges
            const lowStock = `<div class="badge badge-light-warning">Low Stock</div>`;
            const inStock = `<div class="badge badge-light-success">In Stock</div>`;

            // Select data elements
            const image = newTemplate.querySelector('[data-kt-table-widget-4="template_image"]');
            const name = newTemplate.querySelector('[data-kt-table-widget-4="template_name"]');
            const description = newTemplate.querySelector('[data-kt-table-widget-4="template_description"]');
            const cost = newTemplate.querySelector('[data-kt-table-widget-4="template_cost"]');
            const qty = newTemplate.querySelector('[data-kt-table-widget-4="template_qty"]');
            const total = newTemplate.querySelector('[data-kt-table-widget-4="template_total"]');
            const stock = newTemplate.querySelector('[data-kt-table-widget-4="template_stock"]');

            // Populate elements with data
            const imageSrc = image.getAttribute('data-kt-src-path');
            image.setAttribute('src', imageSrc + d.image + '.png');
            name.innerText = d.name;
            description.innerText = d.description;
            cost.innerText = d.cost;
            qty.innerText = d.qty;
            total.innerText = d.total;
            if (d.stock > 10) {
                stock.innerHTML = inStock;
            } else {
                stock.innerHTML = lowStock;
            }

            // New template border controller
            // When only 1 row is available
            if (data.length === 1) {
                //let borderClasses = ['rounded', 'rounded-end-0'];
                //newTemplate.querySelectorAll('td')[0].classList.add(...borderClasses);
                //borderClasses = ['rounded', 'rounded-start-0'];
                //newTemplate.querySelectorAll('td')[4].classList.add(...borderClasses);

                // Remove bottom border
                //newTemplate.classList.add('border-bottom-0');
            } else {
                // When multiple rows detected
                if (index === (data.length - 1)) { // first row
                    //let borderClasses = ['rounded-start', 'rounded-bottom-0'];
                   // newTemplate.querySelectorAll('td')[0].classList.add(...borderClasses);
                    //borderClasses = ['rounded-end', 'rounded-bottom-0'];
                    //newTemplate.querySelectorAll('td')[4].classList.add(...borderClasses);
                }
                if (index === 0) { // last row
                    //let borderClasses = ['rounded-start', 'rounded-top-0'];
                    //newTemplate.querySelectorAll('td')[0].classList.add(...borderClasses);
                    //borderClasses = ['rounded-end', 'rounded-top-0'];
                    //newTemplate.querySelectorAll('td')[4].classList.add(...borderClasses);

                    // Remove bottom border on last row
                    //newTemplate.classList.add('border-bottom-0');
                }
            }

            // Insert new template into table
            const tbody = table.querySelector('tbody');
            tbody.insertBefore(newTemplate, target.nextSibling);
        });
    }

    // Reset subtable
    const resetSubtable = () => {
        const subtables = document.querySelectorAll('[data-kt-table-widget-4="subtable_template"]');
        subtables.forEach(st => {
            st.parentNode.removeChild(st);
        });

        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(r => {
            r.classList.remove('isOpen');
            if (r.querySelector('[data-kt-table-widget-4="expand_row"]')) {
                r.querySelector('[data-kt-table-widget-4="expand_row"]').classList.remove('active');
            }
        });
    }

    // Public methods
    return {
        init: function () {
            table = document.querySelector('#kt_table_widget_4_table');

            if (!table) {
                return;
            }

            initDatatable();
            handleSearchDatatable();
            handleStatusFilter();
            handleActionButton();
        }
    }
}();

// Webpack support
if (typeof module !== 'undefined') {
    module.exports = KTTablesWidget4;
}

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTTablesWidget4.init();
});
