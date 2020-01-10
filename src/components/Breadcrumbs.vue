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
		<div data-dir="/" class="crumb svg">
			<a href="#/folders/">
				<span class="icon icon-bw icon-items" />
			</a>
		</div>
		<div v-for="(folder, index) in folders" :key="index" class="crumb svg">
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
		}
	},
	computed: {
		folders() {
			return this.path.split('/')
		}
	},
	methods: {
		folderPath(index) {
			return this.folders.slice(0, index + 1).join('/')
		}
	}
}
</script>
