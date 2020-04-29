<?php
if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access pages directly.
/**
 *
 * Class Field backup
 *
 * @since 	1.0 	2019-03-07	Release
 * @since 	1.4.9	2019-07-12	Now you can also import / export for metabox
 * @since	1.5.5	2019-10-28	The name of the textarea where the import code is placed to 'pf_transient[pf_import_data]' was corrected
 *
 */
if( ! class_exists( 'PF_Field_backup' ) ) {
class PF_Field_backup extends PF_classFields {

	public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
		parent::__construct( $field, $value, $unique, $where, $parent );
	}

	/**
	 * Render field
	 *
	 * @since	1.6		2019-12-02		Remove the primaty-button class
	 * @since	1.6.32	2020-01-02		The sanitize variable was used to pass the values
	 * @return	string
	 */
	public function render() {

		$unique = $this->unique;

		// ─── Sanitize get ────────
		//array_walk_recursive( $sanitize_get = wp_unslash( $_GET ), 'sanitize_text_field' );
		$sanitize_get = $_GET;

		if( $this->where != 'metabox' ){
			$val = json_encode( get_option( $unique ) );
		}else{
			$val = get_post_meta( ( isset($sanitize_get['post']) ? $sanitize_get['post'] : 0 ) , $unique );
			$val = json_encode( $val[0] );
		}

		$nonce  = wp_create_nonce( 'pf_backup_nonce' );
		$export = add_query_arg( array( 'action' => 'pf-export', 'export' => $unique, 'nonce' => $nonce, 'where' => $this->where, 'post_id' => (isset($sanitize_get['post']) ? $sanitize_get['post'] : 0) ), admin_url( 'admin-ajax.php' ) );

		echo $this->field_before();

		echo '<textarea name="pf_transient[pf_import_data]" class="pf-import-data"></textarea>';
		echo '<button type="submit" class="button button-primary pf-confirm pf-import" data-unique="'. $unique .'" data-nonce="'. $nonce .'">'. esc_html__( 'Import', 'pf' ) .'</button>';
		echo '<small>( '. esc_html__( 'copy-paste your backup string here', 'pf' ).' )</small>';

		echo '<hr />';
		echo '<textarea readonly="readonly" class="pf-export-data">'. $val .'</textarea>';
		echo '<a href="'. esc_url( $export ) .'" class="button button-primary pf-export target="_blank">'. esc_html__( 'Export and Download Backup', 'pf' ) .'</a>';

		if( $this->where != 'metabox' ){
			echo '<hr />';
			echo '<button type="submit" name="'.$this->field_name().'" value="pf_reset_all" class="button pf-warning-primary pf-confirm pf-reset" data-unique="'. $unique .'" data-nonce="'. $nonce .'">'. esc_html__( 'Reset All', 'pf' ) .'</button>';
			echo '<small class="pf-text-error">'. esc_html__( 'Please be sure for reset all of options.', 'pf' ) .'</small>';
		}

		if( $this->where === 'metabox' ){
			echo '<input type="hidden" name="unique" class="pf-backup-unique" value="'.$unique.'" />';
			echo '<input type="hidden" name="where"  class="pf-backup-where" value="'.$this->where.'" />';
			echo '<input type="hidden" name="post_id"  class="pf-backup-post_id" value="'.( isset($sanitize_get['post']) ? $sanitize_get['post'] : 0 ).'" />';
		}

		echo $this->field_after();

	}

}
}
