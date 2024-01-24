import { mount, shallowMount, RouterLinkStub } from '@vue/test-utils'
import EntityTable from '../../../../../src/components/EntityTable/EntityTable.vue'
import { describe, expect, it } from 'vitest'

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
				filterOnly: true,
			},
		})
		store.commit('setSearchString', '')
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
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'Cat1')
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
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'Maker1')
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
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'tags:Cat1')
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
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'comment:Maker1')
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
				filterOnly: true,
			},
		})
		store.commit('setSearchString', 'itemNumber:42')
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
				filterOnly: true,
			},
		})
		store.commit('setSearchString', '')
		const itemsFound = wrapper.vm.filteredEntities
		expect(itemsFound.length).toBe(0)
	})

	it('selects item when clicked', () => {
		const wrapper = mount(EntityTable, {
			props: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
			global: {
				plugins: [store],
				stubs: {
					RouterLink: RouterLinkStub,
				},
			},
		})
		store.commit('setSearchString', '')
		const itemCheckbox = wrapper.findAll('[data-testid="item-checkbox"] input')
		itemCheckbox.at(0).setChecked()
		itemCheckbox.at(1).setChecked()
		itemCheckbox.at(2).setChecked()
		const itemsFound = wrapper.vm.selectedItems
		const allSelected = wrapper.vm.allEntitiesSelected
		expect(itemsFound.length).toBe(3)
		expect(allSelected).toBe(true)
	})

	it('selects all items on checkbox click and unselects on second click', () => {
		const wrapper = mount(EntityTable, {
			props: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
			global: {
				plugins: [store],
				stubs: {
					RouterLink: RouterLinkStub,
				},
			},
		})
		store.commit('setSearchString', '')
		const selectAll = wrapper.find('[data-testid="select-all-checkbox"] input')
		selectAll.setChecked()
		let itemsFound = wrapper.vm.selectedItems
		let allSelected = wrapper.vm.allEntitiesSelected
		expect(itemsFound.length).toBe(3)
		expect(allSelected).toBe(true)
		selectAll.setChecked(false)
		itemsFound = wrapper.vm.selectedItems
		allSelected = wrapper.vm.allEntitiesSelected
		expect(itemsFound.length).toBe(0)
		expect(allSelected).toBe(false)
	})

	it('selects item when clicked on label', () => {
		const wrapper = mount(EntityTable, {
			props: {
				items,
				showDropdown: false,
				filterOnly: true,
			},
			global: {
				plugins: [store],
				stubs: {
					RouterLink: RouterLinkStub,
				},
			},
		})
		store.commit('setSearchString', '')
		const itemCheckbox = wrapper.find('[data-testid="item-checkbox"] input')
		itemCheckbox.setChecked()
		let selectedItems = wrapper.vm.selectedItems
		let allSelected = wrapper.vm.allEntitiesSelected
		expect(selectedItems.length).toBe(1)
		expect(allSelected).toBe(false)
		itemCheckbox.setChecked(false)
		selectedItems = wrapper.vm.selectedItems
		allSelected = wrapper.vm.allEntitiesSelected
		expect(selectedItems.length).toBe(0)
		expect(allSelected).toBe(false)
	})
})
