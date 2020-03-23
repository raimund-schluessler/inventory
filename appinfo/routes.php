<?php
/**
* Nextcloud - Inventory
*
* @author Raimund Schlüßler
* @copyright 2020 Raimund Schlüßler raimund.schluessler@mailbox.org
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
		['name' => 'page#index',			'url' => '/',											'verb' => 'GET'],
		['name' => 'items#getAll',			'url' => '/items',										'verb' => 'GET'],
		['name' => 'items#getByPath',		'url' => '/items',										'verb' => 'POST'],
		['name' => 'items#get',				'url' => '/item/{itemID}',								'verb' => 'GET'],

		['name' => 'attachment#getAll',		'url' => '/item/{itemID}/attachments',						'verb' => 'GET'],
		['name' => 'attachment#link',		'url' => '/item/{itemID}/attachment/link',					'verb' => 'POST'],
		['name' => 'attachment#create',		'url' => '/item/{itemID}/attachment/create',				'verb' => 'POST'],
		['name' => 'attachment#delete',		'url' => '/item/{itemID}/attachment/{attachmentID}/delete',	'verb' => 'DELETE'],
		['name' => 'attachment#unlink',		'url' => '/item/{itemID}/attachment/{attachmentID}/unlink',	'verb' => 'DELETE'],
		['name' => 'attachment#display',	'url' => '/item/{itemID}/attachment/{attachmentID}/display','verb' => 'GET'],
		['name' => 'attachment#update',		'url' => '/item/{itemID}/attachment/{attachmentID}/update',	'verb' => 'PUT'],
		// also allow to use POST for updates so we can properly access files when using application/x-www-form-urlencoded
		['name' => 'attachment#update',		'url' => '/item/{itemID}/attachment/{attachmentID}/update',	'verb' => 'POST'],

		['name' => 'attachment#getInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachments',						'verb' => 'GET'],
		['name' => 'attachment#linkInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachment/link',					'verb' => 'POST'],
		['name' => 'attachment#createInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachment/create',					'verb' => 'POST'],
		['name' => 'attachment#deleteInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/delete',	'verb' => 'DELETE'],
		['name' => 'attachment#unlinkInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/unlink',	'verb' => 'DELETE'],
		['name' => 'attachment#displayInstance','url' => '/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/display',	'verb' => 'GET'],
		['name' => 'attachment#updateInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/update',	'verb' => 'PUT'],
		// also allow to use POST for updates so we can properly access files when using application/x-www-form-urlencoded
		['name' => 'attachment#updateInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}/update',	'verb' => 'POST'],

		['name' => 'attachment#setFolder',		'url' => '/settings/attachmentFolder/set',				'verb' => 'POST'],

		['name' => 'attachment#uploadImage','url' => '/item/{itemID}/image/upload',					'verb' => 'POST'],

		['name' => 'items#getSub',			'url' => '/item/{itemID}/sub',							'verb' => 'GET'],
		['name' => 'items#getParent',		'url' => '/item/{itemID}/parent',						'verb' => 'GET'],
		['name' => 'items#getRelated',		'url' => '/item/{itemID}/related',						'verb' => 'GET'],
		['name' => 'items#getCandidates',	'url' => '/item/{itemID}/candidates/{relationType}',	'verb' => 'GET'],
		['name' => 'items#link',			'url' => '/item/{itemID}/link/{relationType}',			'verb' => 'POST'],
		['name' => 'items#unlink',			'url' => '/item/{itemID}/unlink/{relationType}',		'verb' => 'POST'],
		['name' => 'items#enlist',			'url' => '/item/add',									'verb' => 'POST'],
		['name' => 'items#delete',			'url' => '/item/{itemID}/delete',						'verb' => 'DELETE'],
		['name' => 'items#edit',			'url' => '/item/{itemID}/edit',							'verb' => 'PATCH'],
		['name' => 'items#move',			'url' => '/item/{itemID}/move',							'verb' => 'PATCH'],

		['name' => 'instance#add',			'url' => '/item/{itemID}/instance/add',					'verb' => 'POST'],
		['name' => 'instance#delete',		'url' => '/item/{itemID}/instance/{instanceID}/delete',	'verb' => 'DELETE'],
		['name' => 'instance#edit',			'url' => '/item/{itemID}/instance/{instanceID}/edit',	'verb' => 'PATCH'],
		['name' => 'instance#addUuid',		'url' => '/item/{itemID}/instance/{instanceID}/uuid/{uuid}',	'verb' => 'PUT'],
		['name' => 'instance#deleteUuid',	'url' => '/item/{itemID}/instance/{instanceID}/uuid/{uuid}',	'verb' => 'DELETE'],

		['name' => 'settings#get',			'url' => '/settings',									'verb' => 'GET'],
		['name' => 'settings#set',			'url' => '/settings/{setting}/{value}',					'verb' => 'POST'],

		['name' => 'folders#getByPath',		'url' => '/folders',									'verb' => 'POST'],
		['name' => 'folders#add',			'url' => '/folders/add',								'verb' => 'POST'],
		['name' => 'folders#delete',		'url' => '/folders/{folderID}/delete',					'verb' => 'DELETE'],
		['name' => 'folders#rename',		'url' => '/folders/{folderID}/rename',					'verb' => 'PATCH'],
		['name' => 'folders#move',			'url' => '/folders/{folderID}/move',					'verb' => 'PATCH'],

		['name' => 'search#find',			'url' => '/search',										'verb' => 'POST'],
	]
];
