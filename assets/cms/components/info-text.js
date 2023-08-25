import ClassicEditor from '../../admin-theme/plugins/custom/ckeditor/ckeditor-classic.bundle.js';
import FormCompnent from "./form-component";

class InfoText extends FormCompnent{
    name = 'Infotext'
    label = 'Infotext'
    type = 'Infotext'
    getForm() {
       return `<div data-popover-form="${this.id}">
                  <textarea  id='ckeditor_${this.id}' class="min-w-500px min-h-500px">${this.value}</textarea>
                  <div class="my-3">
                  <button type="button" class="btn btn-sm btn-default" data-popover-dismiss="${this.id}">Close</button>
                  <button type="button" class="btn btn-sm btn-primary" data-popover-save="${this.id}">Save changes</button>
                  </div>
            </div>`
    }

    saveValues(popover) {
        this.value = this.editor.getData();
        this.element.dataset.value = this.value;
        this.element.querySelector('[data-html]').innerHTML = this.value;
    }
    init(popoverElement) {
        ClassicEditor
            .create(document.querySelector('#ckeditor_'+ this.id ))
            .then(editor => {
                this.editor = editor
            })
            .catch(error => {
                console.error(error);
            });
    }
}
export default InfoText;