<?php

class Database
{
	/**
	 * Class version
	 */
	const VERSION = '1.0';

	/**
	 * PDO bind parameter for string value
	 */
	const STR = \PDO::PARAM_STR;

	/**
	 * PDO bind parameter for integer value
	 */
	const INT = \PDO::PARAM_INT;

	/**
	 * PDO bind parameter for boolean value
	 */
	const BOOL = \PDO::PARAM_BOOL;

	/**
	 * PDO bind parameter for null value
	 */
	const NULL = \PDO::PARAM_NULL;

	/**
	 * Regex to delete incorrect symbols from column name
	 */
	const COLUMN_REGEX = "/[^a-z0-9\_\-\.\`]/i";

	/**
	 * Main regex for question placeholders
	 */
	const SQL_REGEX = "/(\?[a|s|i|f|h|j|w|r|q|b|n]?)[0-9]*/i";

	/**
	 * PDO class
	 *
	 * @var \PDOStatement
	 */
	protected $stmt = null;

	/**
	 * PDO statement object
	 *
	 * @var \PDOStatement
	 */
	protected $lastStatement = null;

	/**
	 * Fetch mode
	 *
	 * @var int
	 */
	protected $fetchMode = \PDO::FETCH_ASSOC;

	/**
	 * Queue object
	 *
	 * @var \SplQueue
	 */
	protected $queue = null;

	/**
	 * Activate debug
	 *
	 * @var bool
	 */
	public $debug = false;

	/**
	 * Constructor
	 *
	 * @param  string $host    MySQL host
	 * @param  string $name    MySQL db name
	 * @param  string $user    MySQL user
	 * @param  string $pass    MySQL pass
	 * @param  array  $options Avalaible options: port, unixSocket, charset, fetchMode.
	 *
	 * @throws
	 * 
	 * @return void
	 */
	public function __construct($host, $dbName, $user, $pass, array $options = array())
	{
		$dsn = 'mysql:host=' . $host . ';';

		if(isset($options['port'])) {
			$dsn .= 'port=' . $options['port'] . ';';
		}

		if(isset($options['unixSocket'])) {
			$dsn = 'mysql:unix_socket=' . $options['unixSocket'] . ';';
		}

		$dsn .= 'dbname=' . $dbName;

		if(isset($options['charset'])) {
			$dsn .= ';charset=' . $options['charset'];
		}

		try {
			$this->stmt = new \PDO($dsn, $user, $pass, array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . (isset($options['charset']) ? $options['charset'] : 'utf8')));

			if(isset($options['fetchMode'])) {
				$this->setFetchMode($options['fetchMode']);
			}
		} catch (\PDOException $e) {
			if($this->debug === true) {
				throw new Exception();
			}
		}
	}

	/**
	 * You can use this little method if you want to close the PDO connection
	 * 
	 * @author indieteq
	 * 
	 * @return void
	 */
	public function closeConnection()
	{
		// Set the PDO object to null to close the connection
		// http://www.php.net/manual/en/pdo.connections.php
		$this->stmt = null;
	}

	/**
	 * Set fetch mode
	 *
	 * @param  int   $fetchMode \PDO fetch mode
	 *
	 * @throws
	 * 
	 * @return void
	 */
	protected function setFetchMode($fetchMode)
	{
		if(!is_int($fetchMode)) {
			throw new Exception('Unknown fetch mode.');
		}

		$this->fetchMode = $fetchMode;
	}

	/**
	 * Get fetch mode
	 *
	 * @return int
	 */
	protected function getFetchMode()
	{
		return $this->fetchMode;
	}

	/**
	 * Set last statement
	 * 
	 * @author fightbulc
	 *
	 * @param  \PDOStatement $cursor 
	 * 
	 * @return void
	 */
	protected function setLastStatement(\PDOStatement $cursor)
	{
		$this->lastStatement = $cursor;
	}

	/**
	 * Get last statement
	 * 
	 * @author fightbulc
	 *
	 * @return \PDOStatement
	 */
	protected function getLastStatement()
	{
		return $this->lastStatement;
	}

	/**
	 * Check if last statement is exists
	 *
	 * @return bool
	 */
	protected function hasLastStatement()
	{
		return !is_null($this->lastStatement);
	}

	/**
	 * Clear last statement
	 *
	 * @return void
	 */
	protected function clearLastStatement()
	{
		$this->lastStatement = null;
	}

	/**
	 * Get type of parameter
	 *
	 * @param  mixed $val 
	 *
	 * @return int
	 */
	protected function getValueParam($val)
	{
		switch ($val) {
			case is_int($val):
				return self::INT;
				// no break;
			case is_bool($val):
				return self::BOOL;
				// no break;
			case is_null($val):
				return self::NULL;
				// no break;
			default:
				return self::STR;
				// no break;
		}
	}

	/**
	 * Initiate \SplQueue class
	 *
	 * @return void
	 */
	protected function initQueue()
	{
		if($this->hasQueue()) {
			return false;
		}

		$this->queue = new \SplQueue();
		$this->queue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_DELETE);
	}

	/**
	 * Close \SplQueue class
	 *
	 * @return void
	 */
	protected function closeQueue()
	{
		$this->queue = null;
	}

	/**
	 * Check if \SplQueue is initiated
	 *
	 * @return bool
	 */
	protected function hasQueue()
	{
		return !is_null($this->queue);
	}

	/**
	 * Add to current bind queue
	 *
	 * @param  mixed  $val   value
	 * @param  int    $param type of paramater
	 * @param  string $name  name of bind
	 *
	 * @return bool
	 */
	protected function addToQueue($val, $param, $name = null)
	{
		return $this->queue->enqueue(array($val, $param, $name));
	}

	/**
	 * Add to bind queue string value
	 *
	 * @param  string $val value to bind
	 *
	 * @return bool
	 */
	protected function bindStr($val)
	{
		return $this->addToQueue($val, self::STR);
	}

	/**
	 * Add to bind queue integer value
	 *
	 * @param  int  $val value to bind
	 *
	 * @return bool
	 */
	protected function bindInt($val)
	{
		return $this->addToQueue($val, self::INT);
	}

	/**
	 * Add to bind queue boolean value
	 *
	 * @param  bool $val value to bind
	 *
	 * @return bool
	 */
	protected function bindBool($val)
	{
		return $this->addToQueue($val, self::BOOL);
	}

	/**
	 * Add to bind queue null value
	 *
	 * @param  null $val value to bind
	 *
	 * @return bool
	 */
	protected function bindNull($val)
	{
		return $this->addToQueue($val, self::NULL);
	}

	/**
	 * Add to bind queue default value
	 *
	 * @param  mixed $val value to bind
	 *
	 * @return bool
	 */
	protected function bindDef($val)
	{
		return $this->addToQueue($val, $this->getValueParam($val));
	}

	/**
	 * Add to bind queue named placeholders
	 *
	 * @param  array $arr array with named placeholders
	 *
	 * @return void
	 */
	protected function bindNamedPlaceholders(array $arr)
	{
		foreach ($arr as $key => $val) {
			$this->addToQueue($val, $this->getValueParam($val), $key);
		}
	}

	/**
	 * Create string for placeholders
	 *
	 * @param  array  $val value
	 *
	 * @return string
	 */
	protected function fill(array $val)
	{
		return implode(',', array_fill(0, count($val), '?'));
	}

	/**
	 * Here goes all magic
	 *
	 * @param  string $key placeholder
	 * @param  string $val value
	 *
	 * @return string
	 */
	protected function parseSqlParam($key, $val)
	{
		switch(strtolower($key)) {
			case '?': // pdo default
				$this->bindDef($val);
				break;
			case '?q': // string without html
				$val = strip_tags($val);
			case '?s': // string
			case '?f': // float
				$this->bindStr($val);
				$key = '?';
				break;
			case '?i': // integer
				$this->bindInt($val);
				$key = '?';
				break;
			case '?b': // boolean
				$this->bindBool($val);
				$key = '?';
				break;
			case '?n': // null
				$this->bindNull($val);
				$key = '?';
				break;
			case '?r': // raw
				$key = $val;
				break;
			case '?a': // integer array
				if(is_array($val)) {
					$key = $this->fill($val);
					foreach ($val as $v) {
						$this->bindInt($v);
					}
				} else {
					$this->bindInt($value);
					$key = '?';
				}
				break;
			case '?j': // string array
				if(is_array($val)) {
					$key = $this->fill($val);
					foreach ($val as $v) {
						$this->bindStr($v);
					}
				} else {
					$this->bindStr($value);
					$key = '?';
				}
				break;
			case '?h': // string array with column name and without delimeter
				if(is_array($val)) {
					$tableNames = array();
					foreach ($val as $k => $v) {
						$tableNames[] = preg_replace(self::COLUMN_REGEX, '', $k);
						$this->bindStr($v);
					}
					$key = implode(' = ?, ', $tableNames) . ' = ?';
				} else {
					$this->bindStr($val);
					$key = '?';
				}
				break;
			case '?w': // string array with column name and delimeter AND
				if(is_array($val)) {
					$tableNames = array();
					foreach ($val as $k => $v) {
						$tableNames[] = preg_replace(self::COLUMN_REGEX, '', $k);
						$this->bindStr($v);
					}
					$key = implode(' = ? AND ', $tableNames) . ' = ?';
				} else {
					$this->bindStr($val);
					$key = '?';
				}
				break;
			default:
				$key = '';
				break;
		}

		return $key;
	}

	/**
	 * Check if array has named placeholder or not
	 *
	 * @param  array $arr
	 *
	 * @return bool
	 */
	protected function checkArrayForNamedPlaceholders(array $arr)
	{
		return strpos(key($arr), ':') === 0;
	}

	/**
	 * Main function to parse sql and execute
	 *
	 * @param  array  $args
	 *
	 * @return \PDOStatement
	 */
	protected function init(array $args)
	{
		$this->clearLastStatement();
		$this->initQueue();
		// first arguments 100% sql query
		$sql = $args[0];

		if(count($args) > 1) {
			if(preg_match(self::SQL_REGEX, $sql) === 1) {
				$sql = preg_split(self::SQL_REGEX, $sql, -1, PREG_SPLIT_DELIM_CAPTURE);
				for($i = 1, $n = 1; $n < count($sql); $n += 2, $i++) {
					$sql[$n] = $this->parseSqlParam($sql[$n], $args[$i]);
				}
				$sql = implode('', $sql);
			} else {
				$this->bindNamedPlaceholders($args[1]);
			}
		}

		$query = $this->stmt->prepare($sql);
		$this->setLastStatement($query);
		$this->runQueue();
		$query->execute();

		if ($query->errorCode() !== '00000') {
			if($this->debug === true) {
				throw new Exception($sql);
			}
		}

		return $query;
	}

	/**
	 * Start queue that bind values
	 *
	 * @return void
	 */
	protected function runQueue()
	{
		$i = 1;
		foreach($this->queue as $val) {
			$this->getLastStatement()->bindValue((isset($val[2]) ? $val[2] : $i), $val[0], $val[1]);
			$i++;
		}
		$this->closeQueue();
	}

	/**
	 * Select query.
	 * 
	 * @example 
	 *    select('SELECT COLUMN1, COLUMN2, COLUMN3 FROM `table_name` WHERE COLUMN4 = ?s AND COLUMN5 = ?i OR COLUMN6 = ?', 'column4', 5, 'column6');
	 *    Result pdo: SELECT COLUMN1, COLUMN2, COLUMN3 FROM `table_name` WHERE COLUMN4 = ? AND COLUMN5 = ? OR COLUMN6 = ?
	 *    Result sql: SELECT COLUMN1, COLUMN2, COLUMN3 FROM `table_name` WHERE COLUMN4 = 'column4' AND COLUMN5 = 5 OR COLUMN6 = 'column6'
	 *
	 *    select('SELECT * FROM `?r` WHERE ?w', 'table_name', array('COLUMN1' => 'column1', 'COLUMN2' => 213));
	 *    Result pdo: SELECT * FROM `table_name` WHERE COLUMN1 = ? AND COLUMN2 = ?
	 *    Result sql: SELECT * FROM `table_name` WHERE COLUMN1 = 'column1' AND COLUMN2 = 213
	 *
	 *    select('SELECT * FROM `table_name` WHERE COLUMN1 = :column1 OR COLUMN2 = :column2', array(':column1' => 'column1', ':column2' => 213));
	 *    Result sql: SELECT * FROM `table_name` WHERE COLUMN1 = 'column1' OR COLUMN2 = 213
	 *
	 * @return array result
	 */
	public function select()
	{
		$result = $this->init(func_get_args());

		return $result->fetchAll($this->fetchMode);
	}

	/**
	 * Select first column in first row
	 *
	 * @return string first column
	 */
	public function selectCell()
	{
		$result = $this->init(func_get_args());

		return $result->fetchColumn(0);
	}

	public function column()
	{
		$result = $this->init(func_get_args());

		return $result->fetchColumn(0);
	}

	public function cell()
	{
		$result = $this->init(func_get_args());

		return $result->fetchColumn(0);
	}

	/**
	 * Select first row
	 *
	 * @return array first row
	 */
	public function selectRow()
	{
		$result = $this->init(func_get_args());

		return $result->fetch($this->getFetchMode());
	}

	public function fetch()
	{
		$result = $this->init(func_get_args());

		return $result->fetch($this->getFetchMode());
	}

	public function row()
	{
		$result = $this->init(func_get_args());

		return $result->fetch($this->getFetchMode());
	}

	/**
	 * Select first column from rows
	 *
	 * @return array
	 */
	public function selectArray()
	{
		$result = array();
		$query = $this->init(func_get_args());
		foreach ($query->fetchAll(\PDO::FETCH_NUM) as $value) {
			$result[] = $value[0];
		}

		return $result;
	}

	/**
	 * Select first column as key, second column as value from rows
	 *
	 * @return array
	 */
	public function selectHash()
	{
		$result = array();
		$query = $this->init(func_get_args());
		foreach ($query->fetchAll(\PDO::FETCH_NUM) as $value) {
			$result[$value[0]] = $value[1];
		}

		return $result;
	}

	public function hash()
	{
		$result = array();
		$query = $this->init(func_get_args());
		foreach ($query->fetchAll(\PDO::FETCH_NUM) as $value) {
			$result[$value[0]] = $value[1];
		}

		return $result;
	}

	/**
	 * Execute query.
	 *
	 * @return integer affected rows
	 */
	public function query()
	{
		$result = $this->init(func_get_args());

		return $result->rowCount();
	}

	/**
	 * Get number of affected rows in last query
	 *
	 * @return mixed affected rows or false if last statement not found
	 */
	public function getRowCount()
	{
		if ($this->hasLastStatement() === false) {
			return false;
		}

		return (int) $this->getLastStatement()->rowCount();
	}

	/**
	 * Get last inserted id
	 *
	 * @return int
	 */
	public function getLastId()
	{
		return (int) $this->stmt->lastInsertId();
	}

	public function lastId()
	{
		return (int) $this->stmt->lastInsertId();
	}

	/**
	 * Get string for value with prepared statements
	 *
	 * @param  array  $value 
	 *
	 * @return string
	 */
	protected function getInsertValues(array $value)
	{
		foreach ($value as $val) {
			$this->bindDef($val);
		}
		return '(' . implode(',', array_fill(0, count($value), '?')) . ')';
	}

	/**
	 * Prepare columns names for query
	 *
	 * @param  array  $value 
	 *
	 * @return array
	 */
	protected function getInsertColumns(array $value)
	{
		$arr = array();
		foreach ($value as $key => $val) {
			$arr[] = preg_replace(self::COLUMN_REGEX, '', $key);
		}
		return $arr;
	}

	/**
	 * Insert into tables.
	 * @example
	 *    insert('TABLE_NAME', array(array('COLUMN1' => 123, 'COLUMN2' => 123), array(234, 234), array(345, 345), array(456, 456)));
	 *    insert('TABLE_NAME', array(array(123, 123), array(234, 234), array(345, 345), array(456, 456)), array('COLUMN1', 'COLUMN2'));
	 *    insert('TABLE_NAME', array(123, 123), array('COLUMN1', 'COLUMN2'));
	 *
	 * Column names obligatory need to be in $insert first array as key or in $column array as value.
	 *
	 * @param  string     $table    table name
	 * @param  array      $inserts  array of insert values
	 * @param  array|null $columns  column names.
	 *
	 * @return integer              affected rows
	 */
	public function insert($table, array $inserts, array $columns = null)
	{
		$this->initQueue();
		$values = array();
		if(isset($inserts[0]) && is_array($inserts[0])) {
			if(!is_array($columns)) {
				$columns = $this->getInsertColumns($inserts[0]);
			}
			foreach($inserts as $val) {
				$values[] = $this->getInsertValues($val);
			}
		} else {
			if(!is_array($columns)) {
				$columns = $this->getInsertColumns($inserts);
			}
			$values[] = $this->getInsertValues($inserts);
		}
		$sql = 'INSERT INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES ' . implode(',', $values);
		$result = $this->init(array($sql));

		return $result->rowCount();
	}

	public function insertIgnore($table, array $inserts, array $columns = null)
	{
		$this->initQueue();
		$values = array();
		if(is_array($inserts[0])) {
			if(!is_array($columns)) {
				$columns = $this->getInsertColumns($inserts[0]);
			}
			foreach($inserts as $val) {
				$values[] = $this->getInsertValues($val);
			}
		} else {
			if(!is_array($columns)) {
				$columns = $this->getInsertColumns($inserts);
			}
			$values[] = $this->getInsertValues($inserts);
		}
		$sql = 'INSERT IGNORE INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES ' . implode(',', $values);
		$result = $this->init(array($sql));

		return $result->rowCount();
	}

	public function replace($table, array $inserts, array $columns = null)
	{
		$this->initQueue();
		$values = array();
		if(is_array($inserts[0])) {
			if(!is_array($columns)) {
				$columns = $this->getInsertColumns($inserts[0]);
			}
			foreach($inserts as $val) {
				$values[] = $this->getInsertValues($val);
			}
		} else {
			if(!is_array($columns)) {
				$columns = $this->getInsertColumns($inserts);
			}
			$values[] = $this->getInsertValues($inserts);
		}
		$sql = 'REPLACE INTO `' . $table . '` (' . implode(',', $columns) . ') VALUES ' . implode(',', $values);
		$result = $this->init(array($sql));

		return $result->rowCount();
	}

	/**
	 * Starts the transaction
	 *
	 * @return bool
	 */

	public function beginTransaction()
	{
		return $this->stmt->beginTransaction();
	}

	public function start()
	{
		return $this->stmt->beginTransaction();
	}

	/**
	 * Execute Transaction
	 *
	 * @return bool
	 */
	public function executeTransaction()
	{
		return $this->stmt->commit();
	}

	public function finish()
	{
		return $this->stmt->commit();
	}

	public function commit()
	{
		return $this->stmt->commit();
	}

	/**
	 * Rollback of Transaction
	 *
	 * @return bool
	 */
	public function rollBack()
	{
		return $this->stmt->rollBack();
	}

	public function cancel()
	{
		return $this->stmt->rollBack();
	}

	/**
	 * Lock tables
	 *
	 * @param  array  $tables tables
	 *
	 * @return bool
	 */
	public function lock(array $tables)
	{
		$arr = array();
		foreach ($tables as $val) {
			$arr[] = preg_replace(self::COLUMN_REGEX, '', $val) . ' WRITE';
		}

		$this->stmt->exec('LOCK TABLES ' . implode(',', $arr));
		return true;
	}

	/**
	 * Unlock tables
	 *
	 * @return bool
	 */
	public function unlock()
	{
		$this->stmt->exec('UNLOCK TABLES');
		return true;
	}
}
