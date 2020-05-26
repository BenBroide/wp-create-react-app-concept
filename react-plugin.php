<?php
/**
 * Plugin Name:     WordPress React Create App
 * Description:     Load React app in WorePress Admin concept
 * Author:          Ben Broide
 * Text Domain:     react-plugin
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         React_Plugin
 */


// Setting hook to load files.
add_action('wp_enqueue_scripts','rp_load_react_app');

/**
 * Load react app files in WordPress admin.
 *
 * @return bool|void
 */
function rp_load_react_app($hook){
	// Only load react app scripts in site front end home page.
	if( !is_front_page())
		return;

	// Setting path variables.
	$plugin_app_dir_url =plugin_dir_url( __FILE__ ) . 'react-app/';
	$react_app_build = $plugin_app_dir_url .'build/';
	$manifest_url = $react_app_build. 'asset-manifest.json';

	// Request manifest file.
	$request = file_get_contents( $manifest_url );

	// If the remote request fails, wp_remote_get() will return a WP_Error, so let’s check if the $request variable is an error:
	if( !$request )
		return false;

	// Convert json to php array.
	$files_data = json_decode($request);
	if($files_data === null)
		return ;

	if(!property_exists($files_data,'entrypoints'))
		return false;

	// Get assets links.
	$assets_files = $files_data->entrypoints;

	$js_files = array_filter($assets_files,'rp_filter_js_files');
	$css_files = array_filter($assets_files,'rp_filter_css_files');

	// Load css files.
	foreach ($css_files as $index => $css_file){
		wp_enqueue_style('react-plugin-'.$index, $react_app_build . $css_file);
	}

	// Load js files.
	foreach ($js_files as $index => $js_file){
		wp_enqueue_script('react-plugin-'.$index, $react_app_build . $js_file, array(), 1, true);
	}

	// Variables for app use.
	wp_localize_script('react-plugin-0', 'rpReactPlugin',
		array('appSelector' => '#site-footer')
	);
}

/**
 * Get js files from assets array.
 *
 * @param array $file_string
 *
 * @return bool
 */
function rp_filter_js_files ($file_string){
  return pathinfo($file_string, PATHINFO_EXTENSION) === 'js';
}

/**
 * Get css files from assets array.
 *
 * @param array $file_string
 *
 * @return bool
 */
function rp_filter_css_files ($file_string) {
	return pathinfo( $file_string, PATHINFO_EXTENSION ) === 'css';
}
