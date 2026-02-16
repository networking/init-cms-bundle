import Sortable from "../vendor/sortablejs/sortablejs.bundle.js"

class OneToManySortable {
	constructor(element) {
		console.log(element)
		this.element = element
		this.draggable = this.element.dataset.cmsSortableDraggable
		this.initialize()
		document.body.addEventListener("sonata.add_element", () => {
			this.initialize()
		})
		document.body.addEventListener("sonata.delete_element", () => {
			this.applyPosition()
		})
	}
	addHandles() {
		const items = this.element.querySelectorAll(
			this.element.dataset.cmsSortableSelector,
		)
		items.forEach((item) => {
			const handle = document.createElement("span")
			handle.setAttribute("class", "sonata-ba-sortable-handler")
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
			const items = this.element.querySelectorAll(
				this.element.dataset.cmsSortableSelector,
			)
			items.forEach((item, index) => {
				item.querySelector("input").value = index
			})
		}, 1000)
	}
}

const CMSOneToManySortable = {
	init() {
		document
			.querySelectorAll("[data-cms-sortable-one-to-many]")
			.forEach((element) => {
				const sortable = new OneToManySortable(element)
			})
	},
}

export default CMSOneToManySortable
