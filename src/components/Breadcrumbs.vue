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
	<div ref="container" class="breadcrumb">
		<div data-dir="#/folders/"
			class="crumb svg root"
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
		<div v-for="(folder, index) in folders1"
			:key="`f1${index}`"
			class="crumb svg folder"
			:class="{'hidden': isHidden(index)}"
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
		<div v-if="hiddenFolders.length" class="crumb svg exclude">
			<Actions class="dropdown"
				:force-menu="true"
				:open.sync="actionsOpen"
				draggable="false"
				@dragstart.native="dragstart"
				@dragover.native="dragOver($event)"
				@drop.native="dropOnActions"
				@dragenter.native="actionsOpen = true"
				@dragleave.native="closeActions">
				<ActionRouter
					v-for="(folder, index) in hiddenFolders"
					:key="`dropdown${index}`"
					:to="`/folders/${folderPath(hiddenIndices[index])}`"
					icon="icon-folder"
					class="crumb exclude"
					draggable="false"
					@dragstart.native="dragstart"
					@drop.native="dropped(hiddenIndices[index], $event)"
					@dragover.native="dragOver($event)"
					@dragenter.native="($event) => dragEnter(hiddenIndices[index], $event)"
					@dragleave.native="dragLeave">
					{{ folder }}
				</ActionRouter>
			</Actions>
		</div>
		<div v-for="(folder, index) in folders2"
			:key="`f2${index}`"
			class="crumb svg folder"
			:class="{'hidden': isHidden(index + folders1.length)}"
			draggable="false"
			@dragstart="dragstart"
			@drop="dropped(index + folders1.length, $event)"
			@dragover="dragOver($event)"
			@dragenter="($event) => dragEnter(index + folders1.length, $event)"
			@dragleave="dragLeave">
			<a :href="`#/folders/${folderPath(index + folders1.length)}`">
				<span>{{ folder }}</span>
			</a>
		</div>
		<div v-if="item" class="crumb svg item">
			<a :href="`#/folders/${(item.path) ? item.path + '/' : ''}item-${item.id}`">
				<span>{{ item.description }}</span>
			</a>
		</div>
	</div>
</template>

<script>
import debounce from 'debounce'
import { Actions } from '@nextcloud/vue/dist/Components/Actions'
import { ActionRouter } from '@nextcloud/vue/dist/Components/ActionRouter'

export default {
	components: {
		Actions,
		ActionRouter,
	},
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
	data: function() {
		return {
			hiddenIndices: [],
			actionsOpen: false,
		}
	},
	computed: {
		folders() {
			return (this.path === '') ? [] : this.path.split('/')
		},

		folders1() {
			if (this.hiddenIndices.length) {
				return this.folders.slice(0, Math.round(this.folders.length / 2))
			}
			return this.folders
		},

		folders2() {
			if (this.hiddenIndices.length) {
				return this.folders.slice(Math.round(this.folders.length / 2))
			}
			return []
		},

		hiddenFolders() {
			const folders = []
			for (let jj = 0; jj < this.hiddenIndices.length; jj++) {
				folders.push(this.folders[this.hiddenIndices[jj]])
			}
			return folders
		},

		crumbs() {
			return document.getElementsByClassName('crumb folder')
		},
	},
	watch: {
		path: function(val) {
			this.actionsOpen = false
			this.$nextTick(() => this.handleWindowResize())
		},
	},
	created() {
		window.addEventListener('resize', debounce(() => {
			this.handleWindowResize()
		}, 100))
	},
	mounted() {
		this.handleWindowResize()
	},
	beforeDestroy() {
		window.removeEventListener('resize', this.handleWindowResize)
	},
	methods: {
		closeActions(e) {
			// Get the correct element, the events are bound to the <a>
			if (e.target.closest) {
				const target = e.target.closest('.crumb .dropdown')
				// Don't do anything if we leave towards a child element.
				if (target.contains(e.relatedTarget)) {
					return
				}
				this.actionsOpen = false
			}
		},

		isHidden(index) {
			return this.hiddenIndices.includes(index)
		},

		handleWindowResize() {
			if (this.$refs.container) {
				const hiddenIndices = []
				const availableWidth = this.$refs.container.offsetWidth
				const totalWidth = this.getTotalWidth()
				let overflow = totalWidth - availableWidth
				// If we overflow, we have to take the action-item width into account as well.
				overflow += (overflow > 0) ? 51 : 0
				let i = 0
				const startIndex = ((this.crumbs.length % 2) ? this.crumbs.length + 1 : this.crumbs.length) / 2 - 1
				while (overflow > 0 && i < this.crumbs.length) {
					const currentIndex = startIndex - ((i % 2) ? i + 1 : i) / 2 * Math.pow(-1, i + (this.crumbs.length % 2))
					overflow -= this.getWidth(this.crumbs[currentIndex])
					hiddenIndices.push(currentIndex)
					i++
				}
				this.hiddenIndices = hiddenIndices.sort()
			}
		},

		getTotalWidth() {
			const crumbs = document.querySelectorAll('.crumb:not(.exclude)')
			return Array.from(crumbs).reduce((width, crumb) => width + this.getWidth(crumb), 0)
		},

		getWidth(el) {
			const hide = el.classList.contains('hidden')
			el.classList.remove('hidden')
			const w = el.offsetWidth
			if (hide) {
				el.classList.add('hidden')
			}
			return w
		},

		folderPath(index) {
			if (index === -1) {
				return '/'
			}
			return this.folders.slice(0, index + 1).join('/')
		},
		cancelEvent(e) {
			e.stopPropagation()
			e.preventDefault()
			return false
		},
		dropOnActions(e) {
			this.actionsOpen = false
			return this.cancelEvent(e)
		},
		dragstart(e) {
			return this.cancelEvent(e)
		},
		dropped(index, e) {
			this.cancelEvent(e)
			// If it is the last element in the path,
			// don't do anything
			if (index === (this.folders.length - 1)) {
				return
			}
			const newPath = (index === -1) ? '' : this.folderPath(index)
			this.$emit('dropped', newPath)
			this.actionsOpen = false
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
				const target = e.target.closest('.crumb')
				if (target.classList && target.classList.contains('crumb')) {
					const folders = document.querySelectorAll('.crumb')
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
				const target = e.target.closest('.crumb')
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
