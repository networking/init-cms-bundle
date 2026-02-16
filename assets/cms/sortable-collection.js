import Sortable from "../vendor/sortablejs/sortablejs.bundle.js"

class SortableCollection {
	constructor(element) {
		this.element = element
		this.type = element.dataset.sortableCollectionType
		this.draggable = this.element.dataset.cmsSortableDraggable
		this.initialize()

		document.addEventListener("afterAddItem", () => {
			this.initialize()
		})

		document.addEventListener("afterRemoveItem", () => {
			this.applyPosition()
		})
	}
	addHandles() {
		const items = this.element.querySelectorAll(this.draggable)
		items.forEach((item) => {
			if (item.dataset.hasHandle) {
				return
			}
			item.dataset.hasHandle = 1
			const handle = document.createElement("span")
			handle.setAttribute("class", "sonata-ba-sortable-handler float-end")
			handle.innerHTML = '<i class="fa fa-bars"></i>'
			item.insertBefore(handle, item.firstChild)
		})
	}
	initialize() {
		const container = this.element.querySelector(
			this.element.dataset.cmsSortableContainer,
		)

		if (!container) {
			return
		}

		new Sortable(container, {
			draggable: this.draggable,
			animation: 150,
			easing: "cubic-bezier(1, 0, 0, 1)",
			onEnd: () => {
				this.applyPosition()
			},
			onStart: (/**Event*/ e) => {
				e.oldIndex // element index within parent
			},
			onRemove() {
				this.applyPosition()
			},
		})

		this.addHandles()

		this.applyPosition()
	}
	applyPosition() {
		setTimeout(() => {
			const items = this.element.querySelectorAll(`input.${this.type}`)
			items.forEach((item, index) => {
				item.value = index
			})
		}, 1000)
	}
}

const CMSSortableCollection = {
	init() {
		document
			.querySelectorAll("[data-sortable-collection-type]")
			.forEach((element) => {
				if (element.dataset.cmsCollectionType) {
					return
				}

				new SortableCollection(element)
			})
	},
}

export default CMSSortableCollection
