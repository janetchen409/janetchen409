<?php
if( ! class_exists('sqlQueryBuilder') ){
/**
 * SQL QUERY BUILDER CLASS
 * PHP version 5.3
 * @category 	Library
 * @author   	Lenin Zapata <leninzapata.com>
 * @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
 *
 * @since 	1.0
 * @since	1.1		2019-05-04		- In the function of cleaning query, all the variables are already set
 * 									- The paracentesis was eliminated in the group by function
 * @since	1.2		2019-07-20		The SELECT function was modified
 * @since	1.2.1	2019-07-27		With the 'more_table' function you can nest tables
 * @since	1.2.2	2019-10-04		- Now the orderBy function can be assigned without ordering asc desc
 * 									- Tabulation in the classes for better interpretation.
 *
 */
final class sqlQueryBuilder{

	/*----------------------------------------------------------------------------*
	* Variables
	*----------------------------------------------------------------------------*/
	/**
	 * Existing instance
	 *
	 * Store DB class object to allow one connection
	 * with database (deny duplicate).
	 *
	 * @var array
	 */
	protected static $instance;

	private
	/**
	 *  @var $_query string store sql statement
	 *  @static
	 */
	$_query = '',
	/**
	 *  @var $_count int store row count for _results variable
	 */
	$_count,
	/**
	 *  @var $_error bool if cant fetch sql statement = true otherwise = false
	 */
	$_error = false,
	/**
	 *  @var $_schema string store DDL sql query
	 */
	$_schema,
	/**
	 *  @var $_where string where type to using by default = WHERE
	 */
	$_where = "WHERE",
	/**
	 *  @var $_sql string save query string
	 */
	$_sql,
	/**
	 *  @var $_colsCount integer columns count for query results
	 * using into dataView() method to generate columns
	 */
	$_colsCount = -1,
	/**
	 * @var $_newValues null to save new value to use save() method
	 */
	$_newValues = null,
	/**
	 * @var $_ordering Check if there is an order by already started, then concatenate it.
	 */
	$_ordering = false;

	protected
	/**
	 *  @var $_pdo object PDO object
	 */
	$_pdo,
	/**
	 * @var $_results array store sql statement result
	 */
	$_results,
	/**
	 * @var $_idColumn string|null id columns name for current table by default is id
	 */
	$_idColumn = "id",
	/**
	 * @var $_firstWhere boolean|false state variable indicating that the 'where' clause has already been set
	 */


	// Sentinel or status variable for first where
	$_firstWhere = false,
	/**
	 * @var $_groupingWhere string|'' indicates the start of a group of conditions with the beginning of the parenthesis "("
	 */
	$_groupingWhere = '',
	/**
	 * @var $_operator_logical string|'' Logical operator that will be temporary to validate SQL statements
	 */
	$_operator_logical = '',
	/**
	 * @var $_where_in_between string|'' Verify that if the where is 'between' or 'IN'
	 */
	$_where_in_between = '';



	protected static
	/**
	 * @var $_squery string|'' Gets the entire string of the query variable statically
	 */
	$_squery = '',
	/**
	 * @var $_is_raw boolean|false Allows you to know if a function is called by a Raw call
	 */
	$_is_raw = false,
	/**
	 * @var $_table string current table name
	 */
	$_table;


	/**
	* Adds the pdo instance to the query builder object. See @ref qb_getting_started
	* @param	object	$pdo	the pdo object
	*/
	public function __construct(){
		//return $this;
	}
	private final function __clone() { }


	/**
	 * Get class instance.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( ! isset( self::$instance )  || self::$instance  == NULL ) {
			self::$instance = new self();
			self::$instance->setup();
		}
		return self::$instance;
	}


	/**
	 * Setup the singleton.
	 */
	public function setup() {
		// Silence
	}



	/*----------------------------------------------------------------------------*
	* Collection
	*----------------------------------------------------------------------------*/
	/**
	 * change id columns name
	 * @param string $idName
	 */
	public function idName($idName = "id"){
		$this->_idColumn = $idName;
		return $this;
	}


	/**
	 * Add where condition to sql statement
	 *
	 * @param  string  $field    field name from table
	 * @param  string  $operator operator (= , <>, .. etc)
	 * @param  mix $value    the value
	 * @return object        this class
	 */
	public function where( $field, $operator = '=', $value = false ){

		// Set changes the logical operator that is required according to the function called
		$_stringWhere = "WHERE";
		$operator_log = !$this->_operator_logical ? "AND" : $this->_operator_logical;

		if( is_array( $field ) ){

			foreach ($field as $k => $v) {

				$_field    = isset($v[0]) ? $v[0] : null;
				$_operator = isset($v[1]) ? $v[1] : null;
				$_value    = isset($v[2]) ? $v[2] : false;

				/**
				 * if $value is not set then set $_operator to (=) and
				 * $value to $_operator
				 */
				if( $_value === false ){
					$_value    = $_operator;
					$_operator = "=";
				}


				if( is_string( $_value ) ){
					$_value = "'$_value'";
				}elseif( is_array( $_value ) ){
					$_value = '(' . $_value[0] . " AND " . $_value[1] . ')';
				}

				$this->_where = $this->ifWhereIsReady() ? $operator_log : $_stringWhere;
				$this->_query .= " $this->_where $this->_groupingWhere $_field $_operator $_value";

				$this->_groupingWhere = '';
			}

		}else if( is_callable( $field ) ){
			$this->_groupingWhere = ' (';
				call_user_func_array( $field, [$this] );
			$this->_query .= ' ) ';
		}elseif( is_string( $field ) ){
			/**
			 * if $value is not set then set $operator to (=) and
			 * $value to $operator
			 */
			if($value === false){
				$value    = $operator;
				$operator = "=";
			}

			if( is_string($value) ){
				if( $this->_where_in_between == 'in' ){
					$value = '(' . "'$value'" . ')';
				}else{
					$value = "'$value'";
				}
			}elseif( is_array( $value ) ){
				if( $this->_where_in_between == 'between' ){
					$value = '(' . $value[0] . " AND " . $value[1] . ')';
				}elseif( $this->_where_in_between == 'in' ){
					$value = '(' . implode(", ", array_map( array($this, 'add_quotes'), (array)$value) ) . ')';
				}
			}

			/**
			 * If it is a raw condition
			 * then we delete the variables operator and values
			 */
			if( true === self::$_is_raw ){
				$operator = $value = null;
			}

			$this->_where = $this->ifWhereIsReady() ? $operator_log : $_stringWhere;
			$this->_query .= " $this->_where $this->_groupingWhere $field $operator $value";
		}


		// reset var
		$this->_groupingWhere    = '';
		$this->_operator_logical = '';
		self::$_is_raw           = false;

		return $this;
	}


	/**
	 * Check if the 'WHERE' clause was already set.
	 *
	 * @param
	 */
	private function ifWhereIsReady(){

		if( ! $this->_firstWhere ){
			$_return = false;
			$this->_firstWhere = true;
		}else{
			$_return = true;
		}

		return $_return;
	}


	/**
	 * Add 'OR' condition to sql statement
	 *
	 * @param  string  $field    field name from table
	 * @param  string  $operator operator (= , <>, .. etc)
	 * @param  mix $value    the value
	 * @return object        this class
	 */
	public function orWhere( $field, $operator = '=', $value = false ){
		$this->_operator_logical = "OR";
		$this->where( $field, $operator, $value);
		return $this;
	}

	/**
	 * Add 'AND' condition to sql statement
	 *
	 * @param  string  $field    field name from table
	 * @param  string  $operator operator (= , <>, .. etc)
	 * @param  mix $value    the value
	 * @return object        this class
	 */
	public function andWhere( $field, $operator = '=', $value = false ){
		$this->_operator_logical = "AND";
		$this->where( $field, $operator, $value);
		return $this;
	}


	/**
	 * Enter a raw SQL and place it where it is called.
	 *
	 * @param  string $sql_raw   SQL raw
	 * @return string
	 */
	public static function raw( $sql_raw = '', $value = '' ){
		// Init raw
		self::$_is_raw = true;
		return $value ? str_replace( '?' , $value , $sql_raw ) : $sql_raw;
	}


	/**
	 * Inserts a custom validation 'where' with a value
	 *
	 * @param  string 	$sql_raw   	SQL raw
	 * @param  mix 		$value   	Value to place inside the String sql
	 * @return object
	 */
	public function whereRaw( $sql_raw = '', $value = null ){
		// Init raw
		self::$_is_raw = true;

		// Adjust string
		$sql_raw       = str_replace( '?' , $value , $sql_raw );

		// Call functios where for manipulate estring
		$this->where( $sql_raw );

		return $this;
	}

	/**
	 * Inserts a custom validation 'where' with operator OR
	 *
	 * @param  string 	$sql_raw   	SQL raw
	 * @param  mix 		$value   	Value to place inside the String sql
	 * @return object
	 */
	public function orwhereRaw( $sql_raw = '', $value = null ){
		// Init raw
		self::$_is_raw = true;

		// Adjust string
		$sql_raw       = str_replace( '?' , $value , $sql_raw );

		// Call functios where for manipulate estring
		$this->orWhere( $sql_raw );

		return $this;
	}

	/**
	 * Where Between a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$range   	Value in arrangement
	 * @return object
	 */
	public function whereBetween( $field, $range = array() ){
		$this->_operator_logical = "AND";
		$this->_where_in_between = "between";
		$this->where( $field, 'BETWEEN', $range);
		return $this;
	}


	/**
	 * Where Not Between a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$range   	Value in arrangement
	 * @return object
	 */
	public function whereNotBetween( $field, $range = array() ){
		$this->_operator_logical = "AND";
		$this->_where_in_between = "between";
		$this->where( $field, 'Not BETWEEN', $range);
		return $this;
	}

	/**
	 * Where IN a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$range   	Value in arrangement
	 * @return object
	 */
	public function whereIn( $field, $range = array() ){
		$this->_operator_logical = "AND";
		$this->_where_in_between = "in";
		$this->where( $field, 'IN ', $range);
		return $this;
	}


	/**
	 * Where NOT IN a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$values   	Value in arrangement
	 * @return object
	 */
	public function whereNotIn( $field, $values = array() ){
		$this->_operator_logical = "AND";
		$this->_where_in_between = "in";
		$this->where( $field, 'NOT IN ', $values);
		return $this;
	}


	/**
	 * Where DATE a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$value   	Value in arrangement
	 * @return object
	 */
	public function whereDate( $field, $value = '' ){
		$this->_operator_logical = "AND";
		$this->where( 'DATE(' . $field . ')' , $value );
		return $this;
	}


	/**
	 * Where MONTH a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$value   	Value in arrangement
	 * @return object
	 */
	public function whereMonth( $field, $value = '' ){
		$this->_operator_logical = "AND";
		$this->where( 'MONTH(' . $field . ')' , $value );
		return $this;
	}


	/**
	 * Where YEAR a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$value   	Value in arrangement
	 * @return object
	 */
	public function whereYear( $field, $value = '' ){
		$this->_operator_logical = "AND";
		$this->where( 'YEAR(' . $field . ')' , $value );
		return $this;
	}


	/**
	 * Where DAY a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$value   	Value in arrangement
	 * @return object
	 */
	public function whereDay( $field, $value = '' ){
		$this->_operator_logical = "AND";
		$this->where( 'DAY(' . $field . ')' , $value );
		return $this;
	}


	/**
	 * Where TIME a custom validation 'where' with a value
	 *
	 * @param  string 	$field   	Comparison field
	 * @param  array	$value   	Value in arrangement
	 * @return object
	 */
	public function whereTime( $field, $value = '' ){
		$this->_operator_logical = "AND";
		$this->where( 'TIME(' . $field . ')' , $value );
		return $this;
	}


	/**
	 * Inserts a custom validation 'where' with a value
	 *
	 * @param  string 	$sql_raw   	SQL raw
	 * @param  mix 		$value   	Value to place inside the String sql
	 * @return object
	 */
	public function selectRaw( $sql_raw = '', $value = null ){
		// Init raw
		self::$_is_raw = true;

		// Adjust string
		$sql_raw       = str_replace( '?' , $value , $sql_raw );

		// Call functios where for manipulate estring
		return $this->select( $sql_raw );
	}





	/*----------------------------------------------------------------------------*
	* DML
	*----------------------------------------------------------------------------*/
	/**
	 * set _table var value
	 * @param  string $table the table name
	 * @return object - DBContent
	 */
	public static function table($table){
		self::$_table = $table;
		return new static;
	}

	/**
	 * Add nested tables after the parent 'table' function
	 *
	 * @since 	1.2.1	2019-07-27 13:14:38		Release
	 * @param 	string	$table					Nested tables
	 * @return 	void
	 */
	public function more_table( $table ){
		$separator = ! empty( self::$_table ) ? "," : '';
		self::$_table .= $separator . $table;
		return $this;
	}

	/**
	 * Select from database
	 *
	 * @since	1.2		2019-07-20 14:51:06		He commented functions and I leave it more natural
	 * @param  	array  	$fields fields we need to select
	 * @return 	string 	result of select as Collection object
	 */
	public function select( $fields = ['*'] ){
		/* if($fields === true){
			$fields = ['*'];
			$last   = true;
		}
		*/
		if( !is_null($this->_idColumn)){
			if(is_array( $fields ) && !in_array($this->_idColumn, $fields)){
				if( $fields[0] == '*' ){ unset($fields[0]); }
				array_unshift($fields,$this->_idColumn);
			}elseif( is_string( $fields ) ){
				$fields = explode(",",$fields);
			}
		}

		/*if(!$last){
			$sql = "SELECT " . implode(', ', $fields)
				. " FROM " . self::$_table . " {$this->_query}";
		}
		else{
			//$this->_query .= ($this->_ordering == false ? " ORDER BY {$this->_idColumn} DESC" : '');
			$sql = "SELECT * FROM (
						SELECT " . implode(', ', $fields) . "
						FROM " . self::$_table . "

						{$this->_query}
						) sub  ORDER by {$this->_idColumn} ASC";
		}
		$this->_query    = $sql;*/

		// Reset
		$this->_ordering = false;
		self::$_is_raw   = false;

		return
			self::$_squery = "SELECT " . implode(', ', $fields) . " FROM " . self::$_table . " {$this->_query}";

	}


	/**
	 * Functions allows to select data from multiple tables using various JOIN operation
	 *
	 * @param  string 		$new_table   	Second comparison table of the join
	 * @param  string 		$fields_compare Union of fields and logic comparison
	 * @param  string 		$type  			Type of join
	 * @return object
	 */
	public function join( $new_table = '', $fields_compare = '', $type = 'INNER JOIN' ){
		if( is_string($new_table) ){
			//self::$_table .= $this->_join_callback_init === true ? '' : '';
			self::$_table .= " $type $new_table on $fields_compare";
			//$this->_join_callback_init === false;

		}else if( is_callable( $new_table ) ){
			//$this->_join_callback_init = true;
			self::$_table .= ' (';
				call_user_func_array( $new_table, [$this] );
			self::$_table .= ' ) ';

		}

		return $this;
	}


	/**
	 * LEFTJOIN operation FROM function 'join'
	 *
	 * @param  string 		$new_table   	Second comparison table of the join
	 * @param  string 		$fields_compare Union of fields and logic comparison
	 * @return object
	 */
	public function lelfJoin( $new_table = '', $fields_compare = ''){
		$this->join( $new_table, $fields_compare, 'LEFT JOIN' );
		return $this;
	}


	/**
	 * RIGHTJOIN operation FROM function 'join'
	 *
	 * @param  string 		$new_table   	Second comparison table of the join
	 * @param  string 		$fields_compare Union of fields and logic comparison
	 * @return object
	 */
	public function rightJoin( $new_table = '', $fields_compare = '' ){
		$this->join( $new_table, $fields_compare, 'RIGHT JOIN' );
		return $this;
	}


	/**
	 * Sentence GROUP BY
	 *
	 * @since 	1.1			2019-05-04 02:09:50		Release
	 * @param  	mix 		$fields   				Fields grouped in string or array
	 * @return 	object
	 */
	public function groupBy( $fields = null ){
		if( is_string( $fields ) ){
			$this->_query .=" GROUP BY $fields";
		}elseif( is_array( $fields ) ){
			$this->_query .=" GROUP BY " . implode(',',(array)$fields) . "";
		}
		return $this;
	}


	/**
	 * Sentence Order By
	 *
	 * @since	1.2.2		2019-10-04		Now order can be empty and not assigned
	 *
	 * @param  	string 		$field   		Field to order
	 * @param  	string 		$order   		Order 'ASC' or 'DESC'
	 */
	public function orderBy( $field, $order = 'DESC' ){
		if( $order == '' || ! $order  ) $order = '';
		if( $this->_ordering == false ){
			$this->_query .= " ORDER BY {$field} {$order}";
			$this->_ordering = true;
		}else{
			$this->_query .= ",{$field} {$order}";
		}

		return $this;
	}


	/**
	 * Sentence Order By for IN
	 *
	 * @param  string 		$field   	Field to order
	 * @param  mixed|array  $values   	Values to order
	 */
	public function orderByIn( $field, $values = array() ){
		$this->_query .= " ORDER BY FIELD({$field}," . implode(",",$values) .")";
		return $this;
	}


	/**
	 * Sentence Order By force ASC
	 *
	 * @param  string 		$field   	Field to order
	 * @param  string 		$order   	Order 'ASC' or 'DESC'
	 */
	public function first( $field = '' ){
		$field = ! empty($field) ? $field : $this->_idColumn;
		$this->_query .= " ORDER BY {$field} ASC";
		return $this;
	}


	/**
	 * Sentence Order By force DESC
	 *
	 * @param  string 		$field   	Field to order
	 * @param  string 		$order   	Order 'ASC' or 'DESC'
	 */
	public function latest( $field = '' ){
		$field = ! empty($field) ? $field : $this->_idColumn;
		$this->_query .= " ORDER BY {$field} DESC";
		return $this;
	}


	/**
	 * Sentence Order By RANDOM
	 *
	 * @param  string 		$field   	Field to order
	 * @param  string 		$order   	Order 'ASC' or 'DESC'
	 */
	public function randomOrder( $field = '' ){
		//$field = ! empty($field) ? $field : $this->_idColumn;
		$this->_query .= " ORDER BY {$field} RAND()";
		return $this;
	}


	/**
	 * Initial value of the limit
	 *
	 * @param  int 		$value   	Skip valie
	 */
	public function offset( $value = 0 ){
		$this->_query .= " LIMIT {$value}";
		return $this;
	}


	/**
	 * Second value of the limit
	 *
	 * @param  int 		$value   	Skip valie
	 */
	public function limit( $value = 0 ){
		$this->_query .= ", {$value}";
		return $this;
	}


	public static function Formatter(){
		include_once 'SqlFormatter.php';
		return \SqlFormatter::format( self::$_squery );
	}


	/**
	 * Check if each item of a string
	 *
	 * @param mix 	$item 	Item of an array
	 */
	public function add_quotes( $item ){
		if( is_string($item) ){
			return "'$item'";
		}else{
			return $item;
		}
	}

	/**
	 * Clean the current SQL statement
	 *
	 * @since 1.0
	 * @since 1.1	2019-04-30 02:10:57		Added all the setting environment variables because
	 * 										they were accumulating for another query.
	 */
	public function clear(){
		$this->_query            = '';
		$this->_count            = null;
		$this->_error            = false;
		$this->_schema           = null;
		$this->_where            = "WHERE";
		$this->_sql              = null;
		$this->_colsCount        = -1;
		$this->_newValues        = null;
		$this->_ordering         = false;
		$this->_pdo              = null;
		$this->_results          = null;
		$this->_idColumn         = "id";
		$this->_firstWhere       = false;
		$this->_groupingWhere    = '';
		$this->_operator_logical = '';
		$this->_where_in_between = '';
		$this->_query            = '';

		return $this;
	}

	/**
	 * Reset condition
	 *
	 * @since 1.0
	 */
	public function resetCondional(){
		$this->_operator_logical = "";
		return $this;
	}

}
}