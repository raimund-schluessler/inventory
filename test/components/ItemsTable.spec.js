import { shallowMount } from '@vue/test-utils'
import ItemsTable from '@/components/ItemsTable.vue'
import {OC} from '../OC.js'

global.OC = new OC();

var items = [
	{
		id: 1,
		name: "Item1",
		maker: "Maker1",
		comment: "Comment1"
	},
	{
		id: 2,
		name: "Item2",
		maker: "Maker1",
		comment: "Comment2"
	},
	{
		id: 3,
		name: "Item3",
		maker: "Maker2",
		comment: "Comment3"
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
	
	it('returns no items when items are empty', () => {
		const wrapper = shallowMount(ItemsTable, {
			propsData: {
				items: [],
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
		expect(itemsFound.length).toBe(0);
	})
})
