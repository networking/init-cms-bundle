import {CMSAdmin} from './cms-admin';

import Sortable from '../admin-theme/plugins/custom/sortablejs/sortablejs.bundle.js';
class OneToManySortable{

    constructor(element) {
        this.element = element
        this.draggable = this.element.dataset.cmsSortableDraggable
        this.initialize()
        document.body.addEventListener('sonata.add_element', (event) => {
            this.initialize()
        })
        document.body.addEventListener('sonata.delete_element', (event) => {
            this.applyPosition()
        })

    }
    addHandles(){
        let items = this.element.querySelectorAll(this.element.dataset.cmsSortableSelector)
        items.forEach((item) => {
            let handle = document.createElement('span')
            handle.setAttribute('class', 'sonata-ba-sortable-handler')
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
            let items = this.element.querySelectorAll(this.element.dataset.cmsSortableSelector)
            items.forEach((item, index) => {
                item.querySelector('input').value = index
            })
        }, 1000)
    }

}

const CMSOneToManySortable =  {

    init(){
        document.querySelectorAll('[data-cms-sortable-one-to-many]').forEach((element) => {
            let sortable = new OneToManySortable(element)

        })
    }
}

export default CMSOneToManySortable;