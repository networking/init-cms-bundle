import SmoothScroll from "smooth-scroll"
import Sortable from "sortablejs/modular/sortable.complete.esm.js"

import FormLegend from "./components/form-legend"
import InfoText from "./components/info-text"
import {
	MultipleCheckboxes,
	MultipleCheckboxesInline,
	MultipleRadios,
	MultipleRadiosInline,
} from "./components/multiple-checkboxes"
import { SelectBasic, SelectMultiple } from "./components/select-basic"
import TextArea from "./components/text-area"
import TextInput from "./components/text-input"

import "../scss/form_admin.scss"

let formContent = null
let contentPanes = []
const formFields = new Map()
let swappable = null
let postUrl = null
let objectId = null

const initFormFields = () => {
	const currentFields = formContent.querySelectorAll(".component")

	currentFields.forEach((item) => {
		const block = createFormField(item)
		formFields.set(item.dataset.id, block)
	})
}

const createFormField = (item) => {
	let block = null
	switch (item.dataset.type) {
		case "Legend":
			block = new FormLegend(item.dataset.id, item.dataset.value, item)
			break
		case "Infotext":
			block = new InfoText(item.dataset.id, item.dataset.value, item)
			break
		case "Text Input":
			block = new TextInput(item.dataset.id, item.dataset.value, item)
			break
		case "Text Area":
			block = new TextArea(item.dataset.id, item.dataset.value, item)
			break
		case "Select Basic":
			block = new SelectBasic(item.dataset.id, item.dataset.value, item)
			break
		case "Select Multiple":
			block = new SelectMultiple(item.dataset.id, item.dataset.value, item)
			break
		case "Multiple Checkboxes":
			block = new MultipleCheckboxes(item.dataset.id, item.dataset.value, item)
			break
		case "Multiple Checkboxes Inline":
			block = new MultipleCheckboxesInline(
				item.dataset.id,
				item.dataset.value,
				item,
			)
			break
		case "Multiple Radios":
			block = new MultipleRadios(item.dataset.id, item.dataset.value, item)
			break
		case "Multiple Radios Inline":
			block = new MultipleRadiosInline(
				item.dataset.id,
				item.dataset.value,
				item,
			)
			break
	}

	if (block === null) {
		return
	}
	return block
}
const initDropZone = () => {
	contentPanes.forEach((contentPane) => {
		new Sortable(contentPane, {
			group: {
				name: "shared",
				pull: "clone",
				put: false,
			},
			sort: false,
			handle: ".component",
			onEnd: (/**Event*/ evt) => {
				evt.to.classList.remove("bg-light-primary")
			},
			onChange: (/**Event*/ evt) => {
				evt.to.classList.add("bg-light-primary")
			},
		})
	})

	swappable = new Sortable(formContent, {
		group: {
			name: "shared",
			pull: true,
			put: true,
		},
		revertClone: true,
		handle: ".component",
		animation: 150,
		dataIdAttr: "data-id",
		onSort: (/**Event*/ evt) => {
			// saveLayoutBlockSort(evt, (response) => {
			//     CMSAdmin.createInitCmsMessageBox(response.data.messageStatus, response.data.message);
			// })
			const item = evt.item
			item.dataset.sort = evt.newIndex
		},
		onAdd: (/**CustomEvent*/ evt) => {
			const item = evt.item
			const now = Date.now()

			item.dataset.id = now
			item.id = `item_${now}`

			const toolbar = item.querySelector(".toolbar")
			toolbar.innerHTML = `<div class="d-flex align-items-center">
                                        <button type="button"
                                                data-id="${now}"
                                                class="me-2 btn btn-sm btn-light-danger delete_block"
                                                >
                                            <i class="fa fa-trash soft-background pe-0"></i>
                                        </button>
                                        <button type="button"
                                                data-id="${now}"
                                                class="me-2 btn btn-sm btn-light-primary edit_block"
                                                >
                                            <i class="fa-regular fa-pen-to-square layout-link  pe-0"></i>
                                        </button>
                                    </div>`

			const form = document.createElement("form")
			form.id = `form_${now}`
			item.append(form)
			item.dataset.sort = evt.newIndex
			const block = createFormField(item)
			formFields.set(item.dataset.id, block)
		},
		onRemove: () => {},
	})
}

const saveForm = async (event) => {
	event.preventDefault()
	const items = swappable.toArray()

	const form = event.target

	form.elements.forEach((item) => {
		item.classList.remove("is-invalid")
	})
	const collection = []
	const formData = new FormData(form)

	items.forEach((item, index) => {
		const formField = formFields.get(item)
		formField.position = index
		collection.push(formField.toJson())
	})

	formData.append("collection", JSON.stringify(collection))
	let headers = axiosConfig.headers

	if (objectId) {
		headers = { "X-HTTP-Method-Override": "PUT", ...axiosConfig.headers }
	}
	const scroll = new SmoothScroll()
	try {
		const response = await axios.post(form.action, formData, {
			headers: headers,
		})
		CMSAdmin.createInitCmsMessageBox("success", response.data.message)

		document.querySelector("#kt_app_toolbar").scrollIntoView()
	} catch (error) {
		const translator = await CMSAdmin.getTranslator()
		const data = error.response.data

		if (error.response.data.detail) {
			CMSAdmin.createInitCmsMessageBox(
				"danger",
				translator.trans("Form is invalid", "validators"),
			)
		}
		data.violations.forEach((item) => {
			const path = item.propertyPath
			const message = item.title
			let field = form.querySelector(`[name="${path}"]`)

			field.setAttribute("required", "required")

			if (field.nextElementSibling?.classList.contains("select2-container")) {
				field = field.nextElementSibling
			}

			field.classList.add("is-invalid")

			if (field.nextElementSibling?.classList.contains("invalid-feedback")) {
				field.nextElementSibling.innerHtml = message
				return
			}
			field.insertAdjacentHTML(
				"afterend",
				`<div class="invalid-feedback">${message}</div>`,
			)
		})

		scroll.animateScroll(form, null, { header: "#kt_app_header_wrapper" })
	}
}

const editBlock = (event) => {
	event.preventDefault()
	let el = event.target

	if (!el.dataset.id) {
		el = el.closest("button")
	}

	const block = formFields.get(el.dataset.id)

	el.dataset.bsContent = block.getForm()

	const popover = bootstrap.Popover.getOrCreateInstance(el, {
		html: true,
		sanitize: false,
		title: block.label,
		template: `<div class="popover min-w-600px" role="tooltip">
                            <div class="popover-arrow"></div>
                            <h3 class="popover-header"></h3>
                            <div class="popover-body"></div>
                    </div>`,
	})

	popover.show()
	block.appendListener(popover, el)
}

const deleteBlock = (event) => {
	let element = event.target

	if (!element.dataset.id) {
		element = element.closest("button")
	}

	const block = element.closest(".component")

	block.remove()

	formFields.delete(element.dataset.id)
}

KTUtil.onDOMContentLoaded(() => {
	formContent = document.querySelector("#form-contents")
	contentPanes = document.querySelectorAll(".tab-pane ")
	objectId = formContent.dataset.objectId
	postUrl = formContent.dataset.postUrl

	initFormFields()

	KTUtil.on(formContent, ".delete_block", "click", (event) => {
		deleteBlock(event)
	})

	KTUtil.on(document.body, "#build form", "submit", (event) => {
		saveForm(event)
	})

	KTUtil.on(formContent, ".edit_block", "click", (event) => {
		editBlock(event)
	})
	initDropZone()
})
