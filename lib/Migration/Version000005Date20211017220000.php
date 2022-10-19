<?php

declare(strict_types=1);

namespace OCA\Inventory\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000005Date20211017220000 extends SimpleMigrationStep {
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
			
			if (!$table->hasColumn('description')) {
				$table->addColumn('description', Types::STRING, [
					'notnull' => false,
				]);
			}
		}

		if (!$schema->hasTable('invtry_places_uuids')) {
			$table = $schema->createTable('invtry_places_uuids');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('placeid', Types::BIGINT, [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uuid', Types::STRING, [
				'notnull' => true,
				'length' => 36,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	}
}
