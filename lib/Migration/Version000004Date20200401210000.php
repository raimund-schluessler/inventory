<?php

declare(strict_types=1);

namespace OCA\Inventory\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000004Date20200401210000 extends SimpleMigrationStep {

	/** @var IDBConnection */
	protected $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('invtry_places')) {
			$table = $schema->getTable('invtry_places');
			
			if (!$table->hasColumn('path')) {
				$table->addColumn('path', Types::STRING, [
					'notnull' => false,
					'length' => 4000,
					'default' => '',
				]);
			}
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
		// Set every parentId which is NULL to -1
		$qb = $this->db->getQueryBuilder();
		$qb->update('*PREFIX*invtry_places')
			->set('parentid', $qb->createNamedParameter(-1, IQueryBuilder::PARAM_INT))
			->where(
				$qb->expr()->isNull('parentid')
			);
		$qb->execute();

		// Update the full path for all places
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from('*PREFIX*invtry_places');
		$places = $qb->execute();
		foreach ($places as $place) {
			// Construct the full path
			$path = $place['name'];
			$parent = $place;
			while ($parent = $this->getPlaceById($parent['parentid'])) {
				$path = $parent['name'].'/'.$path;
			}
			// Update the place with the full path
			$qb->update('*PREFIX*invtry_places')
				->set('path', $qb->createNamedParameter($path, IQueryBuilder::PARAM_STR))
				->where(
					$qb->expr()->eq('id', $qb->createNamedParameter($place['id'], IQueryBuilder::PARAM_INT))
				);
			$qb->execute();
		}

		// Set every placeId which is NULL to -1
		$qb = $this->db->getQueryBuilder();
		$qb->update('*PREFIX*invtry_item_instances')
			->set('placeid', $qb->createNamedParameter(-1, IQueryBuilder::PARAM_INT))
			->where(
				$qb->expr()->isNull('placeid')
			);
		$qb->execute();
	}

	/**
	 * Finds a place by id
	 *
	 * @param Integer $placeId
	 * @return Array The place
	 */
	private function getPlaceById($placeId) {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
		->from('*PREFIX*invtry_places')
		->where(
			$qb->expr()->eq('id', $qb->createNamedParameter($placeId, IQueryBuilder::PARAM_INT))
		);
		return $qb->execute()->fetch();
	}
}
