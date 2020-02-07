import { createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'

import store from 'Store/store'
import Item from 'Models/item'

const localVue = createLocalVue()
localVue.use(Vuex)

const itemsData = [
	{
		id: 1,
		name: 'Item1',
		maker: 'Maker1',
		comment: 'Comment1',
		iconurl: 'icon.svg',
		categories: [
			{
				name: 'Cat1',
				id: 1,
			},
			{
				name: 'Cat2',
				id: 2,
			},
		],
	},
	{
		id: 2,
		name: 'Item2',
		maker: 'Maker1',
		comment: 'Comment2 Maker1 is good',
		categories: [
			{
				name: 'Cat1',
				id: 1,
			},
		],
	},
	{
		id: 3,
		name: 'Item3',
		maker: 'Maker2',
		comment: 'Comment3',
		categories: [
			{
				name: 'Cat3',
				id: 3,
			},
		],
	},
]

const items = itemsData.map(item => {
	return new Item(item)
})

store.commit('addItems', items)

export { store, localVue }
