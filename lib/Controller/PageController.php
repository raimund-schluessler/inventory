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

namespace OCA\Inventory\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FeaturePolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\IRequest;

/**
 * Controller class for main page.
 */
class PageController extends Controller {
	private $userId;

	/**
	 * @var IInitialStateService
	 */
	private $initialStateService;

	/**
	 * @param string $AppName
	 * @param IConfig $Config
	 * @param IInitialStateService $initialStateService
	 */
	public function __construct($AppName, IRequest $request, $UserId, IConfig $Config, IInitialStateService $initialStateService) {
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
		$this->config = $Config;
		$this->initialStateService = $initialStateService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$attachmentFolder = $this->config->getUserValue($this->userId, $this->appName, 'attachmentFolder', '/inventory');
		$this->initialStateService->provideInitialState(
			$this->appName,
			'attachmentFolder', $attachmentFolder
		);

		$response = new TemplateResponse('inventory', 'main');

		$csp = new ContentSecurityPolicy();
		$csp->allowEvalScript(true);
		// Needed to get https://github.com/gruhn/vue-qrcode-reader to work.
		// $csp->allowEvalWasm(true);
		$response->setContentSecurityPolicy($csp);

		$featurePolicy = new FeaturePolicy();
		$featurePolicy->addAllowedCameraDomain('\'self\'');
		$response->setFeaturePolicy($featurePolicy);

		return $response;
	}
}
