<?php
/*
Plugin Name: BLD Web Agency Tweaks
Description: Ce plugin modifie le logo sur la page de connexion Wordpress et apporte quelques tweaks à Wordpress pour de meilleures performances.
Version: 1.6
License: GPL
Plugin URI: https://www.bldwebagency.fr/wordpress-plugins/
Author: Martin Bouillaud
Author URI: https://www.bldwebagency.fr

==========================================================================

Copyright (c) 2011-2022 Martin Bouillaud (email: contact@bldwebagency.fr)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//-----------------------------------------------------
// Optimisation contactform7 assets
//-----------------------------------------------------
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

function cf7_script_and_style() {
    if(is_page(90)) {
        if ( function_exists( 'wpcf7_enqueue_scripts' ) ) wpcf7_enqueue_scripts();
        if ( function_exists( 'wpcf7_enqueue_styles' ) ) wpcf7_enqueue_styles();
    }
    else {
        wp_deregister_style('wpcf7-redirect-script-frontend');
        wp_dequeue_style('wp-block-library');
        wp_deregister_script('wpcf7-redirect-script');
        wp_deregister_script('wpcf7-redirect-script-js');
    }
}
add_action( 'wp_enqueue_scripts', 'cf7_script_and_style' );

//-----------------------------------------------------
// Désactiver complètement le CSS de Gutenberg
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'unsiterapide_dequeue_gutenberg_css', PHP_INT_MAX);
function unsiterapide_dequeue_gutenberg_css()
{
  wp_deregister_style('wp-block-library');
  wp_dequeue_style('wp-block-library');
}

//-----------------------------------------------------
// Supprimer les liens RSD
//-----------------------------------------------------
remove_action ('wp_head', 'rsd_link');

//-----------------------------------------------------
// Masquer la version de Wordpress
//-----------------------------------------------------
remove_action ('wp_head', 'wp_generator');

//-----------------------------------------------------
// Disable Heartbeat
//-----------------------------------------------------
add_action ('init', 'stop_heartbeat', 1);
function stop_heartbeat () {
	wp_deregister_script ('heartbeat');
}

//-----------------------------------------------------
// Suppression de comment-reply.min.js si pas nécessaire
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'unsiterapide_dequeue_comment_reply', PHP_INT_MAX);
function unsiterapide_dequeue_comment_reply()
{
  if (is_singular() && (!comments_open() || !get_option('thread_comments') || !get_comments_number(get_the_ID()))) {
    wp_deregister_script('comment-reply');
  }
}

//-----------------------------------------------------
// On ne charge que le CSS des blocs Gutenberg utilisés
//-----------------------------------------------------

add_filter('should_load_separate_core_block_assets', '__return_true');

//-----------------------------------------------------
// On envoie un maximum de CSS en externe pour le concaténer
//-----------------------------------------------------

add_filter('styles_inline_size_limit', '__return_zero');

//-----------------------------------------------------
// Préchargement des images à la Une
//-----------------------------------------------------

add_action('wp_head', 'unsiterapide_preload_main_image', 11);
function unsiterapide_preload_main_image()
{
  if (get_the_post_thumbnail_url()) {
    echo '<link rel="preload" href="'.get_the_post_thumbnail_url(get_the_ID(), 'full').'" as="image">'."\n";
  }
}

//-----------------------------------------------------
// Suppression du link rel=preconnect vers fonts.gstatic.com
//-----------------------------------------------------

add_filter('wp_resource_hints', 'unsiterapide_remove_bad_hints', PHP_INT_MAX, 1);
function unsiterapide_remove_bad_hints($hints)
{
  foreach ($hints as $key => $hint) {
    if (is_array($hint) && array_key_exists('href', $hint) && $hint['href'] === 'https://fonts.gstatic.com') {
      unset($hints[$key]);
    }
  }
  return $hints;
}

//-----------------------------------------------------
// Disable emojis in WordPress
//-----------------------------------------------------

add_action( 'init', 'smartwp_disable_emojis' );

function smartwp_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    add_filter( 'tiny_mce_plugins', 'disable_emojis_tinymce' );
}

function disable_emojis_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, array( 'wpemoji' ) );
    } else {
        return array();
    }
}

//-----------------------------------------------------
// Remove Dashicons from Admin Bar for non logged in users
//-----------------------------------------------------

add_action('wp_print_styles', 'jltwp_adminify_remove_dashicons', 100);

function jltwp_adminify_remove_dashicons()
{
    if (!is_admin_bar_showing() && !is_customize_preview()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}

//-----------------------------------------------------
// Change login logo
//-----------------------------------------------------

function my_login_logo_one() {
	$logo_filename = WP_CONTENT_DIR . '/bldwebagency-login.png';
	if (!file_exists($logo_filename)) {
		$logo_url = 'https://www.bldwebagency.fr/wp-content/login-logo.png';
		file_put_contents($logo_filename, file_get_contents($url));
	}
?>
	<style type="text/css">
	body.login div#login h1 a {
		background-image: url('wp-content/bldwebagency-login.png');
		padding-bottom: 30px;
	}
	</style>
<?php
}

add_action( 'login_enqueue_scripts', 'my_login_logo_one' );
