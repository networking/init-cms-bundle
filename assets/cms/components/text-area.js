import FormCompnent from "./form-component";

let Translator = await CMSAdmin.getTranslations()

export default class TextArea extends FormCompnent{
    name = 'TextArea'
    label = Translator.trans('fields.text_area', {}, 'formGenerator')
    type = Translator.trans('fields.text_area', {}, 'formGenerator')
    placeholder = Translator.trans('fields.text_area', {}, 'formGenerator')
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
                    <label for="value_${this.id}" class="form-label required">${Translator.trans('fields.label', {}, 'formGenerator')}</label>
                    <input type="text" id="value_${this.id}" name="label" value="${this.value}" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="placeholder_${this.id}" class="form-label">${Translator.trans('fields.placeholder', {}, 'formGenerator')}</label>
                    <input type="text" id="placeholder_${this.id}" name="placholder" value="${this.placeholder}" class="form-control">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="required_${this.id}" name="required" value="required" ${this.required?'checked':''}>
                    <label for="required_${this.id}" class="form-check-label">${Translator.trans('fields.required', {}, 'formGenerator')}?</label>
                 </div>
                  <div class="my-3">
                  <button type="button" class="btn btn-sm btn-light" data-popover-dismiss="${this.id}">${Translator.trans('fields.close', {}, 'formGenerator')}</button>
                  <button type="button" class="btn btn-sm btn-primary" data-popover-save="${this.id}">${Translator.trans('fields.save', {}, 'formGenerator')}</button>
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