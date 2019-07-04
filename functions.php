<?php
add_action('wp_enqueue_scripts', 'wilcityChildThemeScripts');

function wilcityChildThemeScripts(){
	$oTheme = wp_get_theme();
	wp_enqueue_style('wilcity-parent', get_template_directory_uri() . '/style.css', array(), $oTheme->get( 'Version' ));
	wp_enqueue_script('wilcity-child', get_stylesheet_directory_uri() . '/script.js', array('jquery'), '1.0', true);
	// wp_enqueue_script('select2-vi', get_stylesheet_directory_uri() . '/vendor/select2-vi.js', array('jquery', 'jquery-select2'), '1.0', true);
	// wp_localize_script('jquery-migrate', 'WILCITY_SELECT2_LENG', 'vi');
}

add_filter('wilcity/hero/search-form-below-category', function(){
     return false;
});

if( !function_exists("dd") ) {
    function dd( $data, $exit_data = true) {
        echo '<pre>'.print_r($data, true).'</pre>';
        if($exit_data == false)
            echo '';
        else
            exit;
    }
}

if ( class_exists( 'myCRED_Core' ) ) {
	require_once  ( get_stylesheet_directory() . '/mycred/init.php' );
}