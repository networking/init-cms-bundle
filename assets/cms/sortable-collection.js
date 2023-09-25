import {CMSAdmin} from './cms-admin';

import Sortable from '../admin-theme/plugins/custom/sortablejs/sortablejs.bundle.js';

class SortableCollection{

    constructor(element) {
        this.element = element
        this.type = element.dataset.sortableCollectionType
        this.draggable = this.element.dataset.cmsSortableDraggable
        this.initialize()

        document.addEventListener('afterAddItem', (event) => {
            this.initialize()
        });

        document.addEventListener('afterRemoveItem', (event) => {
            this.applyPosition()
        });

    }
    addHandles(){
        let items = this.element.querySelectorAll(this.draggable)
        items.forEach((item) => {
            if(item.dataset.hasHandle) {
                return
            }
            item.dataset.hasHandle = 1
            let handle = document.createElement('span')
            handle.setAttribute('class', 'sonata-ba-sortable-handler float-end')
            handle.innerHTML = '<i class="fa fa-bars"></i>'
            item.insertBefore(handle, item.firstChild)
        })
    }
    initialize() {

        let container = this.element.querySelector(this.element.dataset.cmsSortableContainer)

        if(!container) {
            return
        }
        let sortable = new Sortable(container, {
            draggable: this.draggable,
            animation: 150,
            easing: "cubic-bezier(1, 0, 0, 1)",
            onEnd:  (/**Event*/evt) => {
                this.applyPosition();
            },
            onStart:  (/**Event*/evt) => {
                evt.oldIndex;  // element index within parent
            },
            onRemove(evt) {
                this.applyPosition();
            }
        });

        this.addHandles()

        this.applyPosition()

    }
    applyPosition() {
        setTimeout(() => {
            let items = this.element.querySelectorAll(`input.${this.type}`)
            items.forEach((item, index) => {
                item.value = index
            })
        }, 1000)
    }

}

const CMSSortableCollection =  {

    init(){
        document.querySelectorAll('[data-sortable-collection-type]').forEach((element) => {
            if(element.dataset.cmsCollectionType) {
                return
            }

            element.dataset.cmsCollectionType = 1;
            let sortable = new SortableCollection(element)

        })
    }
}

export default CMSSortableCollection;