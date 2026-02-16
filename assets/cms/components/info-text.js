import ClassicEditor from "../../admin-theme/plugins/custom/ckeditor/ckeditor-classic.bundle.js"
import FormCompnent from "./form-component"

const Translator = await CMSAdmin.getTranslator()

class InfoText extends FormCompnent {
	name = "Infotext"
	label = Translator.trans("fields.free_text_area", {}, "formGenerator")
	type = "Infotext"
	getForm() {
		return `<div data-popover-form="${this.id}">
                  <textarea  id='ckeditor_${this.id}' class="min-w-500px min-h-500px">${this.value}</textarea>
                  <div class="my-3">
                    <button type="button" class="btn btn-sm btn-light" data-popover-dismiss="${this.id}">${Translator.trans("fields.close", {}, "formGenerator")}</button>
                    <button type="button" class="btn btn-sm btn-primary" data-popover-save="${this.id}">${Translator.trans("fields.save", {}, "formGenerator")}</button>
                  </div>
            </div>`
	}

	saveValues() {
		this.value = this.editor.getData()
		this.element.dataset.value = this.value
		this.element.querySelector("[data-html]").innerHTML = this.value
	}
	init() {
		ClassicEditor.create(document.querySelector(`#ckeditor_${this.id}`), {
			toolbar: [
				"heading",
				"|",
				"bold",
				"italic",
				"link",
				"bulletedList",
				"numberedList",
				"undo",
				"redo",
			],
			licenseKey: "GPL",
		})
			.then((editor) => {
				this.editor = editor
			})
			.catch((error) => {
				console.error(error)
			})
	}
}
export default InfoText
