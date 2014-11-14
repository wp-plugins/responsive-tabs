<?php
if ( ! defined( 'dkrtbs_DEV') )
	define( 'dkrtbs_DEV', false );

if ( ! defined( 'dkrtbs_PATH') )
	define( 'dkrtbs_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'dkrtbs_URL' ) )
	define( 'dkrtbs_URL', plugins_url( '', __FILE__ ) );

include_once( dkrtbs_PATH . '/classes.fields.php' );
include_once( dkrtbs_PATH . '/class.dkrtbs-meta-box.php' );

// Make it possible to add fields in locations other than post edit screen.
include_once( dkrtbs_PATH . '/fields-anywhere.php' );

// include_once( dkrtbs_PATH . '/example-functions.php' );

/**
 * Get all the meta boxes on init
 *
 * @return null
 */
function dkrtbs_init() {

	if ( ! is_admin() )
		return;

	// Load translations
	$textdomain = 'dkrtbs';
	$locale = apply_filters( 'plugin_locale', get_locale(), $textdomain );

	// By default, try to load language files from /wp-content/languages/custom-meta-boxes/
	load_textdomain( $textdomain, WP_LANG_DIR . '/dkrtbs-meta-boxes/' . $textdomain . '-' . $locale . '.mo' );
	load_textdomain( $textdomain, dkrtbs_PATH . '/languages/' . $textdomain . '-' . $locale . '.mo' );

	$meta_boxes = apply_filters( 'dkrtbs_meta_boxes', array() );

	if ( ! empty( $meta_boxes ) )
		foreach ( $meta_boxes as $meta_box )
			new dkrtbs_Meta_Box( $meta_box );

}
add_action( 'init', 'dkrtbs_init', 50 );

/**
 * Return an array of built in available fields
 *
 * Key is field name, Value is class used by field.
 * Available fields can be modified using the 'dkrtbs_field_types' filter.
 *
 * @return array
 */
function _dkrtbs_available_fields() {

	return apply_filters( 'dkrtbs_field_types', array(
		'text'				=> 'dkrtbs_Text_Field',
		'text_small' 		=> 'dkrtbs_Text_Small_Field',
		'text_url'			=> 'dkrtbs_URL_Field',
		'url'				=> 'dkrtbs_URL_Field',
		'radio'				=> 'dkrtbs_Radio_Field',
		'checkbox'			=> 'dkrtbs_Checkbox',
		'file'				=> 'dkrtbs_File_Field',
		'image' 			=> 'dkrtbs_Image_Field',
		'wysiwyg'			=> 'dkrtbs_wysiwyg',
		'textarea'			=> 'dkrtbs_Textarea_Field',
		'textarea_code'		=> 'dkrtbs_Textarea_Field_Code',
		'select'			=> 'dkrtbs_Select',
		'taxonomy_select'	=> 'dkrtbs_Taxonomy',
		'post_select'		=> 'dkrtbs_Post_Select',
		'date'				=> 'dkrtbs_Date_Field',
		'date_unix'			=> 'dkrtbs_Date_Timestamp_Field',
		'datetime_unix'		=> 'dkrtbs_Datetime_Timestamp_Field',
		'time'				=> 'dkrtbs_Time_Field',
		'colorpicker'		=> 'dkrtbs_Color_Picker',
		'title'				=> 'dkrtbs_Title',
		'group'				=> 'dkrtbs_Group_Field',
		'gmap'				=> 'dkrtbs_Gmap_Field',
	) );

}

/**
 * Get a field class by type
 *
 * @param  string $type
 * @return string $class, or false if not found.
 */
function _dkrtbs_field_class_for_type( $type ) {

	$map = _dkrtbs_available_fields();

	if ( isset( $map[$type] ) )
		return $map[$type];

	return false;

}

/**
 * For the order of repeatable fields to be guaranteed, orderby meta_id needs to be set.
 * Note usermeta has a different meta_id column name.
 *
 * Only do this for older versions as meta is now ordered by ID (since 3.8)
 * See http://core.trac.wordpress.org/ticket/25511
 *
 * @param  string $query
 * @return string $query
 */
function dkrtbs_fix_meta_query_order($query) {

    $pattern = '/^SELECT (post_id|user_id), meta_key, meta_value FROM \w* WHERE post_id IN \([\d|,]*\)$/';

    if (
            0 === strpos( $query, "SELECT post_id, meta_key, meta_value" ) &&
            preg_match( $pattern, $query, $matches )
    ) {

            if ( isset( $matches[1] ) && 'user_id' == $matches[1] )
                    $meta_id_column = 'umeta_id';
            else
                    $meta_id_column = 'meta_id';

            $meta_query_orderby = ' ORDER BY ' . $meta_id_column;

            if ( false === strpos( $query, $meta_query_orderby ) )
                    $query .= $meta_query_orderby;

    }

    return $query;

}

if ( version_compare( get_bloginfo( 'version' ), '3.8', '<' ) )
	add_filter( 'query', 'dkrtbs_fix_meta_query_order', 1 );