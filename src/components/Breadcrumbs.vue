<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2017 Raimund Schlüßler <raimund.schluessler@mailbox.org>

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library.  If not, see <http://www.gnu.org/licenses/>.

-->

<template>
	<div class="breadcrumb">
		<div data-dir="#/folders/"
			class="crumb svg"
			draggable="false"
			@dragstart="dragstart"
			@drop="dropped(-1, $event)"
			@dragover="dragOver"
			@dragenter="($event) => dragEnter(-1, $event)"
			@dragleave="dragLeave">
			<a href="#/folders/">
				<span class="icon icon-bw icon-items" />
			</a>
		</div>
		<div v-for="(folder, index) in folders"
			:key="index"
			class="crumb svg"
			draggable="false"
			@dragstart="dragstart"
			@drop="dropped(index, $event)"
			@dragover="dragOver($event)"
			@dragenter="($event) => dragEnter(index, $event)"
			@dragleave="dragLeave">
			<a :href="`#/folders/${folderPath(index)}`">
				<span>{{ folder }}</span>
			</a>
		</div>
		<div v-if="item" class="crumb svg">
			<a :href="`#/folders/${(item.path) ? item.path + '/' : ''}item-${item.id}`">
				<span>{{ item.description }}</span>
			</a>
		</div>
	</div>
</template>

<script>
import Item from '../models/item.js'
import { mapActions, mapGetters } from 'vuex'

export default {
	props: {
		path: {
			type: String,
			default: '',
		},
		item: {
			type: Object,
			required: false,
			default: undefined,
		},
	},
	computed: {
		...mapGetters({
			draggedEntities: 'getDraggedEntities',
		}),

		folders() {
			return (this.path === '') ? [] : this.path.split('/')
		},
	},
	methods: {
		...mapActions([
			'moveItem',
			'moveFolder',
		]),

		folderPath(index) {
			if (index === -1) {
				return '/'
			}
			return this.folders.slice(0, index + 1).join('/')
		},

		dragstart(e) {
			e.stopPropagation()
			e.preventDefault()
			return false
		},
		dropped(index, e) {
			const entities = this.draggedEntities
			e.stopPropagation()
			e.preventDefault()
			// If it is the last element in the path,
			// don't do anything
			if (index === (this.folders.length - 1)) {
				return
			}
			const newPath = (index === -1) ? '' : this.folderPath(index)
			entities.forEach((entity) => {
				if (entity instanceof Item) {
					this.moveItem({ itemID: entity.id, newPath })
				} else {
					this.moveFolder({ folderID: entity.id, newPath })
				}
			})
			return false
		},
		dragOver(e) {
			if (e.preventDefault) {
				e.preventDefault()
			}
			return false
		},
		dragEnter(index, e) {
			// If it is the last element in the path,
			// don't do anything
			if (index === (this.folders.length - 1)) {
				return
			}
			// Get the correct element, in case we hover a child.
			if (e.target.closest) {
				const target = e.target.closest('div.crumb')
				if (target.classList && target.classList.contains('crumb')) {
					const folders = document.querySelectorAll('div.crumb')
					folders.forEach((f) => { f.classList.remove('over') })
					target.classList.add('over')
				}
			}
		},
		dragLeave(e) {
			// Don't do anything if we leave towards a child element.
			if (e.target.contains(e.relatedTarget)) {
				return
			}
			// Get the correct element, in case we leave directly from a child.
			if (e.target.closest) {
				const target = e.target.closest('div.crumb')
				if (target.contains(e.relatedTarget)) {
					return
				}
				if (target.classList && target.classList.contains('crumb')) {
					target.classList.remove('over')
				}
			}
		},
	},
}
</script>
