<?php
/**
 * @since   6.0     	2019-04-14		Release
 * @since	6.0.5		2019-07-12 		The option was added Post Type: 'Select the post type you want to count and show in the post list column'
 * @since	6.0.9.7		2019-08-01		Option added 'Mode Debug': This is a review or developer mode
 * @since	6.0.9.8		2019-08-28		Title changed, no longer has HTML
 * @since	6.0.9.83	2019-10-04		The save buttons on the footer were removed, now the top menu is sticky
 * @since	6.1.32		2020-01-06		The help text of the views was modified
 */
/*
|--------------------------------------------------------------------------
| Creation options
|--------------------------------------------------------------------------
*/
$imagen = [
	'https://i.imgur.com/SFIgSmV.jpg','https://i.imgur.com/SygStno.jpg','https://i.imgur.com/7iMtzX3.jpg',
	'https://i.imgur.com/GGvrpzn.jpg','https://i.imgur.com/7B1FFGU.jpg','https://i.imgur.com/OfXQuix.jpg',
	'https://i.imgur.com/Qj4W84O.jpg','https://i.imgur.com/C7T6SvH.jpg','https://i.imgur.com/jFcViTG.jpg',
	'https://i.imgur.com/bzZq35o.jpg',
];
$fivestart = '<div class="fdc-fives fdc-tooltip top"><a href="http://bit.ly/Yuzo5Star" target="_blank" style="text-decoration: none">
<span class="dashicons dashicons-wordpress" style="color:black"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
<span class="dashicons dashicons-star-filled" style="color:#178BE7"></span>
</a>
<span class="tiptext"><img src="'. $imagen[rand(0,9)] .'.jpg" />Show us some 💙 by writing your review</span>
</div>';
PF::addSetting( YUZO_ID . '-setting' , array(
    'menu_title'            => 'Yuzo Setting',
    'menu_slug'             => 'yuzo-setting',
    'menu_parent'           => 'yuzo',
    'menu_type'             => 'submenu',
    'menu_title_sub'        => 'Settings',
    'setting_vertical_mode' => FALSE,
    'setting_title'         => __('Setting','yuzo'),
    'ajax_save'             => FALSE,
    'show_search'           => FALSE,
    'show_all_options'      => FALSE,
    'show_buttons_footer'   => FALSE,
    'show_footer'           => FALSE,
    'footer_credit'         => 'Made with 💙 by <span class="yzp-admin-credit">Lenin Zapata</span><span class="fdc-admin-footer-separate">|</span>' . $fivestart,
    'show_reset_section'    => FALSE,
));
// options
PF::addSection(YUZO_ID . '-general', array(
	'parent' => YUZO_ID . '-setting',
    'title'  => __( 'General', 'yuzo' ),
    'icon'   => 'fa fa-sliders',
    'fields' => [
        array(
			'id'      => 'general_image_default',
			'type'    => 'media',
			'title'   => __('Default image URL','yuzo'),
			'desc'    => __('Default image in case there is no image in the post','yuzo'),
			'default' => [ 'thumbnail' =>  YUZO_IMAGE_DEFAULT,'url' => YUZO_IMAGE_DEFAULT ],
			'url'     => false,
        ),
        array(
			'id'         => 'general_seo_feed',
			'type'       => 'switcher',
			'title'      => __('Show on feed','yuzo'),
			'desc'       => __('Displays related post in the feed/rss','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '150',
			'default'    => true,
		),
		array(
			'id'         => 'general_mode_debug',
			'type'       => 'switcher',
			'title'      => __('Mode Debug','yuzo'),
			'subtitle'   => __('This is a developer mode.','yuzo'),
			'desc'       => __('When activating this option, the results of the development tests or errors will be displayed in the browser console, it is recommended to keep
this option disabled', 'yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '150',
			'default'    => false,
        ),
    ],
));

PF::addSection(YUZO_ID . '-seo', array(
    'parent' => YUZO_ID . '-setting',
    'title'  => __('SEO','yuzo'),
    'icon'   => 'fa fa-square-o',
    'fields' => [
        array(
			'id'         => 'general_seo_target_link',
			'type'       => 'switcher',
			'title'      => __('Target link','yuzo'),
			'desc'       => __('When clicking the related post open in a tab (target="_blank")','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '150',
			'default'    => false,
        ),
        array(
			'id'         => 'general_seo_rel_nofollow',
			'type'       => 'switcher',
			'title'      => __('Rel=nofollow','yuzo'),
			'desc'       => __('If you enable this option yuzo related links will not be tracked by search engines','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '150',
			'default'	 => true,
        ),
        array(
			'id'         => 'general_seo_remove_href',
			'type'       => 'switcher',
			'title'      => __('Remove link href in related','yuzo'),
			'desc'       => __('If you activate this option, remove the href attribute from the related links, clicking will work normally','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '150',
			'default'	 => true,
        ),
	],
) );


PF::addSection(YUZO_ID . '-view', array(
    'parent' => YUZO_ID . '-setting',
    'title'  => __('Views','yuzo'),
    'icon'   => 'fa fa-eye',
    'fields' => [
        array(
			'id'          => 'general_cpt_to_counter',
			'type'        => 'button_set',
			'title'       => __('Post type','yuzo'),
			//'chosen'      => true,
			'multiple'    => true,
			//'placeholder' => __('Select one or several','yuzo'),
			//'options'     => 'post_types',
			'options' => ['post'=>__( 'Post', 'yuzo' ),'page'=>__( 'Page', 'yuzo' )],
			'default' => ['post'],
			'desc'    => __('Select the post type you want to count and show in the post list column','yuoz'),
		),
        array(
			'id'      => 'general_views_format',
			'type'    => 'select',
			'title'   => __('Format thousands','yuzo'),
			'options' => array(
                'none'  => __('None','yuzo'),
                ',' => __(',','yuzo'),
                '.' => __('.','yuzo'),
			),
			'desc'    => __('Select between 2 formats that you can identify thousands in the hit counter by Post', 'yuzo'),
			'default' => 'none',
        ),
        array(
			'id'         => 'general_views_1k',
			'type'       => 'switcher',
			'title'      => __('1000 to 1K','yuzo'),
			'desc'       => __('Cut the hit counter','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '150',
			'default'    => true,
        ),
		array(
			'id'      => 'general_show_views',
			'type'    => 'select',
			'title'   => __('Show views','yuzo'),
			'options' => array(
                'none'         => __('Not show','yuzo'),
                'views_top'    => __('Show top of content','yuzo'),
                'views_bottom' => __('Show under content','yuzo'),
			),
			'desc'    => __('With this you can show the views that each post has, you can also use the different ways 👇🏻 to show the views to suit your Theme.', 'yuzo'),
			'default' => 'views_top',
		),
		array(
			'id'         => 'general_show_views_text',
			'type'       => 'text',
			'title'      => __('View text','yuzo'),
			'desc'       => __('This is the text that will be just before showing the number of visits.<br />
			Use the variable <code>{views}</code> to display the views, you can also use <code>{icon}</code> to show an icon.','yuzo'),
			'default'    => '{icon} {views}',
			'dependency' => array( 'general_show_views', 'any', 'views_top,views_bottom' ),
        ),
		array(
            'type'    => 'content',
            'content' => '<div class="pf-note">You can put the  <code>[yuzo_views]</code> or for template <code>&lt;?php echo do_shortcode( "[yuzo_views]" ); ?&gt;</code>
            With this option you can put the hit counter anywhere via a shortcode.<br />
            And if you want to display the counter of a specific post you can use this <code>[yuzo_views id=123]</code>.</div>',
		),
	],
) );

PF::addSection(YUZO_ID . '-custom', array(
    'parent' => YUZO_ID . '-setting',
    'title'  => __('Custom Style','yuzo'),
    'icon'   => 'fa fa-paint-brush',
    'fields' => [
		array(
			'id'       => 'custom_css',
			'type'     => 'code_editor',
			'before'   => '<p class="pf-text-muted"><strong>CSS Custom</strong> You can customize the CSS of Yuzo in a general way or by specific Yuzo (remember reference the class with the yuzo ID).</p>',
			'settings' => array(
				'theme'  => 'mbo',
				'mode'   => 'css',
			),
			'default' =>'/* Style for general Yuzo
.wp-yuzo {
	color: #000;
}

/* Style for specific Yuzo, where 7465 is the specific Yuzo ID
.wp-yuzo.yzp-id-7465 {
	text-align: center;
}*/',
		),
	],
) );
