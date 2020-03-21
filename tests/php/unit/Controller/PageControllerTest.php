<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler <raimund.schluessler@mailbox.org>
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

namespace OCA\Inventory\Controller;

use OCP\AppFramework\Http\TemplateResponse;
use PHPUnit\Framework\TestCase as Base;


class PageControllerTest extends Base {

	private $controller;

	public function setUp(): void {
		$request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$config = $this->getMockBuilder('OCP\IConfig')->getMock();
		$initialState = $this->getMockBuilder('OCP\IInitialStateService')->getMock();

		$this->controller = new PageController(
			'inventory',
			$request,
			'admin',
			$config,
			$initialState
		);
	}


	public function testIndex() {
		$result = $this->controller->index();

		$this->assertEquals('main', $result->getTemplateName());
		$this->assertEquals('user', $result->getRenderAs());
		$this->assertTrue($result instanceof TemplateResponse);
	}
}
