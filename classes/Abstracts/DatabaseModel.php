<?php

namespace StackonetSupportTicket\Abstracts;

use StackonetSupportTicket\Interfaces\DataStoreInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class Model
 * A thin layer using wpdb database class form rapid development
 */
abstract class DatabaseModel extends AbstractModel implements DataStoreInterface {

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * The type of the primary key
	 * '%s' for string and '%d' for integer
	 *
	 * @var string
	 */
	protected $primaryKeyType = '%d';

	/**
	 * Data format
	 *
	 * @var array
	 */
	protected $data_format = [];

	/**
	 * The number of models to return for pagination.
	 *
	 * @var int
	 */
	protected $perPage = 15;

	/**
	 * @var string
	 */
	protected $cache_group;

	/**
	 * Model constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data = [] ) {
		if ( $data ) {
			$this->data = $this->read( $data );
		}
	}

	/**
	 * Find multiple records from database
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function find( $args = [] ) {
		$per_page     = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : $this->perPage;
		$paged        = isset( $args['paged'] ) ? absint( $args['paged'] ) : 1;
		$current_page = $paged < 1 ? 1 : $paged;
		$offset       = ( $current_page - 1 ) * $per_page;
		$orderby      = $this->primaryKey;
		if ( isset( $args['orderby'] ) && in_array( $args['orderby'], array_keys( $this->default_data ) ) ) {
			$orderby = $args['orderby'];
		}
		$order = isset( $args['order'] ) && 'ASC' == $args['order'] ? 'ASC' : 'DESC';

		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$query = "SELECT * FROM {$table} WHERE 1=1";

		$query   .= " ORDER BY {$orderby} {$order}";
		$query   .= $wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset );
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results;
	}

	/**
	 * Find record by id
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public function find_by_id( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$sql  = "SELECT * FROM {$table} WHERE {$this->primaryKey} = {$this->primaryKeyType}";
		$item = $wpdb->get_row( $wpdb->prepare( $sql, $id ), ARRAY_A );

		return $item;
	}

	/**
	 * Create data
	 *
	 * @param array $data
	 *
	 * @return int
	 */
	public function create( array $data ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$_data = [];
		foreach ( $this->default_data as $key => $default ) {
			$_data[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : $default;
		}

		if ( isset( $_data[ $this->primaryKey ] ) ) {
			unset( $_data[ $this->primaryKey ] );
		}

		$wpdb->insert( $table, $_data );

		return $wpdb->insert_id;
	}

	/**
	 * Method to read a record.
	 *
	 * @param mixed $data
	 *
	 * @return array
	 */
	public function read( $data ) {
		if ( is_array( $data ) ) {
			$item = [];
			foreach ( $this->default_data as $key => $default ) {
				$item[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : $default;
			}

			return $item;
		}

		if ( is_numeric( $data ) ) {
			$data = $this->find_by_id( $data );
		}

		if ( $data instanceof self ) {
			return $data->data;
		}

		return $this->default_data;
	}

	/**
	 * Update data
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public function update( array $data ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;
		$id    = isset( $data[ $this->primaryKey ] ) ? intval( $data[ $this->primaryKey ] ) : 0;

		$item = $this->find_by_id( $id );
		if ( ! $item instanceof self ) {
			return false;
		}

		$_data = [];
		foreach ( $this->default_data as $key => $default ) {
			$_data[ $key ] = isset( $data[ $key ] ) ? $data[ $key ] : $item->get( $key );
		}
		$_data[ $this->primaryKey ] = $id;

		if ( $wpdb->update( $table, $_data, [ $this->primaryKey => $id ], $this->data_format, $this->primaryKeyType ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Delete data
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	public function delete( $id = 0 ) {
		global $wpdb;
		$table = $wpdb->prefix . $this->table;

		$item = $this->find_by_id( $id );
		if ( ! $item instanceof self ) {
			return false;
		}

		return ( false !== $wpdb->delete( $table, [ $this->primaryKey => $id ], $this->primaryKeyType ) );
	}

	/**
	 * Count total records from the database
	 *
	 * @return array
	 */
	abstract public function count_records();

	/**
	 * Create database table
	 *
	 * @return void
	 */
	abstract public function create_table();

	/**
	 * Handle dynamic static method calls into the method.
	 *
	 * @param  string $method
	 * @param  array $parameters
	 *
	 * @return mixed
	 */
	public static function __callStatic( $method, $parameters ) {
		return ( new static )->$method( ...$parameters );
	}
}
