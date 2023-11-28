import Sortable from 'sortablejs/modular/sortable.complete.esm.js';
import SmoothScroll from 'smooth-scroll';

import FormLegend from './components/form-legend';
import InfoText from './components/info-text';
import TextInput from './components/text-input';
import TextArea from './components/text-area';
import {SelectBasic, SelectMultiple} from './components/select-basic';
import {MultipleCheckboxes, MultipleCheckboxesInline, MultipleRadios, MultipleRadiosInline} from './components/multiple-checkboxes';

import '../scss/form_admin.scss';

let formContent = null;
let contentPanes = [];
let formFields = new Map();
let swappable = null;
let postUrl = null;
let objectId = null;

let initFormFields = () => {
    let currentFields = formContent.querySelectorAll('.component');

    currentFields.forEach((item) => {
        let block = createFormField(item);
        formFields.set(item.dataset.id, block);
    })
}

let createFormField = (item) => {
    let block = null;
    switch (item.dataset.type) {
        case 'Legend':
            block = new FormLegend(item.dataset.id, item.dataset.value, item);
            break;
        case 'Infotext':
            block = new InfoText(item.dataset.id, item.dataset.value, item);
            break;
        case 'Text Input':
            block = new TextInput(item.dataset.id, item.dataset.value, item);
            break;
        case 'Text Area':
            block = new TextArea(item.dataset.id, item.dataset.value, item);
            break;
        case 'Select Basic':
            block = new SelectBasic(item.dataset.id, item.dataset.value, item);
            break;
        case 'Select Multiple':
            block = new SelectMultiple(item.dataset.id, item.dataset.value, item);
            break;
        case 'Multiple Checkboxes':
            block = new MultipleCheckboxes(item.dataset.id, item.dataset.value, item);
            break;
        case 'Multiple Checkboxes Inline':
            block = new MultipleCheckboxesInline(item.dataset.id, item.dataset.value, item);
            break;
        case 'Multiple Radios':
            block = new MultipleRadios(item.dataset.id, item.dataset.value, item);
            break;
        case 'Multiple Radios Inline':
            block = new MultipleRadiosInline(item.dataset.id, item.dataset.value, item);
            break;
    }

    if(block === null){
        console.log('Block not found', item.dataset.type)
    }
    return block
}
let initDropZone = ()=> {

    contentPanes.forEach((contentPane) => {
        let contentItems = new Sortable(contentPane, {
            group: {
                name: 'shared',
                pull: 'clone',
                put: false
            },
            sort: false,
            handle: ".component",
            onEnd: function (/**Event*/evt) {
                evt.to.classList.remove('bg-light-primary')
            },
            onChange: function (/**Event*/evt) {
                evt.to.classList.add('bg-light-primary')
            }
        })
    })


    swappable = new Sortable(formContent, {
        group: {
            name: 'shared',
            pull: true,
            put: true
        },
        revertClone: true,
        handle: ".component",
        animation: 150,
        dataIdAttr: 'data-id',
        onSort: function (/**Event*/evt) {
            // saveLayoutBlockSort(evt, (response) => {
            //     CMSAdmin.createInitCmsMessageBox(response.data.messageStatus, response.data.message);
            // })
            let item = evt.item;
            item.dataset.sort = evt.newIndex

        },
        onAdd: function (/**CustomEvent*/evt, dragEl) {
            let item = evt.item;
            let now = Date.now()

            item.dataset.id = now
            item.id = 'item_' + now

            let toolbar = item.querySelector('.toolbar')
            toolbar.innerHTML = `<div class="d-flex align-items-center">
                                        <button type="button"
                                                data-id="${now}"
                                                class="me-2 btn btn-sm btn-light-danger delete_block"
                                                >
                                            <i class="fa fa-trash soft-background pe-0"></i>
                                        </button>
                                        <button type="button"
                                                data-id="${now}"
                                                class="me-2 btn btn-sm btn-light-primary edit_block"
                                                >
                                            <i class="fa-regular fa-pen-to-square layout-link  pe-0"></i>
                                        </button>
                                    </div>`


            let form = document.createElement('form');
            form.id = 'form_' + now
            item.append(form)
            item.dataset.sort = evt.newIndex
            let block = createFormField(item);
            formFields.set(item.dataset.id, block);

        },
        onRemove: function (/**Event*/evt) {
        }
    });
}

let saveForm = async (event)=> {
    event.preventDefault()
    let items = swappable.toArray();


    let form = event.target

    form.elements.forEach((item) => {
        item.classList.remove('is-invalid')
    })
    let collection = [];
    let formData = new FormData(form);


    items.forEach((item, index) => {
        let formField = formFields.get(item);
        formField.sortOrder = index
        collection.push(formField.toJson())
    })

    formData.append('collection', JSON.stringify(collection));
    let headers = axiosConfig.headers;

    if(objectId) {
        headers = {"X-HTTP-Method-Override": "PUT", ...axiosConfig.headers}
    }
    let scroll = new SmoothScroll();
    try{
        let response = await axios.post(form.action, formData, {headers: headers})
        CMSAdmin.createInitCmsMessageBox('success', response.data.message);

        scroll.animateScroll(document.querySelector('#kt_app_toolbar'), null, {header: '#kt_app_header_wrapper'})
    }catch (error){
        let translator = await CMSAdmin.getTranslator()
        let data = error.response.data

        if(error.response.data.detail) {

            CMSAdmin.createInitCmsMessageBox('danger', translator.trans('Form is invalid', 'validators'));
        }
        data.violations.forEach((item) => {
            let path = item.propertyPath
            let message = item.title

            let field = form.querySelector('#'+form.dataset.uniqId + '_'+path)


            if(!field) {
                return
            }
            field.classList.add('is-invalid')
            field.setAttribute('required', 'required')

            if(field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')){
                field.nextElementSibling.innerHtml = message;
                return
            }
            field.insertAdjacentHTML('afterend', '<div class="invalid-feedback">' + message + '</div>')
        })


        scroll.animateScroll(form, null, {header: '#kt_app_header_wrapper'})
    }
}

let editBlock = (event) => {
    event.preventDefault()
    let el = event.target;

    if (!el.dataset.id) {
        el = el.closest('button');
    }

    let item = document.querySelector('#item_' + el.dataset.id)

    let formFieldContainer = document.querySelector('#form_' + el.dataset.id)
    let block = formFields.get(el.dataset.id)

    el.dataset.bsContent = block.getForm()

    let popover = bootstrap.Popover.getOrCreateInstance(el, {
        html: true,
        sanitize: false,
        title: block.label,
        template: `<div class="popover min-w-600px" role="tooltip">
                            <div class="popover-arrow"></div>
                            <h3 class="popover-header"></h3>
                            <div class="popover-body"></div>
                    </div>`
    })

    popover.show()
    block.appendListener(popover, el)
}

let deleteBlock = (event) => {
    let element = event.target

    if (!element.dataset.id) {
        element = element.closest('button')
    }

    let block = element.closest('.component')

    block.remove()

    formFields.delete(element.dataset.id)

}



KTUtil.onDOMContentLoaded(function () {
    formContent = document.querySelector("#form-contents")
    contentPanes = document.querySelectorAll(".tab-pane ")
    objectId = formContent.dataset.objectId
    postUrl = formContent.dataset.postUrl

    initFormFields();

    KTUtil.on(formContent, '.delete_block', 'click', (event) => {
        deleteBlock(event)
    });

    KTUtil.on(document.body, '#build form', 'submit', (event) => {
        saveForm(event)
    })

    KTUtil.on(formContent, '.edit_block', 'click', (event) => {
        editBlock(event)
    })
    initDropZone();
})