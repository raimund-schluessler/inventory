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
				@update:model-value="navigate" />
		</div>
		<EntityTable v-if="selectedTags.length" :items="items" />
		<NcEmptyContent v-else :name="loading ? t('inventory', 'Loading available tags.') : t('inventory', 'Please select a tag to search for.')">
			<template #icon>
				<NcLoadingIcon v-if="loading" />
				<TagMultiple v-else :size="50" />
			</template>
		</NcEmptyContent>
	</div>
</template>

<script>
import EntityTable from '../../components/EntityTable/EntityTable.vue'

import { showError } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'
import {
	NcEmptyContent,
	NcLoadingIcon,
	NcSelect,
} from '@nextcloud/vue'

import TagMultiple from 'vue-material-design-icons/TagMultiple.vue'

import { mapGetters, mapActions } from 'vuex'

export default {
	components: {
		NcEmptyContent,
		NcLoadingIcon,
		NcSelect,
		EntityTable,
		TagMultiple,
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
			tags: 'getSortedTags',
			loading: 'loadingTags',
		}),
	},
	async created() {
		await this.loadTags()
		this.setTagIds(this.path)
		this.loadItems()
	},
	beforeUnmount() {
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
			const selectedTags = []
			this.tagIds = (path?.split('/') || [])
				.map(id => parseInt(id))
				.filter(id => {
					if (isNaN(id)) {
						return false
					}
					// We check if a tag with this id exists, and push it to the selected tags array
					const tag = this.tags.find(tag => tag.id === id)
					return tag && selectedTags.push(tag)
				})
			this.selectedTags = selectedTags
		},

		async loadTags() {
			// Abort possibly running requests from previous paths
			this.abortControllerTags?.abort()
			this.abortControllerTags = new AbortController()
			await this.getTags({ signal: this.abortControllerTags.signal })
		},

		async loadItems() {
			// Abort possibly running requests from previous paths
			this.abortController?.abort()
			this.abortController = new AbortController()
			try {
				await this.getItemsByTags({ tagIds: this.tagIds, signal: this.abortController.signal })
			} catch (error) {
				if (error?.response?.status === 500) {
					showError(t('inventory', 'Loading the items failed.'))
				}
			}
		},
	},
}
</script>
