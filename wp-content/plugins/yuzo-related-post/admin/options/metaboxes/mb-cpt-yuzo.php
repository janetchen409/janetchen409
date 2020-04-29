<?php
/**
 * Metabox for Related / List post options individually
 * @since 	6.0			2019-05-18		Release
 * @since 	6.0.4		2019-07-12		New fields were added
 * @since	6.0.5		2019-07-12		Two columns as the default value for the post view in mobile
 * @since	6.0.9		2019-07-21		Text and speed improvements
 * @since	6.0.9.7		2019-08-01		- Added the option 'Categories to include' for relationship based on titles
 * 										- The function <code>get_yuzo(YUZO_ID)</code> is enabled to be able to put it anywhere in the template
 * 										- New instructional message for the migration process, in order to have a better understanding.
 * @since	6.0.9.8		2019-08-28		- New post design builder, now it is more practical and intuitive
 * @since	6.0.9.81	2019-08-29		-  The css for margin, padding and background of the yuzo wrap was added & Post spacing added
 * @since	6.0.9.83	2019-10-04		Minimal improvements of classes in attribute
 * @since	6.0.9.84	2019-11-25		New option display_as_list_template is added: This makes Yuzo a list post based on your archive.php inserted by a shortcode
 */



/*
|--------------------------------------------------------------------------
| Creation METABOX
|--------------------------------------------------------------------------
*/
// ─── Start the session to capture the category taxonomy data only once ────────
/*lzSession::init(84000);
if( ! lzSession::get('yuzo_admin_cpt_tx_default') || ( isset($_POST['yuzo']['_restore']) && $_POST['yuzo']['_restore'] ) ){
	$args = array(
		'hide_empty'        => false,
		'orderby'           => 'name',
		'order'             => 'ASC',
		'exclude'           => array(),
		'exclude_tree'      => array(),
		'include'           => array(),
		'number'            => '',
		'fields'            => 'ids',
		'slug'              => '',
		'parent'            => '',
		'hierarchical'      => true,
		'child_of'          => 0,
		'get'               => '',
		'name__like'        => '',
		'description__like' => '',
		'pad_counts'        => false,
		'offset'            => '',
		'search'            => '',
		'cache_domain'      => 'core'
	);
	$terms_defaults = get_terms('category', $args);
	lzSession::set( 'yuzo_admin_cpt_tx_default',$terms_defaults );
}else{
	$terms_defaults = lzSession::get( 'yuzo_admin_cpt_tx_default' );
}*/
//lzSession::destroy('lz_', true);
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
// ─── Metabox ────────
PF::addMetabox( YUZO_ID , array(
	'title'              => 'Settings', // Need!
	'post_type'          => 'yuzo',
	'show_restore'       => true,
	'custom_fields_path' => YUZO_PATH . 'admin/options/custom-fields/',
	'footer_credit'      => 'Made with 💙 by <span class="yzp-admin-credit">Lenin Zapata</span><span class="fdc-admin-footer-separate">|</span>' . $fivestart,
) );


// ─── Metabox design2 ────────
PF::addSection( YUZO_ID . '-mb-design', array(
	'parent' => YUZO_ID,
	'title'  => __('Builder','yuzo'),
	'icon'   => 'fa fa-picture-o',
	'fields' => array(
		array(
			'id'     => 'fieldset_design',
			'type'   => 'fieldset',
			'title'  => '',
			'class'	 => 'yuzo-fieldset-twocolumns',
			'fields' => array(
				array(
					'id'         => 'panels-design',
					'type'       => 'accordion',
					'title'      => '',
					'collapsible'=> true,
					'accordions' => array(
						array(
							'title'  => __('Title','yuzo'),
							'active' => true,
							'fields' => array(
								array(
									'id'          => 'title',
									'type'        => 'text',
									'title'       => __('Title to show','yuzo'),
									//'subtitle'  => __('Title that will go to the top of the posts', 'yuzo'),
									'desc'      => __('The title also supports html headers for your best adaptation to the Theme','yuzo'),
									//'default'   => '<h4>You may also like</h4>',
									'attribute' => [
										'class' => 'yuzo_input_title',
									]
								),
							)
						),
						array(
							'title'  => __('Location','yuzo'),
							'fields' => array(
								array(
									'id'          => 'where_show',
									'type'        => 'button_set',
									'title'       => __('Location','yuzo'),
									'options'     => array(
										'content'   => __('Content','yuzo'),
										'widget'    => __('Widget','yuzo'),
										'shortcode' => __('Shortcode','yuzo'),
									),
									'desc'     => __('Select the type location where you want it to appear','yuzo'),
									//'subtitle' => __('There are 3 ways to do it ','yuzo'),
									//'default'  => 'content',
								),
								array(
									'id'         => 'yuzo_msg_show_wid1',
									'type'       => 'notice',
									'style'      => 'warning',
									'content'    => '<strong>Note:</strong> After saving this configuration you must go to <i>Appearance</i> ⇨ <i>Widgets</i> ⇨ drag a Yuzo Widget and select this configuration.',
									//'class'      => ' hidden',
									'dependency' => array( 'where_show', '==', 'widget' ),
								),
								array(
									'id'      => 'content_location',
									'type'    => 'radio',
									'title'   => __('Section','yuzo'),
									'inline'  => false,
									'options' => array(
										'top-post-content'        => __('Above Post Content','yuzo'),
										'middle-post-content'     => __('Middle Post Content','yuzo'),
										'below-post-content'      => __('Below Post Content','yuzo'),
										'left-post-content'       => __('To the Left of Post Content','yuzo'),
										'right-post-content'      => __('To the Right of Post Content','yuzo'),
										'top-paragraph-number'    => __('After the paragraph number (From the top)','yuzo'),
										'bottom-paragraph-number' => __('After the paragraph number (From the bottom)','yuzo'),
									),
									//'subtitle'   => __('You can select from 7 section where you want to show the post.','yuzo'),
									//'default'    => 'below-post-content',
									//'class'      => 'yzp-not-overflow',
									'dependency' => array( 'where_show', '==', 'content' ),
								),
								array(
									'id'     => 'content_appende_and_order',
									'type'   => 'fieldset',
									'title'  => '',
									//'class'	 => 'yuzo-fieldset-twocolumns',
									'fields' => array(
										array(
											'id'       => 'location_priority',
											'type'     => 'spinner',
											'title'    => __('Display location priority', 'yuzo'),
											'desc'     => __('A higher number will cause the posts to be processed later and move their display further down after the post content','yuzo'),
											'subtitle' => __('Yuzo will hook into the Content Filter at a priority as specified in this option', 'yuzo'),
											'max'      => 100,
											'min'      => 0,
											'step'     => 10,
											'default'  => 10,
											'class'    => 'pf-overflow',
										),
									),
									'dependency' => array( 'where_show|content_location', '==|==', 'content|below-post-content' ),
									//'default'    => array( 'location_priority' => 10 ),
									//'class'      => 'sub-field',
								),
								array(
									'id'     => 'content_appende_paragraph_order',
									'type'   => 'fieldset',
									'title'  => '',
									'fields' => array(
										array(
											'id'       => 'location_paragraph',
											'type'     => 'spinner',
											'title'    => __('Location within the paragraph', 'yuzo'),
											'subtitle' => __('Number of paragraph where you want to show the post','yuzo'),
											'desc'     => __('In case your content is less than this number, the last one that is displayed will be shown either from above or below, according to the selected option.', 'yuzo'),
											'max'      => 5,
											'min'      => 1,
											'step'     => 1,
											'class'    => 'pf-overflow',
										),
									),
									'dependency' => array( 'where_show|content_location', '==|any', 'content|top-paragraph-number,bottom-paragraph-number' ),
									//'default'    => array( 'location_paragraph' => 2 ),
									//'class'      => 'sub-field',
								),
							)
						),
						array(
							'title'  => __('Templates','yuzo'),
							'fields' => array(
								array(
									'id'          => 'template',
									'type'        => 'select',
									'title'       => __('','yuzo'),
									'options'     => array(
										'Grids' => [
											'default'      => __('Grid - Default','yuzo'),
											'old-related'  => __('Grid - Old Related','yuzo'),
											'old-related2' => __('Grid - Old Related 2','yuzo'),
											'jet-pack'     => __('Grid - Jet pack','yuzo'),
											'yuzo'         => __('Grid - Yuzo','yuzo'),
										],
										'Lists' => [
											'default-l'            => __('List - Default','yuzo'),
											'default-image'        => __('List - Default with image','yuzo'),
											'default-image-medium' => __('List - Default with image x2','yuzo'),
											'default-image-large'  => __('List - Default with image x3','yuzo'),
											'default-image-big'    => __('List - Default with image x4','yuzo'),
											//'sadra'                => __('List - Sadra','yuzo'),
											'yuzo-l'  => __('List - Yuzo','yuzo'),
											'colours' => __('List - Colours','yuzo'),
										],
										'Inlines' => [
											'default-i' => __('Inline - Default','yuzo'),
											'yuzo-i'    => __('Inline - Yuzo','yuzo'),
											'yuzo-i2'   => __('Inline - Yuzo with image','yuzo'),
										],
									),
									'desc'    => __('Select the template (layout) you want to display','yuzo'),
									//'default' => 'yuzo',
								),
								array(
									'id'      => 'template_type',
									'type'    => 'text',
									'default' => 'grid',
								),
								array(
									'id'       => 'template_colours',
									'type'     => 'palette',
									'title'    => 'Dressing',
									'subtitle' => __('Select the color palette you want to display','yuzo'),
									'options'  => array(
										'set-1'  => array( '#FD8783', '#F9D2B1', '#F9EB83', '#E2F7B1', '#DBF59D', '#99E0F2' ),
										'set-2'  => array( '#F2558A', '#EFA151', '#F9E760', '#B7C443', '#9FCE53', '#33AACF' ),
										'set-3'  => array( '#A1B1E9', '#71E5C0', '#FFB5A9', '#EBADDD', '#ECFF5F', '#34BDE6' ),
									),
									'default'    => 'set-1',
									'dependency' => array( 'template', '==', 'colours', true ),
								),
								array(
									'id'         => 'template_color_1',
									'type'       => 'color',
									'title'      => __('Color A','yuzo'),
									'default'    => '#f1f1f1',
									'dependency' => array( 'template', 'any', 'yuzo-i,default-i', true ),
								),
								array(
									'id'         => 'template_color_2',
									'type'       => 'color',
									'title'      => __('Color B','yuzo'),
									'default'    => '#2f7eff',
									'dependency' => array( 'template', 'any', 'default-i,yuzo-i2', true ),
								),
							)
						),
						array(
							'title'  => __('Text Customization','yuzo'),
							'fields' => array(
								array(
									'id'                => 'design_text_font_size',
									'type'              => 'dimensions',
									'title'             => __('Font size','yuzo'),
									'height'            => false,
									'units'             => ['px'],
									'width_placeholder' => __( '16', 'yuzo' ),
									'width_icon'        => '<i class="fa fa-font"></i>'
								),
								array(
									'id'                => 'design_text_line_height',
									'type'              => 'dimensions',
									'title'             => __('Line height','yuzo'),
									'units'             => ['px'],
									'height'            => false,
									'width_placeholder' => '16',
									'width_icon'        => '<i class="fa fa-arrows-v"></i>'
								),
								array(
									'id'      => 'design_text_font_weight',
									'type'    => 'select',
									'title'   => __('Font weight','yuzo'),
									'options' => ['100' => 100,'200' => 200,'300' => 300,'400' => 400,'500' => 500,'600' => 600,'700' => 700,'800' => 800,'900'=> 900],
								),
								array(
									'id'    => 'design_text_color_hover',
									'type'  => 'link_color',
									'title' => 'Text Color',
								),
							)
						),
						array(
							'title'  => __('Number posts / Devices','yuzo'),
							'fields' => array(
								array(
									'id'          => 'design_screen',
									'type'        => 'button_set',
									'options'     => array(
										'mobile'  => '<i class="fa fa-mobile"></i>',
										'tablet'  => '<i class="fa fa-tablet"></i>',
										'desktop' => '<i class="fa fa-desktop"></i>',
									),
									'attributes_sub' => [
										'tooltip' => ['desktop'=>'Resolution > 1024','tablet'=>'Resolution between 769 and 1024','mobile'=>'Resolution < 769']
									],
									//'desc' => __('Select the type of related post you want to see.','yuzo'),
									//'default' => 'mobile'
								),
								array(
									'id'     => 'design_screen_mobile',
									'type'   => 'fieldset',
									'fields' => array(
										array(
											'id'   => 'design_screen_mobile_columns',
											'type' => 'spinner',
											//'desc'    => __('Set the time until where you want to show related posts','yuzo'),
											'max'    => 2,
											'min'    => 1,
											'step'   => 1,
											'unit'   => __('columns','yuzo'),
											//'before' => '<div class="yuzo-class-numberpost yuzo-class-numberpost-mobile">',
										),
										array(
											'id'   => 'design_screen_mobile_rows',
											'type' => 'spinner',
											//'desc'    => __('Set the time until where you want to show related posts','yuzo'),
											'max'   => 20,
											'min'   => 1,
											'step'  => 1,
											'unit'  => __('rows','yuzo'),
										),
									),
									'inline'     => true,
									//'default'    => array( 'design_screen_mobile_columns' => 2, 'design_screen_mobile_rows' => 2 ),
									'dependency' => array( 'design_screen', '==', 'mobile', true ),
								),
								array(
									'id'     => 'design_screen_tablet',
									'type'   => 'fieldset',
									'fields' => array(
										array(
											'id'   => 'design_screen_tablet_columns',
											'type' => 'spinner',
											//'desc'    => __('Set the time until where you want to show related posts','yuzo'),
											'max'        => 3,
											'min'        => 1,
											'step'       => 1,
											'unit'       => __('columns','yuzo'),
										),
										array(
											'id'   => 'design_screen_tablet_rows',
											'type' => 'spinner',
											//'desc'    => __('Set the time until where you want to show related posts','yuzo'),
											'max'        => 20,
											'min'        => 1,
											'step'       => 1,
											'unit'       => __('rows','yuzo'),
										),
									),
									'inline'     => true,
									//'default'    => array( 'design_screen_tablet_columns' => 3, 'design_screen_tablet_rows' => 1 ),
									'dependency' => array( 'design_screen', '==', 'tablet', true ),
								),
								array(
									'id'     => 'design_screen_desktop',
									'type'   => 'fieldset',
									'fields' => array(
										array(
											'id'   => 'design_screen_desktop_columns',
											'type' => 'spinner',
											//'desc'    => __('Set the time until where you want to show related posts','yuzo'),
											'max'  => 4,
											'min'  => 1,
											'step' => 1,
											'unit' => __('columns','yuzo'),
										),
										array(
											'id'   => 'design_screen_desktop_rows',
											'type' => 'spinner',
											//'desc'    => __('Set the time until where you want to show related posts','yuzo'),
											'max'        => 20,
											'min'        => 1,
											'step'       => 1,
											'unit'       => __('rows','yuzo'),
										),
									),
									'inline'     => true,
									//'default'    => array( 'design_screen_desktop_columns' => 3, 'design_screen_desktop_rows' => 1 ),
									'dependency' => array( 'design_screen', '==', 'desktop', true ),
								),
								array(
									'id'         => 'yuzo_msg_show_wid2',
									'type'       => 'notice',
									'style'      => 'warning',
									'content'    => '<strong>Note:</strong> Your seleted template is an LIST and do not have columns, so what you do is multiply <code>rows x columns</code> to get the total posts.',
									//'dependency' => array( 'template_type', '==', 'list', 1 ),
								),
								array(
									'id'         => 'yuzo_msg_show_wid3',
									'type'       => 'notice',
									'style'      => 'info',
									'content'    => '<strong>Note:</strong> Your seleted template is an INLINE this only has 1 post that shows, does not take into consider the number of rows and columns.',
								),
								//'dependency' => array( 'template_type', '==', 'inline' ),
							)
						),
						array(
							'title'  => __('Excerpt','yuzo'),
							'fields' => array(
								array(
									'id'         => 'design_show_excerpt',
									'type'       => 'switcher',
									'title'      => __('Show excerpt','yuzo'),
									'text_on'    => __('Enabled','yuzo'),
									'text_off'   => __('Disabled','yuzo'),
									'text_width' => '95',
									//'desc'       => __('If you activate this option below each post, an extract of the content will be displayed','yuzo'),
									'default'    => false,
									//'dependency' => array( 'related_type|related_to', '==|!=', 'related|title' ),
								),
								array(
									'id'        => 'design_text_length',
									'type'      => 'text',
									'title'     => __('Text length', 'yuzo'),
									'desc'      => __('Number of text lettering post (content)','yuzo'),
									//'default'   => '80',
								),
								array(
									'id'          => 'design_text_content',
									'type'        => 'select',
									'title'       => __('Text to display','yuzo'),
									'options'     => array(
										'from_content' => __('Text in the article begins','yuzo'),
										'from_excert'  => __('Excerpt from article','yuzo'),
									),
									'desc'    => __('You can show between the first text of the content or the extract that you have put to the post','yuzo'),
									//'default' => 'from_content',
								),
							)
						),

						array(
							'title'  => __('Image','yuzo'),
							'fields' => array(
								array(
									'id'         => 'template_show_imagen',
									'type'       => 'switcher',
									'title'      => __('Show imagen','yuzo'),
									'text_on'    => __('Enabled','yuzo'),
									'text_off'   => __('Disabled','yuzo'),
									'text_width' => '85',
									'dependency' => array( 'template', '==', 'colours', true ),
								),
								array(
									'id'      => 'design_thumbnail_size',
									'type'    => 'button_set',
									'title'   => __('Thumbnail size','yuzo'),
									'options' => array(
										'thumbnail' => __('Thumbnail','yuzo'),
										'medium'    => __('Medium','yuzo'),
										'full'      => __('Full','yuzo'),
									),
									'desc'    => __('Select the size of the image (by weight) that you want to load','yuzo'),
									//'default' => 'medium'
								),
								array(
									'id'      => 'design_image_size',
									'type'    => 'image_select',
									'title'   => 'Aspect ratio',
									'options' => array(
										'4-3'    => YUZO_URL . 'admin/assets/images/size-images.png',
										'1-1'    => YUZO_URL . 'admin/assets/images/size-images.png',
									),
									'texts' => array(
										'4-3'    => '4 - 3',
										'1-1'    => '1 - 1',
									),
									'attributes' => array(
										'width'  => '100px',
										'height' => '60px',
									),
									'before'  => '',
									'default' => '1-1',
									'after'   => '<span class="pf-text-desc">Choose from a variety of aspect ratios</span>',
								),
							)
						),

						array(
							'title'  => __('Meta','yuzo'),
							'fields' => array(
								array(
									'id'             => 'design_metas',
									'type'           => 'sorter',
									'title'          => '',
									'enabled_title'  => __('to show','yuzo'),
									'disabled_title' => __('available','yuzo'),
									'default'        => array(
										'enabled'      => array(
										),
										'disabled'     => array(
											'meta-date'        => __('Date','yuzo'),
											'meta-date-update' => __('Date last update','yuzo'),
											'meta-category'    => __('Category','yuzo'),
											'meta-view'        => __('View','yuzo'),
											'meta-author'      => __('Author','yuzo'),
											'meta-comment'     => __('Comment','yuzo'),
										),
									),
									'desc' => __('Drag and drop the meta you want to show in the \'To show\' column','yuzo')
								),
							)
						),
						array(
							'title'  => __('Wrap box','yuzo'),
							'fields' => array(
								array(
									'id'      => 'design_box_margin',
									'type'    => 'spacing',
									'title'   => __('Margin','yuzo'),
									'desc'    => __('Adjust the margin of the Yuzo wrap box','yuzo'),
								),
								array(
									'id'      => 'design_box_padding',
									'type'    => 'spacing',
									'title'   => __('Padding','yuzo'),
									'desc'    => __('Adjust the padding of the Yuzo wrap box','yuzo'),
								),
								array(
									'id'    => 'design_box_background',
									'type'  => 'color',
									'title' => __('Background color','yuzo'),
								)
							)
						),
						array(
							'title'  => __('Wrap post','yuzo'),
							'fields' => array(
								array(
									'id'              => 'design_post_spacing',
									'title'           => __('Spacing','yuzo'),
									'desc'            => __('Adjust the space between posts','yuzo'),
									'type'              => 'dimensions',
									'units'             => ['px'],
									'height'            => false,
									'width_placeholder' => '30',
									'width_icon'        => '<i class="fa fa-arrows-alt"></i>'
								),
							)
						),
						array(
							'title'  => __('Text Above/Below','yuzo'),
							'fields' => array(
								array(
									'id'    => 'design_html_above',
									'type'  => 'textarea',
									'title' => __('Above', 'yuzo'),
									'desc'  => __('Enter any TEXT/HTML about this Yuzo','yuzo'),
								),
								array(
									'id'    => 'design_html_below',
									'type'  => 'textarea',
									'title' => __('Below', 'yuzo'),
									'desc'  => __('Enter any TEXT/HTML about this Yuzo','yuzo'),
								),
							)
						),
					),
				),
				array(
					'id'     => 'fieldset_design_preview',
					'type'   => 'fieldset',
					'title'  => '',
					'fields' => array(
						array(
							'id'      => 'design_header_screen_number_post',
							'type'    => 'subheading',
							'content' => __('Preview on different screens <i>(Random post)</i> <span class="yzp-preview-header-devices"></span>','yuzo'),
						),
						array(
							'id'          => 'design_screen2',
							'type'        => 'button_set',
							'options'     => array(
								'mobile'  => '<i class="fa fa-mobile"></i>',
								'tablet'  => '<i class="fa fa-tablet"></i>',
								'desktop' => '<i class="fa fa-desktop"></i>',
							),
							'attributes_sub' => [
								'tooltip' => ['desktop'=>'Resolution > 1024','tablet'=>'Resolution between 769 and 1024','mobile'=>'Resolution < 769']
							],
						),
						array(
							'id'   => 'design_preview_mobile',
							'type' => 'content',
							'content' => '
<div id="frames" class="widthOnly">
	<div id="f3" class="frame frame-mobile">
		<h2>480<span> x 640</span> <span class="small">(resolution < 768)</span> </h2>
		<iframe sandbox="allow-same-origin allow-forms allow-scripts" seamless width="481" height="641"></iframe>
	</div>
	<div id="f2" class="frame frame-mobile">
		<h2>320<span> x 480</span> <span class="small">(resolution < 768)</span> </h2>
		<iframe sandbox="allow-same-origin allow-forms allow-scripts" seamless width="321" height="480"></iframe>
	</div>
	<div id="f4" class="frame frame-tablet">
		<h2>768<span> x 1024</span> <span class="small">(resolution >= 768 and <= 1024)</span> </h2>
		<iframe sandbox="allow-same-origin allow-forms allow-scripts" seamless width="769" height="1024"></iframe>
	</div>
	<div id="f5" class="frame frame-desktop">
		<h2>1024<span> x 768</span> <span class="small">(resolution > 1024)</span> </h2>
		<iframe sandbox="allow-same-origin allow-forms allow-scripts" seamless width="1025" height="768"></iframe>
	</div>
</div>
',
						),
					)
				)
			),
			'default' => array(
				'panels-design' => [
					'title'                           => '<h4>You may also like</h4>',
					'where_show'                      => 'content',
					'content_location'                => 'below-post-content',
					'content_appende_and_order'       => array( 'location_priority' => 10 ),
					'content_appende_paragraph_order' => array( 'location_paragraph' => 2 ),
					'template'                        => 'yuzo',
					'template_type'                   => 'grid',
					'template_show_imagen'            => true,
					'design_screen'                   => 'mobile',
					'design_screen_mobile'            => array( 'design_screen_mobile_columns' => 2, 'design_screen_mobile_rows' => 2 ),
					'design_screen_tablet'            => array( 'design_screen_tablet_columns' => 3, 'design_screen_tablet_rows' => 1 ),
					'design_screen_desktop'           => array( 'design_screen_desktop_columns' => 3, 'design_screen_desktop_rows' => 2 ),
					'design_show_excerpt'             => false,
					'design_text_length'              => '80',
					'design_text_content'             => 'from_content',
					'button_set'                      => 'medium',
					'design_image_size'               => '1-1',
					'design_image_custom'             => '2:3',
					'design_thumbnail_size'           => 'medium',
					'design_text_color_hover'		  => ['color' => '#000'],
					'design_post_spacing'             => ['all' => 30],
				],
				'fieldset_design_preview' => array( 'design_screen2' => 'mobile' ),
			),
		),
	)
) );

// ─── Metabox relation ────────
PF::addSection( YUZO_ID . '-mb-relation', array(
	'parent' => YUZO_ID,
	'title'  => 'Algorithm',
	'icon'   => 'fa fa-lastfm',
	'fields' => array(

		array(
			'id'         => 'related_post_active',
			'type'       => 'switcher',
			'title'      => __('Activate this Yuzo','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '130',
			'desc'       => __('Activate or deactivate the Yuzo of the current configuration','yuzo'),
			'default'    => true,
		),

		array(
			'id'          => 'related_type',
			'type'        => 'select',
			'title'       => __('Algorithm type','yuzo'),
			'options'     => array(
				'related' => __('Related post','yuzo'),
				'list'    => __('List post','yuzo'),
			),
			'subtitle'     	=> __('Select which post you want the algorithm to show', 'yuzo'),
			'desc' 		 	=> __('<strong>Related post:</strong> It will show post related to your configuration<br />
<strong>List post:</strong> It will show a list of post with the variant you want','yuzo'),
			'default' => 'related',
		),
		array(
			'id'         => 'group_list',
			'type'       => 'subheading',
			'content'    => __('List','yuzo'),
			'dependency' => array( 'related_type', '==', 'list' ),
		),
		array(
			'id'          => 'list_post',
			'type'        => 'select',
			'title'       => __('Post list available','yuzo'),
			'options'     => array(
				'last-post'    => __('Last Posts','yuzo'),
				'most-view'    => __('Most View','yuzo'),
				'most-popular' => __('Most Popular (most Commented)','yuzo'),
				'most-clicked' => __('Most Clicked','yuzo'),
				'rand'         => __('Rand','yuzo'),
				'a-z'          => __('Alphabetically [A-Z]','yuzo'),
				'z-a'          => __('Alphabetically [Z-A]','yuzo'),
			),
			'desc' => __('Select list you want to show', 'yuzo'),
			'default' => 'last-post',
			'dependency' => array( 'related_type', '==', 'list' ),
		),
		array(
			'id'         => 'group_related',
			'type'       => 'subheading',
			'content'    => __('Relationship','yuzo'),
			'dependency' => array( 'related_type', '==', 'related' ),
		),
		array(
			'id'          => 'related_to',
			'type'        => 'select',
			'title'       => __('Related Post based','yuzo'),
			'options'     => array(
				'tags'           => __('Enhanced Tag','yuzo'),
				'categories'     => __('Enhanced Category','yuzo'),
				'object_related' => __('Yuzo Relationship (recommended)','yuzo'),
			),
			'desc'       => __('Select the type of relationship you want that appear in your post.', 'yuzo'),
			'default'    => 'object_related',
			'dependency' => array( 'related_type', '==', 'related' ),
		),
		array(
			'id'     => 'order_by',
			'type'   => 'fieldset',
			'title'  => __('Ordering','yuzo'),
			'inline' => true,
			'fields' => array(
				array(
					'id'          => 'order',
					'type'        => 'button_set',
					'options'     => array(
						'asc'  => __('Ascending','yuzo'),
						'desc' => __('Descending','yuzo'),
					),
				),
				array(
					'id'          => 'by',
					'type'        => 'select',
					'options'     => array(
						'ID'            => __('ID','yuzo'),
						'author'        => __('Author','yuzo'),
						'title'         => __('Title','yuzo'),
						'date'          => __('Date','yuzo'),
						'modified'      => __('Modified','yuzo'),
						'rand'          => __('Rand','yuzo'),
						'comment_count' => __('Comment Count','yuzo'),
						'most_visited'  => __('Most visited (based on the result)','yuzo'),
					),
				),
			),
			'default' => array( 'order' => 'desc', 'by' => 'modified'  ),
			'desc'    => __('Order by a criterion the related post (Ascending / Descending)','yuzo'),
			'dependency' => array( 'related_type|related_to', '==|!=', 'related|object_related' ),
		),
		array(
			'id'          => 'relation_no_result',
			'type'        => 'select',
			'title'       => __('If there is no related post, display','yuzo'),
			'options'     => array(
				''                      => __('None','yuzo'),
				'random_based_cpt'      => __('Random posts based on current posts type','yuzo'),
				//'random_based_taxonomy' => __('Last posts based on current Taxonomy','yuzo'),
			),
			'desc'    => __('Choose which posts you want displayed when there is no relationship matches', 'yuzo'),
			'default' => 'random_based_cpt',
			'dependency' => array( 'related_type', '==', 'related' ),
		),
		array(
			'id'     => 'time_and_space',
			'type'   => 'fieldset',
			'title'  => __('Time and space','yuzo'),
			'fields' => array(
				array(
					'id'          => 'range',
					'type'        => 'button_set',
					'options'     => array(
						'all-along'     =>  __('All time','yuzo'),
						'last-week'     =>  __('Last week','yuzo'),
						'last-month'    =>  __('Last month','yuzo'),
						'last-year'     =>  __('Last year','yuzo'),
					),
				),
			),
			'desc'     => __('Time interval that the posts will show','yuzo'),
			'subtitle' => __('Time relative','yuzo'),
			'default'  => array( 'range' => 'all-along' ),
		),
		array(
			'id'      => 'cpt_to_related',
			'type'    => 'button_set',
			'title'   => __('Include post type', 'yuzo'),
			'multiple'=> true,
			'options' => ['post'=>__( 'Post', 'yuzo' ),'page'=>__( 'Page', 'yuzo' )],
			'desc'    => __('Post type that you want to include in the list of posts that are shown', 'yuzo'),
			'default' => array('post'),
			'class'   => 'yzp-not-overflow',
		),
		array(
			'id'         => 'group_list',
			'type'       => 'subheading',
			'content'    => __('What do you want to show?','yuzo'),
			'dependency' => array( 'related_type', '==', 'list' ),
		),
		array(
			'id'         => 'include_taxonomy_hierarchical',
			'type'       => 'taxonomies',
			'title'      => __('Categories to include', 'yuzo'),
			'desc'       => __('Select the terms you want to be listed in the post list', 'yuzo'),
			'subtitle'	 => __('Terms of taxonomy that will be included','yuzo'),
			'default'    => array( 'category' => ['all' => '1'] ),
			'dependency' => array( 'related_type', '==', 'list' ),
			'setting' => [
				'cpt' => ['post'],
				'tax' => ['category'],
			]
		),
		array(
			'id'     => 'include_taxonomy_hierarchical_operator',
			'type'   => 'fieldset',
			'title'  => '',
			'fields' => array(
				array(
					'id'          => 'include_taxonomy_relation',
					'type'        => 'select',
					'title'       => __('Selected terms','yuzo'),
					'options'     => array(
						'or'  => __('OR = At least one matches','yuzo'),
						'and' => __('AND = Selected must match','yuzo'),
					)
				),
			),
			'dependency' => array( 'related_type', '==', 'list' ),
			'default'    => array( 'include_taxonomy_relation' => 'or' ),
			'class'      => 'sub-field',
		),
		array(
			'id'          => 'taxonomies_cat_tag_relation',
			'type'        => 'button_set',
			'title'		  => __('↑ Relationship between Categories and Tags ↓','yuzo'),
			'options'     => array(
				'or'     =>  __('OR','yuzo'),
				'and'     =>  __('AND','yuzo'),
			),
			'desc'     => __('<strong>OR</strong> = The post shown can contain between categories and selected tags<br />
<strong>AND</strong> = The posts to show must match between categories and tags','yuzo'),
			'default' => 'or',
			'dependency' => array( 'related_type', '==', 'list' ),
		),
		array(
			'id'           => 'related_taxonomy_no_hierarchical',
			'type'         => 'taxonomies',
			'hierarchical' => false,
			'title'        => __('Tags to include', 'yuzo'),
			'desc'         => __('Add the terms you want to be listed in the post list', 'yuzo'),
			'subtitle'     => __('<strong>Note:</strong> If you leave it empty, do not take this option into consideration .', 'yuzo'),
			'dependency'   => array( 'related_type', '==', 'list' ),
			'setting' => [
				'cpt' => ['post'],
				'tax' => ['post_tag'],
			]
		),
		array(
			'id'     => 'include_taxonomy_not_hierarchical_operator',
			'type'   => 'fieldset',
			'title'  => '',
			'fields' => array(
				array(
					'id'          => 'include_taxonomy_no_relation',
					'type'        => 'select',
					'title'       => __('Aggregated terms','yuzo'),
					'options'     => array(
						'or'  => __('OR = At least one matches','yuzo'),
						'and' => __('AND = Selected must match','yuzo'),
					)
				),
			),
			'dependency' => array( 'related_type', '==', 'list' ),
			'default'    => array( 'include_taxonomy_relation' => 'or' ),
			'class'      => 'sub-field',
		),
		array(
			'id'         => 'group_exclude',
			'type'       => 'subheading',
			'content'    => __('Exclude','yuzo'),
			//'dependency' => array( 'related_type', '==', 'list' ),
		),
		array(
			'id'               => 'exclude_taxonomy_hierarchical',
			'type'             => 'taxonomies',
			'title'            => __('Exclude categories', 'yuzo'),
			'desc'             => __('Select which categories (hierarchical taxonomies) you do not want to show', 'yuzo'),
			'hierarchical_nav' => false,
			'setting' => [
				'cpt' => ['post'],
				'tax' => ['category'],
			]
		),
		array(
			'id'           => 'exclude_taxonomy_no_hierarchical',
			'type'         => 'taxonomies',
			'hierarchical' => false,
			'title'        => __('Exclude tags', 'yuzo'),
			'desc'         => __('Write the slug/name separated by a commas "," for no relations in post', 'yuzo'),
			'setting' => [
				'cpt' => ['post'],
				'tax' => ['post_tag'],
			]
		),
		array(
			'id'    => 'exclude_post_id',
			'title' => __('Exclude post by ID','yuzo'),
			'type'  => 'tag',
			'desc'  => __('Write the IDs separated by a commas "," which you do not want to be shown to the post','yuzo')
		),
		array(
			'id'    => 'not_appear_inside',
			'title' => __('Not appear inside','yuzo'),
			'type'  => 'tag',
			'desc'  => __('Write the ID separated by a commas "," posts you want to Yuzo not appear','yuzo')
		),
		array(
			'id'         => 'group_strict',
			'type'       => 'subheading',
			'content'    => __('Strict','yuzo'),
			'dependency' => array( 'related_type', '==', 'related' ),
		),
		array(
			'id'         => 'related_post_only_add_metabox',
			'type'       => 'switcher',
			'title'      => __('Show only posts included from the Metabox','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '110',
			'desc'       => __('With this only posts that are added manually from  the METABOX in the post will be displayed. You can put together the posts that you want to appear manually','yuzo'),
			'default'    => false,
			'dependency' => array( 'related_type', '==', 'related' ),
		),

	)
) );


// ─── Metabox Display ────────
PF::addSection( YUZO_ID . '-mb-diplay', array(
	'parent' => YUZO_ID,
	'title'  => 'Display',
	'icon'   => 'fa fa-low-vision',
	'fields' => array(

		array(
			'id'       => 'show_only_in_type_post',
			'type'     => 'button_set',
			'title'    => __('Show only in post type', 'yuzo'),
			'multiple' => true,
			'options'  => ['post'=>__( 'Post', 'yuzo' ),'page'=>__( 'Page', 'yuzo' )],
			'desc'     => __('Post type where the posts will be displayed', 'yuzo'),
			'default'  => array('post'),
			'class'    => 'yzp-not-overflow',
		),
		array(
			'id'      => 'yuzo_msg_show_cpt1',
			'type'    => 'notice',
			'style'   => 'info',
			'content' => '<strong>'. __('Note','yuzo') .':</strong> '. __('Generally the pages do not have taxonomies (categories, tags) if that is the case then it will be shown related by the title','yuzo') ,
			'class'   => ' hidden',
		),
		array(
			'id'      => 'show_only_in_taxonomy_hierarchical',
			'type'    => 'taxonomies',
			'title'   => __('Categories will appear', 'yuzo'),
			'inline'  => true,
			'desc'    => __('Categories (taxonomies hierarchical) where the posts will be displayed', 'yuzo'),
			'default' => array( 'category' => ['all' => '1'] ),
			'setting' => [
				'cpt' => ['post'],
				'tax' => ['category'],
			]
		),
		array(
			'id'      => 'show_only_in_places_on_the_page',
			'type'    => 'checkbox',
			'title'   => __('Places on the page', 'yuzo'),
			'inline'  => true,
			'options' => [
				'homepage' => __('Show on homepage', 'yuzo'),
				'category' => __('Show on category pages', 'yuzo'),
				'tag'      => __('Show on tag pages', 'yuzo'),
				'author'   => __('Show on author pages', 'yuzo'),
				'search'   => __('Show on search pages', 'yuzo'),
				'archive'  => __('Show on archive pages', 'yuzo'),
				'feed'     => __('Show on feed pages', 'yuzo'),
			],
			'desc'    => __('The post will be shown as long as the Theme allows it', 'yuzo'),
			'default' => array('archive'),
			'class'   => 'yzp-not-overflow',
			//'dependency' => array( 'where_show', 'any', 'content,widget' ),
		),
		array(
			'id'         => 'display_as_list_template',
			'type'       => 'switcher',
			'title'      => __('Use this Yuzo as a template list','yuzo'),
			'text_on'    => __('Enabled','yuzo'),
			'text_off'   => __('Disabled','yuzo'),
			'text_width' => '110',
			'desc'       => __('If you want Yuzo to be your template list, replace the <code>archive.php</code> loop
for this Yuzo in shortcode, then your list posts will be shown by Yuzo.','yuzo'),
			'default'    => false,
		),
		array(
			'id'    => 'display_only_specific_postid',
			'title' => __('Show only in post specific','yuzo'),
			'type'  => 'tag',
			'desc' =>  __('Put IDs posts where you want ONLY the posts are displayed (Separated by commas)','yuzo'),
			'attributes_js' => [
				'"placeholder"' => '"66, 201, 421, 489"',
				//'"onChange"' => 'eval("alert(1)")',
			]
		),
		//

	)
) );

// ─── Metabox design ────────
PF::addSection( YUZO_ID . '-mb-export-import', array(
	'parent' => YUZO_ID,
	'title'  => __('Export/Import','yuzo'),
	'icon'   => 'fa fa-info',
	'fields' => array(
		array(
			'type' => 'backup',
		),
	)
) );

/* Allows you to modify a checkbox type input for the pixel framework */
//add_filter( 'pf_taxonomies_item_template', 'yuzo_admin_taxonomies_check_item', 10, 4);
function yuzo_admin_taxonomies_check_item($in_html, $name_field, $checked, $term){
	$output  = "<input type='button' name='{$name_field}[]' value='$term->term_id' {$checked} /> ";
    return $output;
}



// ─── Metabox sode ────────
PF::addMetabox( YUZO_ID . '-metaboxside', array(
	'title'        => __('Code','yuzo'),   // Need!
	'post_type'    => 'yuzo',
	'show_restore' => false,
	'context'      => 'side'
) );
PF::addSection( YUZO_ID . '-mb-shortcode', array(
	'parent' => YUZO_ID . '-metaboxside',
	//'title'  => '',
	//'icon'   => 'fa fa-low-vision',
	'fields' => array(
		array(
			'id'      => 'design_preview_tablet2',
			'type'    => 'content',
			'content' => '
<div id="shortcodeout" style="display:none;"></div>
<div class="yuzo_shortcode">
<p>Shortcode code</p>
<input type="text" class="input_id_shortcode" readonly value="[yuzo id=\'save first\']" />
<p>PHP code wordress</p>
<input type="text" class="input_id_shortcode2" readonly value="&lt;?php echo do_shortcode( \'[yuzo id=(save first)]\' ) ?&gt;" />
<p>PHP code native</p>
<input type="text" class="input_id_shortcode3" readonly value="&lt;?php if ( function_exists( \'get_yuzo\' ) ) { get_yuzo( \'save first\' ); } ?&gt;" />
</div>
',
		)
	)
) );