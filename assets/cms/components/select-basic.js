import FormCompnent from "./form-component";
let Translator = await CMSAdmin.getTranslator()
class SelectBasic extends FormCompnent{
    name = 'SelectBasic'
    label = Translator.trans('fields.dropdown_select_menu', {}, 'formGenerator')
    type = 'Select Basic'
    required = false
    options = []
    constructor(id, value, element) {
        super(id, value, element);

        this.options = this.config.options
        this.required = this.config.required
    }
    getForm() {
        return `<div data-popover-form="${this.id}">
                <div class="mb-3">
                    <label for="value_${this.id}" class="form-label required">${Translator.trans('fields.label', {}, 'formGenerator')}</label>
                    <input type="text" id="value_${this.id}" name="label" value="${this.value}" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="options_${this.id}" class="form-label">${Translator.trans('fields.options', {}, 'formGenerator')}</label>
                    <textarea  id='options_${this.id}' class="form-control min-w-500px min-h-200px">${this.options.join("\n")}</textarea>
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
        this.options = popover.querySelector(`#options_${this.id}`).value.split("\n");
        this.required = popover.querySelector(`#required_${this.id}`).checked;


        this.element.dataset.value = this.value;
        let text = this.value;

        let field = this.element.querySelector('[data-options]')
        let label = this.element.querySelector('[data-label]')
        if(this.required){
            field.setAttribute('required', 'required')
            label.classList.add('required')
        }else{
            label.classList.remove('required')
            field.removeAttribute('required')
        }

        field.options.length = 0;
        this.options.forEach((option, index)=>{
            field.options[field.options.length] = new Option(option, index);
        })


        label.innerHTML = text;

        this.config = {
            options: this.options,
            required: this.required
        }

        this.element.dataset.config = JSON.stringify(this.config)


    }
}

class SelectMultiple extends SelectBasic {
    name = 'SelectMultiple'
    label = Translator.trans('fields.multiple_dropdown_select_menu', {}, 'formGenerator')
    type = 'Select Multiple'
}

export {SelectBasic, SelectMultiple}