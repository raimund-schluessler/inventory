import { mount, shallowMount, RouterLinkStub } from '@vue/test-utils'
import EntityTable from 'Components/EntityTable/EntityTable.vue'

import { store, localVue } from '../../setupStore.js'

const items = store.getters.getAllItems

describe('EntityTable.vue', () => {
	'use strict'

	it('returns all items when search is empty', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', '')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(3)
	})

	it('finds items when searching with text in tags "Cat1"', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'Cat1')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(2)
	})

	it('returns filtered items when search is "Maker1"', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'Maker1')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(2)
	})

	it('searches in tags "tags:Cat1"', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'tags:Cat1')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(2)
	})

	it('searches only in given keywords "comment:Maker1"', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'comment:Maker1')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(1)
	})

	it('handles if item has no entry with given keyword "itemNumber:42"', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'itemNumber:42')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(0)
	})

	it('does not fail when no items are passed', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', '')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(0)
	})

	it('selects item when clicked', () => {
		const wrapper = mount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
			stubs: {
				RouterLink: RouterLinkStub,
			},
		})
		store.commit('setSearchString', '')
		wrapper.find('label[for="select-item-1-' + wrapper.vm.compUid + '"]').trigger('click')
		wrapper.find('label[for="select-item-2-' + wrapper.vm.compUid + '"]').trigger('click')
		wrapper.find('label[for="select-item-3-' + wrapper.vm.compUid + '"]').trigger('click')
		const itemsFound = wrapper.vm.selectedItems
		const allSelected = wrapper.vm.allEntitiesSelected
		expect(itemsFound.length).toBe(3)
		expect(allSelected).toBe(true)
	})

	it('selects all items on checkbox click and unselects on second click', () => {
		const wrapper = shallowMount(EntityTable, {
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
		})
		store.commit('setSearchString', '')
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
			localVue,
			store,
			propsData: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
			stubs: {
				RouterLink: RouterLinkStub,
			},
		})
		store.commit('setSearchString', '')
		wrapper.find('label[for="select-item-1-' + wrapper.vm.compUid + '"]').trigger('click')
		let selectedItems = wrapper.vm.selectedItems
		let allSelected = wrapper.vm.allEntitiesSelected
		expect(selectedItems.length).toBe(1)
		expect(allSelected).toBe(false)
		wrapper.find('label[for="select-item-1-' + wrapper.vm.compUid + '"]').trigger('click')
		selectedItems = wrapper.vm.selectedItems
		allSelected = wrapper.vm.allEntitiesSelected
		expect(selectedItems.length).toBe(0)
		expect(allSelected).toBe(false)
	})
})
