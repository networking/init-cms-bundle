window.axiosConfig = { headers: { "X-Requested-With": "XMLHttpRequest" } }

import CMSGalleryEntity from "./gallery-entity"
import CMSMediaEntity from "./media-entity"
import CMSModelList from "./model-list"
import CMSOneToManySortable from "./one-to-many-sortable"
import CMSSortableCollection from "./sortable-collection"
import { CMSTranslator } from "./translator"
import "select2"

$.fn.select2.defaults.set("theme", "bootstrap5")
$.fn.select2.defaults.set("width", "100%")
$.fn.select2.defaults.set("selectionCssClass", ":all:")
// Class definition
const CMSAdmin = {
	// Define shared variables
	listDialog: null,
	routing: null,
	translations: null,
	collectionCounters: [],
	debug: false,

	async getTranslator() {
		if (!this.translations) {
			this.translations = CMSTranslator.load()
		}

		return this.translations
	},

	async init(debug) {
		this.debug = debug
		this.translations = await this.getTranslator()
		CMSMediaEntity.init()
		CMSGalleryEntity.init()
		CMSModelList.init()
		CMSOneToManySortable.init()
		this.initLinkDialogs()
		this.initializeDatePickers()
		this.initializeDateTimePickers()
		this.initSelect2()
		this.initCkeditor()
		this.initCollectionType()
		this.initTabs()
	},

	initTabs(el) {
		el ??= document.body

		const elements = [].slice.call(
			document.querySelectorAll('[data-kt-tabs="true"]'),
		)

		elements.forEach((element) => {
			if (element.getAttribute("data-kt-initialized") === "1") {
				return
			}

			const triggerEl = el.querySelector(
				`a[href="${element.dataset.activeTab}"]`,
			)
			const tab = new bootstrap.Tab(triggerEl) // Select tab by name
			const tabFieldId = element.dataset.tabField
			tab.show()
			const tabEls = document.querySelectorAll('a[data-bs-toggle="tab"]')
			const tabInputField = document.querySelector(tabFieldId)
			tabEls.forEach((el) => {
				el.addEventListener("shown.bs.tab", (event) => {
					tabInputField.value = event.target.dataset.tabIndex
				})
			})
		})
	},

	initToolTips(el) {
		el ??= document.body

		const tooltipTriggerList = [].slice.call(
			el.querySelectorAll('[data-bs-toggle="tooltip"]'),
		)
		tooltipTriggerList.map(
			(tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl),
		)
	},
	initLinkDialogs() {
		KTUtil.on(document.body, ".dialog-link", "click", (event) => {
			this.createLinkDialog(event)
		})
	},

	createLinkDialog(event) {
		event.preventDefault()
		event.stopPropagation()
		this.initializeDialog()

		let link = event.target

		if (!link.getAttribute("href")) {
			link = link.closest("a")
		}

		fetch(link.getAttribute("href"), {
			method: "GET",
			headers: {
				"X-Requested-With": "XMLHttpRequest",
			},
		})
			.then((response) => {
				return response.text()
			})
			.then((html) => {
				document
					.querySelector("#list_dialog")
					.querySelector(".modal-content").innerHTML = html
				this.listDialog.show()
			})
	},

	initializeDialog() {
		if (!this.listDialog) {
			this.listDialog = new bootstrap.Modal(
				document.querySelector("#list_dialog", {
					height: "auto",
					width: 650,
					show: false,
				}),
			)
		}
	},

	initializeDatePickers() {
		document
			.querySelectorAll('[data-provider="datepicker"]')
			.forEach((element) => {
				const dateFormat = element.dataset.dateFormat
				const locale = element.dataset.dateLanguage
				flatpickr(element, {
					dateFormat: dateFormat,
					locale: locale,
					allowInput: true,
				})
			})
	},

	initializeDateTimePickers() {
		document
			.querySelectorAll('[data-provider="datetimepicker"]')
			.forEach((element) => {
				const dateFormat = element.dataset.dateFormat
				const locale = element.dataset.dateLanguage
				flatpickr(element, {
					dateFormat: dateFormat,
					locale: locale,
					enableTime: true,
					allowInput: true,
					time_24hr: true,
				})
			})
	},
	initSelect2() {
		const elements = [].slice.call(
			document.querySelectorAll(
				'[data-control="select2"], [data-kt-select2="true"]',
			),
		)

		elements.forEach((element) => {
			if (element.getAttribute("data-kt-initialized") === "1") {
				return
			}
			const select = $(`#${element.id}`)
			const parent = select.parent()
			select
				.select2({
					dropdownParent: parent,
				})
				.on("select2:select", (e) => {
					const event = new Event("change", { bubbles: true })
					e.target.dispatchEvent(event)
				})

			element.setAttribute("data-kt-initialized", "1")
		})
	},
	initCkeditor() {
		const elements = [].slice.call(
			document.querySelectorAll(
				'[data-control="ckeditor"], [data-kt-ckeditor="true"]',
			),
		)

		elements.forEach((element) => {
			if (element.getAttribute("data-kt-initialized") === "1") {
				return
			}
			const id = element.getAttribute("id")

			const config = JSON.parse(element.dataset.config)
			const plugins = JSON.parse(element.dataset.plugins)
			const templates = JSON.parse(element.dataset.templates)
			const styles = JSON.parse(element.dataset.styles)

			JSON.parse(element.dataset.filebrowser)
			if (CKEDITOR.instances[id]) {
				CKEDITOR.instances[id].destroy(true)
				delete CKEDITOR.instances[id]
			}

			for (const [key, value] of Object.entries(plugins)) {
				CKEDITOR.plugins.addExternal(key, value.path, value.filename)
			}

			for (const [key, value] of Object.entries(styles)) {
				if (CKEDITOR.stylesSet.get(key) === null) {
					CKEDITOR.stylesSet.add(key, value)
				}
			}

			for (const [key, value] of Object.entries(templates)) {
				CKEDITOR.addTemplates(key, value)
			}

			const params = {}

			for (const [key, value] of Object.entries(config)) {
				if (key === "filebrowserBrowseRoute") {
					params.filebrowserBrowseUrl = CMSRouting.generate(
						value,
						config.filebrowserBrowseRouteParameters,
					)
					continue
				}

				if (key === "filebrowserImageBrowseRoute") {
					params.filebrowserImageBrowseUrl = CMSRouting.generate(
						value,
						config.filebrowserImageBrowseRouteParameters,
					)
					continue
				}

				if (key === "filebrowserUploadRoute") {
					params.filebrowserUploadUrl = CMSRouting.generate(
						value,
						config.filebrowserUploadRouteParameters,
					)
					continue
				}

				if (key === "filebrowserImageUploadRoute") {
					params.filebrowserImageUploadUrl = CMSRouting.generate(
						value,
						config.filebrowserImageUploadRouteParameters,
					)
					continue
				}

				if (
					key === "filebrowserBrowseRouteParameters" ||
					key === "filebrowserImageBrowseRouteParameters" ||
					key === "filebrowserUploadRouteParameters" ||
					key === "filebrowserImageUploadRouteParameters"
				) {
					continue
				}

				params[key] = value
			}
			CKEDITOR.disableAutoInline = true
			const editor = CKEDITOR.replace(id, params)

			editor.on("change", (evt) => {
				element.value = evt.editor.getData()
			})

			element.setAttribute("data-kt-initialized", "1")
		})
	},
	initCollectionType() {
		document.querySelectorAll("[data-collection-list]").forEach((element) => {
			this.setupCollectionType(element)
		})
	},
	setupCollectionType(subject) {
		if (subject.dataset.cmsCollectionType) {
			return
		}

		CMSSortableCollection.init()

		KTUtil.on(subject, "[data-collection-add-btn]", "click", (event) => {
			event.preventDefault()
			let btn = event.target
			if (!btn.classList.contains("btn")) {
				btn = btn.closest(".btn")
			}

			let counter = 0
			const containerName = btn.dataset.collectionAddBtn
			const container = document.querySelector(`#${containerName}`)
			const lastItem = [
				...document.querySelectorAll(`div[id^="${container.id}_"]`),
			].pop()
			if (lastItem) {
				counter = parseInt(lastItem.id.replace(`${container.id}_`, ""))
				counter += 1
			}

			let proto = container.dataset.prototype
			const protoName = container.dataset.prototypeName || "__name__"
			// Set field id
			const idRegexp = new RegExp(`${container.id}_${protoName}`, "g")
			proto = proto.replace(idRegexp, `${container.id}_${counter}`)

			// Set field name
			const parts = container.id.split("_")
			const nameRegexp = new RegExp(
				`${parts[parts.length - 1]}\\]\\[${protoName}`,
				"g",
			)
			proto = proto.replace(
				nameRegexp,
				`${parts[parts.length - 1]}][${counter}`,
			)

			container.insertAdjacentHTML("beforeend", proto)
			btn.dispatchEvent(
				new CustomEvent("afterAddItem", {
					bubbles: true,
					detail: { proto: proto, counter: counter },
				}),
			)
			CMSAdmin.initSpecialFields()
		})

		KTUtil.on(subject, "[data-collection-remove-btn]", "click", (event) => {
			event.preventDefault()

			let btn = event.target

			if (!btn.classList.contains("btn")) {
				btn = btn.closest(".btn")
			}
			btn.closest(".collection-item").remove()
			btn.dispatchEvent(new CustomEvent("afterRemoveItem", { bubbles: true }))
		})

		subject.dataset.cmsCollectionType = 1
	},
	initSpecialFields() {
		CMSMediaEntity.init()
		CMSGalleryEntity.init()
		CMSModelList.init()
		this.initializeDatePickers()
		this.initializeDateTimePickers()
		this.createSelect2()
		this.initCkeditor()
		this.initCollectionType()
	},
	log(...args) {
		if (!this.debug) {
			return
		}

		const msg = `[Init CMS] ${Array.prototype.join.call(args, ", ")}`

		if (window.console?.log) {
			window.console.log(msg)
		} else if (window.opera?.postError) {
			window.opera.postError(msg)
		}
	},
	error(...args) {
		if (!this.debug) {
			return
		}

		const msg = `[Init CMS] ${Array.prototype.join.call(args, ", ")}`
		if (window.console?.error) {
			window.console.error(msg)
		} else if (window.opera?.postError) {
			window.opera.postError(msg)
		}
	},
	createInitCmsMessageBox(
		status,
		message,
		timeout = 2000,
		positionClass = "toastr-top-right",
	) {
		toastr.options = {
			closeButton: true,
			debug: false,
			newestOnTop: true,
			progressBar: false,
			positionClass: positionClass,
			preventDuplicates: true,
			onclick: null,
			showDuration: "2000",
			hideDuration: "1000",
			timeOut: timeout,
			extendedTimeOut: "1000",
			showEasing: "linear",
			hideEasing: "linear",
			showMethod: "fadeIn",
			hideMethod: "fadeOut",
		}
		if (status === "success") {
			toastr.success(message)
		} else if (status === "warning") {
			toastr.warning(message)
		} else if (status === "error" || status === "danger") {
			toastr.error(message)
		} else {
			toastr.info(message)
		}
	},
	createSelect2() {
		// Check if jQuery included
		const elements = [].slice.call(
			document.querySelectorAll(
				'[data-control="select2"], [data-kt-select2="true"]',
			),
		)

		elements.forEach((element) => {
			if (element.getAttribute("data-kt-initialized") === "1") {
				return
			}

			const options = {
				dir: document.body.getAttribute("direction"),
			}

			if (element.closest(".modal")) {
				options.dropdownParent = element.closest(".modal")
			}

			if (element.getAttribute("data-hide-search") === "true") {
				options.minimumResultsForSearch = Infinity
			}

			$(element).select2(options)
			$(element).on("select2:select", () => {
				const event = new Event("change", { bubbles: true })
				element.dispatchEvent(event)
			})

			// Handle Select2's KTMenu parent case
			if (
				element.hasAttribute("data-dropdown-parent") &&
				element.hasAttribute("multiple")
			) {
				const parentEl = document.querySelector(
					element.getAttribute("data-dropdown-parent"),
				)

				if (parentEl?.hasAttribute("data-kt-menu")) {
					const menu = new KTMenu(parentEl)

					if (menu) {
						$(element).on("select2:unselect", () => {
							element.setAttribute("data-multiple-unselect", "1")
						})

						menu.on("kt.menu.dropdown.hide", () => {
							if (element.getAttribute("data-multiple-unselect") === "1") {
								element.removeAttribute("data-multiple-unselect")
								return false
							}
						})
					}
				}
			}
			element.setAttribute("data-kt-initialized", "1")
		})
	},
}

global.CMSAdmin = CMSAdmin
window.CMSAdmin = CMSAdmin
export { CMSAdmin }
