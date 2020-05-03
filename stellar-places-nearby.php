<?php
/**
 * Plugin Name:  Stellar Places - Nearby
 * Plugin URI:   https://wpscholar.com
 * Description:  A plugin that makes it easy to show a location along with any locations nearby.
 * Version:      1.0
 * Author:       Micah Wood
 * Author URI:   https://wpscholar.com
 * Text Domain:  stellar-places-nearby
 * Domain Path:  languages
 * License:      GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Stellar Places Nearby
 */

if ( ! class_exists( 'Stellar_Places' ) ) {

	require __DIR__ . '/includes/shortcodes/nearby.php';

	add_shortcode( Stellar_Places_Nearby_Shortcode::NAME, array( 'Stellar_Places_Nearby_Shortcode', 'shortcode' ) );

}
