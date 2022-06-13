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
.tags li {
	box-sizing: border-box;
	background-color: var(--color-background-darker);
	border: 1px solid var(--color-border-dark);
	border-radius: 2px;
	color: var(--color-text-light);
	float: left;
	font-weight: bold;
	margin-right: 8px;
	padding: 2px 5px;
	min-height: 22px;
	line-height: 15px;
	height: 15px;

	&:last-child {
		margin-right: 0;
	}
}
</style>
