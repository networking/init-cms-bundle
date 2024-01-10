import {CMSAdmin} from './cms-admin';
class ModelList{
    constructor(element) {

        if(element.dataset.modelListInitialized) {
            return
        }

        this.element = element
        this.id = element.dataset.fieldId
        this.isListMode = element.dataset.isListMode
        this.fieldReloadUrl = element.dataset.fieldReloadUrl
        this.field = document.querySelector('#' + this.id)
        this.preview = element.querySelector('#field_widget_' + this.id)
        this.createModal()

        this.listLink = element.querySelector('#field_list_link_' + this.id)
        this.addLink = element.querySelector('#field_add_link_' + this.id)
        this.removeLink = element.querySelector('#field_remove_link_' + this.id)
        this.objectId = this.field.value
        this.selectModelEventId = ''
        this.searchModelEventId = ''
        this.perPageEventId = ''
        this.addModelEventId = ''

        KTUtil.on(this.element, '#' + this.id, 'change', this.updatePreview.bind(this));
        KTUtil.on(this.element, '#field_dialog_' + this.id, 'hide.bs.modal', this.removeListeners.bind(this));

        this.initialize()

    }
    initialize() {

        this.element.dataset.modelListInitialized = true
        if(this.listLink){
            this.listLink.addEventListener('click', this.createListDialog.bind(this))
        }
        if(this.removeLink){
            this.removeLink.addEventListener('click', this.removeSelectedElement.bind(this))
        }
        if(this.addLink){
            this.addLink.addEventListener('click', this.addDialog.bind(this))
        }
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
    removeListeners() {
        KTUtil.off(this.dialogContainer, 'click', this.selectModelEventId);
        KTUtil.off(this.dialogContainer, 'submit', this.searchModelEventId);
        KTUtil.off(this.dialogContainer, 'submit', this.addModelEventId);
    }
    addClickOnLinkListeners() {
        if(this.selectModelEventId !== '') {
            KTUtil.off(this.dialogContainer, 'click', this.selectModelEventId);
        }
        this.selectModelEventId = KTUtil.on(this.dialogContainer, 'a', 'click', this.clickLinkInDialog.bind(this));
    }
    addSearchListeners() {
        if(this.selectModelEventId !== '') {
            KTUtil.off(this.dialogContainer, 'submit', this.searchModelEventId);
        }
        this.searchModelEventId = KTUtil.on(this.dialogContainer, 'form', 'submit', this.searchModel.bind(this));
    }
    addSubmitisteners() {
        if(this.addModelEventId !== '') {
            KTUtil.off(this.dialogContainer, 'submit', this.addModelEventId);
        }
        this.addModelEventId = KTUtil.on(this.dialogContainer, 'form', 'submit', this.submitModel.bind(this));
        this.dialogContainer.querySelectorAll('[data-bs-dismiss="modal"]').forEach((element) => {
            element.addEventListener('click', (event) => {
                event.preventDefault()
                this.dialog.hide()
            })
        })
    }
    addPerPageListeners() {
        if(this.perPageEventId !== '') {
            KTUtil.off(this.dialogContainer, 'change', this.perPageEventId);
        }

        this.perPageEventId = KTUtil.on(this.dialogContainer, '.per-page', 'change', (e) => {
            e.preventDefault();
            let url = new URL(e.target.dataset.url)

            url.searchParams.append('filter[_page]', 1)
            url.searchParams.append('filter[_per_page]', e.target.value)

            axios.get(url.toString(), {...axiosConfig})
                .then(response => {

                    this.dialogContainer.querySelector('.modal-content').innerHTML = response.data;
                    this.addSearchListeners()
                    this.addClickOnLinkListeners()
                    this.addPerPageListeners()
                })

        })
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
            this.addPerPageListeners()
            this.dialog.show();
        })
    }
    clickLinkInDialog(event) {
        event.preventDefault();
        let link = event.target
        if(link.classList.contains('select-model') || link.dataset.objectId) {
            this.selectModel(event)
            return
        }

        //check if link starts with # or has data-bs-toggle attribute
        if(link.dataset.bsToggle) {
            return
        }
        
        if(link.tagName.toLowerCase() !== 'a') {
            link = link.closest('a')
        }

        let url = link.getAttribute('href')

        if(!url || url.indexOf('#') > -1) {
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
            this.addClickOnLinkListeners()
            this.addPerPageListeners()
            CMSAdmin.initSpecialFields()
        })
    }
    selectModel(event) {
        event.preventDefault();
        let link = event.target
        this.field.value = link.dataset.objectId
        this.updatePreview()
        this.dialog.hide();
    }
    searchModel(event) {
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
                this.addClickOnLinkListeners()
                this.addPerPageListeners()
            })

    }
    addDialog(event) {
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
            this.addSubmitisteners()
            this.addClickOnLinkListeners()
            this.dialog.show();
        })
    }
    submitModel(event) {
        event.preventDefault();
        event.stopPropagation();

        let form = event.target

        let formData = new FormData(form);

        axios.post(form.getAttribute('action'), formData, {...axiosConfig})
            .then(response => {

                if(response.data.result === 'ok' && this.isListMode === 'true') {

                    this.field.value = response.data.objectId
                    this.updatePreview()
                    this.dialog.hide()
                    return
                }

                if(response.data.result === 'ok' &&  this.isListMode === 'false') {
                    this.reloadFormField(response.data.objectId)
                    this.dialog.hide()
                    return
                }

                this.dialogContainer.querySelector('.modal-content').innerHTML = response.data;
                this.addSubmitisteners()
                this.addClickOnLinkListeners()
                CMSAdmin.createSelect2()

            }).catch(error => {

        })
    }
    reloadFormField(objectId) {
        axios.get(this.fieldReloadUrl, {...axiosConfig}).then((response) => {

            let shadow = document.createElement('div')
            shadow.innerHTML = response.data

            let previewHtml = shadow.querySelector('#field_widget_' + this.id).innerHTML
            this.preview.innerHTML = previewHtml
            this.field = this.preview.querySelector('#' + this.id)

            let newElement = this.preview.querySelector('#' + this.id+ ' [value="'+objectId+'"]')


            let fieldType = newElement.tagName.toLowerCase()

            if(fieldType === 'input'){
                newElement.setAttribute('checked', 'checked')
            }

            if(fieldType === 'select'){
                newElement.setAttribute('selected', 'selected')
            }

            if(this.field.dataset.control === 'select2'){
                // CMSAdmin.createSelect2()
                this.field.dispatchEvent(new Event('change'));
            }

        })
    }
    updatePreview() {
        if(this.isListMode === 'false'){
            return
        }

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

const CMSModelList =  {

    init(){
        document.querySelectorAll('.model-list').forEach((element) => {
            let modelList = new ModelList(element)

        })
    }
}

export default CMSModelList;

