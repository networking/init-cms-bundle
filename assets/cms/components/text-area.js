import FormCompnent from "./form-component";

export default class TextArea extends FormCompnent{
    name = 'TextArea'
    label = 'Text Area'
    type = 'Text Area'
    placeholder = 'Text Area'
    required = false
    constructor(id, value, element) {
        super(id, value, element);

        let config = JSON.parse(element.dataset.config)

        this.placeholder = config.placeholder
        this.required = config.required
    }
    getForm() {
        return `<div data-popover-form="${this.id}">
                <div class="mb-3">
                    <label for="value_${this.id}" class="form-label required">Label</label>
                    <input type="text" id="value_${this.id}" name="label" value="${this.value}" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="placeholder_${this.id}" class="form-label">Placeholder</label>
                    <input type="text" id="placeholder_${this.id}" name="placholder" value="${this.placeholder}" class="form-control">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="required_${this.id}" name="required" value="required" ${this.required?'checked':''}>
                    <label for="required_${this.id}" class="form-check-label">Required?</label>
                 </div>
                  <div class="my-3">
                  <button type="button" class="btn btn-sm btn-default" data-popover-dismiss="${this.id}">Close</button>
                  <button type="button" class="btn btn-sm btn-primary" data-popover-save="${this.id}">Save changes</button>
                  </div>
            </div>`
    }

    saveValues(popover) {


        this.value = popover.querySelector(`#value_${this.id}`).value;
        this.placeholder = popover.querySelector(`#placeholder_${this.id}`).value;
        this.required = popover.querySelector(`#required_${this.id}`).checked;


        this.element.dataset.value = this.value;
        let text = this.value;

        let placeholderText = this.placeholder;

        let field = this.element.querySelector('[data-placeholder]')
        let label = this.element.querySelector('[data-label]')
        if(this.required){
            field.setAttribute('required', 'required')
            label.classList.add('required')
        }else{
            label.classList.remove('required')
            field.removeAttribute('required')
        }

        field.placeholder = placeholderText


        label.innerHTML = text;

        this.config = {
            placeholder: this.placeholder,
            required: this.required
        }

        this.element.dataset.config = JSON.stringify(this.config)


    }
}