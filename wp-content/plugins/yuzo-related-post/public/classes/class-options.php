<?php
namespace YUZO\Publi;
use YUZO\Core\YUZO_Core as yuzo;

if( ! class_exists( 'Yuzo_Options' ) ){
/**
 * Solve the options for multiple proporse
 *
 * It allows to obtain all the list of options of the 'related post'
 * and saves them in an array.
 * It also allows you to validate which option is active and which does not
 *
 *
 * @since 		6.0         2019-04-26 20:27:39     Release
 * @package    	Yuzo
 * @subpackage 	Yuzo/Public
 *
 */
class Yuzo_Options {

	private
	/**
	 * Variable contains an SQL builder object
	 *
	 * @since 	6.0		2019-04-27 14:51:09		Release
	 * @access 	public
	 * @var 	object	$sqlbuilder 			Class construct sql
	 */
	$sqlbuilder = null;

	/**
	 * Init class
	 *
	 * @since 	6.0		22019-04-27 18:25:45		Release
	 */
	public function __construct(){

		// ─── We get the last instance of the sql constructor ────────
		$this->sqlbuilder = \sqlQueryBuilder::instance();

	}

	/**
	 * I get the ID list of all active related posts currently created
	 *
	 * @since 	6.0		2019-04-27 12:19:34		Release
	 * @return 	array
	 */
	public function get_list_id_active(){

		global $wpdb;

		$this->sqlbuilder->clear();
		$this->sqlbuilder->table("{$wpdb->prefix}posts a");
		$this->sqlbuilder->join("{$wpdb->prefix}postmeta b","a.ID = b.post_id");
		$this->sqlbuilder->where("a.post_type","yuzo");
		$this->sqlbuilder->where("a.post_status","publish");
		$this->sqlbuilder->where("b.meta_key", YUZO_ID . "_related_post_active");
		$this->sqlbuilder->where("b.meta_value",1);
		$sql = $this->sqlbuilder->select("a.ID");

		yuzo::instance()->logs->log("Public ⇨ Options ⇨ call func: get_list_id_active ⇨ sql: $sql ");

		return $this->unique_id(
					$wpdb->get_results(
						$sql,
						ARRAY_A
					)
				);

	}

	/**
	 * From a list of associative array, transfer it in a bidimentional
	 *
	 * @since 	6.0		2019-04-27 12:27:06		Release
	 * @return 	array
	 */
	public function unique_id( $array = [] ){

		$new_array = [];
		if( is_array( $array ) ){
			foreach ($array as $key => $value) {
				foreach ($value as $k => $v) {
					$new_array[] = $v;
				}
			}
		}

		yuzo::instance()->logs->log("Public ⇨ Options ⇨ func: unique_id ⇨ var #_new_array: ", $new_array);
		return array_unique($new_array);
	}

	/**
	 * Repeta all the information of a complete setting of a related post
	 *
	 * @since 	6.0				2019-04-27		Release
	 * @since	6.0.9.4			2019-07-27		Valid if valid ids_list_post exists
	 * @since	6.0.9.83		2019-10-04		Metabox validation improvements
	 *
	 * @param 	array|int 		$ids_list_post	Id or ID (array) of the settings to search
	 * @return	array
	 */
	public function get_setting_by_id( $ids_list_post = [] ){

		global $wpdb;

		if( empty( $ids_list_post ) ) return;

		// ─── Valid if the yuzo is active by metabox ────────
		if( ! is_array($ids_list_post)
			&& ! empty( (int) $ids_list_post )
			&& yuzo::instance()->public->related_algorithm->disabled_current_yuzo( $ids_list_post ) ) return;

		$this->sqlbuilder->clear();

		$this->sqlbuilder->table("{$wpdb->prefix}postmeta");

		if( is_array($ids_list_post) ){

			$this->sqlbuilder->whereIn("post_id",$ids_list_post);

		}else{

			$this->sqlbuilder->where("post_id",$ids_list_post);

		}

		$this->sqlbuilder->where("meta_key", YUZO_ID );

		$this->sqlbuilder->groupBy( 'post_id' );

		$sql = $this->sqlbuilder->select('post_id, meta_value as setting');

		yuzo::instance()->logs->log("Public ⇨ Options ⇨ func: get_setting_by_id ⇨ var #sql: $sql" );

		return 	$wpdb->get_results(
					$sql,
					ARRAY_A
				);
	}

	/**
	 * Get the setting list of each related post created an array
	 *
	 * * 1. Obtain the IDs of the related active post.
	 * * 2. With each ID you get the individual settings of each one
	 * * 3. You can also get a single list by ID, as long as it is active
	 *
	 * @since 	6.0		2019-04-27 13:21:12		Release
	 * @since	6.0.9.4	2019-07-27 01:18:46		Valid by Yuzo ID or an arrangement of many, always active
	 *
	 * @param	int		$id						ID setting
	 * @return 	array
	 */
	public function get_list_settings_related_post( $id = null ){

		$list_settings_related = [];
		yuzo::instance()->logs->group("Public ⇨ Options ⇨ func: get_list_settings_related_post" );

		// ─── get yuzo active ────────
		$yuzo_actives = $this->get_list_id_active() ?: [];

		if( !empty( $id )){
			yuzo::instance()->logs->debug(" # from param #id funcion = $id and compare with #this->get_list_id_active() ⇨ #yuzo_actives ", $yuzo_actives );

			// ─── Validate if get list by unique ID ────────
			if( in_array( $id, $yuzo_actives ) ){
				// ─── Get setting db ────────
				$list_settings_related = $this->get_setting_by_id( $id );
				yuzo::instance()->logs->info(" Call get_setting_by_id for #id ", $id );
			}
		}else{
			yuzo::instance()->logs->info(" param #id == null, then Call get_setting_by_id from #this->get_list_id_active() ⇨ #yuzo_actives: ", $yuzo_actives );
			$list_settings_related = $this->get_setting_by_id( $yuzo_actives );
		}

		yuzo::instance()->logs->alert(" return var #list_settings_related ", ! empty( $list_settings_related ) ? '{json}' : 'null' );
		yuzo::instance()->logs->groupEnd();

		return $list_settings_related;

	}

} }
