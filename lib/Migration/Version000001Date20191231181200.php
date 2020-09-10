<?php

declare(strict_types=1);

namespace OCA\Inventory\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000001Date20191231181200 extends SimpleMigrationStep {

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

		if (!$schema->hasTable('invtry_folders')) {
			$table = $schema->createTable('invtry_folders');
			$table->addColumn('id', Type::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uid', Type::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', Type::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('path', Type::STRING, [
				'notnull' => false,
				'length' => 4000,
			]);
			$table->addColumn('parentid', Type::BIGINT, [
				'notnull' => false,
				'length' => 8,
			]);
			$table->setPrimaryKey(['id']);
		}

		if ($schema->hasTable('invtry_items')) {
			$table = $schema->getTable('invtry_items');
			
			if (!$table->hasColumn('folderid')) {
				$table->addColumn('folderid', Type::BIGINT, [
					'notnull' => true,
					'length' => 8,
					'default' => '-1',
				]);
			}
			if (!$table->hasColumn('path')) {
				$table->addColumn('path', Type::STRING, [
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
	}
}
