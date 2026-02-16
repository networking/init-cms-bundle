import Sortable from "../vendor/sortablejs/sortablejs.bundle.js"
import { CMSAdmin } from "./cms-admin"

let containers = null
let dropzones = null
let contentTypeList = null
const addBlockUrl = CMSRouting.generate(
	"admin_networking_initcms_layoutblock_addBlock",
)
const sortUrl = CMSRouting.generate(
	"admin_networking_initcms_layoutblock_updateLayoutBlockSort",
)
const deleteUrl = CMSRouting.generate(
	"admin_networking_initcms_layoutblock_deleteAjax",
)
let lastTranslationSettingsHtml = null
let pageId = null
const erroredContainers = new Set()
const Translator = await CMSAdmin.getTranslator()

function initDropZone() {
	containers = document.querySelectorAll(".draggable-zone")
	dropzones = document.querySelectorAll(".dropzone")
	contentTypeList = document.querySelector("#content_item_list")
	pageId = document
		.querySelector("meta[name='init-cms-page-id']")
		.getAttribute("content")

	if (containers.length === 0) {
		return false
	}

	const contentItems = new Sortable(contentTypeList, {
		group: {
			name: "shared",
			pull: "clone",
			put: false,
			revertClone: true,
		},
		sort: false,
		onEnd: (/**Event*/ evt) => {
			hideContainerErrors()
		},
	})

	containers.forEach((container) => {
		const swappable = new Sortable(container, {
			group: { name: "shared", pull: true, put: acceptLayoutBlock },
			animation: 150,
			onEnd: (/**Event*/ evt) => {
				hideContainerErrors()

				if (evt.newIndex === evt.oldIndex && evt.to.id === evt.from.id) {
					return
				}

				saveLayoutBlockSort(evt, (response) => {
					CMSAdmin.createInitCmsMessageBox(
						response.data.messageStatus,
						response.data.message,
						1000,
					)
				})
			},
			onAdd: (/**CustomEvent*/ evt, dragEl) => {
				const count = evt.to.querySelectorAll(".draggable").length
				const item = evt.item

				if (!item.id) {
					const dropzone = container.parentElement
					createItem(
						item.dataset.contentType,
						dropzone.dataset.pageId,
						dropzone.dataset.zone,
						evt.newIndex,
					)
						.then((response) => {
							item.outerHTML = response.data.html
							saveLayoutBlockSort(evt)
							document
								.querySelector(
									"#layoutBlock_" +
										response.data.layoutBlockId +
										"  .create_block",
								)
								.click()
						})
						.catch((error) => {
							if (!error.response) {
								console.error(error)
								return
							}

							const message = error.response.data.detail

							CMSAdmin.createInitCmsMessageBox("error", message)
						})
				}

				if (count) {
					evt.to.querySelector(".empty_layout_block").classList.add("d-none")
					return
				}

				evt.to.querySelector(".empty_layout_block").classList.remove("d-none")
			},
			onRemove: (/**Event*/ evt) => {
				const count = evt.from.querySelectorAll(".draggable").length
				if (count) {
					evt.from.querySelector(".empty_layout_block").classList.add("d-none")
					return
				}

				evt.from.querySelector(".empty_layout_block").classList.remove("d-none")
			},
		})

		document.addEventListener("fade-out-blocks", (e) => {
			swappable.option("disabled", true)
		})

		document.addEventListener("fade-in-blocks", (e) => {
			swappable.option("disabled", false)
		})
	})
}

const showContainerError = (container) => {
	const dropzone = container.closest(".dropzone")
	dropzone.classList.add("bg-light-danger")
	dropzone.closest(".dropzone").classList.add("border-danger")
	erroredContainers.add(dropzone)
}

const hideContainerErrors = () => {
	erroredContainers.forEach((item) => {
		item.classList.remove("bg-light-danger")
		item.classList.remove("border-danger")
	})
}

const acceptLayoutBlock = (to, from, dragEl) => {
	const container = to.el
	const contentBlock = dragEl
	const contentTypes = JSON.parse(container.dataset.contentTypes)
	if (contentTypes.length > 0) {
		if (!contentTypes.includes(contentBlock.dataset.contentType)) {
			showContainerError(container)
			return false
		}
	}

	const maxItems = container.dataset.maxItems

	const count = container.querySelectorAll(".draggable").length

	if (maxItems > 0) {
		if (count >= maxItems) {
			showContainerError(container)
			return false
		}
	}
	hideContainerErrors()
	return true
}

const saveLayoutBlockSort = (event, callback) => {
	// CMSAdmin.createInitCmsMessageBox(xhr.messageStatus, xhr.message);

	const zones = []
	let pageId = null
	let adminCode = null

	dropzones.forEach((dropzone) => {
		const layoutBlocks = dropzone.querySelectorAll(".draggable")
		const addButtons = dropzone.querySelectorAll(".add-layout")
		const zone = dropzone.dataset.zone

		if (!pageId) {
			pageId = dropzone.dataset.pageId
		}

		if (!adminCode) {
			adminCode = dropzone.dataset.adminCode
		}

		const layoutBlockIds = []

		layoutBlocks.forEach((layoutBlock, index) => {
			layoutBlock.dataset.sortOrder = index
			layoutBlockIds.push(layoutBlock.id)
		})
		zones.push({
			zone: zone,
			layoutBlocks: layoutBlockIds,
		})
	})

	submitLayoutSort(zones, pageId, adminCode).then((response) => {
		const event = new CustomEvent("page-updated")
		document.body.dispatchEvent(event)
		if (callback !== undefined) {
			callback(response)
		}
	})
}

const createItem = async (contentType, pageId, zone, sortOrder) => {
	return await axios.get(
		addBlockUrl,
		{
			params: {
				subclass: contentType,
				zone: zone,
				pageId: pageId,
				sortOrder: sortOrder,
			},
		},
		axiosConfig,
	)
}

const submitLayoutSort = async (zones, pageId, adminCode) => {
	return await axios.post(
		sortUrl,
		{
			zones: zones,
			pageId: pageId,
			code: adminCode,
		},
		axiosConfig,
	)
}

const fadeOutContentBlocks = (except) => {
	const contentBlocks = document.querySelectorAll(".content_type_item")
	contentBlocks.forEach((item) => {
		if (item === except) {
			return
		}
		item.classList.add("opacity-5")
	})
	document.dispatchEvent(new CustomEvent("fade-out-blocks"))
}

const fadeInContentBlocks = () => {
	const contentBlocks = document.querySelectorAll(".content_type_item")
	contentBlocks.forEach((item) => {
		item.classList.remove("opacity-5")
	})
	document.dispatchEvent(new CustomEvent("fade-in-blocks"))
}

const editBlock = (e) => {
	e.preventDefault()
	let el = e.target

	if (el.classList.contains("fa-pen-to-square")) {
		el = el.parentElement
	}

	const id = el.dataset.value

	const layoutBlock = document.getElementById("layoutBlock_" + id)

	fadeOutContentBlocks(layoutBlock)

	layoutBlock.querySelector(".edit_block").setAttribute("disabled", true)
	layoutBlock.querySelector(".delete_block").setAttribute("disabled", true)

	const editUrl = CMSRouting.generate(
		"admin_networking_initcms_layoutblock_edit",
		{ id: id },
	)
	const displayBlock = document.getElementById("layoutBlockHtml" + id)
	const editBlock = document.getElementById("editBlockHtml" + id)

	axios
		.get(editUrl, axiosConfig)
		.then((response) => {
			editBlock.innerHTML = response.data.html
			displayBlock.classList.add("d-none")
			editBlock.classList.remove("d-none")
			document.body.dispatchEvent(
				new CustomEvent("fields:added", {
					detail: {
						id: id,
						contentType: layoutBlock.dataset.contentType,
					},
				}),
			)
			layoutBlock.scrollIntoView()
		})
		.catch((error) => {
			const message = error.response.data.detail
			CMSAdmin.createInitCmsMessageBox("error", message)
		})
}

const createBlock = (e) => {
	e.preventDefault()
	let el = e.target

	if (el.classList.contains("fa-pen-to-square")) {
		el = el.parentElement
	}

	const id = el.dataset.value

	const layoutBlock = document.getElementById("layoutBlock_" + id)

	fadeOutContentBlocks(layoutBlock)
	layoutBlock.querySelector(".create_block").setAttribute("disabled", true)

	const createUrl = CMSRouting.generate(
		"admin_networking_initcms_layoutblock_create",
		{
			subclass: layoutBlock.dataset.contentType,
			zone: layoutBlock.dataset.zone,
			pageId: layoutBlock.dataset.pageId,
			sortOrder: layoutBlock.dataset.sortOrder,
		},
	)

	const displayBlock = document.getElementById("layoutBlockHtml" + id)
	const editBlock = document.getElementById("editBlockHtml" + id)

	axios
		.get(createUrl, axiosConfig)
		.then((response) => {
			editBlock.innerHTML = response.data.html
			displayBlock.classList.add("d-none")
			editBlock.classList.remove("d-none")
			document.body.dispatchEvent(
				new CustomEvent("fields:added", {
					detail: {
						id: id,
						contentType: layoutBlock.dataset.contentType,
					},
				}),
			)
			layoutBlock.scrollIntoView()
		})
		.catch((error) => {
			if (!error.response) {
				console.error(error)
				return
			}

			const message = error.response.data.detail
			CMSAdmin.createInitCmsMessageBox("error", message)
		})
}

const cancelEditBlock = (e) => {
	const id = e.target.dataset.value
	const displayBlock = document.getElementById("layoutBlockHtml" + id)
	const editBlock = document.getElementById("editBlockHtml" + id)
	const layoutBlock = document.getElementById("layoutBlock_" + id)
	layoutBlock.querySelector(".edit_block").removeAttribute("disabled")
	layoutBlock.querySelector(".delete_block").removeAttribute("disabled")
	editBlock.classList.add("d-none")
	editBlock.innerHTML = ""
	displayBlock.classList.remove("d-none")
	fadeInContentBlocks()
}

const cancelCreateBlock = (e) => {
	e.preventDefault()
	const form = e.target.closest("form")
	const div = form.closest("[data-zone]")
	const id = div.getAttribute("id").replace("layoutBlock_", "")
	const displayBlock = document.getElementById("layoutBlockHtml" + id)
	const editBlock = document.getElementById("editBlockHtml" + id)
	const layoutBlock = document.getElementById("layoutBlock_" + id)
	layoutBlock.querySelector(".create_block").removeAttribute("disabled")
	editBlock.classList.add("d-none")
	editBlock.innerHTML = ""
	displayBlock.classList.remove("d-none")
	fadeInContentBlocks()
}

const saveLayoutBlock = (e) => {
	e.preventDefault()
	const form = e.target.closest("form")
	const config = {
		url: form.action,
		method: form.method,
		data: new FormData(form),
		...axiosConfig,
	}

	if (form.enctype === "multipart/form-data") {
		config.headers["Content-Type"] = "multipart/form-data"
	}
	axios
		.request(config)
		.then((response) => {
			if (response.status === 200) {
				const id = response.data.id
				const displayBlock = document.getElementById("layoutBlockHtml" + id)
				const editBlock = document.getElementById("editBlockHtml" + id)
				const layoutBlock = document.getElementById("layoutBlock_" + id)
				layoutBlock.querySelector(".edit_block").removeAttribute("disabled")
				layoutBlock.querySelector(".delete_block").removeAttribute("disabled")
				editBlock.classList.add("d-none")
				editBlock.innerHTML = ""
				displayBlock.classList.remove("d-none")
				displayBlock.innerHTML = response.data.html
				CMSAdmin.createInitCmsMessageBox(
					response.data.status,
					response.data.message,
					1000,
				)
				const event = new CustomEvent("page-updated")
				document.body.dispatchEvent(event)
				const layoutBlockEvent = new CustomEvent("layout-block-updated", {
					detail: displayBlock.id,
				})
				document.body.dispatchEvent(layoutBlockEvent)
				fadeInContentBlocks()
			}
		})
		.catch((error) => {
			if (!error.response) {
				console.error(error)
				return
			}

			const id = error.response.data.id
			const editBlock = document.getElementById("editBlockHtml" + id)
			if (error.response.data.html) {
				editBlock.innerHTML = error.response.data.html
			}
			CMSAdmin.createInitCmsMessageBox("error", error.response.data.message)
			document.body.dispatchEvent(
				new CustomEvent("fields:added", { detail: id }),
			)
		})
}

const createLayoutBlock = (e) => {
	e.preventDefault()
	const form = e.target.closest("form")
	const config = {
		url: form.action,
		method: form.method,
		data: new FormData(form),
		...axiosConfig,
	}

	if (form.enctype === "multipart/form-data") {
		config.headers["Content-Type"] = "multipart/form-data"
	}

	const div = form.closest("[data-zone]")

	const id = div.getAttribute("id").replace("layoutBlock_", "")
	axios
		.request(config)
		.then((response) => {
			if (response.status === 200) {
				const template = document.createElement("template")
				template.innerHTML = response.data.html.trim()
				div.replaceWith(template.content.firstChild)
				CMSAdmin.createInitCmsMessageBox(
					response.data.status,
					response.data.message,
					1000,
				)
				const event = new CustomEvent("page-updated")
				document.body.dispatchEvent(event)
				const layoutBlockEvent = new CustomEvent("layout-block-updated", {
					detail: "layoutBlock_" + response.data.layoutBlockId,
				})
				document.body.dispatchEvent(layoutBlockEvent)
				fadeInContentBlocks()
			}
		})
		.catch((error) => {
			if (!error.response) {
				console.error(error)
				return
			}

			const createBlock = document.getElementById("editBlockHtml" + id)
			createBlock.innerHTML = error.response.data.html
			CMSAdmin.createInitCmsMessageBox("error", error.response.data.message)
			document.body.dispatchEvent(
				new CustomEvent("fields:added", { detail: id }),
			)
		})
}

const toggleActive = (e) => {
	e.preventDefault()
	let el = e.target
	if (el.classList.contains("ki-outline")) {
		el = el.parentElement
	}
	const icon = el.querySelector("i")
	const id = el.dataset.value
	const displayBlock = document.getElementById("layoutBlockHtml" + id)
	const url = CMSRouting.generate(
		"admin_networking_initcms_layoutblock_toggleActive",
	)

	axios
		.post(url, { id: id }, axiosConfig)
		.then((response) => {
			CMSAdmin.createInitCmsMessageBox("success", response.data.message, 1000)
			if (response.data.active) {
				icon.classList.remove("ki-minus-circle")
				icon.classList.add("ki-check-circle")
				el.classList.remove("btn-light-danger")
				el.classList.add("btn-light-success")
				displayBlock.classList.remove("opacity-25")
				return
			}
			icon.classList.add("ki-minus-circle")
			icon.classList.remove("ki-check-circle")
			el.classList.add("btn-light-danger")
			el.classList.remove("btn-light-success")
			displayBlock.classList.add("opacity-25")
			const event = new CustomEvent("page-updated")
			document.body.dispatchEvent(event)
		})
		.catch((error) => {
			CMSAdmin.createInitCmsMessageBox("error", error.response.data.message)
		})
}

const deleteBlock = (e) => {
	e.preventDefault()
	let el = e.target

	if (el.classList.contains("fa-trash")) {
		el = el.parentElement
	}

	const container = el.closest(".draggable-zone")

	Swal.fire({
		html: Translator.trans("page_admin.confirm", [], "PageAdmin"),
		icon: "warning",
		buttonsStyling: false,
		showCancelButton: true,
		confirmButtonText: Translator.trans(
			"button.confirm_delete",
			[],
			"PageAdmin",
		),
		cancelButtonText: Translator.trans("button.cancel", [], "PageAdmin"),
		customClass: {
			confirmButton: "btn btn-sm btn-danger",
			cancelButton: "btn btn-sm btn-light",
		},
	}).then((result) => {
		if (result.isConfirmed) {
			axios
				.post(deleteUrl, {
					id: el.dataset.value,
					_method: "DELETE",
				})
				.then((response) => {
					fadeInContentBlocks()
					document.querySelector("#layoutBlock_" + el.dataset.value).remove()
					CMSAdmin.createInitCmsMessageBox(
						response.data.messageStatus,
						response.data.message,
						1000,
					)
					saveLayoutBlockSort()
					if (container.querySelectorAll(".draggable").length) {
						container
							.querySelector(".empty_layout_block")
							.classList.add("d-none")
						return
					}
					container
						.querySelector(".empty_layout_block")
						.classList.remove("d-none")
				})
				.catch((err) => {
					CMSAdmin.createInitCmsMessageBox("error", "Something went wrong")
				})
		}
	})
}

const unlinkTranslation = (e) => {
	e.preventDefault()
	const el = e.target

	const text = el.dataset.text
	const id = el.dataset.objectId

	Swal.fire({
		html: text,
		icon: "warning",
		buttonsStyling: false,
		showCancelButton: true,
		confirmButtonText: Translator.trans(
			"button.confirm_delete",
			[],
			"PageAdmin",
		),
		cancelButtonText: Translator.trans("button.cancel", [], "PageAdmin"),
		customClass: {
			confirmButton: "btn btn-sm btn-danger",
			cancelButton: "btn btn-sm btn-light",
		},
	}).then((result) => {
		if (result.isConfirmed) {
			const headers = {
				"X-HTTP-Method-Override": "DELETE",
				...axiosConfig.headers,
			}

			axios
				.post(
					el.href,
					{},
					{
						headers: headers,
					},
				)
				.then((response) => {
					document.querySelector("#translations").innerHTML = response.data.html
					CMSAdmin.createInitCmsMessageBox(
						"success",
						response.data.message,
						1000,
					)
				})
				.catch((err) => {
					CMSAdmin.createInitCmsMessageBox("error", "Something went wrong")
				})
		}
	})
}

const linkTranslation = (e) => {
	e.preventDefault()

	const el = e.target

	axios
		.get(el.href, axiosConfig)
		.then((response) => {
			lastTranslationSettingsHtml =
				document.querySelector("#translations").innerHTML
			document.querySelector("#translations").innerHTML = response.data.html
		})
		.catch((err) => {
			CMSAdmin.createInitCmsMessageBox("error", "Something went wrong")
		})
}

const submitTranslationLink = (e) => {
	e.preventDefault()

	const form = e.target.closest("form")

	if (
		e.submitter.classList.contains("btn-cancel") ||
		e.submitter.classList.contains("cancel")
	) {
		document.querySelector("#translations").innerHTML =
			lastTranslationSettingsHtml
		return
	}

	axios
		.post(form.action, new FormData(form), axiosConfig)
		.then((response) => {
			document.querySelector("#translations").innerHTML = response.data.html
			CMSAdmin.createInitCmsMessageBox("success", response.data.message, 1000)
		}, axiosConfig)
		.catch((err) => {
			if (err.response.data.message) {
				return CMSAdmin.createInitCmsMessageBox(
					"error",
					err.response.data.message,
				)
			}

			CMSAdmin.createInitCmsMessageBox("error", err.response.data.detail)
		})
}

const submitPageSettings = (e) => {
	e.preventDefault()
	const form = e.target.closest("form")

	form.elements.forEach((item) => {
		item.classList.remove("is-invalid")
	})

	axios
		.post(form.action, new FormData(form), axiosConfig)
		.then((response) => {
			document.querySelector("#pageStatusSettings").innerHTML =
				response.data.pageStatusSettings

			if (response.data.layoutBlockSettingsHtml) {
				document.querySelector("#page_content").innerHTML =
					response.data.layoutBlockSettingsHtml
				initDropZone()
			}

			CMSAdmin.createInitCmsMessageBox("success", response.data.message, 1000)
		})
		.catch((err) => {
			const data = err.response.data

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
		})
}

const updatePageStatus = () => {
	axios
		.get(
			CMSRouting.generate("admin_networking_initcms_page_getPageStatus", {
				id: pageId,
			}),
			axiosConfig,
		)
		.then((response) => {
			document.querySelector("#pageStatusSettings").innerHTML =
				response.data.pageStatusSettings
		})
		.catch((err) => {
			if (err.response.data.message) {
				return CMSAdmin.createInitCmsMessageBox(
					"error",
					err.response.data.message,
				)
			}

			CMSAdmin.createInitCmsMessageBox("error", err.response.data.detail)
		})
}

const loadLayoutBlockJs = (event) => {
	if (event.detail) {
		const scripts = document
			.querySelector("#" + event.detail)
			.getElementsByTagName("script")
		for (let i = 0; i < scripts.length; ++i) {
			const script = scripts[i]
			eval(script.innerHTML)

			CMSAdmin.log(script.innerHTML)
		}
	}
}

const statusDialog = (e) => {
	e.preventDefault()

	let el = e.target

	if (!el.href) {
		el = el.closest("a")
	}
	const text = el.dataset.text
	Swal.fire({
		html: text,
		icon: "warning",
		buttonsStyling: false,
		showCancelButton: true,
		confirmButtonText: "Ok, got it!",
		cancelButtonText: "Nope, cancel it",
		customClass: {
			confirmButton: "btn btn-danger",
			cancelButton: "btn btn-primary",
		},
	}).then((result) => {
		if (result.isConfirmed) {
			axios
				.post(el.href, axiosConfig)
				.then((response) => {
					if (response.data.redirect) {
						return (window.location = response.data.redirect)
					}
				})
				.catch((err) => {
					if (err.response.data.message) {
						return CMSAdmin.createInitCmsMessageBox(
							"error",
							err.response.data.message,
						)
					}

					CMSAdmin.createInitCmsMessageBox("error", err.response.data.detail)
				})
		}
	})
}

const updateAfterContentBuilder = () => {
	axios
		.get(
			CMSRouting.generate("admin_networking_initcms_page_edit", { id: pageId }),
			axiosConfig,
		)
		.then((response) => {
			if (response.data.layoutBlockSettingsHtml) {
				const pageContentDiv = document.querySelector("#page_content")
				pageContentDiv.innerHTML = response.data.layoutBlockSettingsHtml
				initDropZone()

				var scripts = pageContentDiv.getElementsByTagName("script")
				for (var i = 0; i < scripts.length; ++i) {
					var script = scripts[i]
					eval(script.innerHTML)
					CMSAdmin.log(script.innerHTML)
				}
			}
		})
		.catch((err) => {
			if (!err.response) {
				CMSAdmin.error(err)
				return
			}

			if (err.response.data.message) {
				return CMSAdmin.createInitCmsMessageBox(
					"error",
					err.response.data.message,
				)
			}

			CMSAdmin.createInitCmsMessageBox("error", err.response.data.detail)
		})
}

KTUtil.onDOMContentLoaded(() => {
	KTUtil.on(document.body, ".delete_block", "click", (e) => {
		deleteBlock(e)
	})

	KTUtil.on(document.body, ".edit_block", "click", (e) => {
		editBlock(e)
	})

	KTUtil.on(document.body, ".create_block", "click", (e) => {
		createBlock(e)
	})

	KTUtil.on(document.body, ".toggle-active", "click", (e) => {
		toggleActive(e)
	})

	KTUtil.on(document.body, '[data-dismiss="edit"]', "click", (e) => {
		cancelEditBlock(e)
	})

	KTUtil.on(document.body, '[data-dismiss="create"]', "click", (e) => {
		cancelCreateBlock(e)
	})

	KTUtil.on(document.body, '[data-save="edit"]', "click", (e) => {
		saveLayoutBlock(e)
	})

	KTUtil.on(document.body, '[data-save="create"]', "click", (e) => {
		createLayoutBlock(e)
	})

	KTUtil.on(document.body, ".translation-dialog-unlink", "click", (e) => {
		unlinkTranslation(e)
	})

	KTUtil.on(document.body, ".translation-dialog-link", "click", (e) => {
		linkTranslation(e)
	})

	KTUtil.on(document.body, "#translation-link-form", "submit", (e) => {
		submitTranslationLink(e)
	})

	KTUtil.on(document.body, "#translate-copy-page-form", "submit", (e) => {
		submitTranslationLink(e)
	})

	KTUtil.on(document.body, "#page-settings-form", "submit", (e) => {
		submitPageSettings(e)
	})

	KTUtil.on(document.body, "#page-metadata-form", "submit", (e) => {
		submitPageSettings(e)
	})

	KTUtil.on(document.body, ".status-dialog-link", "click", (e) => {
		statusDialog(e)
	})

	document.body.addEventListener("page-updated", (e) => {
		updatePageStatus()
	})

	document.body.addEventListener("layout-block-updated", (e) => {
		loadLayoutBlockJs(e)
	})

	document.body.addEventListener("content-builder-updated", (e) => {
		updateAfterContentBuilder()
	})

	initDropZone()
})
