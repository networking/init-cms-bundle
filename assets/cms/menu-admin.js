import "jstree"
import "bootstrap"

const CMSMenuAdmin = (() => {
	const showHideTree = (treeSwitch) => {
		const treeId = treeSwitch.dataset.treeId
		trees.find((tree) => {
			if (tree.attr("id") === treeId) {
				if (localStorage.getItem("tree_" + treeId) !== "false") {
					return tree.jstree(true).open_all()
				}
				return tree.jstree(true).close_all()
			}
			return false
		})
	}
	const updateMenuSort = (nodes, doReload) => {
		let ret = []
		let left = 2

		nodes.forEach((node) => {
			left = flattenArray(node, 1, left, ret, node.data.rootId)
		})

		ret = ret.sort((a, b) => a.left - b.left)

		axios.post(updateUrl, { nodes: ret }, axiosConfig).then((response) => {
			CMSAdmin.createInitCmsMessageBox("success", response.data.message)
			menuDialog.hide()
			if (doReload) {
				reloadList()
			}
		})
	}
	const postMenuLoad = () => {
		if (ready) {
			return
		}

		document.querySelectorAll("[data-tree-id]").forEach((item) => {
			item.checked =
				localStorage.getItem(`tree_${item.dataset.treeId}`) !== "false"
			showHideTree(item)
		})
		ready = true
	}
	const setUpSortTree = () => {
		$("div.menu_tree").each((_e, ui) => {
			const tree = $(ui)

			trees.push(tree)

			const treeData = tree.data("tree")
			const plugins = ["dnd", "types", "changed"]

			if (tree.attr("id") !== "placement_menu") {
				plugins.push("externalUrl")
				plugins.push("delete")
			}
			tree.jstree({
				types: {
					default: {
						icon: "la la-file text-primary fs-2x",
					},
					file: {
						icon: "la la-file text-primary fs-2x",
					},
				},
				plugins: plugins,
				core: {
					themes: {
						variant: "large",
					},

					check_callback: true,
					data: (_node, cb) => {
						cb(treeData)
					},
				},
			})

			const treeSwitch = document.querySelector(
				`[data-tree-id="${tree.attr("id")}"]`,
			)

			tree.on("move_node.jstree", (_e, data) => {
				updateMenuSort(
					data.new_instance.get_json(),
					$(data.new_instance.element[0]).attr("id") === "placement_menu",
				)
			})

			if (tree.attr("id") !== "placement_menu") {
				tree.on("after_close.jstree", () => {
					treeSwitch.checked = false
				})

				tree.on("ready.jstree", () => {
					postMenuLoad()
				})
			}
		})
	}
	const selectLastTab = (lastTab) => {
		if (!lastTab) {
			return
		}
		const tabTriggerEl = document.querySelector(`a[href="${lastTab}"]`)

		if (!tabTriggerEl) {
			return
		}
		const tab = new bootstrap.Tab(tabTriggerEl)

		tab.show()
	}
	let lastTab = localStorage.getItem("lastTab")
	let lastEdited
	let menuDialog
	let tabs
	let listUrl
	let updateUrl
	let ready = false
	const trees = []

	const reloadList = () => {
		axios
			.get(tabs.dataset.listUrl, {
				locale: document.querySelector("#filter_locale_value").value,
				...axiosConfig,
			})
			.then((response) => {
				tabs.innerHTML = response.data.html

				if (Object.hasOwn(response.data, "last_edited") > -1) {
					setLastEdited(response.data.last_edited)
				}
				ready = false
				setUpSortTree()
				selectLastTab(lastTab)
			})
	}

	const setLastTab = (tab) => {
		lastTab = tab
		localStorage.setItem("lastTab", lastTab)
	}

	const initializeDialog = () => {
		if (!menuDialog) {
			menuDialog = new bootstrap.Modal(document.querySelector("#menu_dialog"), {
				height: "auto",
				width: 650,
				show: false,
			})
		}
	}

	const createAjaxDialog = (event) => {
		event.preventDefault()

		let link = event.target

		if (!link.getAttribute("href")) {
			link = link.closest("a")
		}

		const locale = document.querySelector("#filter_locale_value").value
		axios
			.get(link.getAttribute("href"), { locale: locale, ...axiosConfig })
			.then((response) => {
				document
					.querySelector("#menu_dialog")
					.querySelector(".modal-content").innerHTML = response.data
				menuDialog.show()
				CMSAdmin.createSelect2()
			})
	}

	const submitAjaxForm = (event) => {
		event.preventDefault()
		event.stopPropagation()

		const form = event.target

		form.elements.forEach((item) => {
			item.classList.remove("is-invalid")
		})
		// form.classList.add('was-validated')
		axios
			.post(form.action, new FormData(form), axiosConfig)
			.then((response) => {
				var data = response.data
				if (data.result === "ok") {
					if (data.is_new_menu_item && data.html) {
						document
							.querySelector("#menu_dialog")
							.querySelector(".modal-content").innerHTML = data.html
					} else {
						menuDialog.hide()
						CMSAdmin.createInitCmsMessageBox(data.status, data.message)
					}
					reloadList()
				}

				if (data.result === "reload") {
					window.location.reload()
				}
			})
			.catch((error) => {
				var data = error.response.data

				if (typeof data !== "object") {
					menuDialog.html(data)
				}

				data.violations.forEach((item) => {
					const path = item.propertyPath
					const message = item.title
					let field = form.querySelector(`[name="${path}"]`)

					field.setAttribute("required", "required")

					if (
						field.nextElementSibling?.classList.contains("select2-container")
					) {
						field = field.nextElementSibling
					}

					field.classList.add("is-invalid")

					if (
						field.nextElementSibling?.classList.contains("invalid-feedback")
					) {
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

	var setLastEdited = (id) => {
		lastEdited = id
	}

	const flattenArray = (item, depth, left, ret, pid) => {
		let right = left + 1,
			id = item.data.id

		if (item.children.length > 0) {
			depth++
			item.children.forEach((node) => {
				right = flattenArray(node, depth, right, ret, id)
			})
			depth--
		}

		if (id) {
			ret.push({
				name: item.text,
				item_id: id,
				parent_id: pid,
				depth: depth,
				left: left,
				right: right,
			})
		}

		left = right + 1
		return left
	}

	const handleShowHideTree = (event) => {
		const treeSwitch = event.target
		const treeId = treeSwitch.dataset.treeId
		localStorage.setItem(`tree_${treeId}`, treeSwitch.checked)
		showHideTree(treeSwitch)
	}

	const initMenu = () => {
		tabs = document.querySelector("#menuTabs")

		lastTab = localStorage.getItem("lastTab")
		lastEdited = tabs.dataset.lastEdited
		listUrl = tabs.dataset.listUrl
		updateUrl = tabs.dataset.updateUrl

		document.querySelectorAll("[data-tree-id]").forEach((item) => {
			item.checked =
				localStorage.getItem(`tree_${item.dataset.treeId}`) !== "false"
		})
		setUpSortTree()
		KTUtil.on(
			document.querySelector("#menuTabs"),
			".menu-dialog-link",
			"click",
			createAjaxDialog,
		)
		KTUtil.on(
			document.querySelector("#menu_dialog"),
			"form",
			"submit",
			submitAjaxForm,
		)
		KTUtil.on(
			document.querySelector("#menuTabs"),
			".tree-show-all",
			"click",
			handleShowHideTree,
		)

		document.body.addEventListener("shown.bs.tab", (e) => {
			setLastTab($(e.target).attr("href"))
		})

		setTimeout(() => {
			if (lastEdited) {
				const lastEditedNode = document.querySelector(
					`#menu-item-${lastEdited}`,
				)
				lastTab = `#menu_${lastEditedNode.dataset.rootId}`
				setLastTab(lastTab)
			}

			selectLastTab(lastTab)
		}, 500)
	}

	return {
		init: () => {
			initMenu()
			initializeDialog()
		},
		reload: () => {
			reloadList()
		},
	}
})()

;(($) => {
	const externalUrl = document.createElement("a")
	externalUrl.className = "external-url float-end"
	$.jstree.defaults.externalUrl = $.noop
	$.jstree.plugins.externalUrl = function (_options, parent) {
		this.teardown = function () {
			if (this.settings.externalUrl) {
				this.element.find(".external-url").remove()
			}
			parent.teardown.call(this)
		}
		this.redraw_node = function (obj, deep, callback, force_draw) {
			obj = parent.redraw_node.call(this, obj, deep, callback, force_draw)
			var node = this.get_node(obj)
			var tmp = externalUrl.cloneNode(true)
			tmp.href = node.data.externalUrl
			tmp.innerHTML = node.data.path
			obj.insertBefore(tmp, obj.childNodes[2])
			return obj
		}
	}

	const link = document.createElement("a")
	link.className = "delete-tag menu-dialog-link float-end "
	link.innerHTML = '<i class="la la-trash fs-2 mx-3"></i>'

	$.jstree.defaults.delete = $.noop
	$.jstree.plugins.delete = function (_options, parent) {
		this.teardown = function () {
			if (this.settings.delete) {
				this.element.find(".delete-tag").remove()
			}
			parent.teardown.call(this)
		}
		this.redraw_node = function (obj, deep, callback, force_draw) {
			obj = parent.redraw_node.call(this, obj, deep, callback, force_draw)

			var tmp = link.cloneNode(true)
			var node = this.get_node(obj)
			tmp.href = node.data.deleteUrl
			obj.insertBefore(tmp, obj.childNodes[2])
			return obj
		}
	}
})($)

// On document ready
KTUtil.onDOMContentLoaded(() => {
	CMSMenuAdmin.init()
})
