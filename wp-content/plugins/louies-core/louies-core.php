<?php
/**
 * Plugin Name: Louie's Core
 * Description: Events with weekly/monthly repeats, plus the drink & food menu. Content lives here, not in the theme, so it survives a redesign.
 * Version:     1.0.0
 * Author:      Anirudha Talmale
 * Text Domain: louies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LOUIES_CORE_VERSION', '1.0.0' );
define( 'LOUIES_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'LOUIES_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once LOUIES_CORE_DIR . 'includes/post-types.php';
require_once LOUIES_CORE_DIR . 'includes/occurrences.php';
require_once LOUIES_CORE_DIR . 'includes/meta-boxes.php';
require_once LOUIES_CORE_DIR . 'includes/admin-columns.php';
require_once LOUIES_CORE_DIR . 'includes/settings.php';
require_once LOUIES_CORE_DIR . 'includes/seo.php';

register_activation_hook( __FILE__, function () {
	louies_register_post_types();
	louies_register_taxonomies();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
