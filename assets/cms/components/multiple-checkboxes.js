import {SelectBasic} from './select-basic';

let Translator = await CMSAdmin.getTranslator()
class MultipleCheckboxes extends SelectBasic {
    name = 'MultipleCheckboxes'
    label = Translator.trans('fields.multiple_checkboxes', {}, 'formGenerator')
    type = 'Multiple Checkboxes'
    saveValues(popover) {


        this.value = popover.querySelector(`#value_${this.id}`).value;
        this.options = popover.querySelector(`#options_${this.id}`).value.split("\n");
        this.required = popover.querySelector(`#required_${this.id}`).checked;


        this.element.dataset.value = this.value;
        let text = this.value;

        let fields = this.element.querySelector('.form-group')
        let label = this.element.querySelector('[data-label]')
        if(this.required){
            label.classList.add('required')
        }else{
            label.classList.remove('required')
        }


        fields.innerHTML = ''



        this.options.forEach((option, index) => {

            let block = document.createElement('div')
            block.classList.add('form-check')
            block.classList.add('mb-3')
            let input = document.createElement('input')
            input.classList.add('form-check-input')
            input.type = 'checkbox'
            let label = document.createElement('label')
            label.classList.add('form-check-label')
            label.innerHTML = option

            block.appendChild(input)
            block.appendChild(label)
            fields.appendChild(block)

        })


        label.innerHTML = text;

        this.config = {
            options: this.options,
            required: this.required
        }

        this.element.dataset.config = JSON.stringify(this.config)


    }
}

class MultipleCheckboxesInline extends SelectBasic {

    name = 'MultipleCheckboxesInline'
    label = Translator.trans('fields.multiple_checkboxes_inline', {}, 'formGenerator')
    type = 'Multiple Checkboxes Inline'
    saveValues(popover) {


        this.value = popover.querySelector(`#value_${this.id}`).value;
        this.options = popover.querySelector(`#options_${this.id}`).value.split("\n");
        this.required = popover.querySelector(`#required_${this.id}`).checked;


        this.element.dataset.value = this.value;
        let text = this.value;

        let fields = this.element.querySelector('.form-group')
        let label = this.element.querySelector('[data-label]')
        if(this.required){
            label.classList.add('required')
        }else{
            label.classList.remove('required')
        }


        fields.innerHTML = ''



        this.options.forEach((option, index) => {

            let block = document.createElement('div')
            block.classList.add('form-check')
            block.classList.add('form-check-inline')
            let input = document.createElement('input')
            input.classList.add('form-check-input')
            input.type = 'checkbox'
            let label = document.createElement('label')
            label.classList.add('form-check-label')
            label.innerHTML = option

            block.appendChild(input)
            block.appendChild(label)
            fields.appendChild(block)

        })


        label.innerHTML = text;

        this.config = {
            options: this.options,
            required: this.required
        }

        this.element.dataset.config = JSON.stringify(this.config)


    }

}

class MultipleRadios extends SelectBasic {

    name = 'MultipleRadios'
    label = Translator.trans('fields.radio_button_group', {}, 'formGenerator')
    type = 'Multiple Radios'
    saveValues(popover) {


        this.value = popover.querySelector(`#value_${this.id}`).value;
        this.options = popover.querySelector(`#options_${this.id}`).value.split("\n");
        this.required = popover.querySelector(`#required_${this.id}`).checked;


        this.element.dataset.value = this.value;
        let text = this.value;

        let fields = this.element.querySelector('.form-group')
        let label = this.element.querySelector('[data-label]')
        if(this.required){
            label.classList.add('required')
        }else{
            label.classList.remove('required')
        }


        fields.innerHTML = ''



        this.options.forEach((option, index) => {

            let block = document.createElement('div')
            block.classList.add('form-check')
            block.classList.add('mb-3')
            let input = document.createElement('input')
            input.classList.add('form-check-input')
            input.type = 'radio'
            let label = document.createElement('label')
            label.classList.add('form-check-label')
            label.innerHTML = option

            block.appendChild(input)
            block.appendChild(label)
            fields.appendChild(block)

        })


        label.innerHTML = text;

        this.config = {
            options: this.options,
            required: this.required
        }

        this.element.dataset.config = JSON.stringify(this.config)


    }

}

class MultipleRadiosInline extends SelectBasic {

    name = 'MultipleRadiosInline'
    label = Translator.trans('fields.radio_button_group_inline', {}, 'formGenerator')
    type = 'Multiple Radios Inline'
    saveValues(popover) {


        this.value = popover.querySelector(`#value_${this.id}`).value;
        this.options = popover.querySelector(`#options_${this.id}`).value.split("\n");
        this.required = popover.querySelector(`#required_${this.id}`).checked;


        this.element.dataset.value = this.value;
        let text = this.value;

        let fields = this.element.querySelector('.form-group')
        let label = this.element.querySelector('[data-label]')
        if(this.required){
            label.classList.add('required')
        }else{
            label.classList.remove('required')
        }


        fields.innerHTML = ''



        this.options.forEach((option, index) => {

            let block = document.createElement('div')
            block.classList.add('form-check')
            block.classList.add('form-check-inline')
            let input = document.createElement('input')
            input.classList.add('form-check-input')
            input.type = 'radio'
            let label = document.createElement('label')
            label.classList.add('form-check-label')
            label.innerHTML = option

            block.appendChild(input)
            block.appendChild(label)
            fields.appendChild(block)

        })


        label.innerHTML = text;

        this.config = {
            options: this.options,
            required: this.required
        }

        this.element.dataset.config = JSON.stringify(this.config)


    }

}


export { MultipleCheckboxes, MultipleCheckboxesInline, MultipleRadios, MultipleRadiosInline };