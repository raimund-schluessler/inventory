<?php

declare(strict_types=1);

namespace OCA\Inventory\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000003Date20200216210000 extends SimpleMigrationStep {
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

		if ($schema->hasTable('invtry_items')) {
			$table = $schema->getTable('invtry_items');
			
			if ($table->hasColumn('description')) {
				$table->changeColumn('description', [
					'type' => Type::getType(Types::TEXT),
					'length' => 65000
				]);
			}
			if ($table->hasColumn('details')) {
				$table->changeColumn('details', [
					'type' => Type::getType(Types::TEXT),
					'length' => 65000
				]);
			}
			if ($table->hasColumn('comment')) {
				$table->changeColumn('comment', [
					'type' => Type::getType(Types::TEXT),
					'length' => 65000
				]);
			}
		}

		if ($schema->hasTable('invtry_item_instances')) {
			$table = $schema->getTable('invtry_item_instances');
			
			if ($table->hasColumn('comment')) {
				$table->changeColumn('comment', [
					'type' => Type::getType(Types::TEXT),
					'length' => 65000
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
