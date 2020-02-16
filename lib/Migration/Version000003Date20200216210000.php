<?php

declare(strict_types=1);

namespace OCA\Inventory\Migration;

use Closure;
use Doctrine\DBAL\Types\Type;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

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
			
			if($table->hasColumn('description')) {
				$table->changeColumn('description', ['type' => Type::getType(Type::TEXT)]);
			}
			if($table->hasColumn('details')) {
				$table->changeColumn('details', ['type' => Type::getType(Type::TEXT)]);
			}
			if($table->hasColumn('comment')) {
				$table->changeColumn('comment', ['type' => Type::getType(Type::TEXT)]);
			}
		}

		if ($schema->hasTable('invtry_items_instances')) {
			$table = $schema->getTable('invtry_items_instances');
			
			if($table->hasColumn('comment')) {
				$table->changeColumn('comment', ['type' => Type::getType(Type::TEXT)]);
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
