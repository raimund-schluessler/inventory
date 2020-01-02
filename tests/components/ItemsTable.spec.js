import { shallowMount } from '@vue/test-utils'
import ItemsTable from '@/components/ItemsTable.vue'

import { store, localVue } from '../setupStore'

const items = store.getters.getAllItems

describe('ItemsTable.vue', () => {
	'use strict'

	it('returns all items when search is empty', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: ''
			},
		})
		var itemsFound = wrapper.vm.filteredItems
		expect(itemsFound.length).toBe(3)
	})

	it('finds items when searching with text in categories "Cat1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'Cat1'
			},
		})
		var itemsFound = wrapper.vm.filteredItems
		expect(itemsFound.length).toBe(2)
	})

	it('returns filtered items when search is "Maker1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'Maker1'
			},
		})
		var itemsFound = wrapper.vm.filteredItems
		expect(itemsFound.length).toBe(2)
	})

	it('searches in categories "categories:Cat1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'categories:Cat1'
			},
		})
		var itemsFound = wrapper.vm.filteredItems
		expect(itemsFound.length).toBe(2)
	})

	it('searches only in given keywords "comment:Maker1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'comment:Maker1'
			},
		})
		var itemsFound = wrapper.vm.filteredItems
		expect(itemsFound.length).toBe(1)
	})

	it('handles if item has no entry with given keyword "itemNumber:42"', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'itemNumber:42'
			},
		})
		var itemsFound = wrapper.vm.filteredItems
		expect(itemsFound.length).toBe(0)
	})

	it('does not fail when no items are passed', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				showDropdown: false,
				searchString: ''
			},
		})
		var itemsFound = wrapper.vm.filteredItems
		expect(itemsFound.length).toBe(0)
	})

	it('selects item when clicked', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: ''
			},
		})
		wrapper.find('label[for="select-item-1-' + wrapper.vm._uid + '"]').trigger('click')
		wrapper.find('label[for="select-item-2-' + wrapper.vm._uid + '"]').trigger('click')
		wrapper.find('label[for="select-item-3-' + wrapper.vm._uid + '"]').trigger('click')
		var itemsFound = wrapper.vm.selectedItems
		var allSelected = wrapper.vm.allVisibleItemsSelected
		expect(itemsFound.length).toBe(3)
		expect(allSelected).toBe(true)
	})

	it('selects all items on checkbox click and unselects on second click', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: ''
			},
		})
		wrapper.find('input.select-all.checkbox').trigger('click')
		var itemsFound = wrapper.vm.selectedItems
		var allSelected = wrapper.vm.allVisibleItemsSelected
		expect(itemsFound.length).toBe(3)
		expect(allSelected).toBe(true)
		wrapper.find('input.select-all.checkbox').trigger('click')
		itemsFound = wrapper.vm.selectedItems
		allSelected = wrapper.vm.allVisibleItemsSelected
		expect(itemsFound.length).toBe(0)
		expect(allSelected).toBe(false)
	})

	it('selects item when clicked on label', () => {
		const wrapper = shallowMount(ItemsTable, {
			localVue,
			store,
			propsData: {
				items: items,
				showDropdown: false,
				searchString: ''
			},
		})
		wrapper.find('label[for="select-item-1-' + wrapper.vm._uid + '"]').trigger('click')
		var selectedItems = wrapper.vm.selectedItems
		var allSelected = wrapper.vm.allVisibleItemsSelected
		expect(selectedItems.length).toBe(1)
		wrapper.find('label[for="select-item-1-' + wrapper.vm._uid + '"]').trigger('click')
		selectedItems = wrapper.vm.selectedItems
		allSelected = wrapper.vm.allVisibleItemsSelected
		expect(selectedItems.length).toBe(0)
		expect(allSelected).toBe(false)
	})
})
