import {CMSAdmin} from './cms-admin';
import 'jstree'
class MediaEntity{
    constructor(element) {

        if(element.dataset.modelListInitialized) {
            return
        }

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
        this.addMediaEventId  = ''

        KTUtil.on(this.element, '#' + this.id, 'change', this.updatePreview.bind(this));
        KTUtil.on(this.element, '#field_dialog_' + this.id, 'hide.bs.modal', this.removeListeners.bind(this));

        this.initialize()

    }
    initialize() {

        this.element.dataset.modelListInitialized = true

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


        document.body.addEventListener('hidden.bs.modal', (event) => {
            this.removeListeners()
        })

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
        if(this.searchMediaEventId !== '') {
            KTUtil.off(this.dialogContainer, 'submit', this.searchMediaEventId);
        }
        this.searchMediaEventId = KTUtil.on(this.dialogContainer, 'form', 'submit', this.searchMedia.bind(this));
    }
    addUploadListeners() {
        if(this.addMediaEventId !== '') {
            KTUtil.off(this.dialogContainer, 'submit', this.addMediaEventId);
        }
        this.addMediaEventId = KTUtil.on(this.dialogContainer, 'form', 'submit', this.uploadMedia.bind(this));
        this.dialogContainer.querySelectorAll('[data-bs-dismiss="modal"]').forEach((element) => {
            element.addEventListener('click', (event) => {
                event.preventDefault()
                this.dialog.hide()
            })
        })
    }
    addClickOnLinkListeners() {
        if(this.selectMediaEventId !== '') {
            KTUtil.off(this.dialogContainer, 'click', this.selectMediaEventId);
        }
        this.selectMediaEventId = KTUtil.on(this.dialogContainer, 'a', 'click', this.clickLinkInDialog.bind(this));
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
            this.addClickOnLinkListeners()
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

        if(!link.getAttribute('href')){
            link = link.closest('a')
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
            this.addUploadListeners()
            this.addClickOnLinkListeners()
            CMSAdmin.initSpecialFields()
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

const CMSMediaEntity =  {

    init(){
        document.querySelectorAll('.media-entity').forEach((element) => {
            let mediaEntity = new MediaEntity(element)

        })
    }
}

export default CMSMediaEntity;

