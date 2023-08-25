import FormCompnent from "./form-component";

class FormLegend extends FormCompnent {
    name = 'Legend'
    label = 'Legend'
    type = 'Legend'
    getForm() {
        return`<div data-popover-form="${this.id}">
                  <input type="text" name="form[legends][${this.id}][name]" class="form-control" placeholder="Form Legend" value="${this.value}">
                  <div class="my-3">
                  <button type="button" class="btn btn-sm btn-default" data-popover-dismiss="${this.id}">Close</button>
                  <button type="button" class="btn btn-sm btn-primary" data-popover-save="${this.id}">Save changes</button>
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