<?php

declare(strict_types=1);

namespace OCA\Inventory\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

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
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('maker', 'string', [
				'notnull' => false,
				'length' => 100,
			]);
			$table->addColumn('description', 'string', [
				'notnull' => false,
				'length' => 100,
			]);
			$table->addColumn('item_number', 'string', [
				'notnull' => false,
				'length' => 100,
			]);
			$table->addColumn('link', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('gtin', 'string', [
				'notnull' => false,
				'length' => 13,
			]);
			$table->addColumn('details', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('comment', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('type', 'integer', [
				'notnull' => true,
				'default' => 0,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_item_instances')) {
			$table = $schema->createTable('invtry_item_instances');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid', 'bigint', [
				'notnull' => true,
				'length' => 8,
				'default' => 0,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('placeid', 'bigint', [
				'notnull' => false,
				'length' => 8,
			]);
			$table->addColumn('price', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('count', 'integer', [
				'notnull' => false,
			]);
			$table->addColumn('available', 'integer', [
				'notnull' => false,
			]);
			$table->addColumn('vendor', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('date', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('comment', 'string', [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_places')) {
			$table = $schema->createTable('invtry_places');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
			]);
			$table->addColumn('parentid', 'bigint', [
				'notnull' => false,
				'length' => 8,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_categories')) {
			$table = $schema->createTable('invtry_categories');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
			]);
			$table->addColumn('parentid', 'bigint', [
				'notnull' => false,
				'length' => 8,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_cat_map')) {
			$table = $schema->createTable('invtry_cat_map');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('categoryid', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_parent_map')) {
			$table = $schema->createTable('invtry_parent_map');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('parentid', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['itemid', 'parentid', 'uid'], 'itemid_parentid_uid');
		}

		if (!$schema->hasTable('invtry_rel_map')) {
			$table = $schema->createTable('invtry_rel_map');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('itemid1', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('itemid2', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['itemid1', 'itemid2', 'uid'], 'itemid1_itemid2_uid');
		}

		if (!$schema->hasTable('invtry_item_types')) {
			$table = $schema->createTable('invtry_item_types');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
			]);
			$table->addColumn('icon', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_instance_uuids')) {
			$table = $schema->createTable('invtry_instance_uuids');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('instanceid', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('uuid', 'string', [
				'notnull' => true,
				'length' => 36,
			]);
			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => 64,
			]);
			$table->setPrimaryKey(['id']);
		}

		if (!$schema->hasTable('invtry_attachments')) {
			$table = $schema->createTable('invtry_attachments');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			$table->addColumn('itemid', 'bigint', [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('instanceid', 'bigint', [
				'notnull' => false,
				'length' => 8,
			]);
			$table->addColumn('basename', 'string', [
				'notnull' => false,
			]);
			$table->addColumn('last_modified', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('created_at', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('created_by', 'string', [
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
