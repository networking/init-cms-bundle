import Sortable from '../admin-theme/plugins/custom/sortablejs/sortablejs.bundle.js';


let containers = null;
let dropzones = null;
let contentTypeList = null;
let addBlockUrl = CMSRouting.generate('admin_networking_initcms_layoutblock_addBlock');
let sortUrl = CMSRouting.generate('admin_networking_initcms_layoutblock_updateLayoutBlockSort');
let deleteUrl = CMSRouting.generate('admin_networking_initcms_layoutblock_deleteAjax');
let lastTranslationSettingsHtml = null;
let pageId = null;
let erroredContainers = new Set();
let Translator = await CMSAdmin.getTranslator();

function initDropZone() {


    containers = document.querySelectorAll(".draggable-zone");
    dropzones = document.querySelectorAll(".dropzone");
    contentTypeList = document.querySelector("#content_item_list");
    pageId = document.querySelector("meta[name='init-cms-page-id']").getAttribute("content");

    if (containers.length === 0) {
        return false;
    }

    let contentItems = new Sortable(contentTypeList, {
        group: {
            name: 'shared',
            pull: 'clone',
            put: false,
            revertClone: true,

        },sort: false,
        onEnd: function (/**Event*/evt) {
            hideContainerErrors()
        },
    })

    containers.forEach(function (container) {
        let swappable = new Sortable(container, {
            group: {name: 'shared', pull: true, put: acceptLayoutBlock},
            animation: 150,
            onEnd: function (/**Event*/evt) {

                hideContainerErrors()

                if(evt.newIndex === evt.oldIndex && evt.to.id === evt.from.id){
                    return
                }

                saveLayoutBlockSort(evt, (response) => {
                    CMSAdmin.createInitCmsMessageBox(response.data.messageStatus, response.data.message);
                })
            },
            onAdd: function (/**CustomEvent*/evt, dragEl) {

                let count = evt.to.querySelectorAll('.draggable').length;
                let item = evt.item;

                if (!item.id) {

                    let dropzone = container.parentElement
                    createItem(item.dataset.contentType, dropzone.dataset.pageId, dropzone.dataset.zone, evt.newIndex)
                        .then((response) => {

                            item.outerHTML = response.data.html
                            saveLayoutBlockSort(evt)
                            document.querySelector('#layoutBlock_' + response.data.layoutBlockId + '  .create_block').click()


                        })
                        .catch((error) => {
                            if(!error.response){
                                console.error(error)
                                return
                            }

                            let message = error.response.data.detail

                            CMSAdmin.createInitCmsMessageBox('error', message);
                        })
                }

                if (count) {
                    evt.to.querySelector('.empty_layout_block').classList.add("d-none")
                    return
                }

                evt.to.querySelector('.empty_layout_block').classList.remove("d-none")


            },
            onRemove: function (/**Event*/evt) {
                let count = evt.from.querySelectorAll('.draggable').length;
                if (count) {
                    evt.from.querySelector('.empty_layout_block').classList.add("d-none")
                    return
                }

                evt.from.querySelector('.empty_layout_block').classList.remove("d-none")

            }
        });

        document.addEventListener('fade-out-blocks', function (e) {
            swappable.option('disabled', true);
        })

        document.addEventListener('fade-in-blocks', function (e) {
            swappable.option('disabled', false);
        })
    })
}



let showContainerError = (container) => {
    let dropzone = container.closest('.dropzone')
    dropzone.classList.add('bg-light-danger')
    dropzone.closest('.dropzone').classList.add('border-danger')
    erroredContainers.add(dropzone)
}

let hideContainerErrors = () => {
    erroredContainers.forEach((item) => {
        item.classList.remove('bg-light-danger')
        item.classList.remove('border-danger')
    })
}

let acceptLayoutBlock = (to, from, dragEl) => {
    let container = to.el
    let contentBlock = dragEl
    let contentTypes = JSON.parse(container.dataset.contentTypes);
    if (contentTypes.length > 0) {
        if (!contentTypes.includes(contentBlock.dataset.contentType)) {
            showContainerError(container)
            return false

        }
    }

    let maxItems = container.dataset.maxItems;

    let count = container.querySelectorAll('.draggable').length;

    if (maxItems > 0) {
        if (count >= maxItems) {
            showContainerError(container)
            return false
        }
    }
    hideContainerErrors()
    return true
}

let saveLayoutBlockSort = (event, callback) => {
    // CMSAdmin.createInitCmsMessageBox(xhr.messageStatus, xhr.message);

    let zones = [];
    let pageId = null;
    let adminCode = null;

    dropzones.forEach(function (dropzone) {
        let layoutBlocks = dropzone.querySelectorAll(".draggable");
        let addButtons = dropzone.querySelectorAll(".add-layout");
        let zone = dropzone.dataset.zone

        if (!pageId) {
            pageId = dropzone.dataset.pageId
        }

        if (!adminCode) {
            adminCode = dropzone.dataset.adminCode
        }


        let layoutBlockIds = [];

        layoutBlocks.forEach(function (layoutBlock, index) {
            layoutBlock.dataset.sortOrder = index;
            layoutBlockIds.push(layoutBlock.id);
        })
        zones.push({
            zone: zone,
            layoutBlocks: layoutBlockIds
        })
    })

    submitLayoutSort(zones, pageId, adminCode).then(function (response) {

        let event = new CustomEvent('page-updated')
        document.body.dispatchEvent(event)
        if (callback !== undefined) {
            callback(response)
        }
    })

};

let createItem = async (contentType, pageId, zone, sortOrder) => {

    return await axios.get(addBlockUrl, {
        params: {
            subclass: contentType,
            zone: zone,
            pageId: pageId,
            sortOrder: sortOrder
        }
    }, axiosConfig)
}

let submitLayoutSort = async (zones, pageId, adminCode) => {
    return await axios.post(sortUrl, {
        zones: zones,
        pageId: pageId,
        code: adminCode,
    }, axiosConfig)
}

let fadeOutContentBlocks = (except) => {
    let contentBlocks = document.querySelectorAll('.content_type_item')
    contentBlocks.forEach((item) => {
        if (item === except) {


            return
        }
        item.classList.add('opacity-5')
    })
    document.dispatchEvent(new CustomEvent('fade-out-blocks'))
}

let fadeInContentBlocks = () => {
    let contentBlocks = document.querySelectorAll('.content_type_item')
    contentBlocks.forEach((item) => {
        item.classList.remove('opacity-5')
    })
    document.dispatchEvent(new CustomEvent('fade-in-blocks'))
}


let editBlock = (e) => {
    e.preventDefault();
    let el = e.target;

    if (el.classList.contains('fa-pen-to-square')) {
        el = el.parentElement
    }

    let id = el.dataset.value


    let layoutBlock = document.getElementById('layoutBlock_' + id)

    fadeOutContentBlocks(layoutBlock)




    layoutBlock.querySelector('.edit_block').setAttribute('disabled', true)
    layoutBlock.querySelector('.delete_block').setAttribute('disabled', true)

    let editUrl = CMSRouting.generate('admin_networking_initcms_layoutblock_edit', {id: id})
    let displayBlock = document.getElementById('layoutBlockHtml' + id)
    let editBlock = document.getElementById('editBlockHtml' + id)

    axios.get(editUrl, axiosConfig).then((response) => {
        editBlock.innerHTML = response.data.html
        displayBlock.classList.add('d-none')
        editBlock.classList.remove('d-none')
        document.body.dispatchEvent(new CustomEvent('fields:added'))

        let scroll = new SmoothScroll();
        scroll.animateScroll(layoutBlock, null, { speed: 1000, easing: 'easeOutCubic' });
    }).catch((error) => {
        let message = error.response.data.detail
        CMSAdmin.createInitCmsMessageBox('error', message);
    })
}

let createBlock = (e) => {
    e.preventDefault();
    let el = e.target;

    if (el.classList.contains('fa-pen-to-square')) {
        el = el.parentElement
    }

    let id = el.dataset.value


    let layoutBlock = document.getElementById('layoutBlock_' + id)

    fadeOutContentBlocks(layoutBlock)
    layoutBlock.querySelector('.create_block').setAttribute('disabled', true)

    let createUrl = CMSRouting.generate('admin_networking_initcms_layoutblock_create',{
        subclass: layoutBlock.dataset.contentType,
        zone: layoutBlock.dataset.zone,
        pageId: layoutBlock.dataset.pageId,
        sortOrder: layoutBlock.dataset.sortOrder
    })

    let displayBlock = document.getElementById('layoutBlockHtml' + id)
    let editBlock = document.getElementById('editBlockHtml' + id)

    axios.get(createUrl, axiosConfig).then((response) => {
        editBlock.innerHTML = response.data.html
        displayBlock.classList.add('d-none')
        editBlock.classList.remove('d-none')
        document.body.dispatchEvent(new CustomEvent('fields:added'))
        let scroll = new SmoothScroll();
        scroll.animateScroll(layoutBlock, null, { speed: 1000, easing: 'easeOutCubic' });
    }).catch((error) => {
        if(!error.response){
            console.error(error)
            return
        }

        let message = error.response.data.detail
        CMSAdmin.createInitCmsMessageBox('error', message);
    })
}

let cancelEditBlock = (e) => {
    let id = e.target.dataset.value
    let displayBlock = document.getElementById('layoutBlockHtml' + id)
    let editBlock = document.getElementById('editBlockHtml' + id)
    let layoutBlock = document.getElementById('layoutBlock_' + id)
    layoutBlock.querySelector('.edit_block').removeAttribute('disabled')
    layoutBlock.querySelector('.delete_block').removeAttribute('disabled')
    editBlock.classList.add('d-none')
    editBlock.innerHTML = ''
    displayBlock.classList.remove('d-none')
    fadeInContentBlocks()

}

let cancelCreateBlock = (e) => {
    e.preventDefault();
    let form = e.target.closest('form')
    let div = form.closest('[data-zone]')
    let id = div.getAttribute('id').replace('layoutBlock_', '')
    let displayBlock = document.getElementById('layoutBlockHtml' + id)
    let editBlock = document.getElementById('editBlockHtml' + id)
    let layoutBlock = document.getElementById('layoutBlock_' + id)
    layoutBlock.querySelector('.create_block').removeAttribute('disabled')
    editBlock.classList.add('d-none')
    editBlock.innerHTML = ''
    displayBlock.classList.remove('d-none')
    fadeInContentBlocks()
}

let saveLayoutBlock = (e) => {
    e.preventDefault();
    let form = e.target.closest('form')
    let config = {
        url: form.action,
        method: form.method,
        data: new FormData(form),
        ...axiosConfig
    }

    if (form.enctype === 'multipart/form-data') {
        config.headers['Content-Type'] = 'multipart/form-data'
    }
    axios.request(config).then((response) => {
        if (response.status === 200) {
            let id = response.data.id
            let displayBlock = document.getElementById('layoutBlockHtml' + id)
            let editBlock = document.getElementById('editBlockHtml' + id)
            let layoutBlock = document.getElementById('layoutBlock_' + id)
            layoutBlock.querySelector('.edit_block').removeAttribute('disabled')
            layoutBlock.querySelector('.delete_block').removeAttribute('disabled')
            editBlock.classList.add('d-none')
            editBlock.innerHTML = ''
            displayBlock.classList.remove('d-none')
            displayBlock.innerHTML = response.data.html
            CMSAdmin.createInitCmsMessageBox(response.data.status, response.data.message);
            let event = new CustomEvent('page-updated')
            document.body.dispatchEvent(event)
            fadeInContentBlocks()
        }
    }).catch((error) => {
        if(!error.response){
            console.error(error)
            return
        }

        let id = error.response.data.id
        let editBlock = document.getElementById('editBlockHtml' + id)
        editBlock.innerHTML = error.response.data.html
        CMSAdmin.createInitCmsMessageBox('error', error.response.data.message);
        document.body.dispatchEvent(new CustomEvent('fields:added'))
    })

}

let createLayoutBlock = (e) => {
    e.preventDefault();
    let form = e.target.closest('form')
    let config = {
        url: form.action,
        method: form.method,
        data: new FormData(form),
        ...axiosConfig
    }

    if (form.enctype === 'multipart/form-data') {
        config.headers['Content-Type'] = 'multipart/form-data'
    }

    let div = form.closest('[data-zone]')

    let id = div.getAttribute('id').replace('layoutBlock_', '')
    axios.request(config).then((response) => {
        if (response.status === 200) {
            let template = document.createElement('template');
            template.innerHTML = response.data.html.trim();;
            div.replaceWith(template.content.firstChild)
            CMSAdmin.createInitCmsMessageBox(response.data.status, response.data.message);
            let event = new CustomEvent('page-updated')
            document.body.dispatchEvent(event)
            fadeInContentBlocks()
        }
    }).catch((error) => {
        if(!error.response){
            console.error(error)
            return
        }

        let editBlock = document.getElementById('editBlockHtml' + id)
        editBlock.innerHTML = error.response.data.html
        CMSAdmin.createInitCmsMessageBox('error', error.response.data.message);
        document.body.dispatchEvent(new CustomEvent('fields:added'))
    })

}

let toggleActive = (e) => {
    e.preventDefault();
    let el = e.target;
    if (el.classList.contains('ki-outline')) {
        el = el.parentElement
    }
    let icon = el.querySelector('i')
    let id = el.dataset.value
    let displayBlock = document.getElementById('layoutBlockHtml' + id)
    let url = CMSRouting.generate('admin_networking_initcms_layoutblock_toggleActive')

    axios.post(url, {id: id}, axiosConfig).then((response) => {


        CMSAdmin.createInitCmsMessageBox('success', response.data.message);
        if (response.data.active) {
            icon.classList.remove('ki-minus-circle')
            icon.classList.add('ki-check-circle')
            el.classList.remove('btn-light-danger')
            el.classList.add('btn-light-success')
            displayBlock.classList.remove('opacity-25')
            return
        }
        icon.classList.add('ki-minus-circle')
        icon.classList.remove('ki-check-circle')
        el.classList.add('btn-light-danger')
        el.classList.remove('btn-light-success')
        displayBlock.classList.add('opacity-25')
        let event = new CustomEvent('page-updated')
        document.body.dispatchEvent(event)
    }).catch((error) => {
        CMSAdmin.createInitCmsMessageBox('error', error.response.data.message);
    })

}


let deleteBlock = (e) => {
    e.preventDefault();
    let el = e.target;

    if (el.classList.contains('fa-trash')) {
        el = el.parentElement
    }

    let container = el.closest('.draggable-zone')


    Swal.fire({
        html: Translator.trans('page_admin.confirm', [], 'PageAdmin'),
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: Translator.trans('button.confirm_delete', [], 'PageAdmin'),
        cancelButtonText: Translator.trans('button.cancel', [], 'PageAdmin'),
        customClass: {
            confirmButton: "btn btn-sm btn-danger",
            cancelButton: 'btn btn-sm btn-light'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(deleteUrl, {
                id: el.dataset.value,
                _method: 'DELETE'
            }).then((response) => {
                fadeInContentBlocks()
                document.querySelector('#layoutBlock_' + el.dataset.value).remove()
                CMSAdmin.createInitCmsMessageBox(response.data.messageStatus, response.data.message);
                saveLayoutBlockSort();
                if (container.querySelectorAll('.draggable').length) {
                    container.querySelector('.empty_layout_block').classList.add("d-none")
                    return
                }
                container.querySelector('.empty_layout_block').classList.remove("d-none")
            }).catch((err) => {

                CMSAdmin.createInitCmsMessageBox('error', 'Something went wrong');
            })
        }
    })
}

let unlinkTranslation = (e) => {
    e.preventDefault();
    let el = e.target;

    let text = el.dataset.text
    let id = el.dataset.objectId

    Swal.fire({
        html: text,
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: Translator.trans('button.confirm_delete', [], 'PageAdmin'),
        cancelButtonText: Translator.trans('button.cancel', [], 'PageAdmin'),
        customClass: {
            confirmButton: "btn btn-sm btn-danger",
            cancelButton: 'btn btn-sm btn-light'
        }
    }).then((result) => {
        if (result.isConfirmed) {

            let headers = {"X-HTTP-Method-Override": "DELETE", ...axiosConfig.headers}

            axios.post(el.href, {}, {
                headers: headers
            }).then((response) => {
                document.querySelector('#translations').innerHTML = response.data.html
                CMSAdmin.createInitCmsMessageBox('success', response.data.message);
            }).catch((err) => {
                CMSAdmin.createInitCmsMessageBox('error', 'Something went wrong');
            })
        }
    })
}

let linkTranslation = (e) => {
    e.preventDefault()

    let el = e.target;

    axios.get(el.href, axiosConfig).then((response) => {
        lastTranslationSettingsHtml = document.querySelector('#translations').innerHTML
        document.querySelector('#translations').innerHTML = response.data.html
    }).catch((err) => {
        CMSAdmin.createInitCmsMessageBox('error', 'Something went wrong');
    })
}

let submitTranslationLink = (e) => {
    e.preventDefault()

    let form = e.target.closest('form')

    if (e.submitter.classList.contains('btn-cancel') || e.submitter.classList.contains('cancel')) {
        document.querySelector('#translations').innerHTML = lastTranslationSettingsHtml
        return;
    }

    axios.post(form.action, new FormData(form), axiosConfig).then((response) => {
        document.querySelector('#translations').innerHTML = response.data.html
        CMSAdmin.createInitCmsMessageBox('success', response.data.message);
    }, axiosConfig).catch((err) => {

        if (err.response.data.message) {
            return CMSAdmin.createInitCmsMessageBox('error', err.response.data.message);
        }

        CMSAdmin.createInitCmsMessageBox('error', err.response.data.detail);
    })
}

let submitPageSettings = (e) => {
    e.preventDefault()
    let form = e.target.closest('form')

    form.elements.forEach((item) => {
        item.classList.remove('is-invalid')
    })

    axios.post(form.action, new FormData(form), axiosConfig).then((response) => {
        document.querySelector('#pageStatusSettings').innerHTML = response.data.pageStatusSettings

        if(response.data.layoutBlockSettingsHtml){
            document.querySelector('#page_content').innerHTML = response.data.layoutBlockSettingsHtml
            initDropZone()
        }

        CMSAdmin.createInitCmsMessageBox('success', response.data.message);
    }).catch((err) => {
        let data = err.response.data

        data.violations.forEach((item) => {
            let path = item.propertyPath
            let message = item.title
            let field = form.querySelector('[name="' + path + '"]')
            field.classList.add('is-invalid')
            field.setAttribute('required', 'required')

            if (field.nextElementSibling && field.nextElementSibling.classList.contains('invalid-feedback')) {
                field.nextElementSibling.innerHtml = message;
                return
            }
            field.insertAdjacentHTML('afterend', '<div class="invalid-feedback">' + message + '</div>')
        })
    })
}

let updatePageStatus = () => {
    axios.get(CMSRouting.generate('admin_networking_initcms_page_getPageStatus', {id: pageId}), axiosConfig)
        .then((response) => {
            document.querySelector('#pageStatusSettings').innerHTML = response.data.pageStatusSettings
        }).catch((err) => {
        if (err.response.data.message) {
            return CMSAdmin.createInitCmsMessageBox('error', err.response.data.message);
        }

        CMSAdmin.createInitCmsMessageBox('error', err.response.data.detail);
    })
}

let statusDialog = (e) => {
    e.preventDefault()

    let el = e.target;

    if (!el.href) {
        el = el.closest('a')
    }
    let text = el.dataset.text
    Swal.fire({
        html: text,
        icon: "warning",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: "Ok, got it!",
        cancelButtonText: 'Nope, cancel it',
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: 'btn btn-primary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(el.href, axiosConfig).then((response) => {
                if (response.data.redirect) {
                    return window.location = response.data.redirect
                }
            }).catch((err) => {
                if (err.response.data.message) {
                    return CMSAdmin.createInitCmsMessageBox('error', err.response.data.message);
                }

                CMSAdmin.createInitCmsMessageBox('error', err.response.data.detail);
            })

        }
    })

}


KTUtil.onDOMContentLoaded(function () {

    KTUtil.on(document.body, '.delete_block', 'click', (e) => {
        deleteBlock(e)
    })

    KTUtil.on(document.body, '.edit_block', 'click', (e) => {
        editBlock(e)
    })

    KTUtil.on(document.body, '.create_block', 'click', (e) => {
        createBlock(e)
    })

    KTUtil.on(document.body, '.toggle-active', 'click', (e) => {
        toggleActive(e)
    })

    KTUtil.on(document.body, '[data-dismiss="edit"]', 'click', (e) => {
        cancelEditBlock(e)
    })


    KTUtil.on(document.body, '[data-dismiss="create"]', 'click', (e) => {
        cancelCreateBlock(e)
    })

    KTUtil.on(document.body, '[data-save="edit"]', 'click', (e) => {
        saveLayoutBlock(e)
    })

    KTUtil.on(document.body, '[data-save="create"]', 'click', (e) => {
        createLayoutBlock(e)
    })

    KTUtil.on(document.body, '.translation-dialog-unlink', 'click', (e) => {
        unlinkTranslation(e)
    });

    KTUtil.on(document.body, '.translation-dialog-link', 'click', (e) => {
        linkTranslation(e)
    });

    KTUtil.on(document.body, '#translation-link-form', 'submit', (e) => {
        submitTranslationLink(e)
    });

    KTUtil.on(document.body, '#translate-copy-page-form', 'submit', (e) => {
        submitTranslationLink(e)
    });

    KTUtil.on(document.body, '#page-settings-form', 'submit', (e) => {
        submitPageSettings(e)
    });

    KTUtil.on(document.body, '#page-metadata-form', 'submit', (e) => {
        submitPageSettings(e)
    });

    KTUtil.on(document.body, '.status-dialog-link', 'click', (e) => {
        statusDialog(e)
    });

    document.body.addEventListener('page-updated', (e) => {
        updatePageStatus()
    })


    initDropZone();
})