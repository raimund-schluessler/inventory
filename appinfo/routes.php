<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2023 Raimund Schlüßler raimund.schluessler@mailbox.org
 *
 * @author Julius Härtl
 * @copyright 2018 Julius Härtl <jus@bitgrid.net>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
return [
	'routes' => [
		['name' => 'page#index',			'url' => '/',						'verb' => 'GET'],
		['name' => 'page#index',			'url' => '/folders',				'verb' => 'GET', 'postfix' => 'view.folders'],
		['name' => 'page#index',			'url' => '/folders/',				'verb' => 'GET', 'postfix' => 'view.folders./'],
		[
			'name' => 'page#index',
			'url' => '/folders/{path}',
			'verb' => 'GET',
			'postfix' => 'view.folders.path',
			'requirements' => array('path' => '.+')
		],
		['name' => 'page#index',			'url' => '/places',					'verb' => 'GET', 'postfix' => 'view.places'],
		['name' => 'page#index',			'url' => '/places/',				'verb' => 'GET', 'postfix' => 'view.places./'],
		[
			'name' => 'page#index',
			'url' => '/places/{path}',
			'verb' => 'GET',
			'postfix' => 'view.places.path',
			'requirements' => array('path' => '.+')
		],
		['name' => 'page#index',			'url' => '/tags',					'verb' => 'GET', 'postfix' => 'view.tags'],
		['name' => 'page#index',			'url' => '/tags/',					'verb' => 'GET', 'postfix' => 'view.tags./'],
		[
			'name' => 'page#index',
			'url' => '/tags/{tags}',
			'verb' => 'GET',
			'postfix' => 'view.tags.tags',
			'requirements' => array('tags' => '.+')
		],
		['name' => 'items#getAll',			'url' => '/api/v1/items',			'verb' => 'GET'],
		['name' => 'items#getByFolder',		'url' => '/api/v1/items/folder',	'verb' => 'POST'],
		['name' => 'items#getByPlace',		'url' => '/api/v1/items/place',		'verb' => 'POST'],
		['name' => 'items#getByTags',		'url' => '/api/v1/items/tags',		'verb' => 'POST'],
		['name' => 'items#get',				'url' => '/api/v1/item/{itemID}',	'verb' => 'GET'],

		['name' => 'items#getSub',			'url' => '/api/v1/item/{itemID}/sub',							'verb' => 'GET'],
		['name' => 'items#getParent',		'url' => '/api/v1/item/{itemID}/parent',						'verb' => 'GET'],
		['name' => 'items#getRelated',		'url' => '/api/v1/item/{itemID}/related',						'verb' => 'GET'],
		['name' => 'items#getCandidates',	'url' => '/api/v1/item/{itemID}/candidates/{relationType}',		'verb' => 'GET'],
		['name' => 'items#link',			'url' => '/api/v1/item/{itemID}/link/{relationType}',			'verb' => 'POST'],
		['name' => 'items#unlink',			'url' => '/api/v1/item/{itemID}/unlink/{relationType}',			'verb' => 'POST'],
		['name' => 'items#enlist',			'url' => '/api/v1/item/add',									'verb' => 'POST'],
		['name' => 'items#delete',			'url' => '/api/v1/item/{itemID}/delete',						'verb' => 'DELETE'],
		['name' => 'items#edit',			'url' => '/api/v1/item/{itemID}/edit',							'verb' => 'PATCH'],
		['name' => 'items#move',			'url' => '/api/v1/item/{itemID}/move',							'verb' => 'PATCH'],
		['name' => 'items#moveByUuid',		'url' => '/api/v1/item/move',									'verb' => 'PATCH'],
		['name' => 'items#moveInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/move',	'verb' => 'PATCH'],

		['name' => 'attachment#getAll',		'url' => '/api/v1/item/{itemID}/attachments',					'verb' => 'GET'],
		['name' => 'attachment#link',		'url' => '/api/v1/item/{itemID}/attachment/link',				'verb' => 'POST'],
		['name' => 'attachment#create',		'url' => '/api/v1/item/{itemID}/attachment/create',				'verb' => 'POST'],
		['name' => 'attachment#delete',		'url' => '/api/v1/item/{itemID}/attachment/{attachmentID}/delete',	'verb' => 'DELETE'],
		['name' => 'attachment#unlink',		'url' => '/api/v1/item/{itemID}/attachment/{attachmentID}/unlink',	'verb' => 'DELETE'],
		['name' => 'attachment#display',	'url' => '/api/v1/item/{itemID}/attachment/{attachmentID}/display',	'verb' => 'GET'],
		['name' => 'attachment#update',		'url' => '/api/v1/item/{itemID}/attachment/{attachmentID}/update',	'verb' => 'PUT'],
		// also allow to use POST for updates so we can properly access files when using application/x-www-form-urlencoded
		['name' => 'attachment#update',		'url' => '/api/v1/item/{itemID}/attachment/{attachmentID}/update',	'verb' => 'POST'],

		['name' => 'attachment#getInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachments',							'verb' => 'GET'],
		['name' => 'attachment#linkInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachment/link',						'verb' => 'POST'],
		['name' => 'attachment#createInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachment/create',					'verb' => 'POST'],
		['name' => 'attachment#deleteInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/delete',	'verb' => 'DELETE'],
		['name' => 'attachment#unlinkInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/unlink',	'verb' => 'DELETE'],
		['name' => 'attachment#displayInstance','url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/display',	'verb' => 'GET'],
		['name' => 'attachment#updateInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/update',	'verb' => 'PUT'],
		// also allow to use POST for updates so we can properly access files when using application/x-www-form-urlencoded
		['name' => 'attachment#updateInstance',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/update',	'verb' => 'POST'],

		['name' => 'attachment#setFolder',		'url' => '/api/v1/settings/attachmentFolder/set',				'verb' => 'POST'],

		['name' => 'attachment#uploadImage','url' => '/api/v1/item/{itemID}/image/upload',						'verb' => 'POST'],

		['name' => 'instance#add',			'url' => '/api/v1/item/{itemID}/instance/add',						'verb' => 'POST'],
		['name' => 'instance#delete',		'url' => '/api/v1/item/{itemID}/instance/{instanceID}/delete',		'verb' => 'DELETE'],
		['name' => 'instance#edit',			'url' => '/api/v1/item/{itemID}/instance/{instanceID}/edit',		'verb' => 'PATCH'],
		['name' => 'instance#addUuid',		'url' => '/api/v1/item/{itemID}/instance/{instanceID}/uuid/{uuid}',	'verb' => 'PUT'],
		['name' => 'instance#deleteUuid',	'url' => '/api/v1/item/{itemID}/instance/{instanceID}/uuid/{uuid}',	'verb' => 'DELETE'],

		['name' => 'settings#get',			'url' => '/api/v1/settings',										'verb' => 'GET'],
		['name' => 'settings#set',			'url' => '/api/v1/settings/{setting}/{value}',						'verb' => 'POST'],

		['name' => 'folders#getByFolder',	'url' => '/api/v1/folders',											'verb' => 'POST'],
		['name' => 'folders#add',			'url' => '/api/v1/folders/add',										'verb' => 'POST'],
		['name' => 'folders#delete',		'url' => '/api/v1/folders/{folderID}/delete',						'verb' => 'DELETE'],
		['name' => 'folders#rename',		'url' => '/api/v1/folders/{folderID}/rename',						'verb' => 'PATCH'],
		['name' => 'folders#move',			'url' => '/api/v1/folders/{folderID}/move',							'verb' => 'PATCH'],

		['name' => 'places#getByPlace',		'url' => '/api/v1/places',											'verb' => 'POST'],
		['name' => 'places#add',			'url' => '/api/v1/places/add',										'verb' => 'POST'],
		['name' => 'places#delete',			'url' => '/api/v1/places/{placeID}/delete',							'verb' => 'DELETE'],
		['name' => 'places#rename',			'url' => '/api/v1/places/{placeID}/rename',							'verb' => 'PATCH'],
		['name' => 'places#move',			'url' => '/api/v1/places/{placeID}/move',							'verb' => 'PATCH'],

		['name' => 'places#get',			'url' => '/api/v1/place',												'verb' => 'POST'],
		['name' => 'places#addUuid',		'url' => '/api/v1/place/{placeID}/uuid/add',						'verb' => 'POST'],
		['name' => 'places#deleteUuid',		'url' => '/api/v1/place/{placeID}/uuid/delete',						'verb' => 'POST'],
		['name' => 'places#setDescription',	'url' => '/api/v1/place/{placeID}/description',						'verb' => 'POST'],

		['name' => 'search#find',			'url' => '/api/v1/search',											'verb' => 'POST'],

		['name' => 'tags#getAll',			'url' => '/api/v1/tags',											'verb' => 'POST'],
	]
];
