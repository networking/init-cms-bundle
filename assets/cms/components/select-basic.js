import FormCompnent from "./form-component";

class SelectBasic extends FormCompnent{
    name = 'SelectBasic'
    label = 'Select Basic'
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
                    <label for="value_${this.id}" class="form-label required">Label</label>
                    <input type="text" id="value_${this.id}" name="label" value="${this.value}" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="options_${this.id}" class="form-label">Options</label>
                    <textarea  id='options_${this.id}' class="form-control min-w-500px min-h-200px">${this.options.join("\n")}</textarea>
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
    label = 'Select Multiple'
    type = 'Select Multiple'
}

export {SelectBasic, SelectMultiple}