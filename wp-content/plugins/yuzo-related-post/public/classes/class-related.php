<?php
namespace YUZO\Publi;
use YUZO\Core\YUZO_Core as yuzo;

if( ! class_exists( 'Yuzo_Related' ) ){
/**
 * Relationship algorithm
 *
 * It allows to carry out the processes to create
 * the relationship SQL according to the user's needs.
 *
 * @since 		6.0         2019-04-27		Release
 * @since		6.0.9.83	2019-10-04		- Class variable $metabox_data is added to the class
 * 											- Tabulation in class for better interpretation
 * @package    	Yuzo
 * @subpackage 	Yuzo/Public
 *
 */
final class Yuzo_Related {

	public
	/**
	 * Variable that contains the options chosen by the user from the setting
	 *
	 * @since 	6.0		2019-04-27 14:36:39		Release
	 * @access 	public
	 * @var 	array	$options    			Get options from the database
	 */
	$options = [],
	/**
	 * Variable contains the accumulated terms of exclusion.
	 * It was created to avoid being recalculated 2 times.
	 *
	 * @since 	6.0		2019-04-27 14:51:09		Release
	 * @access 	public
	 * @var 	array	$var_exclude_terms 		List of terms that are excluded
	 */
	$var_exclude_terms = [],
	/**
	 * Variable contains an SQL builder object
	 *
	 * @since 	6.0		2019-04-27 14:51:09		Release
	 * @access 	public
	 * @var 	object	$sqlbuilder 			Class construct sql
	 */
	$sqlbuilder = null,
	/**
	 * Save the total of post that will be shown in the grid type for
	 * different devices.
	 *
	 * @since 	6.0		2019-06-19 21:50:46		Release
	 * @access 	public
	 * @var 	object	$totals_post_devices	Total post post fix by device
	 */
	$totals_post_devices = [],
	/**
	 * This is the additional post number that is calculated in the query
	 *
	 * The problem that if the user requests 6 does not always send the 6, sometimes less
	 * then this is not accurate, so this variable will try to compensate.
	 *
	 * @since	6.0		2019-07-10 23:07:09		Release
	 * @access	public
	 */
	$number_of_slack = 2;

	private
	/**
	 * Contains the information of the current Yuzo from the metabox
	 *
	 * @since	1.0		2019-09-22		Release
	 * @access	private
	 */
	$metabox_data = null;

	/**
	 * Init class
	 *
	 * @since 	6.0		2019-04-27 16:46:06		Release
	 */
	public function __construct(){

		// ─── This only works for front-end ────────
		if( is_admin() ) return;

		// ─── We get the last instance of the sql constructor ────────
		$this->sqlbuilder = \sqlQueryBuilder::instance();

	}

	/**
	 * SQL to form the list post
	 *
	 * @since 	6.0			2019-05-05	Release
	 * @since	6.0.9.84	2019-10-05	Sort alphabetically with numbers correctly
	 * @access 	public
	 *
	 * @param 	object|mixed	$opt	Options of the current configuration
	 * @return 	array
	 */
	public function get_sql_list_post( $opt = [] ){

		global $wpdb, $post;

		// ─── Set current related values ────────
		$this->options = $opt;

		// ─── Prefix ────────
		$prefix = $wpdb->prefix;

		// ─── Clear sql ────────
		$this->sqlbuilder->clear();

		// ─── Select main table ────────
		$this->sqlbuilder->table( "{$prefix}posts p" )->idName('p.ID');

		// ─── Validate if I select the most viewed ────────
		if( $this->options->list_post == 'most-view' ){
			$this->sqlbuilder->lelfJoin( "{$wpdb->prefix}yuzoviews yv", "p.ID = yv.post_id" );
		}elseif( $this->options->list_post == 'most-clicked' ){
			$this->sqlbuilder->lelfJoin( "{$wpdb->prefix}yuzoclicks yc", "p.ID = yc.post_id" );
		}

		// ─── CPT to related ────────
		if( is_array( $ypost_type = $this->get_cpt_related() ) && ! empty( $ypost_type ) ){
			$this->sqlbuilder->whereIn( 'p.post_type' , $ypost_type );
		}else{
			$this->sqlbuilder->where( 'p.post_type', $ypost_type );
		}

		// Post Status
		if( $post_status = $this->status_post() ){
			$this->sqlbuilder->where("p.post_status",$post_status);
		}

		// Time and Space
		$time_relative = ! empty( $this->options->time_and_space['range'] ) ?
		$this->options->time_and_space['range'] : 'all-along';
		if(  $time_relative != 'all-along' ){
			$this->sqlbuilder->where( 'p.post_date', '>=', $this->time_and_space( $time_relative ) );
		}

		// Add custom term tax her/no her
		if( ! empty( $this->options->include_taxonomy_hierarchical ) ||
		! empty( $this->options->related_taxonomy_no_hierarchical ) ){
			$_sql_tax = $this->set_sql_include_taxonomies();
			if( ! empty( $_sql_tax ) ){
				$this->sqlbuilder->whereRaw(" p.ID IN (" . $_sql_tax . " )  ");
			}
		}

		// Exclude by post id
		if( $post_ids = $this->exclude_post_by_id() ){
			$this->sqlbuilder->whereNotIn( 'p.ID',  $post_ids );
		}

		// Exclude current post
		if( isset($post->ID) ){
			$this->sqlbuilder->where( 'p.ID', '<>',  $post->ID );
		}

		// Exclude terms hierarchical/non-hierarchical
		if(  ! empty( $this->options->exclude_taxonomy_hierarchical ) ||
		! empty( $this->options->exclude_taxonomy_no_hierarchical )  ){
			$_sql_tax = $this->set_sql_exclude_taxonomies2();
			if( ! empty( $_sql_tax ) ){
				$this->sqlbuilder->whereRaw(" p.ID NOT IN (" . $_sql_tax . " )  ");
			}
		}

		// Group by
		// Group as long as the order is by the most viewed
		if( $this->options->list_post == 'most-view' ){
			$this->sqlbuilder->groupBy( "p.ID, yv.views" );
		}elseif( $this->options->list_post == 'most-clicked' ){
			$this->sqlbuilder->groupBy( "p.ID" );
		}else{
			$this->sqlbuilder->groupBy( 'p.ID' );
		}


		// Order related
		if( $this->options->list_post == 'most-view' ){
			$this->sqlbuilder->orderBy( 'post_most_views' , 'DESC' );
		}elseif( $this->options->list_post == 'most-clicked' ) {
			$this->sqlbuilder->orderBy( 'total' , 'DESC' );
		}elseif( $this->options->list_post == 'last-post' ){
			$this->sqlbuilder->orderBy( 'p.post_date' , 'DESC' );
		}elseif( $this->options->list_post == 'most-popular' ){
			$this->sqlbuilder->orderBy( 'p.comment_count' , 'DESC' );
		}elseif( $this->options->list_post == 'a-z' ){
			$this->sqlbuilder->orderBy( '(p.post_title = "0")' , 'DESC' );
			$this->sqlbuilder->orderBy( '(p.post_title+0 > 0)' , 'DESC' );
			$this->sqlbuilder->orderBy( 'p.post_title+0' , 'ASC' );
			$this->sqlbuilder->orderBy( 'p.post_title' , 'ASC' );
		}elseif( $this->options->list_post == 'z-a' ){
			$this->sqlbuilder->orderBy( 'p.post_title' , 'DESC' );
		}elseif( $this->options->list_post == 'rand' ){
			$this->sqlbuilder->orderBy( 'RAND()' , '' );
		}


		// Number min/max post
		$this->sqlbuilder->offset( 0 );
		$this->sqlbuilder->limit( $this->get_number_post() );

		// Final field
		if( $this->options->list_post == 'most-view' ){
			$sql_string = $this->sqlbuilder->select('p.ID, yv.views as post_most_views');
		}elseif( $this->options->list_post == 'most-clicked' ){
			$sql_string = $this->sqlbuilder->select('p.ID, count(*) total');
		}else{
			$sql_string = $this->sqlbuilder->select('p.ID');
		}


		//echo( $sql_string );

		// VIEW: See the current related options that are showing
		//var_dump(self::$options);

		// VIEW: Look at the SQL that is showing for this relationship
		// echo \sqlQueryBuilder::Formatter($sql_string);

		//var_dump( \sqlQueryBuilder::Formatter($sql_string) );exit;
		yuzo::instance()->logs->log($sql_string);

		return (array)$wpdb->get_results( $sql_string, ARRAY_A );
												//↑ Send the results in the form of an array
	}

	/**
	 * SQL to form the relationship in the related post
	 *
	 * @since 	6.0		2019-04-27 16:51:19		Release
	 * @since	6.0.9.4	2019-07-27 15:50:13		The most viewed algorithm was not working, now it works
	 * @since	6.0.9.7	2019-08-01 23:16:41		- The special character and ASC order filter was removed in the title-based realization
	 * 											- The 'Include Category' options were added for the title based relationship
	 * @access 	public
	 *
	 * @return 	array
	 */
	public function get_sql_related( $opt = [] ){

		global $wpdb, $post;

		// ─── Set current related values ────────
		$this->options = $opt;

		// ─── Prefix ────────
		$prefix = $wpdb->prefix;

		// ─── Clear sql ────────
		$this->sqlbuilder->clear();

		// ─── CPT to related ────────
		if( is_array( $ypost_type = $this->get_cpt_related() ) && ! empty( $ypost_type ) ){

			$this->sqlbuilder->whereIn( 'p.post_type' , $ypost_type );

		}else{

			$this->sqlbuilder->where( 'p.post_type', $ypost_type );

		}

		/*
		|--------------------------------------------------------------------------
		| Valid whether the current custom post type has a taxonomy or not
		|--------------------------------------------------------------------------
		*/
		if( ! $this->is_cpt_has_taxonomy( get_post_type( $post->ID ) ) ){
			$this->options->related_to     = 'title';
			$this->options->relation_title = 3;
		}

		/*
		|--------------------------------------------------------------------------
		| Type Related based on
		|--------------------------------------------------------------------------
		*/
		// ─── by Tags ────────
		if( ! empty ( $this->options->related_to ) && $this->options->related_to == 'tags' ){

			$terms = $this->get_tags_terms();

			if( ! empty( $terms ) ){

				$this->sqlbuilder->table( "{$prefix}posts p")->idName('p.ID');
				// ─── Validate if select the most viewed ────────
				if( $this->options->order_by['by'] == 'most_visited' ){
					$this->sqlbuilder->lelfJoin( "{$wpdb->prefix}yuzoviews yv", "p.ID = yv.post_id" );
				}
				$this->sqlbuilder->more_table( "{$prefix}terms t, {$prefix}term_taxonomy tt, {$prefix}term_relationships tr" );
				$this->sqlbuilder->whereRaw("
					p.ID IN (
						select p.ID from {$prefix}posts p where
						p.id = tr.object_id AND
						t.term_id = tt.term_id AND
						tr.term_taxonomy_id = tt.term_taxonomy_id AND
						( tt.taxonomy = 'post_tag' and tt.term_id = t.term_id and t.term_id in (".implode(",", $terms )."))
					)
				");
			}else{
				return null;
			}

		}else // ─── Categories ────────
			if( $this->options->related_to == 'categories' ){

				$terms = $this->get_categories_terms();

				if( ! empty( $terms ) ){

					$this->sqlbuilder->table( "{$prefix}posts p")->idName('p.ID');
					// ─── Validate if I select the most viewed ────────
					if( $this->options->order_by['by'] == 'most_visited' ){
						$this->sqlbuilder->lelfJoin( "{$wpdb->prefix}yuzoviews yv", "p.ID = yv.post_id" );
					}
					$this->sqlbuilder->more_table( "{$prefix}terms t, {$prefix}term_taxonomy tt, {$prefix}term_relationships tr" );
					$this->sqlbuilder->whereRaw("
						(p.id = tr.object_id AND
						t.term_id = tt.term_id AND
						tr.term_taxonomy_id = tt.term_taxonomy_id ) AND
						( tt.term_id = t.term_id and t.term_id in (".implode(",", $terms )."))
					");
				}else{
					return null;
				}
		}else // ─── Object Related (terms) ────────
			if( $this->options->related_to == 'object_related' ){
				$ids_obj_rel = $this->get_id_object_related();
				$this->sqlbuilder->table( "{$prefix}posts p" )->idName('p.ID');
				$this->sqlbuilder->whereIN( 'p.ID', $ids_obj_rel );
				//$this->terms_with_exclude_terms();
		}else // ─── Title ────────
			if( $this->options->related_to == 'title' ){
				yuzo::instance()->logs->group("Related type title");
				$array = [];
				if( in_array( $this->options->relation_title, [3,6] ) ){
					// title the words that related
					yuzo::instance()->logs->debug("Words",$this->options->relation_title);
					$words = explode( " ", $post->post_title );
					$word_length = $this->options->relation_title;
					$array = array_filter( $words, function($word) use($word_length){
						if( strlen($word) >= $word_length ){
							return $word;
						}
					});
				}elseif( in_array( $this->options->relation_title, ['f2','f3'] ) ){
					yuzo::instance()->logs->debug( "Phrases",$this->options->relation_title );
					$array = array_slice(explode( ' ', $post->post_title ), 0,  (int)substr( $this->options->relation_title , 1));
					yuzo::instance()->logs->debug( "Phrases of title '$post->post_title' result: ", $array );
					$array = (array) implode(' ', $array);
					yuzo::instance()->logs->debug( "Phrases of title join ", $array );
				}

				$this->sqlbuilder->where( function($sub_sql) use( $array ) {
					if( is_array($array) && ! empty( $array ) ){
						$i = 1;
						foreach ($array as $value) {
							if( $i == 1 )
								$sub_sql->where('p.post_title','like','%'.strtolower($value).'%');
							else
								$sub_sql->orWhere('p.post_title','like','%'.strtolower($value).'%');
							$i++;
						}
					}else{
						return null;
					}
				});

				yuzo::instance()->logs->info( "Sql search", $array );
				$this->sqlbuilder->table( "{$prefix}posts p")->idName('p.ID');

				// ─── Validate if I select the most viewed ────────
				if( $this->options->order_by['by'] == 'most_visited' ){
					$this->sqlbuilder->lelfJoin( "{$wpdb->prefix}yuzoviews yv", "p.ID = yv.post_id" );
				}
				yuzo::instance()->logs->groupEnd();
			}

		// Post Status
		if( $post_status = $this->status_post() ){
			$this->sqlbuilder->where("p.post_status",$post_status);
		}

		// Time and Space
		$time_relative = ! empty( $this->options->time_and_space['range'] ) ?
		$this->options->time_and_space['range'] : 'all-along';
		if(  $time_relative != 'all-along' ){
			$this->sqlbuilder->where( 'p.post_date', '>=', $this->time_and_space( $time_relative ) );
		}

		// Add custom term tax her/no her
		if( ! empty( $this->options->include_taxonomy_hierarchical_by_title ) &&
			$this->options->related_to == 'title') {
			$_sql_tax = $this->set_sql_include_taxonomies( $this->options->include_taxonomy_hierarchical_by_title, [] );
			if( ! empty( $_sql_tax ) ){
				$this->sqlbuilder->whereRaw(" p.ID IN (" . $_sql_tax . " )  ");
			}
		}

		// Exclude by post id
		if( $post_ids = $this->exclude_post_by_id() ){
			$this->sqlbuilder->whereNotIn( 'p.ID',  $post_ids );
		}

		// Exclude terms hierarchical/non-hierarchical
		if(  ! empty( $this->options->exclude_taxonomy_hierarchical ) ||
		! empty( $this->options->exclude_taxonomy_no_hierarchical )  ){
			$_sql_tax = $this->set_sql_exclude_taxonomies2();
			if( ! empty( $_sql_tax ) ){
				$this->sqlbuilder->whereRaw(" p.ID NOT IN (" . $_sql_tax . " )  ");
			}
		}

		// Exclude current post
		$this->sqlbuilder->where( 'p.ID', '<>',  $post->ID );

		// Group by
		// Group as long as the order is by the most viewed
		if( $this->options->order_by['order'] == 'most_visited' && $this->options->related_to != 'object_related' ){
			$this->sqlbuilder->groupBy( "p.ID, yv.views" );
		}else{
			$this->sqlbuilder->groupBy( 'p.ID' );
		}

		// Order related
		if( $this->options->related_to != 'object_related' ) {
			if( $this->options->order_by['by'] == 'most_visited' ){
				$this->sqlbuilder->orderBy( 'post_most_views' , 'DESC' );
			}else{
				$this->sqlbuilder->orderBy($this->ordering('field') , $this->ordering('order') );
			}
		}else{
			$this->sqlbuilder->orderByIn( 'p.ID' , $ids_obj_rel );
		}


		// Number min/max post
		$this->sqlbuilder->offset( 0 );
		$this->sqlbuilder->limit( $this->get_number_post() );

		// $sql_string = $this->sqlbuilder->select('SQL_CALC_FOUND_ROWS p.ID');
		// Final field
		if( $this->options->order_by['by'] == 'most_visited' && $this->options->related_to != 'object_related' ){
			$sql_string = $this->sqlbuilder->select('p.ID, yv.views as post_most_views');
		}else{
			$sql_string = $this->sqlbuilder->select('p.ID');
		}

		// echo( $sql_string );

		// VIEW: See the current related options that are showing
		//var_dump(self::$options);

		// VIEW: Look at the SQL that is showing for this relationship
		//echo \sqlQueryBuilder::Formatter($sql_string);;exit;
		yuzo::instance()->logs->log( "related sql: ",$sql_string);

		return (array)$wpdb->get_results( $sql_string, ARRAY_A );
												//↑ Send the results in the form of an array
	}

	/**
	 * Class to show posts when the option to show posts based on is activated
	 * in the results of the archive list.
	 *
	 * @since	6.0.9.84	2019-11-25	Release
	 *
	 * @param	object		$opt		Current Yuzo option that is running
	 * @return	array
	 */
	private function get_sql_related_archive( $opt ){
		global $wpdb, $wp_query;

		// ─── Ids result ────────
		$ids = [];

		// ─── Validation of which page they are on ────────
		if( is_category() ){
			$posts = new \WP_Query([ 'cat' => get_query_var('cat'), 'paged' => get_query_var( 'paged' ) ] );
		}elseif( is_tag() ){
			$posts = new \WP_Query([ 'tag' => get_query_var('tag'), 'paged' => get_query_var( 'paged' ) ] );
		}elseif( is_search() ){
			$posts = new \WP_Query([ 's' => get_query_var('s'), 'paged' => get_query_var( 'paged' ) ] );
		}elseif( is_tax() ){
			return yuzo_formater_ids( $wpdb->get_results( $wp_query->request , ARRAY_A ) );
		}
		$ids = yuzo_formater_ids( $wpdb->get_results( $posts->request , ARRAY_A ) );
		return $ids;
	}

	/**
	 * CPT to related
	 *
	 * Validate the current cpt vs the ones that the user chose to show
	 * or relate
	 *
	 * @since 	6.0		2019-04-27 16:59:34		Release
	 * @return 	mixed|array
	 *
	 * @todo
	 * //LATER: In the Free version this option must be activated, in the Pro version
	 * you must have the option to mix several CPTs because remember that the
	 * types 'pages' can have the same taxonomy then they
	 * should be able to relate pages and post.
	 * Or Lite version only 'post' and 'page', and the rest pair to the Pro version
	 */
	private function get_cpt_related(){

		return (array)$this->options->cpt_to_related;
				//└─────┘→ Convert into an array if it has another type of data
	}

	/**
	 * Order related
	 *
	 * @since 	6.0		2019-04-27 17:01:46		Release
	 *
	 * @param 	string 	$param					If it is 'field' for the field to order and
	 * 											'order' to indicate the type of order.
	 * @return 	string
	 */
	private function ordering( $param = null ){

		if( $param == 'field' ){

			$order_field_db = array(
				'id'            => 'ID',
				'author'        => 'post_author',
				'title'         => 'post_title',
				'date'          => 'post_date',
				'modified'      => 'post_modified',
				'rand'          => 'RAND()',
				'comment_count' => 'comment_count'
			);

			return $order_field_db[ $this->options->order_by['by'] ] ;

		}elseif( $param == 'order' ){

			return $this->options->order_by['order'];

		}
	}

	/**
	 * Order list
	 *
	 * @since 	6.0		2019-05-06 00:24:12		Release
	 * @since	6.0.4	2019-07-12 08:05:25		Added the order of az and za
	 *
	 * @param 	string 	$param					If it is 'field' for the field to order and
	 * 											'order' to indicate the type of order.
	 * @return 	string
	 */
	private function ordering2( $param = null ){

		if( $param == 'field' ){

			$order_field_db = array(
				'last-post'    => 'p.post_modified',
				'rand'         => 'RAND()',
				'most-popular' => 'comment_count',
				'a-z'          => 'p.post_title',
				'z-a'          => 'p.post_title',
			);

			return $order_field_db[ $this->options->list_post ] ;

		}elseif( $param == 'order' ){

			if( $this->options->list_post == 'last-post' || $this->options->list_post == 'most-popular' )
				return 'DESC';
			elseif( $this->options->list_post == 'a-z' )
				return 'ASC';
			elseif( $this->options->list_post == 'z-a' )
				return 'DESC';
			elseif( $this->options->list_post == 'rand' )
				return '';

		}
	}

	/**
	 * ONLY FOR RELATIONSHIP BASED ON TAXONOMIES NO-HIERARCHICAL
	 *
	 * Gets all the hierarchical terms of the current post for the relationship
	 * Gets all the non-hierarchical terms of the current post for the relationship
	 *
	 * * 1. Valid if the post is CPT post
	 * * 2. Valid if CPT to obtain the terms
	 * * 3. Add terms customizable by the user
	 *
	 * @since 	6.0		2019-04-27 17:03:01		Release
	 * @since	6.0.9.6	2019-07-28 18:53:42		The term_id_taxonomy field is changed to term_id
	 * @return 	array
	 */
	private function get_tags_terms(){

		global $post;

		// Var
		$terms_item = array();

		// Set post type
		$post_type  = get_post_type();

		// Valid CPT standar 'post'
		if( $post_type == 'post' ){

			$tags = wp_get_post_tags($post->ID);

			if ( is_array($tags) && count($tags)>0 ) {

				foreach($tags as $individual_tag) {
					$terms_item[] = $individual_tag->term_id;
				}
			}

		}else{ // Or valid if custom type post

			// Get all taxonomy from CPT include builtin tax
			$taxonomies = get_object_taxonomies( $post_type, 'objects' );
			foreach ( $taxonomies  as $taxonomy ){
				if ( is_taxonomy_hierarchical( $taxonomy->name ) ) { continue; }
				elseif( $taxonomy->name != 'post_format' ){
					$taxonomies_array[] = $taxonomy->name;
				}
			}

			// get id taxonomies add to post for add to sql
			if( is_array($taxonomies_array) && $taxonomies_array ){

				foreach ($taxonomies_array as $taxonomies_array_key => $taxonomies_array_value) {
					// Get the terms of the current taxonomy not-hierarchical
					$terms = get_the_terms( get_the_ID() , $taxonomies_array_value );

					if( is_array($terms) ){
						foreach ($terms as $terms_key => $terms_value) {
							$terms_item[] = $terms_value->term_id;
						}
					}

				}
			}
		}

		return $this->terms_with_exclude_terms( $terms_item );
	}

	/**
	 * ONLY FOR RELATIONSHIP BASED ON TAXONOMIES HIERARCHICAL
	 * Gets all the hierarchical terms of the current post for the relationship
	 *
	 * * 1. Valid if the post is CPT post
	 * * 2. Valid if CPT to obtain the terms
	 *
	 * @since 	6.0		2019-04-27 17:07:10		Release
	 * @return 	array
	 */
	private function get_categories_terms(){
		global $post;

		$terms_item = array();

		// Valid CPT standar 'post'
		if( get_post_type() == 'post' ){

			$categories	= get_the_category( $post->ID );
			if( $categories ){
				foreach ($categories as $categories_key => $categories_value) {
					$terms_item[] = $categories_value->term_id;
				}
			}

		}else{ // Or valid if custom type post

			$tax_object_by_type_post = get_object_taxonomies( get_post_type( $post->ID ) );

			if( $tax_object_by_type_post ){
				foreach( $tax_object_by_type_post as $tax_object_by_type_post_key => $tax_object_by_type_post_value ) {
					if ( !is_taxonomy_hierarchical($tax_object_by_type_post_value) ) { continue; }
					$terms_only = get_the_terms( $post->ID , $tax_object_by_type_post_value );

					if($terms_only){
						foreach ($terms_only as $terms_only_key => $terms_only_value) {
							$terms_item[] = $terms_only_value->term_id;
						}
					}
				}
			}
		}

		return $this->terms_with_exclude_terms( $terms_item );
	}

	/**
	 * Exclude hierarchical/non-hierarchical term of the relationship
	 *
	 * * 1. Exclude taxonomies (hierarchical)
	 * * 2. Exlude taxonomies (no hierarchical)
	 *
	 * @since 	6.0		2019-04-27 17:07:36		Release
	 *
	 * @param 	array	$current_terms 			It contains all the current terms that
	 *											will be made in order to compare and exclude them.
		* @return 	array
		* @see 	functions get_categories_terms|get_tags_terms
		*/
	private function terms_with_exclude_terms( $current_terms = array() ){

		$terms_item_exclude = array();

		//  add id terms exclude taxonomies (hierarchical)
		if( ! empty( $this->options->exclude_taxonomy_hierarchical ) ){
			foreach((array)$this->options->exclude_taxonomy_hierarchical as $key => $values) $terms_item_exclude[] = (int)$values;
		}

		// add id terms of tags for exclude (no hierarchical)
		if( ! empty( $this->options->exclude_taxonomy_no_hierarchical ) && is_array( $this->options->exclude_taxonomy_no_hierarchical ) ){

			foreach( $this->options->exclude_taxonomy_no_hierarchical as $k => $v ){
				$array_term_exclude[] = explode(",", $v);
			}
			$array_term_exclude = yuzo_array_flatten( $array_term_exclude , true );

			if( is_array($array_term_exclude) && ! empty( $array_term_exclude ) ){
				foreach ($array_term_exclude as $key => $value) {
					$split_string = explode ("|", $value);
					if( ! empty( $split_string[0] ) && ! empty( $split_string[1] ) ){
						$temp_term    = get_term_by( 'name', $split_string[0], $split_string[1] );
						if( ! empty( $temp_term ) ) { $terms_item_exclude[] = $temp_term->term_taxonomy_id ; }
					}

				}
			}
		}
		$this->var_exclude_terms = $terms_item_exclude;
		return array_diff($current_terms, $terms_item_exclude);
	}

	/**
	 * ONLY FOR RELATIONSHIP BASED ON OBJECT/TERM
	 *
	 * It is about getting the post related in terms of the greatest relevance ordered,
	 * possibly the best relationship.
	 *
	 * @since 	6.0		2019-04-27 17:08:30		Release
	 * @return 	array
	 */
	public function get_id_object_related(){

		global $wpdb, $post;

		// Get term related by id post
		$args = "SELECT term_taxonomy_id FROM {$wpdb->term_relationships} WHERE object_id = " . $post->ID;

		// get post status
		$post_status = 'publish';

		// Get column term_taxonomy_id
		$term_taxonomy_ids = $wpdb->get_col( "$args" );
		if ( !$term_taxonomy_ids ) { return; }

		// Separate by commas each term found
		$term_taxonomy_ids_str = implode( ",", $term_taxonomy_ids );

		// You get the post that have those terms
		$object_ids = array();
		$object_ids = $wpdb->get_col( "SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ( {$term_taxonomy_ids_str} ) " );
		if ( !$object_ids ) { return; }

		// Form an array with the number of repeats of items
		$object_ids = array_count_values( $object_ids );
		arsort( $object_ids );

		// Get order relevance
		$order_by = ! empty( $this->options->ordering_object_id ) ? $this->options->ordering_object_id : '';

		/**
		 * Validation series to carry out the order of the post id found.
		 * @since 6.0
		 */
		$array_id_post_return = array();
		if ( $order_by == "related_scores_high__speedy" ) {
			foreach ( $object_ids as $object_id => $relevancy_score ) {
				$related_post = $wpdb->get_row( "SELECT ID FROM {$wpdb->posts} WHERE ID = {$object_id} AND post_status = '{$post_status}'" );
				if ( $related_post ) {
					$array_id_post_return[] = $related_post->ID;
				}
			}
		} else {
			$relevancy_scores = array();
			$post_ids         = array();
			$post_date        = array();
			$post_modified    = array();
			foreach ( $object_ids as $object_id => $relevancy_score ) {
				$related_post = $wpdb->get_row( "SELECT ID, post_date, post_modified FROM {$wpdb->posts} WHERE ID = {$object_id} AND  post_status = '{$post_status}'" );
				if ( $related_post ) {
					array_push( $relevancy_scores, $relevancy_score );
					array_push( $post_ids, $related_post->ID );
					array_push( $post_date, $related_post->post_date );
					array_push( $post_modified, $related_post->post_modified );
				}
			}
			if ( $post_ids ) {
				if ( $order_by == "related_scores_high__date_published_old" ){
					array_multisort( $relevancy_scores, SORT_DESC, $post_date, SORT_ASC, $post_ids, SORT_ASC, $post_modified, SORT_ASC );
				} elseif ( $order_by == "related_scores_low__date_published_new" ) {
					array_multisort( $relevancy_scores, SORT_ASC, $post_date, SORT_DESC, $post_ids, SORT_DESC, $post_modified, SORT_DESC );
				} elseif ( $order_by == "related_scores_low__date_published_old" ) {
					array_multisort( $relevancy_scores, SORT_ASC, $post_date, SORT_ASC, $post_ids, SORT_ASC, $post_modified, SORT_ASC );
				} elseif ( $order_by == "related_scores_high__date_modified_new" ) {
					array_multisort( $relevancy_scores, SORT_DESC, $post_modified, SORT_DESC, $post_date, SORT_DESC, $post_ids, SORT_DESC );
				} elseif ( $order_by == "related_scores_high__date_modified_old" ) {
					array_multisort( $relevancy_scores, SORT_DESC, $post_modified, SORT_ASC, $post_date, SORT_ASC, $post_ids, SORT_ASC );
				} elseif ( $order_by == "related_scores_low__date_modified_new" ) {
					array_multisort( $relevancy_scores, SORT_ASC, $post_modified, SORT_DESC, $post_date, SORT_DESC, $post_ids, SORT_DESC );
				} elseif ( $order_by == "related_scores_low__date_modified_old" ) {
					array_multisort( $relevancy_scores, SORT_ASC, $post_modified, SORT_ASC, $post_date, SORT_ASC, $post_ids, SORT_ASC );
				} elseif ( $order_by == "date_published_new" ) {
					array_multisort( $post_date, SORT_DESC, $post_ids, SORT_DESC, $post_modified, SORT_DESC, $relevancy_scores, SORT_DESC );
				} elseif ( $order_by == "date_published_old" ) {
					array_multisort( $post_date, SORT_ASC, $post_ids, SORT_ASC, $post_modified, SORT_ASC, $relevancy_scores, SORT_DESC );
				} elseif ( $order_by == "date_modified_new" ) {
					array_multisort( $post_modified, SORT_DESC, $post_date, SORT_DESC, $post_ids, SORT_DESC, $relevancy_scores, SORT_DESC );
				} elseif ( $order_by == "date_modified_old" ) {
					array_multisort( $post_modified, SORT_ASC, $post_date, SORT_ASC, $post_ids, SORT_ASC, $relevancy_scores, SORT_DESC );
				} elseif( $order_by == "related_scores_high__date_published_new" ) {
					array_multisort( $relevancy_scores, SORT_DESC, $post_date, SORT_DESC, $post_ids, SORT_DESC, $post_modified, SORT_DESC );
				}

				foreach ( $post_ids as $key => $post_id ) {
					if( $post->ID != $post_id ){
						$array_id_post_return[] = (int)$post_id;
					}
				}
			}
		}

		return $array_id_post_return;
	}

	/**
	 * Gets to where it will show the related post backwards.
	 *
	 * @since 	6.0		2019-04-27 17:09:00		Release
	 * @param	string	 $range					Chain time range to validate
	 * @return 	string|date
	 */
	private function time_and_space( $range ){

		$option_time = $range;

		if( $option_time == 'last-24h' ){
			return date('Y-m-d', strtotime('-1 days')) ;
		}elseif( $option_time == 'last-48h' ){
			return date('Y-m-d', strtotime('-2 days')) ;
		}elseif( $option_time == 'last-week' ){
			return date('Y-m-d', strtotime('-7 days')) ;
		}elseif( $option_time == 'last-month' ){
			return date('Y-m-d', strtotime('-30 days')) ;
		}elseif( $option_time == 'last-month-3' ){
			return date('Y-m-d', strtotime('-90 days')) ;
		}elseif( $option_time == 'last-month-6' ){
			return date('Y-m-d', strtotime('-180 days')) ;
		}elseif( $option_time == 'last-month-10' ){
			return date('Y-m-d', strtotime('-300 days')) ;
		}elseif( $option_time == 'last-year' ){
			return date('Y-m-d', strtotime('-365 days')) ;
		}

	}

	/**
	 * Status post to show
	 *
	 * @since 	6.0		2019-04-27 17:09:30		Release
	 * @return 	string
	 */
	private function status_post(){
		return "publish";
	}

	/**
	 * Post exclusion by id
	 *
	 * @since 	6.0		2019-04-27 17:09:46		Release
	 * @return 	array
	 */
	private function exclude_post_by_id(){

		$ids = $this->options->exclude_post_id;

		// ─── Check if there are ids from the metabox ────────
		$ids .= $this->get_exclude_from_metabox( $this->options->post_id );

		if( ! empty ( $ids ) ){
			return array_map( 'intval' , array_filter( explode( ",", $ids ) , function ($a) { return ! empty($a); } ) );
		}
	}

	/**
	 * Control whether or not it shows the related post already drawn
	 *
	 *
	 * @since 	6.0			2019-04-27	Release
	 * @since	6.0.9.84	2019-11-25	Validation is added to see if this Yuzo is a list archive
	 * @access 	public
	 *
	 * @param 	$opt 	 	object		Current options related post
	 * @return 	string|html
	 */
	public function get_result_yuzo_post( $opt = null ){

		$this->options = $opt;
		$array_ids     = array();

		yuzo::instance()->logs->group( "Public ⇨ Alogorithm ⇨ call func get_result_yuzo_post Algorithm Start from yuzo {$this->options->post_id}" );

		// Get Ids to show
		if( $this->options->related_type == 'related' ){
			yuzo::instance()->logs->log("Algorithm type",$this->options->related_type);
			// Valid if you only want to show the related post from the metabox
			yuzo::instance()->logs->log("Related only metabox: ", $this->options->related_post_only_add_metabox);
			if( empty( $this->options->related_post_only_add_metabox ) ){
				/**
				 * Verify that if Yuzo is in an archive then this
				 * if it is a 'related' type, it will try to relate with respect
				 * to the type of archive it is in.
				 */
				if( ! empty( $this->options->display_as_list_template ) ){
					$array_ids = (array)$this->get_sql_related_archive( $opt );
				}else{
					$array_ids = (array)$this->get_sql_related( $opt );
				}
			}
		}elseif( $this->options->related_type == 'list' ){
			yuzo::instance()->logs->log("Algorithm type",$this->options->related_type);
			$array_ids = $this->get_sql_list_post( $opt );

			// Get only the formatted IDS
			if( ! empty( $array_ids ) ){
				$array_ids = yuzo_formater_ids( $array_ids, "ID" );
			}
		}

		yuzo::instance()->logs->log("Ids sql result",$array_ids);

		// Get post include from metabox
		$array_ids_metabox = [];
		$array_ids_metabox = $this->includes_post( $this->options->post_id );

		yuzo::instance()->logs->log("Metabox include",$array_ids_metabox);

		// Validate if no exists result, ask if it shows others results
		if( empty( $array_ids ) && empty( $array_ids_metabox) && isset( $opt->relation_no_result ) && $opt->relation_no_result
				// && $this->options->related_type == 'related-post'
				&& ! $opt->related_post_only_add_metabox
				&& $this->options->related_type != 'list'
			){

			$array_ids = $this->when_not_results( $opt );

			if( ! empty( $array_ids_metabox ) ){
				$array_ids = array_merge( $array_ids, $array_ids_metabox );
			}
		}else{ // Otherwise, add ids from the metabox if you have it

			$array_ids = array_merge( $array_ids_metabox, $array_ids );

		}

		yuzo::instance()->logs->log("Ids sql + Metabox include",$array_ids);

		yuzo::instance()->logs->groupEnd();

		// Get theme for related
		return is_array($array_ids) && count($array_ids) > 0 ?

					yuzo::instance()
						->public
						->related_template
						->design( $array_ids, $this->options ) : '';
	}

	/**
	 * Verify that another possible result can be sent in case there are no coincidences.
	 *
	 *
	 * @since 	6.0		2019-04-27 17:12:11		Release
	 * @since	6.0.8.4 2019-07-19 01:55:21		The exclusion filter was added
	 * @since	6.0.9.2 2019-07-24 07:41:31		Removed the fact that you printed an SQL as a result
	 * @access 	public
	 *
	 * @param 	object 	$opt	Current options related post
	 * @return 	array|null
	 */
	public function when_not_results( $opt = null ){

		global $wpdb, $post;

		// Prefix
		$prefix = $wpdb->prefix;

		// Clear sql current
		$this->sqlbuilder->clear();

		if( $opt->relation_no_result == 'random_based_cpt' ){
			$this->sqlbuilder->table( "{$prefix}posts p" )->idName('p.ID');
			// Search by type post
			$this->sqlbuilder->whereIn( 'p.post_type' , get_post_type( $post->ID ) );
			// Only public posts
			$this->sqlbuilder->where( 'p.post_status' , 'publish' );
			// Exclude current post
			$this->sqlbuilder->where( 'p.ID', '<>',  $post->ID );
			// Exclude terms hierarchical/non-hierarchical
			if(  ! empty( $this->options->exclude_taxonomy_hierarchical ) ||
			! empty( $this->options->exclude_taxonomy_no_hierarchical )  ){
				$_sql_tax = $this->set_sql_exclude_taxonomies2();
				if( ! empty( $_sql_tax ) ){
					$this->sqlbuilder->whereRaw(" p.ID NOT IN (" . $_sql_tax . " )  ");
				}
			}
			// Group by
			$this->sqlbuilder->groupBy( 'p.ID' );
			// Orden
			$this->sqlbuilder->randomOrder();
			// Number min/max post
			$this->sqlbuilder->offset( 0 );
			$this->sqlbuilder->limit( $this->get_number_post() );
		}else{
			return null;
		}

		// echo \sqlQueryBuilder::Formatter( $this->sqlbuilder->select('p.ID') );exit;
		return $wpdb->get_results( $this->sqlbuilder->select('p.ID') , ARRAY_A );

	}

	/**
	 * Get the total number of post to show in relation/list post
	 *
	 * @since	6.0			2019-06-19	Release
	 * @since	6.0.9.84	2019-11-25	If it is a Yuzo type list archive, then it sends another post number
	 * @since	6.1			2019-12-13	Some log functions added
	 *
	 * @return 	int
	 */
	// this validation is only for the sql of the relationship, but at the design level add the include post alli
	// must also be validated.
	public function get_number_post( $options = null, $return_totals = false ){

		/**
		 * If it's an archive page, then it doesn't have to calculate anything.
		 * Within the query of the archive the number is the one you have in the configuration of
		 * Wordpress list.
		 */
		if( ! empty( $this->options->display_as_list_template ) ||  ! empty( $this->options->related_post_only_add_metabox )  ){
			return $return_totals == false ? 400 : ['desktop' => 400, 'tablet' => 400, 'mobile' => 400];
		}

		$this->options = $options ?: $this->options;
		$this->options = yuzo_fix_var_design($this->options);
		// If the template is an INLINE type then it will always return 1
		if( $this->options->fieldset_design['panels-design']['template_type'] == 'inline' ) return 1;

		// ─── First I get the data of the post numbers by columns / rows and devices ────────
		// Correct the new way of displaying data
		$this->options->fieldset_design['panels-design']['design_screen_mobile']  = $this->options->fieldset_design['panels-design']['design_screen_mobile']  ?: $this->options->design_screen_mobile;
		$this->options->fieldset_design['panels-design']['design_screen_tablet']  = $this->options->fieldset_design['panels-design']['design_screen_tablet']  ?: $this->options->design_screen_tablet;
		$this->options->fieldset_design['panels-design']['design_screen_desktop'] = $this->options->fieldset_design['panels-design']['design_screen_desktop']  ?: $this->options->design_screen_desktop;
		// ───────────────────────────

		$mobile_columns  = $this->options->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_columns'];
		$mobile_rows     = $this->options->fieldset_design['panels-design']['design_screen_mobile']['design_screen_mobile_rows'];
		$tablet_columns  = $this->options->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_columns'];
		$tablet_rows     = $this->options->fieldset_design['panels-design']['design_screen_tablet']['design_screen_tablet_rows'];
		$desktop_columns = $this->options->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_columns'];
		$desktop_rows    = $this->options->fieldset_design['panels-design']['design_screen_desktop']['design_screen_desktop_rows'];

		// ─── Total rows and columns ────────
		$this->totals_post_devices['mobile']  = $mobile_columns * $mobile_rows;
		$this->totals_post_devices['tablet']  = $tablet_columns * $tablet_rows;
		$this->totals_post_devices['desktop'] = $desktop_columns * $desktop_rows;

		// The highest value must be obtained
		$totals = $return_totals == false ? (int) max( $this->totals_post_devices ) + $this->get_number_slack() : $this->totals_post_devices;
		yuzo::instance()->logs->log("Total calculate to show:", (int)$totals);
		return $totals;
	}

	/**
	 * Calculate if it is necessary to show more post than normal
	 *
	 * @since	6.1.52	2020-02-09	Release
	 * @return 	int
	 */
	public function get_number_slack(){
		if( ! empty( $this->settings->general_exclude_post_without_image ) ){
			return $this->number_of_slack;
		}
		return 0;
	}

	/**
	 * Check if a given or requested taxonomy is currently associated with a given type of publication.
	 *
	 * @author Dominik Schilling
	 * @license GPLv2
	 * @link http://wpgrafie.de/137/
	 *
	 * @version 0.1
	 * @since	6.0				2019-05-02 04:28:08		Release
	 * @param 	object|string 	$post_type
	 * @param 	string 			$taxonomy Optional. The default value is zero.
	 * @return 	bool 			True if Taxonomy is assigned to the Publication Type, false if not, and if entered incorrectly
	 */
	private function is_taxonomy_assigned_to_post_type( $post_type, $taxonomy = null ) {
		if ( is_object( $post_type ) )
			$post_type = $post_type->post_type;
		if ( empty( $post_type ) )
			return false;
		$taxonomies = get_object_taxonomies( $post_type );
		if ( empty( $taxonomy ) )
			$taxonomy = get_query_var( 'taxonomy' );
		return in_array( $taxonomy, $taxonomies );
	}

	/**
	 * Check if a CPT has any taxonomy
	 *
	 * @param 	string 	$post_type	Name custom post type
	 * @return 	boolean
	 */
	public function is_cpt_has_taxonomy( $post_type ){
		if ( is_object( $post_type ) )
			$post_type = $post_type->post_type;
		if ( empty( $post_type ) )
			return false;

		return get_object_taxonomies( $post_type );
	}

	/**
	 * Get all the custom terms of non-hierarchical taxonomies from the
	 * options.
	 *
	 * @since 	6.0		2019-05-04 19:24:24		Release
	 * @return 	array
	 */
	public function get_terms_from_options_tax_no_her(){

		$terms_item         = [];
		$term_from_options = $this->options->related_taxonomy_no_hierarchical;

		// Add id terms of tags add custom (no hierarchical)
		if( ! empty ( $term_from_options ) ){

			foreach( $term_from_options as $k => $v ){
				$array_term_no_hierarchical[] = explode(",", $v);
			}
			$array_term_no_hierarchical = yuzo_array_flatten( $array_term_no_hierarchical , true );

			if( is_array($array_term_no_hierarchical) ){

				foreach ($array_term_no_hierarchical as $value) {

					$split_string = explode ("|", $value);

					if( ! empty( $split_string[0] ) && ! empty( $split_string[1] ) ){

						$temp_term = get_term_by( 'name', (string)$split_string[0], (string)$split_string[1] );

						if( ! empty( $temp_term ) ) { $terms_item[] = $temp_term->term_taxonomy_id ; }

					}
				}

			}
		}

		return $terms_item;

	}

	/**
	 * Exclude post desde metabox
	 *
	 * @since 	6.0		2019-05-19 15:55:50		Release
	 *
	 * @param 	int 	$post_id	ID setting current
	 * @return 	string
	 */
	private function get_exclude_from_metabox( $post_id ){

		global $post;
		$metabox_yuzo = empty($this->metabox_data) ? $this->metabox_data =  get_post_meta( (isset( $post ) && is_object($post) ? $post->ID : 0), "yuzo-in-post" ) : $this->metabox_data;
		$id_setting   = $post_id;
		$ids_exclude  = '';

		if( ! empty( $metabox_yuzo ) ){

			$ids_exclude = ! empty( $metabox_yuzo[0]['in-custom-post']['exclude_post'.$id_setting] ) ?
									$metabox_yuzo[0]['in-custom-post']['exclude_post'.$id_setting] : '';
		}

		return $ids_exclude;

	}

	/**
	 * Include post from metabox
	 *
	 * @since 	6.0		2019-05-19 15:55:50		Release
	 *
	 * @param 	int 	$post_id	ID setting current
	 * @return 	array
	 */
	private function includes_post( $post_id ){

		global $post;
		$metabox_yuzo = empty($this->metabox_data) ? $this->metabox_data =  get_post_meta( (isset( $post ) && is_object($post) ? $post->ID : 0), "yuzo-in-post" ) : $this->metabox_data;
		$id_setting   = $post_id;
		$ids_include  = '';

		if( ! empty( $metabox_yuzo ) ){

			$ids_include = ! empty( $metabox_yuzo[0]['in-custom-post']['include_post_'.$id_setting] ) ?
									$metabox_yuzo[0]['in-custom-post']['include_post_'.$id_setting] : '';
		}

		return (array)pf_string_to_array_valid($ids_include);
	}

	/**
	 * Disable the current Yuzo
	 *
	 * @since	6.0.9.83	2019-10-03		Release
	 *
	 * @param 	int			$post_id		Id post
	 * @return	bool
	 */
	public function disabled_current_yuzo( $post_id ){
		global $post;

		$metabox_yuzo = empty($this->metabox_data) ? $this->metabox_data =  get_post_meta( (isset( $post ) && is_object($post) ? $post->ID : 0), "yuzo-in-post" ) : $this->metabox_data;

		if( ! empty( $metabox_yuzo ) ){

			return 	! empty( $metabox_yuzo[0]['in-custom-post']['disabled_yuzo_'.$post_id] )
					&& $metabox_yuzo[0]['in-custom-post']['disabled_yuzo_'.$post_id]
					? true : false;
		}

		return false;
	}

	/**
	 * Vefifica si un list/related post esta activo
	 *
	 * @since	6.0		2019-06-24 03:14:03		Release
	 * @param 	int		$id						ID of one Yuzo
	 * @return 	bool
	 */
	public function verify_is_list_is_active( $id = null ){

		global $wpdb;

		// ─── We get the last instance of the sql constructor ────────
		$sqlbuilder = \sqlQueryBuilder::instance();

		$sqlbuilder->clear();
		$sqlbuilder->table( "{$wpdb->prefix}postmeta" );
		$sqlbuilder->where( "post_id", (int)$id );
		$sqlbuilder->where( "meta_key", 'yuzo_related_post_active' );

		$r = $wpdb->get_row(
			$sqlbuilder->select('meta_value as _active_'),
			ARRAY_A
		);

		return isset( $r['_active_'] ) && $r['_active_'] == "1";

	}

	/**
	 * Function that calculates taxonomy hierarchical and no non-hierarchical for queries
	 *
	 * @since 	6.0.9.7		2019-08-01 23:21:12		Release
	 *
	 * @param	string		$tax					Taxonomy options
	 * @param	string		$tax_no					Taxonomy non-hierarchical options
	 * @return	string
	 */
	public function set_sql_include_taxonomies( $tax = null, $tax_no = null ){

		$this->options->include_taxonomy_hierarchical = $tax != null ? $tax : $this->options->include_taxonomy_hierarchical;
		$this->options->related_taxonomy_no_hierarchical = $tax_no != null ? $tax_no : $this->options->related_taxonomy_no_hierarchical;

		if( ! empty( $this->options->include_taxonomy_hierarchical ) ||
			! empty( $this->options->related_taxonomy_no_hierarchical ) ){
			// ─── Vars ────────
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tax    = $this->options->include_taxonomy_hierarchical;
			$tax_no = $this->options->related_taxonomy_no_hierarchical;

			// ─── Get the sorted data in an array ────────
			$taxonomy_options = pf_get_taxonomy_and_terms_available( $tax );                  // Hierarchical taxonomies
			$taxonomy_all     = ! empty( $taxonomy_options[0] ) ? $taxonomy_options[0] : [];
			$taxonomy_terms   = ! empty( $taxonomy_options[1] ) ? $taxonomy_options[1] : [];
			$taxonomy_no_her  = pf_get_taxonomy_and_terms_available( $tax_no, 2 );

			$tax_tags = [];
			if( $this->options->taxonomies_cat_tag_relation == 'or' ){
				$taxonomy_terms   = array_merge( $taxonomy_terms, pf_get_taxonomy_and_terms_available( $tax_no, 2 ) ); // Non-hierarchical taxonomies
			}else{
				$tax_tags         = $taxonomy_no_her;
			}
			//
			// For non-hierarchical taxonomies


			// ─── If there are taxonomies that have 'all' indicated then I relate the table and condition ────────
			if( ! empty( $taxonomy_all ) ){
				$this->sqlbuilder->lelfJoin( "{$prefix}term_relationships tr" , "tr.object_id = p.ID" );
				$this->sqlbuilder->lelfJoin( "{$prefix}term_taxonomy tt" , "tt.term_taxonomy_id = tr.term_taxonomy_id" );
				$tax_all_implodes =  implode( "','", $taxonomy_all ) ;
				$this->sqlbuilder->whereIN( 'tt.taxonomy', $tax_all_implodes );
			}

			// ─── We need the terms of taxonomies where they do not have the option of 'all' to treat it individually ────────
			// will obtain taxonomies first
			$tax_no_all = [];
			if( ! empty( $taxonomy_terms ) ){
				foreach ($taxonomy_terms as $key => $value) {
					$tax_no_all[] = $key;
				}
			}
			// ─── Started to build the sql of the strict relation by taxonomy ────────
			$_sql_raw = '';
			if( ! empty( $tax_no_all ) ){
				$_sql_raw       = " select p.ID from {$prefix}posts p ";
				$_sql_raw_where = [];
				$i              = 0;

				// If everything is OR then everything must be within the same pattern
				if( $this->options->include_taxonomy_hierarchical_operator['include_taxonomy_relation'] == 'or' &&
					$this->options->include_taxonomy_not_hierarchical_operator['include_taxonomy_no_relation'] == 'or' &&
					$this->options->taxonomies_cat_tag_relation == 'or' ){

					$_sql_raw .= ", {$prefix}terms t{$i}, {$prefix}term_taxonomy tt{$i}, {$prefix}term_relationships tr{$i} ";
					$_sql_raw_where[] = "p.id = tr{$i}.object_id";
					$_sql_raw_where[] = "t{$i}.term_id = tt{$i}.term_id";
					$_sql_raw_where[] = "tr{$i}.term_taxonomy_id = tt{$i}.term_taxonomy_id";
					$_sql_raw_where[] = "(tt{$i}.taxonomy in ('".implode("','",$tax_no_all)."') and tt{$i}.term_id = t{$i}.term_id and t{$i}.slug in ('".implode("','", yuzo_array_flatten($taxonomy_terms, true))."'))";

				}else{

					foreach ($tax_no_all as $key => $value) {
						$i++;
						if( empty( $taxonomy_no_her[$value] ) ){
							// If the terms are by OR
							if( $this->options->include_taxonomy_hierarchical_operator['include_taxonomy_relation'] == 'or' ){
								$_sql_raw .= ", {$prefix}terms t{$i}, {$prefix}term_taxonomy tt{$i}, {$prefix}term_relationships tr{$i} ";
								$_sql_raw_where[] = "p.id = tr{$i}.object_id";
								$_sql_raw_where[] = "t{$i}.term_id = tt{$i}.term_id";
								$_sql_raw_where[] = "tr{$i}.term_taxonomy_id = tt{$i}.term_taxonomy_id";
								$_sql_raw_where[] = "(tt{$i}.taxonomy = '$value' and tt{$i}.term_id = t{$i}.term_id and t{$i}.slug in ('".implode("','", yuzo_array_flatten($taxonomy_terms[$value]))."'))";
							}elseif( $this->options->include_taxonomy_hierarchical_operator['include_taxonomy_relation'] == 'and' ){
								// if the terms are by AND
								$terms  = yuzo_array_flatten($taxonomy_terms[$value]);
								if( count( $terms ) > 0 ){
									foreach ($terms as $_v) {
										$_sql_raw .= ", {$prefix}terms t{$i}, {$prefix}term_taxonomy tt{$i}, {$prefix}term_relationships tr{$i} ";
										$_sql_raw_where[] = "(p.id = tr{$i}.object_id";
										$_sql_raw_where[] = "t{$i}.term_id = tt{$i}.term_id";
										$_sql_raw_where[] = "tr{$i}.term_taxonomy_id = tt{$i}.term_taxonomy_id";
										$_sql_raw_where[] = "(tt{$i}.taxonomy = '$value' and tt{$i}.term_id = t{$i}.term_id and t{$i}.slug = '$_v') )";
										$i++;
									}
								}
							}
						}else{
							if( $this->options->include_taxonomy_not_hierarchical_operator['include_taxonomy_no_relation'] == 'or' ){
								$_sql_raw        .= ", {$prefix}terms t{$i}, {$prefix}term_taxonomy tt{$i}, {$prefix}term_relationships tr{$i} ";
								$_sql_raw_where[] = "p.id = tr{$i}.object_id";
								$_sql_raw_where[] = "t{$i}.term_id = tt{$i}.term_id";
								$_sql_raw_where[] = "tr{$i}.term_taxonomy_id = tt{$i}.term_taxonomy_id";
								$_sql_raw_where[] = "(tt{$i}.taxonomy = '$value' and tt{$i}.term_id = t{$i}.term_id and t{$i}.slug in ('".implode("','", yuzo_array_flatten($taxonomy_terms[$value]))."'))";
							}elseif( $this->options->include_taxonomy_not_hierarchical_operator['include_taxonomy_no_relation'] == 'and' ){
								$terms  = yuzo_array_flatten($taxonomy_terms[$value]);
								if( count( $terms ) > 0 ){
									foreach ($terms as $_v) {
										$_sql_raw .= ", {$prefix}terms t{$i}, {$prefix}term_taxonomy tt{$i}, {$prefix}term_relationships tr{$i} ";
										$_sql_raw_where[] = "(p.id = tr{$i}.object_id";
										$_sql_raw_where[] = "t{$i}.term_id = tt{$i}.term_id";
										$_sql_raw_where[] = "tr{$i}.term_taxonomy_id = tt{$i}.term_taxonomy_id";
										$_sql_raw_where[] = "(tt{$i}.taxonomy = '$value' and tt{$i}.term_id = t{$i}.term_id and t{$i}.slug = '$_v') )";
										$i++;
									}
								}
							}
						}
					}
				}

			}


			$_sql_raw_where2 = [];
			if( ! empty( $tax_tags ) ){
				foreach ($tax_tags as $key => $value) {
					$i++;
					if( $this->options->include_taxonomy_not_hierarchical_operator['include_taxonomy_no_relation'] == 'or' ){
						$_sql_raw        .= ", {$prefix}terms t{$i}, {$prefix}term_taxonomy tt{$i}, {$prefix}term_relationships tr{$i} ";
						$_sql_raw_where2[] = "p.id = tr{$i}.object_id";
						$_sql_raw_where2[] = "t{$i}.term_id = tt{$i}.term_id";
						$_sql_raw_where2[] = "tr{$i}.term_taxonomy_id = tt{$i}.term_taxonomy_id";
						$_sql_raw_where2[] = "(tt{$i}.taxonomy = '$key' and tt{$i}.term_id = t{$i}.term_id and t{$i}.slug in ('".implode("','", yuzo_array_flatten($tax_tags[$key]))."'))";
					}elseif( $this->options->include_taxonomy_not_hierarchical_operator['include_taxonomy_no_relation'] == 'and' ){
						$terms  = yuzo_array_flatten($tax_tags[$key]);
						if( count( $terms ) > 0 ){
							foreach ($terms as $_v) {
								$_sql_raw .= ", {$prefix}terms t{$i}, {$prefix}term_taxonomy tt{$i}, {$prefix}term_relationships tr{$i} ";
								$_sql_raw_where2[] = "(p.id = tr{$i}.object_id";
								$_sql_raw_where2[] = "t{$i}.term_id = tt{$i}.term_id";
								$_sql_raw_where2[] = "tr{$i}.term_taxonomy_id = tt{$i}.term_taxonomy_id";
								$_sql_raw_where2[] = "(tt{$i}.taxonomy = '$key' and tt{$i}.term_id = t{$i}.term_id and t{$i}.slug = '$_v') )";
								$i++;
							}
						}
					}
				}
			}

			if( ! empty( $_sql_raw ) ){
				$_sql_raw .= " where (" . implode(" and  ",$_sql_raw_where) . ")";
				if( ! empty( $_sql_raw_where2 ) ){
					$_sql_raw .= " AND ( ". implode(" and  ",$_sql_raw_where2) . ")";
				}
			}

			return $_sql_raw;

		}
	}

	public function set_sql_exclude_taxonomies(){
		// ─── First I put these values ​​in a temporary variable ────────
		$temp_tax    = $this->options->include_taxonomy_hierarchical;
		$temp_tax_no = $this->options->related_taxonomy_no_hierarchical;

		// ─── I put the exclusion values ​​within the inclusion variables ────────
		// I do this because the function 'set_sql_include_taxonomies' is handled with these 2 variables inside
		$this->options->include_taxonomy_hierarchical    = $this->options->exclude_taxonomy_hierarchical;
		$this->options->related_taxonomy_no_hierarchical = $this->options->exclude_taxonomy_no_hierarchical;

		// ─── I execute the function that the filtered sql returns to me ────────
		$sql = $this->set_sql_include_taxonomies();

		// ─── I return the values ​​corresponding to their variables ────────
		$this->options->include_taxonomy_hierarchical    = $temp_tax;
		$this->options->related_taxonomy_no_hierarchical = $temp_tax_no;

		// ─── Return the SQL ────────
		return $sql;
	}

	public function set_sql_exclude_taxonomies2(){
		if( ! empty( $this->options->exclude_taxonomy_hierarchical ) ||
		! empty( $this->options->exclude_taxonomy_no_hierarchical ) ){

			// ─── Vars ────────
			global $wpdb;
			$prefix = $wpdb->prefix;
			$tax    = $this->options->exclude_taxonomy_hierarchical;
			$tax_no = $this->options->exclude_taxonomy_no_hierarchical;

			// ─── Get the sorted data in an array ────────
			$taxonomy_options = pf_get_taxonomy_and_terms_available( $tax ); // Hierarchical taxonomies
			$taxonomy_terms   = ! empty( $taxonomy_options[1] ) ? $taxonomy_options[1] : [];
			$taxonomy_terms   = array_merge( $taxonomy_terms, pf_get_taxonomy_and_terms_available( $tax_no, 2 ) ); // Non-hierarchical taxonomies

			$term_final = yuzo_array_flatten($taxonomy_terms);
			if( ! empty( $term_final ) ){
				$_sql_raw       = " select p.ID
				from {$prefix}posts p , {$prefix}terms t, {$prefix}term_taxonomy tt, {$prefix}term_relationships tr
				where p.id = tr.object_id and t.term_id = tt.term_id and tr.term_taxonomy_id = tt.term_taxonomy_id
				and t.slug in ('".implode("','", $term_final )."') ";

				return $_sql_raw;
			}
		}

		return null;
	}


} }