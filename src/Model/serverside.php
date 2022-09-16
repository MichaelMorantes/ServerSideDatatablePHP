<?php
require_once "model.php";

class SSP
{
	/**
	 * Perform the SQL queries needed for an server-side processing requested,
	 * utilising the helper functions of this class, limit(), order() and
	 * filter() among others. The returned array is ready to be encoded as JSON
	 * in response to an SSP request, or can be modified if needed before
	 * sending back to the client.
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array|PDO $conn PDO connection resource or connection parameters array
	 *  @param  string $table SQL table to query
	 *  @param  string $primaryKey Primary key of the table
	 *  @param  array $columns Column information array
	 *  @return array          Server-side processing response array
	 */
	static function simple($request, $table, $columns)
	{
		$bindings = array();
		$db = self::db();

		// Build the SQL query string from the request
		$order = self::order($request, $columns);
		$where = self::filter($request, $columns, $bindings);

		$sql = "SELECT " .
			implode(", ", self::pluck($columns)) .
			" FROM $table " . $where . $order;
		$sql = self::limit($request, $sql);
		// print_r($sql);die();//debug
		// Main query to actually get the data
		$data = self::sql_exec($db, $bindings, $sql);

		// Data set length after filtering
		$resFilterLength = self::sql_exec(
			$db,
			$bindings,
			"SELECT COUNT(1)
			 FROM   $table
			 $where"
		);
		$recordsFiltered = $resFilterLength[0][0];

		// Total data set length
		$resTotalLength = self::sql_exec(
			$db,
			"SELECT COUNT(1)
			 FROM   $table"
		);
		$recordsTotal = $resTotalLength[0][0];

		/*
		 * Output
		 */
		return json_encode(array(
			"draw"            => isset($request['draw']) ?
				intval($request['draw']) :
				0,
			"recordsTotal"    => intval($recordsTotal),
			"recordsFiltered" => intval($recordsFiltered),
			"sql" => $sql, //for debug
			"data"            => self::data_output($columns, $data)
		));
	}

	/**
	 * Create the data output array for the DataTables rows
	 *
	 *  @param  array $columns Column information array
	 *  @param  array $data    Data from the SQL get
	 *  @return array          Formatted data in a row based format
	 */
	static function data_output($columns, $data)
	{
		$out = array();

		for ($i = 0, $ien = count($data); $i < $ien; $i++) {
			$row = array();

			for ($j = 0, $jen = count($columns); $j < $jen; $j++) {
				$column = $columns[$j];

				$formatter = function ($v) {
					return $v;
				};
				// Is there a formatter? ex 'formatter' => 'function($v){$status[3] = "OK" ; $status[4] = "ERR" ; return $status[$v];}'
				if (isset($column['formatter'])) {
					eval("\$formatter = " . $column['formatter'] . ";");
				}
				$row[$column] = $formatter($data[$i][$column]);
			}

			$out[] = $row;
		}
	
		return $out;
	}

	/**
	 * Connect to the database
	 *
	 * @param  array $sql_details SQL server connection details array, with the
	 *   properties:
	 *     * host - host name
	 *     * db   - database name
	 *     * user - user name
	 *     * pass - user password
	 * @return resource Database connection handle
	 */
	static function sql_connect()
	{
		$model = new model();
		return $model->getConnection();
	}

	/**
	 * Execute an SQL query on the database
	 *
	 * @param  resource $db  Database handler
	 * @param  array    $bindings Array of PDO binding values from bind() to be
	 *   used for safely escaping strings. Note that this can be given as the
	 *   SQL query string if no bindings are required.
	 * @param  string   $sql SQL query to execute.
	 * @return array         Result from the query (all rows)
	 */
	static function sql_exec($db, $bindings, $sql = null)
	{
		// Argument shifting
		if ($sql === null) {
			$sql = $bindings;
		}

		// var_dump($sql);
		// die;
		$stmt = $db->prepare($sql);
		// 		print_r( $stmt);
		// print_r($bindings);die();//debug

		// Bind parameters
		if (is_array($bindings)) {
			for ($i = 0, $ien = count($bindings); $i < $ien; $i++) {
				$binding = $bindings[$i];
				$stmt->bindValue($binding['key'], $binding['val'], $binding['type']);
			}
		}

		// Execute
		try {
			$stmt->execute();
            // var_dump($stmt);
		} catch (PDOException $e) {
			self::fatal("An SQL error occurred: " . $e->getMessage());
		}

		// Return all
		return $stmt->fetchAll(PDO::FETCH_BOTH);
	}

	/**
	 * Database connection
	 *
	 * Obtain an PHP PDO connection from a connection details array
	 *
	 *  @param  array $conn SQL connection details. The array should have
	 *    the following properties
	 *     * host - host name
	 *     * db   - database name
	 *     * user - user name
	 *     * pass - user password
	 *  @return resource PDO connection
	 */
	static function db()
	{
		return self::sql_connect();
	}

	/**
	 * Paging
	 *
	 * Construct the LIMIT clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL limit clause
	 */
	static function limit($request, $sql)
	{
		if (isset($request['start']) && $request['length'] != -1) {
			// "WHERE r between ".intval($request['start'])." and ".(intval($request['start']) + intval($request['length']));
			$sql =  "SELECT * FROM ( SELECT a.*, ROWNUM rnum FROM ( " .
				$sql .
				" ) a WHERE ROWNUM <= " . (intval($request['start']) + intval($request['length'])) . " ) WHERE rnum >= " . intval($request['start']);
		}

		return $sql;
	}

	/**
	 * Ordering
	 *
	 * Construct the ORDER BY clause for server-side processing SQL query
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @return string SQL order by clause
	 */
	static function order($request, $columns)
	{
		$order = '';

		if (isset($request['order']) && count($request['order'])) {
			$orderBy = array();
			$dataColumns = self::pluck($request["columns"], 'data');

			for ($i = 0, $ien = count($request['order']); $i < $ien; $i++) {
				// Convert the column index into the column data property
				$columnIdx = array_search(intval($request['order'][$i]['column']), $dataColumns);
				$requestColumn = $request['columns'][$columnIdx];
				
				if ($requestColumn['orderable'] != 'true') {
					continue;
				}
				
				$column = $columns[$request['order'][$i]['column']];
				$dir = $request['order'][$i]['dir'] === 'asc' ?
					'ASC' :
					'DESC';

				$orderBy[] = '' . $column . ' ' . $dir;
			}

			if (count($orderBy)) {
				$order = " ORDER BY " . implode(', ', $orderBy);
			}
		}

		return $order;
	}

	/**
	 * Searching / Filtering
	 *
	 * Construct the WHERE clause for server-side processing SQL query.
	 *
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here performance on large
	 * databases would be very poor
	 *
	 *  @param  array $request Data sent to server by DataTables
	 *  @param  array $columns Column information array
	 *  @param  array $bindings Array of values for PDO bindings, used in the
	 *    sql_exec() function
	 *  @return string SQL where clause
	 */
	static function filter($request, $columns, &$bindings)
	{
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck($columns);
		
		if (isset($request['search']) && $request['search']['value'] != '') {
			$str = $request['search']['value'];
			
			for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
				$requestColumn = $request['columns'][$i];
				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				if ($requestColumn['searchable'] == 'true') {
					$binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
					$globalSearch[] = "" . $column . " LIKE UPPER(" . $binding.")";
				}
			}
		}

		// Individual column filtering
		if (isset($request['columns'])) {
			for ($i = 0, $ien = count($request['columns']); $i < $ien; $i++) {
				$requestColumn = $request['columns'][$i];

				if ($requestColumn['searchable'] != 'true') {
					continue;
				}

				$columnIdx = array_search($requestColumn['data'], $dtColumns);
				$column = $columns[$columnIdx];

				$str = $requestColumn['search']['value'];

				if ($str != '') {
					if (isset($requestColumn['search']['operator']) && ($requestColumn['search']['operator'] == "=" ||
						$requestColumn['search']['operator'] == ">" ||
						$requestColumn['search']['operator'] == ">=" ||
						$requestColumn['search']['operator'] == "<=" ||
						$requestColumn['search']['operator'] == "<>"
					)) {
						$binding = self::bind($bindings, $str, PDO::PARAM_STR);
						$columnSearch[] = $column . " " . $requestColumn['search']['operator'] . " " . $binding;
					} else {
						$binding = self::bind($bindings, '%' . $str . '%', PDO::PARAM_STR);
						$columnSearch[] = $column . " LIKE UPPER(" . $binding.")";
					}
				}
			}
		}

		// Combine the filters into a single string
		$where = '';

		if (count($globalSearch)) {
			$where = '(' . implode(' OR ', $globalSearch) . ')';
		}

		if (count($columnSearch)) {
			$where = $where === '' ?
				implode(' AND ', $columnSearch) :
				$where . ' AND ' . implode(' AND ', $columnSearch);
		}

		if ($where !== '') {
			$where = 'WHERE ' . $where;
		}

		return $where;
	}

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Internal methods
	 */

	/**
	 * Throw a fatal error.
	 *
	 * This writes out an error message in a JSON string which DataTables will
	 * see and show to the user in the browser.
	 *
	 * @param  string $msg Message to send to the client
	 */
	static function fatal($msg)
	{
		echo json_encode(array(
			"error" => $msg
		));

		exit(0);
	}

	/**
	 * Create a PDO binding key which can be used for escaping variables safely
	 * when executing a query with sql_exec()
	 *
	 * @param  array &$a    Array of bindings
	 * @param  *      $val  Value to bind
	 * @param  int    $type PDO field type
	 * @return string       Bound key to be used in the SQL where this parameter
	 *   would be used.
	 */
	static function bind(&$a, $val, $type)
	{
		$key = ':binding_' . count($a);

		$a[] = array(
			'key' => $key,
			'val' => $val,
			'type' => $type
		);

		return $key;
	}

	/**
	 * Pull a particular property from each assoc. array in a numeric array, 
	 * returning and array of the property values from each item.
	 *
	 *  @param  array  $a    Array to get data from
	 *  @param  string $prop Property to read
	 *  @return array        Array of property values
	 */
	static function pluck($a, $prop = false)
	{
		$out = array();

		if ($prop) {
			for ($i = 0, $len = count($a); $i < $len; $i++) {
				$out[] = $a[$i][$prop];
			}
		} else {
			for ($i = 0, $len = count($a); $i < $len; $i++) {
				$out[] = $a[$i];
			}
		}
		
		// var_dump($out);
		// die;
		return $out;
	}

	/**
	 * Return a string from an array or a string
	 *
	 * @param  array|string $a Array to join
	 * @param  string $join Glue for the concatenation
	 * @return string Joined string
	 */
	static function _flatten($a, $join = ' AND ')
	{
		if (!$a) {
			return '';
		} else if ($a && is_array($a)) {
			return implode($join, $a);
		}
		return $a;
	}
}