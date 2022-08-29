<?php
/*
Plugin Name: BLD Web Agency Tweaks
Description: Ce plugin modifie le logo sur la page de connexion Wordpress et apporte quelques tweaks à Wordpress pour de meilleures performances.
Version: 1.1
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
// Remove Dashicons from Admin Bar for non logged in users **/
//-----------------------------------------------------

add_action('wp_print_styles', 'jltwp_adminify_remove_dashicons', 100);

function jltwp_adminify_remove_dashicons()
{
    if (!is_admin_bar_showing() && !is_customize_preview()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}


class BWA_Bld_Web_Agency_Plugin {
	public static $instance;
	const CUTOFF = 312;
	public $logo_locations;
	public $logo_location;
	public $width = 0;
	public $height = 0;
	public $original_width;
	public $original_height;
	public $logo_size;
	public $logo_file_exists;

	public function __construct() {
		self::$instance = $this;
		add_action('login_head', array( $this, 'login_head' ));
	}

	public function init() {
		global $blog_id;
		$this->logo_locations = array();

		$url = 'https://www.bldwebagency.fr/wp-content/uploads/2022/08/login-logo.png';
		$img = WP_CONTENT_DIR . '/login-logo.png';
		file_put_contents($img, file_get_contents($url));

		// Finally, we do a global lookup
		$this->logo_locations['global'] =  array(
			'path' => WP_CONTENT_DIR . '/login-logo.png',
			'url' => $this->maybe_ssl(content_url('login-logo.png'))
		);
	}

	private function maybe_ssl($url) {
		if ( is_ssl() ) {
			$url = preg_replace('#^http://#', 'https://', $url);
		}

		return $url;
	}

	private function logo_file_exists() {
		if ( !isset( $this->logo_file_exists ) ) {
			foreach ( $this->logo_locations as $location ) {
				if ( file_exists( $location['path'] ) ) {
					$this->logo_file_exists = true;
					$this->logo_location = $location;
					break;
				} else {
					$this->logo_file_exists = false;
				}
			}
		}

		return !! $this->logo_file_exists;
	}

	private function get_location($what = '') {
		if ($this->logo_file_exists()) {
			if ('path' == $what) {
				return $this->logo_location[$what];
			} elseif ('url' == $what) {
				return $this->logo_location[$what] . '?v=' . filemtime($this->logo_location['path']);
			} else {
				return $this->logo_location;
			}
		}
		return false;
	}

	private function get_width() {
		$this->get_logo_size();
		return absint($this->width);
	}

	private function get_height() {
		$this->get_logo_size();
		return absint($this->height);
	}

	private function get_original_width() {
		$this->get_logo_size();
		return absint($this->original_width);
	}

	private function get_original_height() {
		$this->get_logo_size();
		return absint($this->original_height);
	}

	private function get_logo_size() {
		if (!$this->logo_file_exists()) {
			return false;
		}
		if (!$this->logo_size) {
			if ($sizes = getimagesize($this->get_location('path'))) {
				$this->logo_size = $sizes;
				$this->width  = $sizes[0];
				$this->height = $sizes[1];
				$this->original_height = $this->height;
				$this->original_width = $this->width;
				if ($this->width > self::CUTOFF) {
					// Use CSS 3 scaling
					$ratio = $this->height / $this->width;
					$this->height = ceil($ratio * self::CUTOFF);
					$this->width = self::CUTOFF;
				}
			} else {
				$this->logo_file_exists = false;
			}
		}
		return array( $this->width, $this->height );
	}

	private function css3($rule, $value) {
		foreach (array( '', '-o-', '-webkit-', '-khtml-', '-moz-', '-ms-' ) as $prefix) {
			echo $prefix . $rule . ': ' . $value . '; ';
		}
	}

	public function login_headerurl() {
		return esc_url(trailingslashit(get_bloginfo('url')));
	}

	public function login_headertitle() {
		return esc_attr(get_bloginfo('name'));
	}

	public function login_head() {
		$this->init();

		if (!$this->logo_file_exists()) {
			return;
		}

		add_filter('login_headerurl', array( $this, 'login_headerurl' ));
		add_filter(
			version_compare(get_bloginfo('version'), '5.2', '>=') ? 'login_headertext' : 'login_headertitle',
			array( $this, 'login_headertitle' )
		);

		?>
		<!-- Login Logo plugin for WordPress: https://txfx.net/wordpress-plugins/login-logo/ -->
		<style>
			.login h1 a {
				background: url(<?php echo esc_url_raw($this->get_location('url')); ?>) no-repeat top center;
				width: <?php echo self::CUTOFF; ?>px;
				height: <?php echo $this->get_height(); ?>px;
				margin-left: 8px;
				padding-bottom: 16px;
				<?php
					if (self::CUTOFF < $this->get_original_width()) {
							$this->css3('background-size', 'contain');
					} else {
							$this->css3('background-size', 'auto');
					}
				?>
			}
		</style>

		<?php if (self::CUTOFF < $this->get_width()) { ?>
			<!--[if lt IE 9]>
				<style>
					height: <?php echo $this->get_original_height() + 3; ?>px;
				</style>
			<![endif]-->
		<?php
		}
	}
}

// Bootstrap
new BWA_Bld_Web_Agency_Plugin;
