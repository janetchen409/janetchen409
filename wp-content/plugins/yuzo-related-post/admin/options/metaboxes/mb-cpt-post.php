<?php
/**
 * Metabox Yuzo for post
 * @since 	6.0			2019-05-18		Release
 * @since 	6.0.2		2019-07-12		Valid that only shows on specific pages
 * @since	6.0.9.6		2019-07-28		Comments removed unnecessary, new notes
 * @since	6.0.9.8		2019-08-28		The filter of the manually added posts was not catching correctly, now if you grab them well.
 * @since	6.0.9.83	2019-10-04		- The "general" tab was added
 * 										- The "image posts" option was added to show a specific Yuzo image for that post.
 * 										- Option added <code>Disabled this Yuzo</code> within the metabox, this to be able to deactivate Yuzo in specific posts.
 * @since	6.0.9.84	2019-11-25		- Notice message corrections in the code
 * 										- New message in the metabox when there is no Yuzo created or active
 */

/*
|--------------------------------------------------------------------------
| Creation METABOX
|--------------------------------------------------------------------------
*/
// ─── I get all Yuzo's list ────────
// Each of these lists have their own independent configurations
// FIXME: This can be put in a callback so that it does not run at any time
// make if field is a string then make a callback
// FIXME: validate that they only appear in the CPT where the general setting is taking them
global $pagenow, $post;
if( ! is_admin() || ! in_array( $pagenow, ['post.php','edit.php','post-new.php'] )  ) return;
$list_list_yuzo_active = yuzo_get_all_list_instance();
$metabox_config        = [];
$index                 = 0;
$centinela_old_metabox = false;
$settings              = yuzo_get_option();
$cpt_avalibled         = ! empty( $settings->general_cpt_to_counter ) ? $settings->general_cpt_to_counter : ['post'];
if( ! empty( $list_list_yuzo_active ) ){

	$metabox_config['id']          = 'in-custom-post';
	$metabox_config['type']        = 'accordion';
	$metabox_config['collapsible'] = true;
	$metabox_config['disabled']    = ['disabled_yuzo' => 0];

	$array_where_list = array(
		'content'   => __('Content','yuzo'),
		'widget'    => __('Widget','yuzo'),
		'shortcode' => __('Inline','yuzo'),
	);

	foreach ($list_list_yuzo_active as $key => $value) {
		if( ! empty( $value ) ){
			foreach ($value as $k => $v) {

				if( ! empty( $v ) ){
					$id              = $v['ID'];
					$setting         = unserialize( $v['setting'] );
					$setting_general = empty( $settings ) ? yuzo_get_option() : $settings;
					$image_default   = empty( $settings->general_image_default['url'] ) ? YUZO_IMAGE_DEFAULT : $settings->general_image_default['url'];

					$setting = yuzo_fix_var_design( $setting, true );
					if( empty( $setting['fieldset_design'] )  ) return;
					$metabox_config['accordions'][$id]['title'] = get_the_title( $id ) . ' &nbsp;<i>(' . $array_where_list[$setting['fieldset_design']['panels-design']['where_show']] . ')</i>';
					if( $index == 0 ){
						$metabox_config['accordions'][$id]['active'] 	= true;
					}
					$metabox_config['accordions'][$id]['fields'] 	= array(
						array(
							'id'                 => 'include_post_'.$id,
							'title'              => 'Search post for include',
							'type'               => 'search_post',
							'desc'               => __('Find the post you want to include within this list/related post','yuzo'),
							'enable_show_image'  => true,
							'enable_show_filter' => true,
							'image_default'      => $image_default,
							'post_type'          => $cpt_avalibled
						),
						array(
							'id'                 => 'exclude_post'.$id,
							'title'              => 'Search post for exclude',
							'type'               => 'search_post',
							'desc'               => __('Find the post you want to exclude within this list/related post','yuzo'),
							'enable_show_image'  => true,
							'enable_show_filter' => true,
							'image_default'      => $image_default,
							'post_type'          => $cpt_avalibled
						),
						array(
							'id'         => 'disabled_yuzo_'.$id,
							'type'       => 'switcher',
							'title'      => __( 'Disable this Yuzo', 'yuzo' ),
							'text_on'    => 'Enabled',
							'text_off'   => 'Disabled',
							'text_width' => '100',
						),
					);
				}
				$index++;
			}
		}
	}
}else{
	$metabox_config['id']      = 'in-custom-post';
	$metabox_config['type']    = 'submessage';
	$metabox_config['style']   = 'warning';
	$metabox_config['content'] = __( 'There is no active Yuzo, please activate or <a href="'. admin_url('edit.php?post_type=yuzo') .'">create one</a>', 'fdc' );
}

// ─── Metabox display ────────
PF::addMetabox( YUZO_ID . '-in-post', array(
	'title'        => __('Yuzo custom for this post','yuzo'),   // Need!
	'post_type'    => $cpt_avalibled,  //['post', 'page'],
	'show_restore' => false,
) );
PF::addSection( YUZO_ID . '-metabox-list', array(
	'parent' => YUZO_ID . '-in-post',
	'title'  => __('For every Yuzo','yuzo'),
	'icon'   => null,
	'fields' => array($metabox_config)
));
PF::addSection( YUZO_ID . '-metabox-general-options', array(
	'parent' => YUZO_ID . '-in-post',
	'title'  => __('General','yuzo'),
	'icon'   => null,
	'fields' => array(
		array(
			'id'    => 'photo_post',
			'type'  => 'media',
			'title' => 'Image post',
			'desc'  => __( 'This is a photo that Yuzo will take as the first alternative
in mind to show the image of this post', 'yuzo' )
		),
	)
));