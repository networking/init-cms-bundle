import {CMSAdmin} from './cms-admin';
import 'jstree'
class GalleryEntity{
    constructor(element) {

        if(element.dataset.modelListInitialized) {
            return
        }

        this.element = element
        this.id = this.element.id
        this.createModal()
        this.listLink = element.querySelector('.open-select-media')
        this.collectionContainer = element.dataset.cmsSortableContainer
        this.selectMediaEventId = ''
        this.searchMediaEventId = ''
        this.checkMediaInputId = ''
        this.selected = new Set()
        this.previousSelected = new Set()

        KTUtil.on(this.element, '#field_dialog_' + this.id, 'hide.bs.modal', this.removeListeners.bind(this));

        this.initialize()

    }
    initialize() {

        this.element.dataset.modelListInitialized = true

        this.listLink.addEventListener('click', this.createListDialog.bind(this))
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
        KTUtil.off(this.dialogContainer, 'change', this.checkMediaInputId);
    }
    addSearchListeners() {
        if(this.selectMediaEventId !== '') {
            this.removeListeners()
        }
        this.selectMediaEventId = KTUtil.on(this.dialogContainer, 'a', 'click', this.clickLinkInDialog.bind(this));
        this.checkMediaInputId = KTUtil.on(this.dialogContainer, 'input[data-object-id]', 'change', this.selectMedia.bind(this));
        this.searchMediaEventId = KTUtil.on(this.dialogContainer, 'form', 'submit', this.searchMedia.bind(this));
        KTUtil.on(this.dialogContainer, '.confirm-select', 'click', this.submitSelectedMedia.bind(this));
    }

    createListDialog(event) {
        event.preventDefault();

        let host =  window.location.protocol + '//' + window.location.host
        let href = this.listLink.getAttribute('href')
        if(href.indexOf(host) === -1) {
            this.listLink.setAttribute('href', host + href)
        }

        let listURL = new URL(this.listLink.getAttribute('href'))
        this.element.querySelectorAll('[data-selected]').forEach((element) => {
            this.selected.add(element.dataset.selected)
            this.previousSelected.add(element.dataset.selected)
        })

        listURL.searchParams.append('selected', Array.from(this.selected))
        listURL.searchParams.append('galleryMode', 'gallery')


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
            let input = this.dialogContainer.querySelector(`input[data-object-id="${link.dataset.objectId}"]`)
            this.selectMedia(input)
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

        let host =  window.location.protocol + '//' + window.location.host
        let href = url
        if(url.indexOf(host) === -1) {
            url = host + url
        }

        let listURL = new URL(url)

        listURL.searchParams.append('selected', Array.from(this.selected))
        listURL.searchParams.append('galleryMode', 'gallery')
        listURL.searchParams.append('multiSelect', 'true')


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

        data.set('galleryMode', 'gallery')
        data.set('multiSelect', 'true')
        data.set('selected', Array.from(this.selected))
        filters = Object.fromEntries(data.entries());

        axios.get(tagsContainer.dataset.refreshListUrl, {...axiosConfig, params: filters})
            .then(function (response) {
                document.querySelector('#item_list').innerHTML = response.data
            })
    }
    selectMedia(input) {

        if(input instanceof Event) {
            input.preventDefault();
            input = input.target
        }else{
            input.checked = !input.checked
        }

        if(!input.checked) {
            input.closest('.overlay').classList.remove('overlay-block')
            this.selected.delete(input.dataset.objectId)
            return
        }
        this.selected.add(input.dataset.objectId)
        input.closest('.overlay').classList.add('overlay-block')
    }
    searchMedia(event) {
        event.preventDefault();
        let form = event.target
        let url = new URL(form.action)
        let data = Object.fromEntries(new FormData(form).entries());
        for(const key in data){
            url.searchParams.append(key, data[key])
        }

        url.searchParams.append('selected', Array.from(this.selected))
        url.searchParams.append('galleryMode', 'gallery')
        url.searchParams.append('multiSelect', 'true')

        axios.get(url.toString(), {...axiosConfig})
            .then(response => {
                this.dialogContainer.querySelector('.modal-content').innerHTML = response.data;
                this.addSearchListeners()
                this.setupTree()
            })

    }
    submitSelectedMedia(event) {
        event.preventDefault()
        console.log(this.previousSelected)
        this.dialogContainer.querySelectorAll('input[data-object-id]').forEach( (item, i)=> {
            let id = item.dataset.objectId

            if (item.checked && !this.previousSelected.has(id)) {
                this.addItemToCollection(item.dataset)
                this.previousSelected.add(id)
            }

            if(!item.checked && this.previousSelected.has(id)) {
                document.querySelector('[data-selected="' + id + '"]').remove()
                this.previousSelected.delete(id)
            }
        });
        document.dispatchEvent(new CustomEvent('afterRemoveItem'))
        CMSAdmin.initSpecialFields()
        this.dialog.hide();
    }
    addItemToCollection(item) {
        let node = `<a href="/admin/cms/media/${item.objectId}/edit?provider=sonata.media.provider.image&amp;context=default" target="new">
                                            <img  src="${item.path}" width="75" height="60"></a> &nbsp;&nbsp;`;
        let counter = 0
        const containerName = this.collectionContainer
        const container = document.querySelector(containerName)
        let lastItem = [...document.querySelectorAll(`div[id^="${container.id}_"]`)].pop()
        if (lastItem) {
            counter = parseInt(lastItem.id.replace(`${container.id}_`, ''))
        }
        counter += 1


        let proto = container.dataset.prototype;
        const protoName = container.dataset.prototypeName || '__name__';
        // Set field id
        const idRegexp = new RegExp(`${container.id}_${protoName}`, 'g');
        const fieldId = `${container.id}_${counter}`;
        proto = proto.replace(idRegexp, fieldId);

        // Set field name
        const parts = container.id.split('_');
        const nameRegexp = new RegExp(`${parts[parts.length - 1]}\\]\\[${protoName}`, 'g');
        proto = proto.replace(nameRegexp, `${parts[parts.length - 1]}][${counter}`);


        container.insertAdjacentHTML('beforeend', proto)

        document.querySelector(`#${fieldId}`).closest('.collection-item').dataset.selected = item.objectId
        document.querySelector(`#${fieldId}_media`).value = item.objectId
        document.querySelector(`#field_widget_${fieldId}_media`).querySelector('.inner-field-short-description').innerHTML = node

        document.dispatchEvent(new CustomEvent('afterAddItem', {bubbles: true, detail: {proto: proto, counter: counter}}))

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

const CMSGalleryEntity =  {

    init(){
        document.querySelectorAll('[data-gallery-entity]').forEach((element) => {
            let mediaEntity = new GalleryEntity(element)

        })
    }
}

export default CMSGalleryEntity;

