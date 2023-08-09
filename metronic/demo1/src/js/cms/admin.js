window.axiosConfig  = {headers: {'X-Requested-With': 'XMLHttpRequest'}}


// Class definition
var CMSAdmin = function () {
    // Define shared variables
    var listDialog



    var initLinkDialogs = () => {
        KTUtil.on(document.body, '.dialog-link', 'click', createLinkDialog);
    };

    var createLinkDialog = (event) => {
        event.preventDefault();
        event.stopPropagation();

        initializeDialog();

        let link = event.target;

        if(!link.getAttribute('href')) {
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
            listDialog.show();
        })
    }

    var initializeDialog = () => {
        if (!listDialog) {
            listDialog = new bootstrap.Modal(document.querySelector('#list_dialog', { height:'auto', width:650, show:false}))
        }
    }

    var initializeDatePickers = () => {
        document.querySelectorAll('[data-provider="datepicker"]').forEach((element) => {
            let dateFormat = element.dataset.dateFormat
            let locale = element.dataset.dateLanguage
            flatpickr(element, {dateFormat: dateFormat, locale: locale, allowInput: true})
        })
    }

    var initializeDateTimePickers = () => {
        document.querySelectorAll('[data-provider="datetimepicker"]').forEach((element) => {
            let dateFormat = element.dataset.dateFormat
            let locale = element.dataset.dateLanguage
            flatpickr(element, {
                dateFormat: dateFormat,
                locale: locale,
                enableTime: true,
                allowInput: true,
                time_24hr: true})
        })

    }

    var initSelect2 = () => {
        var elements = [].slice.call(document.querySelectorAll('[data-control="select2"], [data-kt-select2="true"]'));

        elements.map(function (element) {
            $(element).on('select2:select', function (e) {
                let event = new Event('change', {bubbles: true});
                element.dispatchEvent(event);
            });
        });

    }

    var initCkeditor = () => {
        var elements = [].slice.call(document.querySelectorAll('[data-control="ckeditor"], [data-kt-ckeditor="true"]'));
        elements.map(function (element) {
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

            for(const [key, value] of Object.entries(plugins)) {

                CKEDITOR.plugins.addExternal(key, value.path, value.filename);
            }


            let params = {}

            console.log(config)
            for(const [key, value] of Object.entries(config)) {
                if(key === 'filebrowserBrowseRoute') {
                    params['filebrowserBrowseUrl'] = Routing.generate(value, config.filebrowserBrowseRouteParameters)
                    continue;
                }

                if(key === 'filebrowserImageBrowseRoute') {
                    params['filebrowserImageBrowseUrl'] = Routing.generate(value, config.filebrowserImageBrowseRouteParameters)
                    continue;
                }

                if(key === 'filebrowserUploadRoute') {
                    params['filebrowserUploadUrl'] = Routing.generate(value, config.filebrowserUploadRouteParameters)
                    continue;
                }

                if(key === 'filebrowserImageUploadRoute') {
                    params['filebrowserImageUploadUrl'] = Routing.generate(value, config.filebrowserImageUploadRouteParameters)
                    continue;
                }

                if(key === 'filebrowserBrowseRouteParameters' || key === 'filebrowserImageBrowseRouteParameters' || key === 'filebrowserUploadRouteParameters' || key === 'filebrowserImageUploadRouteParameters') {
                    continue
                }

                params[key] = value
            }
            CKEDITOR.disableAutoInline = true
            let editor = CKEDITOR.replace(id,params);

            editor.on( 'change', function( evt ) {
                element.value = evt.editor.getData();
            });


            element.setAttribute("data-kt-initialized", "1");
        });

    }

    // Public methods
    return {
        init: function () {
            initLinkDialogs();
            initializeDatePickers();
            initializeDateTimePickers();
            initSelect2();

        },
        initSpecialFields: function () {
            this.createDatePickers();
            this.createDateTimePickers();
            this.createSelect2()
            this.createCkeditors()
        },
        createInitCmsMessageBox: function(status, message) {
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
        createDatePickers: function () {
            initializeDatePickers();
        },
        createDateTimePickers: function () {
            initializeDateTimePickers();
        },
        createCkeditors: function () {
            initCkeditor()
        },
        createSelect2: function () {
            // Check if jQuery included
            if (typeof jQuery == 'undefined') {
                return;
            }

            // Check if select2 included
            if (typeof $.fn.select2 === 'undefined') {
                return;
            }

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

                            menu.on("kt.menu.dropdown.hide", function(item) {
                                if (element.getAttribute("data-multiple-unselect") === "1") {
                                    element.removeAttribute("data-multiple-unselect");
                                    return false;
                                }
                            });
                        }
                    }
                }
                element.setAttribute("data-kt-initialized", "1");
            });
        }
    }
}();

var CMSMediaEntity = function () {

    var initMediaEntity = () => {
        document.querySelectorAll('.media-entity').forEach((element) => {
            let mediaEntity = new MediaEntity(element)

        })
    }

    return {
        init: function () {
            initMediaEntity();
        }
    }
}();

class MediaEntity{
    constructor(element) {
        this.element = element
        this.id = element.dataset.fieldId
        this.field = document.querySelector('#' + this.id)
        this.preview = element.querySelector('#field_widget_' + this.id)
        this.createModal()

        this.listLink = element.querySelector('#field_list_link_' + this.id)
        this.addLink = element.querySelector('#field_add_link_' + this.id)
        this.removeLink = element.querySelector('#field_remove_link_' + this.id)
        this.objectId = this.field.value
        this.selectMediaEventId = ''
        this.searchMediaEventId = ''

        KTUtil.on(this.element, '#' + this.id, 'change', this.updatePreview.bind(this));
        KTUtil.on(this.element, '#field_dialog_' + this.id, 'hide.bs.modal', this.removeListeners.bind(this));

        this.initialize()

    }
    initialize() {
        this.listLink.addEventListener('click', this.createListDialog.bind(this))
        this.removeLink.addEventListener('click', this.removeSelectedElement.bind(this))
        this.addLink.addEventListener('click', this.addMediaDialog.bind(this))
    }
    createModal() {
        let modal = document.createElement('div')
        modal.setAttribute('id', 'field_dialog_' + this.id)
        modal.setAttribute('class', 'modal fade')
        modal.setAttribute('tabindex', '-1')
        modal.setAttribute('role', 'dialog')
        modal.setAttribute('aria-hidden', 'true')
        modal.innerHTML = `
            <div class="modal-dialog mw-1000px" role="document">
                <div class="modal-content">
                </div>
            </div>
        `
        document.body.appendChild(modal)

        this.dialogContainer = document.querySelector('#field_dialog_' + this.id)
        this.dialog = new bootstrap.Modal(this.dialogContainer, {height:'auto', width:650, show:false})

    }
    setupTree(){
        let tagsContainer = this.dialogContainer.querySelector('#tagsContainer')
        let tree = $(tagsContainer)

        var lastLink = tagsContainer.dataset.selected

        let treeData = tree.data('tagsJson')
        tree.jstree({
            "types": {
                "default": {
                    "icon": "fa fa-folder text-primary fs-2x"
                },
                "file": {
                    "icon": "fa fa-file text-primary fs-2x"
                }
            },
            "plugins": ["sort", "types"],
            "sort": function (a, b) {
                let a1 = this.get_node(a);
                let b1 = this.get_node(b);
                if (a1.data['show_first']) {
                    return -1;
                }

                if (b1.data['show_first']) {
                    return 1;
                }
                return (a1.text.toLowerCase() > b1.text.toLowerCase()) ? 1 : -1;
            },
            'core':
                {
                    "themes": {
                        "variant":   "large"
                    },
                    data: function (node, cb) {
                        cb(treeData)
                    }
                }
        })
    }
    removeListeners() {
        KTUtil.off(this.dialogContainer, 'click', this.selectMediaEventId);
        KTUtil.off(this.dialogContainer, 'submit', this.searchMediaEventId);
        KTUtil.off(this.dialogContainer, 'submit', this.addMediaEventId);
    }
    addSearchListeners() {
        if(this.selectMediaEventId !== '') {
            this.removeListeners()
        }
        this.selectMediaEventId = KTUtil.on(this.dialogContainer, 'a', 'click', this.clickLinkInDialog.bind(this));
        this.searchMediaEventId = KTUtil.on(this.dialogContainer, 'form', 'submit', this.searchMedia.bind(this));
    }
    addUploadListeners() {
        if(this.addMediaEventId !== '') {
            this.removeListeners()
        }
        this.addMediaEventId = KTUtil.on(this.dialogContainer, 'form', 'submit', this.uploadMedia.bind(this));
    }
    createListDialog(event) {
        event.preventDefault();
        let listURL = this.listLink.getAttribute('href')
        fetch(listURL, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        }).then(response => {
            return response.text()
        }).then(html => {

            this.dialogContainer.querySelector('.modal-content').innerHTML = html;
            this.addSearchListeners()
            this.dialog.show();
            this.setupTree()
        })
    }
    clickLinkInDialog(event) {
        event.preventDefault();
        let link = event.target
        if(link.classList.contains('select-media')) {
            this.selectMedia(event)
            return
        }
        if(link.classList.contains('tag_link')) {
            var tagId = link.dataset.pk;
            this.refreshList({'filter[tags][value]': tagId});
            return
        }
        if(link.classList.contains('show_all_media')) {
            this.refreshList({'filter[tags][value]': ''});
            return
        }
        let url = link.getAttribute('href')

        if(!url || url === '#') {
            return
        }

        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        }).then(response => {
            return response.text()
        }).then(html => {
            this.dialogContainer.querySelector('.modal-content').innerHTML = html;
            this.addSearchListeners()
            this.setupTree()
        })
    }
    refreshList(filters) {
        if (!filters) {
            filters = {}
        }
        let data = new FormData( document.querySelector('#search-form'))
        for(const key in filters){
            data.append(key, filters[key])
        }

        data.set('galleryMode', 'gallery'),

            filters = Object.fromEntries(data.entries());

        axios.get(tagsContainer.dataset.refreshListUrl, {...axiosConfig, params: filters})
            .then(function (response) {
                document.querySelector('#item_list').innerHTML = response.data
            })
    }
    selectMedia(event) {
        event.preventDefault();
        let link = event.target
        this.field.value = link.dataset.objectId
        this.updatePreview()
        this.dialog.hide();
    }
    searchMedia(event) {
        event.preventDefault();
        let form = event.target
        let url = new URL(form.action)
        let data = Object.fromEntries(new FormData(form).entries());
        for(const key in data){
            url.searchParams.append(key, data[key])
        }

        axios.get(url.toString(), {...axiosConfig})
            .then(response => {

                this.dialogContainer.querySelector('.modal-content').innerHTML = response.data;
                this.addSearchListeners()
                this.setupTree()
            })

    }
    addMediaDialog(event) {
        event.preventDefault();
        let addURL = this.addLink.getAttribute('href')
        fetch(addURL, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        }).then(response => {
            return response.text()
        }).then(html => {

            this.dialogContainer.querySelector('.modal-content').innerHTML = html;
            this.addUploadListeners()
            this.dialog.show();
        })
    }
    uploadMedia(event) {
        event.preventDefault();
        event.stopPropagation();

        let form = event.target

        let formData = new FormData(form);

        axios.post(form.getAttribute('action'), formData, {...axiosConfig})
            .then(response => {
                if(response.data.result == 'ok') {
                    this.field.value = response.data.objectId
                    this.updatePreview()
                    this.dialog.hide()
                    return
                }
                this.dialogContainer.querySelector('.modal-content').innerHTML = response.data;
                this.addUploadListeners()
                CMSAdmin.createSelect2()

            }).catch(error => {

        })
    }
    updatePreview() {
        if(this.field.value === '') {
            this.preview.innerHTML = ''
            return
        }
        let url = this.preview.dataset.previewUrl.replace('__objectId__', this.field.value)
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        }).then(response => {
            return response.text()
        }).then(html => {
            this.preview.innerHTML = html;
        })
    }
    removeSelectedElement(event) {
        event.preventDefault();

        if(this.field.options){
            var elements = this.field.options;

            elements.map((element) => {
                element.selected = false;
            })
        }
        this.field.value = ''
        this.updatePreview()
    }
}




// On document ready
KTUtil.onDOMContentLoaded(function () {
    CMSAdmin.init();
    CMSMediaEntity.init();
    window.CMSAdmin = CMSAdmin;

    document.body.addEventListener('shown.bs.modal', function (e) {
        CMSAdmin.initSpecialFields()
    })
    document.body.addEventListener('fields:added', function (e) {
        CMSAdmin.initSpecialFields()
    })
});

if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = CMSAdmin;
}