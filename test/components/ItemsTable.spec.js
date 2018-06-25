import { shallowMount } from '@vue/test-utils'
import ItemsTable from '@/components/ItemsTable.vue'
import {OC} from '../OC.js'

global.OC = new OC();

var items = [
	{
		id: 1,
		name: "Item1",
		maker: "Maker1",
		comment: "Comment1",
		iconurl: 'icon.svg',
		categories: [
			{
				name: "Cat1",
				id: 1
			},
			{
				name: "Cat2",
				id: 2
			}
		]
	},
	{
		id: 2,
		name: "Item2",
		maker: "Maker1",
		comment: "Comment2 Maker1 is good",
		categories: [
			{
				name: "Cat1",
				id: 1
			}
		]
	},
	{
		id: 3,
		name: "Item3",
		maker: "Maker2",
		comment: "Comment3",
		categories: [
			{
				name: "Cat3",
				id: 3
			}
		]
	}
];

describe('ItemsTable.vue', () => {
	it('returns all items when search is empty', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				items: items,
				showDropdown: false,
				searchString: ''
			},
			methods: {
				t: function (app, string) {
					return string;
				}
			}
		});
		var itemsFound = wrapper.vm.filteredItems;
		expect(itemsFound.length).toBe(3);
	})
	
	it('finds items when searching with text in categories "Cat1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'Cat1'
			},
			methods: {
				t: function (app, string) {
					return string;
				}
			}
		});
		var itemsFound = wrapper.vm.filteredItems;
		expect(itemsFound.length).toBe(2);
	})
	
	it('returns filtered items when search is "Maker1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'Maker1'
			},
			methods: {
				t: function (app, string) {
					return string;
				}
			}
		});
		var itemsFound = wrapper.vm.filteredItems;
		expect(itemsFound.length).toBe(2);
	})
	
	it('searches in categories "categories:Cat1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'categories:Cat1'
			},
			methods: {
				t: function (app, string) {
					return string;
				}
			}
		});
		var itemsFound = wrapper.vm.filteredItems;
		expect(itemsFound.length).toBe(2);
	})
	
	it('searches only in given keywords "comment:Maker1"', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'comment:Maker1'
			},
			methods: {
				t: function (app, string) {
					return string;
				}
			}
		});
		var itemsFound = wrapper.vm.filteredItems;
		expect(itemsFound.length).toBe(1);
	})
	
	it('handles if item has not entry with given keyword "itemNumber:42"', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				items: items,
				showDropdown: false,
				searchString: 'itemNumber:42'
			},
			methods: {
				t: function (app, string) {
					return string;
				}
			}
		});
		var itemsFound = wrapper.vm.filteredItems;
		expect(itemsFound.length).toBe(0);
	})
	
	it('returns no items when items are empty', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				showDropdown: false,
				searchString: ''
			},
			methods: {
				t: function (app, string) {
					return string;
				}
			}
		});
		var itemsFound = wrapper.vm.filteredItems;
		expect(itemsFound).toBe(undefined);
	})
})
