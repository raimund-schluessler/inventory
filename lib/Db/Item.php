<?php
/**
 * ownCloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2016 Raimund Schlüßler raimund.schluessler@googlemail.com
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

use OCP\AppFramework\Db\Entity;

class Item extends Entity {

	public $id;
	public $owner;
	public $name;
	public $maker;
	public $description;
	public $place;
	public $related;
	public $itemNumber;
	public $price;
	public $link;
	public $vendor;
	public $date;
	public $ean;
	public $count;
	public $details;
	public $comment;

	public function __construct() {
		// add types in constructor
		$this->addType('id', 'integer');
		$this->addType('owner', 'integer');
	}
}
