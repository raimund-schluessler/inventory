<!--
Nextcloud - Inventory

@author Raimund Schlüßler
@copyright 2021 Raimund Schlüßler <raimund.schluessler@mailbox.org>

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
	<ul class="tags">
		<li v-for="tag in tags" :key="tag.id">
			<span>{{ tag.name }}</span>
		</li>
	</ul>
</template>

<script>
export default {
	props: {
		tags: {
			type: Array,
			default: () => [],
		},
	},
}
</script>

<style lang="scss" scoped>
.tags {
	display: flex;

	li {
		display: flex;
		align-items: center;
		background-color: var(--color-background-dark);
		border: var(--vs-selected-border-width) var(--vs-selected-border-style) var(--vs-selected-border-color);
		border-radius: var(--border-radius-large);
		color: var(--color-main-text);
		margin-right: var(--default-grid-baseline);
		padding: 0 .5em;
		min-height: 36px;

		&:last-child {
			margin-right: 0;
		}
	}
}
</style>
