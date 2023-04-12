<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2023 Raimund Schlüßler <raimund.schluessler@mailbox.org>

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
License as published by the Free Software Foundation; either
version 3 of the License, or any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU AFFERO GENERAL PUBLIC LICENSE for more details.

You should have received a copy of the GNU Affero General Public
License along with this library. If not, see <http://www.gnu.org/licenses/>.

-->

<template>
	<div>
		<div id="controls">
			<NcSelect v-model="selectedTags"
				:options="tags"
				label="name"
				:multiple="true"
				:loading="loading"
				@input="navigate" />
		</div>
		<EntityTable v-if="selectedTags.length" :items="items" />
		<NcEmptyContent v-else :title="loading ? t('inventory', 'Loading available tags.') : t('inventory', 'Please select a tag to search for.')">
			<template #icon>
				<NcLoadingIcon v-if="loading" />
				<Tag v-else :size="50" />
			</template>
		</NcEmptyContent>
	</div>
</template>

<script>
import EntityTable from '../../components/EntityTable/EntityTable.vue'

import { translate as t } from '@nextcloud/l10n'
import {
	NcEmptyContent,
	NcLoadingIcon,
	NcSelect,
} from '@nextcloud/vue'

import Tag from 'vue-material-design-icons/Tag.vue'

import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		NcEmptyContent,
		NcLoadingIcon,
		NcSelect,
		EntityTable,
		Tag,
	},
	beforeRouteUpdate(to, from, next) {
		if (to.params.path !== from.params.path) {
			this.setTagIds(to.params.path)
			this.loadItems()
		}
		next()
	},
	props: {
		path: {
			type: String,
			default: '',
		},
	},
	data() {
		return {
			abortController: null,
			abortControllerTags: null,
			selectedTags: [],
			tagIds: [],
		}
	},
	computed: {
		...mapGetters({
			items: 'getAllItems',
			tags: 'getTags',
			loading: 'loadingTags',
		}),
	},
	async created() {
		await this.loadTags()
		this.setTagIds(this.path)
		this.loadItems()
	},
	beforeDestroy() {
		// Abort possibly running requests
		this.abortController?.abort()
		this.abortControllerTags?.abort()
	},
	methods: {
		t,

		...mapActions([
			'getTags',
			'getItemsByTags',
		]),

		navigate() {
			this.$router.push(`/tags/${this.selectedTags.map(tag => tag.id).join('/')}`)
		},

		setTagIds(path) {
			this.tagIds = path.split('/').map(id => parseInt(id)).filter(id => !isNaN(id) && this.tags.find(tag => tag.id === id))
			this.selectedTags = this.tagIds.map(id => this.tags.find(tag2 => tag2.id === id))
		},

		async loadTags() {
			// Abort possibly running requests from previous paths
			this.abortControllerTags?.abort()
			this.abortControllerTags = new AbortController()
			await this.getTags({ signal: this.abortControllerTags.signal })
		},

		loadItems() {
			// Abort possibly running requests from previous paths
			this.abortController?.abort()
			this.abortController = new AbortController()
			this.getItemsByTags({ tagIds: this.tagIds, signal: this.abortController.signal })
		},
	},
}
</script>
