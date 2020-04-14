<?php
/**
 * Nextcloud - Inventory
 *
 * @author Raimund Schlüßler
 * @copyright 2019 Raimund Schlüßler raimund.schluessler@mailbox.org
 *
 * @author Julius Härtl <jus@bitgrid.net>
 * @copyright 2016 Julius Härtl jus@bitgrid.net
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

namespace OCA\Inventory\Middleware;

use OCP\AppFramework\Middleware;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http\JSONResponse;
use OCA\Inventory\StatusException;
use OCA\Inventory\Exceptions\ConflictException;

class ExceptionMiddleware extends Middleware {

	/**
	 * ExceptionMiddleware constructor.
	 */
	public function __construct() {
	}

	/**
	 * Return JSON error response in case of an exception
	 *
	 * @param \OCP\AppFramework\Controller $controller
	 * @param string $methodName
	 * @param \Exception $exception
	 * @return JSONResponse
	 * @throws \Exception
	 */
	public function afterException($controller, $methodName, \Exception $exception) {
		if ($exception instanceof ConflictException) {
			return new JSONResponse([
				'status' => $exception->getStatus(),
				'message' => $exception->getMessage(),
				'data' => $exception->getData(),
			], $exception->getStatus());
		}

		if ($exception instanceof StatusException) {
			return new JSONResponse([
				'status' => $exception->getStatus(),
				'message' => $exception->getMessage()
			], $exception->getStatus());
		}

		// Uncaught DoesNotExistExceptions will be thrown when the main entity is not found.
		// We return a 403 so we don't leak information over existing entries.
		// TODO: At some point those should be properly caught in the service classes.
		if ($exception instanceof DoesNotExistException) {
			return new JSONResponse([
				'status' => 403,
				'message' => 'Permission denied.'
			], 403);
		}

		throw $exception;
	}
}
