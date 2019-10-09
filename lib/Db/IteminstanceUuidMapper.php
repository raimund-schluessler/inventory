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

namespace OCA\Inventory\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;
use \OCA\Inventory\Db\IteminstanceUuid;

class IteminstanceUuidMapper extends Mapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'invtry_instance_uuids');
	}

	public function find($instanceId, $uuid, $uid) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_instance_uuids` ' .
			'WHERE `instanceid` = ? AND `uuid` = ? AND `uid` = ?';
		return $this->findEntities($sql, [$instanceId, $uuid, $uid]);
	}

	public function findByInstanceId($instanceId, $uid, $limit=null, $offset=null) {
		$sql = 'SELECT * FROM `*PREFIX*invtry_instance_uuids`' .
			'WHERE `instanceid` = ? AND `uid` = ?';
		return $this->findEntities($sql, [$instanceId, $uid], $limit, $offset);
	}

	public function add($params) {
		$uuid = new IteminstanceUuid();
		$uuid->setInstanceid($params['instanceid']);
		$uuid->setUuid($params['uuid']);
		$uuid->setUid($params['uid']);
		return $this->insert($uuid);
	}
}
