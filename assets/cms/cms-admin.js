import './cms-admin.scss';
import InitCms from "../js/initcms";

window.axiosConfig = {headers: {'X-Requested-With': 'XMLHttpRequest'}}

import CMSMediaEntity from "./media-entity"
import CMSOneToManySortable from "./one-to-many-sortable"
import CMSModelList from "./model-list"
import {CMSRouting} from "./cms-routing"
import 'select2'

$.fn.select2.defaults.set("theme", "bootstrap5");
$.fn.select2.defaults.set("width", "100%");
$.fn.select2.defaults.set("selectionCssClass", ":all:");


// Class definition
const CMSAdmin = {
    // Define shared variables
    listDialog: null,
    routing: null,
    collectionCounters: [],

    async getRouting() {
        if (!this.routing) {
            console.log('loading routing')
            this.routing = CMSRouting.load();
        }

        return this.routing;
    },

    async init() {
        this.routing = await this.getRouting();
        CMSMediaEntity.init();
        CMSModelList.init();
        CMSOneToManySortable.init();
        this.initLinkDialogs();
        this.initializeDatePickers();
        this.initializeDateTimePickers();
        this.initSelect2();
        this.initCkeditor();
        this.initCollectionType()

    },


    initLinkDialogs() {

        KTUtil.on(document.body, '.dialog-link', 'click', (event) => {

            this.createLinkDialog(event);
        });
    },

    createLinkDialog(event) {
        event.preventDefault();
        event.stopPropagation();
        this.initializeDialog();

        let link = event.target;

        if (!link.getAttribute('href')) {
            link = link.closest('a');
        }

        fetch(link.getAttribute('href'), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        }).then(response => {
            return response.text()
        }).then(html => {
            document.querySelector('#list_dialog').querySelector('.modal-content').innerHTML = html;
            this.listDialog.show();
        })
    },

    initializeDialog() {
        if (!this.listDialog) {
            this.listDialog = new bootstrap.Modal(document.querySelector('#list_dialog', {
                height: 'auto',
                width: 650,
                show: false
            }))
        }
    },

    initializeDatePickers() {
        document.querySelectorAll('[data-provider="datepicker"]').forEach((element) => {
            let dateFormat = element.dataset.dateFormat
            let locale = element.dataset.dateLanguage
            flatpickr(element, {dateFormat: dateFormat, locale: locale, allowInput: true})
        })
    },

    initializeDateTimePickers() {
        document.querySelectorAll('[data-provider="datetimepicker"]').forEach((element) => {
            let dateFormat = element.dataset.dateFormat
            let locale = element.dataset.dateLanguage
            flatpickr(element, {
                dateFormat: dateFormat,
                locale: locale,
                enableTime: true,
                allowInput: true,
                time_24hr: true
            })
        })

    },
    initSelect2() {
        var elements = [].slice.call(document.querySelectorAll('[data-control="select2"], [data-kt-select2="true"]'));

        elements.map(function (element) {
            $(element).on('select2:select', function (e) {
                let event = new Event('change', {bubbles: true});
                element.dispatchEvent(event);
            });
        });

    },
    initCkeditor() {
        var elements = [].slice.call(document.querySelectorAll('[data-control="ckeditor"], [data-kt-ckeditor="true"]'));


        elements.map((element) => {
            if (element.getAttribute("data-kt-initialized") === "1") {
                return;
            }
            let id = element.getAttribute("id")

            let config = JSON.parse(element.dataset.config)
            let plugins = JSON.parse(element.dataset.plugins)
            let templates = JSON.parse(element.dataset.templates)
            let styles = JSON.parse(element.dataset.styles)
            let filebrowser = JSON.parse(element.dataset.filebrowser)
            if (CKEDITOR.instances[id]) {
                CKEDITOR.instances[id].destroy(true);
                delete CKEDITOR.instances[id];
            }

            for (const [key, value] of Object.entries(plugins)) {

                CKEDITOR.plugins.addExternal(key, value.path, value.filename);
            }


            let params = {}

            for (const [key, value] of Object.entries(config)) {
                if (key === 'filebrowserBrowseRoute') {
                    params['filebrowserBrowseUrl'] = this.routing.generate(value, config.filebrowserBrowseRouteParameters)
                    continue;
                }

                if (key === 'filebrowserImageBrowseRoute') {
                    params['filebrowserImageBrowseUrl'] = this.routing.generate(value, config.filebrowserImageBrowseRouteParameters)
                    continue;
                }

                if (key === 'filebrowserUploadRoute') {
                    params['filebrowserUploadUrl'] = this.routing.generate(value, config.filebrowserUploadRouteParameters)
                    continue;
                }

                if (key === 'filebrowserImageUploadRoute') {
                    params['filebrowserImageUploadUrl'] = this.routing.generate(value, config.filebrowserImageUploadRouteParameters)
                    continue;
                }

                if (key === 'filebrowserBrowseRouteParameters' || key === 'filebrowserImageBrowseRouteParameters' || key === 'filebrowserUploadRouteParameters' || key === 'filebrowserImageUploadRouteParameters') {
                    continue
                }

                params[key] = value
            }
            CKEDITOR.disableAutoInline = true
            let editor = CKEDITOR.replace(id, params);

            editor.on('change', function (evt) {
                element.value = evt.editor.getData();
            });


            element.setAttribute("data-kt-initialized", "1")
        });

    },
    initCollectionType() {
        let subject = document.querySelector('body');

        if (subject.dataset.cmsCollectionType) {
            return;
        }

        subject.dataset.cmsCollectionType = 1;

        KTUtil.on(subject, '[data-collection-add-btn]', 'click', (event) => {
            event.preventDefault()
            let btn = event.target
            if (btn.classList.contains('btn')) {
                btn = btn.closest('.btn');
            }

            let counter = 0
            const containerName = btn.dataset.collectionAddBtn;
            const container = document.querySelector(`#${containerName}`)
            let lastItem = [...document.querySelectorAll(`div[id^="${container.id}_"]`)].pop()
            if (lastItem) {
                counter = parseInt(lastItem.id.replace(`${container.id}_`, ''))
            }
            counter += 1


            let proto = container.dataset.prototype;
            const protoName = container.dataset.prototypeName || '__name__';
            // Set field id
            const idRegexp = new RegExp(`${container.id}_${protoName}`, 'g');
            proto = proto.replace(idRegexp, `${container.id}_${counter}`);

            // Set field name
            const parts = container.id.split('_');
            const nameRegexp = new RegExp(`${parts[parts.length - 1]}\\]\\[${protoName}`, 'g');
            proto = proto.replace(nameRegexp, `${parts[parts.length - 1]}][${counter}`);

            container.insertAdjacentHTML('beforeend', proto)
            CMSAdmin.initSpecialFields()
        });

        KTUtil.on(subject, '[data-collection-remove-btn]', 'click', (event) => {
            event.preventDefault()

            let btn = event.target

            if (btn.classList.contains('btn')) {
                btn = btn.closest('.btn');
            }
            btn.closest('.collection-item').remove();

        })
    },
    initSpecialFields() {

        CMSMediaEntity.init()
        CMSModelList.init()
        this.initializeDatePickers()
        this.initializeDateTimePickers()
        this.createSelect2()
        this.initCkeditor()
        this.initCollectionType()
    },
    createInitCmsMessageBox(status, message) {
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": false,
            "positionClass": "toastr-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "2000",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "linear",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        if (status === 'success') {
            toastr.success(message);
        } else if (status === 'warning') {
            toastr.warning(message);
        } else if (status === 'error' || status === 'danger') {
            toastr.error(message);
        } else {
            toastr.info(message);
        }
    },
    createSelect2() {
        // Check if jQuery included
        var elements = [].slice.call(document.querySelectorAll('[data-control="select2"], [data-kt-select2="true"]'));


        elements.map(function (element) {
            if (element.getAttribute("data-kt-initialized") === "1") {
                return;
            }

            var options = {
                dir: document.body.getAttribute('direction')
            };

            if (element.getAttribute('data-hide-search') === 'true') {
                options.minimumResultsForSearch = Infinity;
            }

            $(element).select2(options);
            $(element).on('select2:select', function (e) {
                let event = new Event('change', {bubbles: true});
                element.dispatchEvent(event);
            });

            // Handle Select2's KTMenu parent case
            if (element.hasAttribute('data-dropdown-parent') && element.hasAttribute('multiple')) {
                var parentEl = document.querySelector(element.getAttribute('data-dropdown-parent'));

                if (parentEl && parentEl.hasAttribute("data-kt-menu")) {
                    var menu = new KTMenu(parentEl);

                    if (menu) {
                        $(element).on('select2:unselect', function (e) {
                            element.setAttribute("data-multiple-unselect", "1");
                        });

                        menu.on("kt.menu.dropdown.hide", function (item) {
                            if (element.getAttribute("data-multiple-unselect") === "1") {
                                element.removeAttribute("data-multiple-unselect");
                                return false;
                            }
                        });
                    }
                }
            }
            element.setAttribute("data-kt-initialized", "1");
        })
    }
}


global.CMSAdmin = CMSAdmin;
window.CMSAdmin = CMSAdmin;
export {CMSAdmin}