import { mount, shallowMount, RouterLinkStub } from '@vue/test-utils'
import EntityTable from 'Components/EntityTable/EntityTable.vue'

import { store } from '../../setupStore.js'

const items = store.getters.getAllItems

describe('EntityTable.vue', () => {
	'use strict'

	it('returns all items when search is empty', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: '',
			},
		})
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(3)
	})

	it('finds items when searching with text in tags "Cat1"', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: 'Cat1',
			},
		})
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(2)
	})

	it('returns filtered items when search is "Maker1"', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: 'Maker1',
			},
		})
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(2)
	})

	it('searches in tags "tags:Cat1"', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: 'tags:Cat1',
			},
		})
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(2)
	})

	it('searches only in given keywords "comment:Maker1"', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: 'comment:Maker1',
			},
		})
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(1)
	})

	it('handles if item has no entry with given keyword "itemNumber:42"', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: 'itemNumber:42',
			},
		})
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(0)
	})

	it('does not fail when no items are passed', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				showDropdown: false,
				searchString: '',
			},
		})
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(0)
	})

	it('selects item when clicked', () => {
		const wrapper = mount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: '',
			},
			stubs: {
				RouterLink: RouterLinkStub,
			},
		})
		wrapper.find('label[for="select-item-1-' + wrapper.vm._uid + '"]').trigger('click')
		wrapper.find('label[for="select-item-2-' + wrapper.vm._uid + '"]').trigger('click')
		wrapper.find('label[for="select-item-3-' + wrapper.vm._uid + '"]').trigger('click')
		const itemsFound = wrapper.vm.selectedItems
		const allSelected = wrapper.vm.allEntitiesSelected
		expect(itemsFound.length).toBe(3)
		expect(allSelected).toBe(true)
	})

	it('selects all items on checkbox click and unselects on second click', () => {
		const wrapper = shallowMount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: '',
			},
		})
		wrapper.find('input.select-all.checkbox + label').trigger('click')
		let itemsFound = wrapper.vm.selectedItems
		let allSelected = wrapper.vm.allEntitiesSelected
		expect(itemsFound.length).toBe(3)
		expect(allSelected).toBe(true)
		wrapper.find('input.select-all.checkbox + label').trigger('click')
		itemsFound = wrapper.vm.selectedItems
		allSelected = wrapper.vm.allEntitiesSelected
		expect(itemsFound.length).toBe(0)
		expect(allSelected).toBe(false)
	})

	it('selects item when clicked on label', () => {
		const wrapper = mount(EntityTable, {
			global: {
				plugins: [store],
			},
			props: {
				items,
				showDropdown: false,
				searchString: '',
			},
			stubs: {
				RouterLink: RouterLinkStub,
			},
		})
		wrapper.find('label[for="select-item-1-' + wrapper.vm._uid + '"]').trigger('click')
		let selectedItems = wrapper.vm.selectedItems
		let allSelected = wrapper.vm.allEntitiesSelected
		expect(selectedItems.length).toBe(1)
		expect(allSelected).toBe(false)
		wrapper.find('label[for="select-item-1-' + wrapper.vm._uid + '"]').trigger('click')
		selectedItems = wrapper.vm.selectedItems
		allSelected = wrapper.vm.allEntitiesSelected
		expect(selectedItems.length).toBe(0)
		expect(allSelected).toBe(false)
	})
})
