<?php

declare(strict_types=1);

namespace OCA\Inventory\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version000001Date20191015191048 extends SimpleMigrationStep {
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

		if (!$schema->hasTable('invtry_items')) {
			$table = $schema->createTable('invtry_items');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('maker', Types::STRING, [
				'notnull' => false,
				'length' => 100,
			]);
			$table->addColumn('description', Types::STRING, [
				'notnull' => false,
				'length' => 100,
			]);
			$table->addColumn('item_number', Types::STRING, [
				'notnull' => false,
				'length' => 100,
			]);
			$table->addColumn('link', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('gtin', Types::STRING, [
				'notnull' => false,
				'length' => 13,
			]);
			$table->addColumn('details', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('comment', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('type', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_item_instances')) {
			$table = $schema->createTable('invtry_item_instances');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid', Types::BIGINT, [
				'notnull' => true,
				'length' => 8,
				'default' => 0,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('placeid', Types::BIGINT, [
				'notnull' => false,
				'length' => 8,
			]);
			$table->addColumn('price', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('count', Types::INTEGER, [
				'notnull' => false,
			]);
			$table->addColumn('available', Types::INTEGER, [
				'notnull' => false,
			]);
			$table->addColumn('vendor', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('date', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('comment', Types::STRING, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_places')) {
			$table = $schema->createTable('invtry_places');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('parentid', Types::BIGINT, [
				'notnull' => false,
				'length' => 8,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_categories')) {
			$table = $schema->createTable('invtry_categories');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('parentid', Types::BIGINT, [
				'notnull' => false,
				'length' => 8,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_cat_map')) {
			$table = $schema->createTable('invtry_cat_map');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('categoryid', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_parent_map')) {
			$table = $schema->createTable('invtry_parent_map');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('parentid', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['itemid', 'parentid', 'uid'], 'itemid_parentid_uid');
		}

		if (!$schema->hasTable('invtry_rel_map')) {
			$table = $schema->createTable('invtry_rel_map');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid1', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('itemid2', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['itemid1', 'itemid2', 'uid'], 'itemid1_itemid2_uid');
		}

		if (!$schema->hasTable('invtry_item_types')) {
			$table = $schema->createTable('invtry_item_types');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
			]);
			$table->addColumn('icon', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('uid', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_instance_uuids')) {
			$table = $schema->createTable('invtry_instance_uuids');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('instanceid', Types::INTEGER, [
				'notnull' => true,
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

		if (!$schema->hasTable('invtry_attachments')) {
			$table = $schema->createTable('invtry_attachments');
			$table->addColumn('id', Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('itemid', Types::BIGINT, [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('instanceid', Types::BIGINT, [
				'notnull' => false,
				'length' => 8,
			]);
			$table->addColumn('basename', Types::STRING, [
				'notnull' => false,
			]);
			$table->addColumn('last_modified', Types::BIGINT, [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('created_at', Types::BIGINT, [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('created_by', Types::STRING, [
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
