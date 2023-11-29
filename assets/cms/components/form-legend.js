import FormCompnent from "./form-component";

let Translator = await CMSAdmin.getTranslator()

class FormLegend extends FormCompnent {
    name = 'Legend'
    label = Translator.trans('fields.legend', {}, 'formGenerator')
    type = 'Legend'
    getForm() {
        return`<div data-popover-form="${this.id}">
                  <input type="text" name="form[legends][${this.id}][name]" class="form-control" placeholder="${this.label}" value="${this.value}">
                  <div class="my-3">
                  <button type="button" class="btn btn-sm btn-light" data-popover-dismiss="${this.id}">${Translator.trans('fields.close', {}, 'formGenerator')}</button>
                  <button type="button" class="btn btn-sm btn-primary" data-popover-save="${this.id}">${Translator.trans('fields.save', {}, 'formGenerator')}</button>
                  </div>
            </div>`
    }
    saveValues(popover) {
        this.value = popover.querySelector(`[name="form[legends][${this.id}][name]"]`).value;
        this.element.dataset.value = this.value;
        this.element.querySelector('legend').innerHTML = this.value;
    }
    init(popoverElement) {
    }
    destroy() {
    }

}
export default FormLegend;