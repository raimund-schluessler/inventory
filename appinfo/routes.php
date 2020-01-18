<?php
/**
* Nextcloud - Inventory
*
* @author Raimund Schlüßler
* @copyright 2017 Raimund Schlüßler raimund.schluessler@mailbox.org
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
		['name' => 'attachment#getAll',		'url' => '/item/{itemID}/attachments',					'verb' => 'GET'],
		['name' => 'attachment#getInstance','url' => '/item/{itemID}/instance/{instanceID}/attachments',	'verb' => 'GET'],
		['name' => 'attachment#display',	'url' => '/item/{itemID}/attachment/{attachmentID}',	'verb' => 'GET'],
		['name' => 'attachment#displayInstance',	'url' => '/item/{itemID}/instance/{instanceID}/attachment/{attachmentID}',	'verb' => 'GET'],
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
		['name' => 'folders#edit',			'url' => '/folders/{folderID}/edit',					'verb' => 'PATCH'],
		['name' => 'folders#move',			'url' => '/folders/{folderID}/move',					'verb' => 'PATCH'],
	]
];
