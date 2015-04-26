<?php
//
// dev.xiligroup.com - msc - 2015-04-26 - first test

// 1.5.4 - msc - 2015-04-26 - WP 4.2 Powell

define( 'HUEMAN_XILI_VER', '1.1'); // as parent style.css

// main initialisation functions and version testing and message

function hueman_xilidev_setup () {

	$theme_domain = 'hueman';

	$minimum_xl_version = '2.17.0'; // >

	$xl_required_version = false;

	load_theme_textdomain( $theme_domain, get_stylesheet_directory() . '/langs' ); // now use .mo of child

	if ( class_exists('xili_language') ) { // if temporary disabled

		$xl_required_version = version_compare ( XILILANGUAGE_VER, $minimum_xl_version, '>' );

		global $xili_language;

		$xili_language_includes_folder = $xili_language->plugin_path .'xili-includes';

		$xili_functionsfolder = get_stylesheet_directory() . '/functions-xili' ;

		if ( file_exists( $xili_functionsfolder . '/multilingual-classes.php') ) {
			require_once ( $xili_functionsfolder . '/multilingual-classes.php' ); // xili-options created by developers in child theme in priority

		} elseif ( file_exists( $xili_language_includes_folder . '/theme-multilingual-classes.php') ) {
			require_once ( $xili_language_includes_folder . '/theme-multilingual-classes.php' ); // ref xili-options based in plugin
		}

		if ( file_exists( $xili_functionsfolder . '/multilingual-functions.php') ) {
			require_once ( $xili_functionsfolder . '/multilingual-functions.php' );
		}

		global $xili_language_theme_options ; // used on both side
		// Args dedicated to this theme named Hueman
		$xili_args = array (
			'customize_clone_widget_containers' => true, // comment or set to true to clone widget containers
			'settings_name' => 'xili_hueman_theme_options', // name of array saved in options table
			'theme_name' => 'Hueman',
			'theme_domain' => $theme_domain,
			'child_version' => HUEMAN_XILI_VER
		);

		add_action( 'widgets_init', 'hueman_xili_add_widgets' );
		add_theme_support( 'custom_xili_flag' );

		// new in WP 4.1 - now in XL 2.17.1
		if ( !has_filter ( 'get_the_archive_description', array($xili_language, 'get_the_archive_description' ) ) ) {
			add_filter ( 'get_the_archive_description', 'xili_get_the_archive_description' );
		}

		if ( is_admin() ) {

		// Admin args dedicaced to this theme

			$xili_admin_args = array_merge ( $xili_args, array (
				'customize_adds' => true, // add settings in customize page
				'customize_addmenu' => false, // done by 2013
				'capability' => 'edit_theme_options',
				'authoring_options_admin' => false
			) );

			if ( class_exists ( 'xili_language_theme_options_admin' ) ) {
				$xili_language_theme_options = new xili_language_theme_options_admin ( $xili_admin_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}


		} else { // visitors side - frontend

			if ( class_exists ( 'xili_language_theme_options' ) ) {
				$xili_language_theme_options = new xili_language_theme_options ( $xili_args );
				$class_ok = true ;
			} else {
				$class_ok = false ;
			}
		}
		// new ways to add parameters in authoring propagation
		add_theme_support('xiliml-authoring-rules', array (
			'post_content' => array('default' => '',
				'data' => 'post',
				'hidden' => '',
				'name' => 'Post Content',
				/* translators: added in child functions by xili */
				'description' => __('Will copy content in the future translated post', 'hueman')
			),
			'post_parent' => array('default' => '1', // (checked)
				'data' => 'post',
				'name' => 'Post Parent',
				'hidden' => '', // checkbox not visible in dashboard UI
				/* translators: added in child functions by xili */
				'description' => __('Will copy translated parent id (if original has parent and translated parent)!', 'hueman')
			))
		); //

		if ( $class_ok ) {
			$xili_theme_options = get_theme_xili_options() ;
			// to collect checked value in xili-options of theme
			if ( file_exists( $xili_functionsfolder . '/multilingual-permalinks.php') && $xili_language->is_permalink && isset( $xili_theme_options['perma_ok'] ) && $xili_theme_options['perma_ok']) {
				require_once ( $xili_functionsfolder . '/multilingual-permalinks.php' ); // require subscribing premium services
			}
			if ( $xl_required_version ) { // msg choice is inside class
				$msg = $xili_language_theme_options->child_installation_msg( $xl_required_version, $minimum_xl_version, $class_ok );
			} else {
				$msg = '
				<div class="error">'.
					/* translators: added in child functions by xili */
					'<p>' . sprintf ( __('The %1$s child theme requires xili_language version more recent than %2$s installed', 'hueman' ), get_option( 'current_theme' ), $minimum_xl_version ).'</p>
				</div>';

			}
		} else {

			$msg = '
			<div class="error">'.
				/* translators: added in child functions by xili */
				'<p>' . sprintf ( __('The %s child theme requires xili_language_theme_options class installed and activated', 'hueman' ), get_option( 'current_theme' ) ).'</p>
			</div>';

		}

	} else {

		$msg = '
		<div class="error">'.
			/* translators: added in child functions by xili */
			'<p>' . sprintf ( __('The %s child theme requires xili-language plugin installed and activated', 'hueman' ), get_option( 'current_theme' ) ).'</p>
		</div>';

	}

	// errors and installation informations
	// after activation and in themes list
	if ( isset( $_GET['activated'] ) || ( ! isset( $_GET['activated'] ) && ( ! $xl_required_version || ! $class_ok ) ) )
		add_action( 'admin_notices', $c = create_function( '', 'echo "' . addcslashes( $msg, '"' ) . '";' ) );

	// end errors...
	add_filter( 'pre_option_link_manager_enabled', '__return_true' ); // comment this line if you don't want links/bookmarks features

}
add_action( 'after_setup_theme', 'hueman_xilidev_setup', 11 ); // called after parent

function hueman_xili_add_widgets () {
	register_widget( 'xili_Widget_Categories' ); // in xili-language-widgets.php since 2.16.3
}

/**
 * This function is an example to customize flags with flags incorporated inside another child theme folder.
 * by default, xili-language plugin uses those in flags sub-folder overridden by those in medial library
 *
 */
function hueman_xili_flags_customize () {
	remove_theme_support ( 'custom_xili_flag' ); // default created in setup above
	$args = array(
		'en_us'	=> array(
			'path' => '%2$s/images/flags_2/en_us.png',
			'height'				=> 24,
			'width'					=> 24
			),
		'es_es'	=> array(
			'path' => '%2$s/images/flags_2/es_es.png',
			'height'				=> 24,
			'width'					=> 24
			),
		'fr_fr'	=> array(
			'path' => '%2$s/images/flags_2/fr_fr.png',
			'height'				=> 24,
			'width'					=> 24
			),
		'de_de'	=> array(
			'path' => '%2$s/images/flags_2/de_de.png', //wp-content/themes/twentyfifteen-xili/images/flags_2/de_de.png
			'height'				=> 24,
			'width'					=> 24
			),
	);
	add_theme_support ( 'custom_xili_flag', $args );
}
add_action( 'after_setup_theme', 'hueman_xili_flags_customize', 13 );

function hueman_get_default_xili_flag_options ( $default_array, $parent_theme ) {
	// because here above sizes have changed, and hueman need create default values
	// to recover these values: do not forget to reset to default values in xili flag options dashboard screen

		$default_array['css_ul_nav_menu'] = '#nav-header';
		$default_array['css_li_hover'] = 'background-color:#f5f5f5; background:rgba(255,255,255,0.3);' ;
		$default_array['css_li_a'] = 'text-indent:-999px; width:24px; background:transparent no-repeat 0 12px; margin:0; padding-right:1px;' ;
		$default_array['css_li_a_hover'] = 'background: no-repeat 0 13px !important;' ;
	return $default_array;
}
add_filter( 'get_default_xili_flag_options', 'hueman_get_default_xili_flag_options', 10, 2);

/**
 * condition to filter adjacent links
 * @since 1.1.4
 *
 */
function is_xili_adjacent_filterable() {

	if ( is_search () ) { // for multilingual search
		return false;
	}
	return true;
}

/** series of parent functions modified to follow WP core codex **/

function alx_site_title() {

	// Text or image?
	if ( ot_get_option('custom-logo') ) {
		$logo = '<img src="'.ot_get_option('custom-logo').'" alt="'.get_bloginfo('name', 'display').'">'; // xili
	} else {
		$logo = get_bloginfo('name', 'display'); // xili
	}

	$link = '<a href="'.home_url('/').'" rel="home">'.$logo.'</a>';

	if ( is_front_page() || is_home() ) {
		$sitename = '<h1 class="site-title">'.$link.'</h1>'."\n";
	} else {
		$sitename = '<p class="site-title">'.$link.'</p>'."\n";
	}

	return $sitename;
}




?>
