import store from '../../../src/store/store.js'
import Item from '../../../src/models/item.js'

const itemsData = [
	{
		id: 1,
		name: 'Item1',
		maker: 'Maker1',
		comment: 'Comment1',
		iconurl: 'icon.svg',
		tags: [
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
		tags: [
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
		tags: [
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

export { store }
