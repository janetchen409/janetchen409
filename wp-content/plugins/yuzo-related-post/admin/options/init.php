<?php
/**
 * Start of system options (settings, pages, widgets, metaboxs, etc.)
 * @since   6.0     2019-05-22 22:37:49     Release
 *
 */

PF::new_plugin();
 // ─── Start of call for options (Backend & Manager Login) ────────
if( yuzo_isUserAllow() ){
    // ─── It serves to create CPT ────────
    require_once YUZO_PATH . 'admin/framework/posttypes/Columns.php';
    require_once YUZO_PATH . 'admin/framework/posttypes/Taxonomy.php';
    require_once YUZO_PATH . 'admin/framework/posttypes/PostType.php';

    require_once YUZO_PATH . 'admin/options/settings/options.php';
    require_once YUZO_PATH . 'admin/options/custom-post-type/cpt-yuzo.php';
    require_once YUZO_PATH . 'admin/options/metaboxes/mb-cpt-post.php';
    require_once YUZO_PATH . 'admin/options/metaboxes/mb-cpt-yuzo.php';
    require_once YUZO_PATH . 'admin/options/pages/dashboard.php';
}
// ─────────────────────────── ───────────────────────────

// ─── Start of options that also run on the frontend ────────
require_once YUZO_PATH . 'admin/options/widgets/yuzo-widget.php';
// ─────────────────────────── ───────────────────────────
// Add plugin to framework
PF::addPlugin(YUZO_ID);