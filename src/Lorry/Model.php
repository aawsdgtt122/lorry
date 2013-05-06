<?php

namespace Lorry;

use Lorry\Service\PersistenceService;

abstract class Model implements ModelInterface {

	/**
	 *
	 * @var \Lorry\Service\PersistenceService
	 */
	protected $persistence;

	public function setPersistenceService(PersistenceService $persistence) {
		$this->persistence = $persistence;
	}

	private $table;
	private $schema;
	private $values;
	private $changes;
	private $loaded;

	public function __construct($table, $rows) {
		$this->loaded = false;

		$this->table = $table;
		$this->schema = $rows;

		$this->values = array();
		foreach($this->schema as $row) {
			$this->values[$row] = null;
		}

		$this->changes = array();
	}

	public function isLoaded() {
		return $this->loaded;
	}

	public final function getTable() {
		return $this->table;
	}

	public final function getSchema() {
		return $this->schema;
	}

	public final function getId() {
		return $this->getValue('id');
	}

	public final function setValue($name, $value) {
		$this->changes[$name] = $this->ensureType($name, $value);
		return true;
	}

	public final function getValue($name) {
		$this->ensureLoaded();
		$this->ensureRow($name);
		if(isset($this->changes[$name]))
			return $this->changes[$name];
		return $this->values[$name];
	}

	public final function byId($id) {
		return $this->byValue('id', $id);
	}

	private $multiple = false;

	public final function all() {
		$this->ensureUnloaded();

		$this->multiple = true;
		return $this;
	}

	private $order_row = 'id';
	private $order_descending = false;

	public final function order($row, $descending = false) {
		$this->order_row = $row;
		$this->order_descending = $descending;
		return $this;
	}

	protected final function byValue($row, $value) {
		$this->ensureUnloaded();
		$this->ensureRow($row);

		// do not allow fetching based on empty values
		if(empty($value)) {
			return false;
		}

		if($this->multiple) {
			$rows = $this->persistence->loadAll($this, $row, $value, $this->order_row, $this->order_descending);

			if(empty($rows)) {
				return array();
			}

			$instances = array();
			foreach($rows as $row) {
				$instance = clone $this;
				$instance->unserialize($row);
				$instances[] = $instance;
			}

			return $instances;
		} else {
			$row = $this->persistence->load($this, $row, $value, $this->order_row, $this->order_descending);

			if(empty($row)) {
				return false;
			}

			$this->unserialize($row);

			return $this;
		}
	}

	protected final function match($row, $value) {
		$this->ensureLoaded();
		$this->ensureRow($row);

		if($this->values[$row] != $value) {
			return false;
		}

		return true;
	}

	protected final function ensureType($row, $value) {
		$this->ensureRow($row);
		switch($this->schema[$row]) {
			case 'int':
				return intval($value);
			case 'string':
			default:
				return $value;
		}
	}

	public final function ensureRow($row) {
		if(!array_key_exists($row, $this->schema) && $row != 'id') {
			throw new \InvalidArgumentException('row "'.$row.'" does not exist');
		}
		return true;
	}

	public final function ensureLoaded() {
		if(!$this->loaded) {
			throw new \Exception('model has not been loaded');
		}
		return true;
	}

	public final function ensureUnloaded() {
		if($this->loaded) {
			throw new \Exception('model has already been loaded');
		}
		return true;
	}

	public final function unserialize($row) {
		if(empty($row)) {
			return false;
		}

		foreach($row as $key => $value) {
			$this->values[$key] = $value;
		}

		$this->loaded = true;

		return true;
	}

	/**
	 * Ensure that all changes to model are persistent.
	 * @return boolean True, if all changes will be persistent
	 */
	public final function save() {
		if(empty($this->changes))
			return true;
		if($this->loaded) {
			return $this->persistence->update($this, $this->changes);
		} else {
			return $this->persistence->save($this, $this->changes);
		}
	}

}