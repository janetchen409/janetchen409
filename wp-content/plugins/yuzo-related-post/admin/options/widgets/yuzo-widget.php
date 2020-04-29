<?php
/**
 * Configuracion del widget
 *
 * @since	6.0.9.8		2019-08-28		Release
 * @since	6.0.9.83	2019-10-04		Now if there is no active Yuzo result then it does not display the full widget
 */
// Get yuzo widget list active
use YUZO\Core\YUZO_Core as yuzo;
PF::addWidget( 'yuzo_widget', array(
	'title'       => 'uzo Widget',
	'classname'   => 'class-yuzo-widget',
	'description' => 'Show a list/related post from the Yuzo settings',
	'fields'      => array(
		array(
			'id'      => 'title',
			'type'    => 'text',
			'title'   => 'Title',
		),
        array(
			'id'          => 'yuzo-widget-selected',
			'type'        => 'select',
			'title'       => __('Select a Yuzo','yuzo'),
			'placeholder' => __('Select a Yuzo widget','yuzo'),
			'desc'        => __('Select a Yuzo widget to display','yuzo'),
			'options'     => yuzo_get_list_widget_active(), // FIXME: make it run by callback better
		),
    ),
));

if( ! function_exists( 'yuzo_widget' ) ) {
function yuzo_widget( $args, $instance ) {

	global $post;

	$id_options = (int) $instance['yuzo-widget-selected'];
	$opts       = yuzo::instance()->public->related_options->get_setting_by_id( $id_options );
	yuzo::instance()->logs->group('Widget: '.$id_options);
	$opt_serial = ! empty( $opts ) && ! empty( $opts[0] ) ? $opts[0]['setting'] : null;
	$id_serial  = ! empty( $opts ) && ! empty( $opts[0] ) ? $opts[0]['post_id'] : null;

	if( ! empty( $opt_serial ) ){

		$opt_object = unserialize( $opt_serial );

		// check if it is a widget because the user can change its location
		if( $opt_object['fieldset_design']['panels-design']['where_show'] != 'widget' ) return;

		$opt_object['post_id'] = $id_serial; // ? $post->ID : 0;

		if( $opt_object ){

			// Convert to object
			$opt_object         = (object)$opt_object;
			$yuzo_widget_active = yuzo::instance()->public->related_algorithm->verify_is_list_is_active( $id_options );

			// If yuzo list type widget is deactivated it should not be displayed
			if( ! $yuzo_widget_active ) return;
			yuzo::instance()->logs->info( "The widget is active");

			// Valid if it should be shown on this page
			$display = yuzo::instance()->public->related_display->display_related_post( $opt_object );
			if( $display ){

				$html = yuzo::instance()->public->related_algorithm->get_result_yuzo_post( (object)$opt_object );

				if( $html ){
					yuzo::instance()->logs->log( "Before printing the widget");
					echo $args['before_widget'];

					if ( ! empty( $instance['title'] ) ) {
						echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
					}else{
						echo apply_filters( 'widget_title', $instance['title'] );
					}

					echo $html;

					echo $args['after_widget'];
					yuzo::instance()->logs->log( "After printing the widget");
				}

			}
		}
	}
	yuzo::instance()->logs->groupEnd();
}
}