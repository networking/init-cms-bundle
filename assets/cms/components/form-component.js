class FormComponent {
    name = 'FormComponent'
    type = 'FormComponent'
    label = null
    form = null;
    value = 'add text here';
    config = {}
    position = 0;

    constructor(
        id,
        value,
        element
    ) {
        this.id = id;
        this.value = value;
        this.element = element;

        if(element.dataset.config){
            this.config = JSON.parse(element.dataset.config)
        }
    }


    appendListener(popoverElement, el) {
        let popover = document.querySelector(`[data-popover-form="${this.id}"]`)
        let handler = () => {
            el.setAttribute('disabled', 'disabled');
            this.init(popoverElement);
        }
        el.addEventListener('shown.bs.popover', handler)

        el.addEventListener('hide.bs.popover', () => {
            el.removeAttribute('disabled');
            el.removeEventListener('shown.bs.popover', handler)
        })

        KTUtil.on(popover, '[data-popover-dismiss]', 'click', (event) => {
            popoverElement.dispose()
            this.destroy(popoverElement)
            el.removeAttribute('disabled');
            el.removeEventListener('shown.bs.popover', handler)
        })

        KTUtil.on(popover, '[data-popover-save]', 'click', (event) => {
            this.saveValues(popover)
            this.destroy(popoverElement)
            popoverElement.dispose();
            el.removeAttribute('disabled');
            el.removeEventListener('shown.bs.popover', handler)
        })
    }

    saveValues() {
    }

    init(popoverElement) {
    }

    destroy(popoverElement) {
    }

    getForm() {
        return  ``
    }

    toJson() {
        return {
            "id": this.id,
            "name": this.name,
            "type": this.type,
            "label": this.label,
            "value": this.value,
            "config": this.config,
            "position": this.position,
        }
    }
}

export default FormComponent;