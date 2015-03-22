<?php
/**
Plugin Name: Responsive Tabs
Plugin URI: http://wpdarko.com/support/documentation/get-started-responsive-tabs/
Description: A responsive, simple and clean way to display your content. Create new tabs in no-time (custom type) and copy-paste the shortcode into any post/page. Find support and information on the <a href="http://wpdarko.com/responsive-tabs/">plugin's page</a>. This free version is NOT limited and does not contain any ad. Check out the <a href='http://wpdarko.com/items/responsive-tabs-pro/'>PRO version</a> for more great features.
Version: 2.0
Author: WP Darko
Author URI: http://wpdarko.com
License: GPL2
 */

/* Check for the PRO version */
function rtbs_free_pro_check() {
    if (is_plugin_active('responsive-tabs-pro/rtbs_pro.php')) {
        
        function my_admin_notice(){
        echo '<div class="updated">
                <p><strong>PRO</strong> version is activated.</p>
              </div>';
        }
        add_action('admin_notices', 'my_admin_notice');
        
        deactivate_plugins(__FILE__);
    }
}

add_action( 'admin_init', 'rtbs_free_pro_check' );

/* Enqueue scripts & styles */
function add_rtbs_scripts() {
	wp_enqueue_style( 'rtbs', plugins_url('css/rtbs_custom_style.min.css', __FILE__));
    wp_enqueue_script( 'rtbs', plugins_url('js/rtbs.min.js', __FILE__), array( 'jquery' ));
}

add_action( 'wp_enqueue_scripts', 'add_rtbs_scripts', 99 );

/* Create the Tabs custom type */
function create_rtbs_tabs_type() {
	$labels = array(
		'name'               => _x( 'Tab Sets', 'post type general name', 'responsive-tabs-plugin' ),
		'singular_name'      => _x( 'Tab Set', 'post type singular name', 'responsive-tabs-plugin' ),
		'menu_name'          => _x( 'Tab Sets', 'admin menu', 'responsive-tabs-plugin' ),
		'name_admin_bar'     => _x( 'Tab Set', 'add new on admin bar', 'responsive-tabs-plugin' ),
		'add_new'            => _x( 'Add New', 'Tab Set', 'responsive-tabs-plugin' ),
		'add_new_item'       => __( 'Add New Tab Set', 'responsive-tabs-plugin' ),
		'new_item'           => __( 'New Tab Set', 'responsive-tabs-plugin' ),
		'edit_item'          => __( 'Edit Tab Set', 'responsive-tabs-plugin' ),
		'view_item'          => __( 'View Tab Set', 'responsive-tabs-plugin' ),
		'all_items'          => __( 'All Tab Sets', 'responsive-tabs-plugin' ),
		'search_items'       => __( 'Search Tab Sets', 'responsive-tabs-plugin' ),
		'not_found'          => __( 'No Tab Set found.', 'responsive-tabs-plugin' ),
		'not_found_in_trash' => __( 'No Tab Sets found in Trash.', 'responsive-tabs-plugin' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'tabs' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 60,
		'supports'           => array( 'title' ),
        'menu_icon'    => 'dashicons-plus',
	);

	register_post_type( 'rtbs_tabs', $args );
}

add_action( 'init', 'create_rtbs_tabs_type' );

/**
 * Tabs update messages.
 *
 * @param array $messages Existing post update messages.
 *
 * @return array Amended post update messages with new CPT update messages.
 */
function rtbs_tabs_updated_messages( $messages ) {
	$post             = get_post();
	$post_type        = get_post_type( $post );
	$post_type_object = get_post_type_object( $post_type );

	$messages['rtbs_tabs'] = array(
		0  => '', // Unused. Messages start at index 1.
		1  => __( 'Tab set updated.', 'responsive-tabs-plugin' ),
		2  => __( 'Custom field updated.', 'responsive-tabs-plugin' ),
		3  => __( 'Custom field deleted.', 'responsive-tabs-plugin' ),
		4  => __( 'Tab set updated.', 'responsive-tabs-plugin' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Tab set restored to revision from %s', 'responsive-tabs-plugin' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6  => __( 'Tab set published.', 'responsive-tabs-plugin' ),
		7  => __( 'Tab set saved.', 'responsive-tabs-plugin' ),
		8  => __( 'Tab set submitted.', 'responsive-tabs-plugin' ),
		9  => sprintf(
			__( 'Tab set scheduled for: <strong>%1$s</strong>.', 'responsive-tabs-plugin' ),
			// translators: Publish box date format, see http://php.net/date
			date_i18n( __( 'M j, Y @ G:i', 'responsive-tabs-plugin' ), strtotime( $post->post_date ) )
		),
		10 => __( 'Tab set draft updated.', 'responsive-tabs-plugin' )
	);

	if ( $post_type_object->publicly_queryable ) {
		$permalink = get_permalink( $post->ID );

		$view_link = sprintf( ' <a href="%s">%s</a>', esc_url( $permalink ), __( 'View tab set', 'responsive-tabs-plugin' ) );
		$messages[ $post_type ][1] .= $view_link;
		$messages[ $post_type ][6] .= $view_link;
		$messages[ $post_type ][9] .= $view_link;

		$preview_permalink = add_query_arg( 'preview', 'true', $permalink );
		$preview_link = sprintf( ' <a target="_blank" href="%s">%s</a>', esc_url( $preview_permalink ), __( 'Preview tab set', 'responsive-tabs-plugin' ) );
		$messages[ $post_type ][8]  .= $preview_link;
		$messages[ $post_type ][10] .= $preview_link;
	}

	return $messages;
}

add_filter( 'post_updated_messages', 'rtbs_tabs_updated_messages' );

/* Hide View/Preview since it's a shortcode */
function rtbs_tabs_admin_css() {
    global $post_type;
    $post_types = array( 
                        'rtbs_tabs',
                  );
    if(in_array($post_type, $post_types))
    echo '<style type="text/css">#post-preview, #view-post-btn{display: none;}</style>';
}

function remove_view_link_rtbs_tabs( $action ) {

    unset ($action['view']);
    return $action;
}

add_filter( 'post_row_actions', 'remove_view_link_rtbs_tabs' );
add_action( 'admin_head-post-new.php', 'rtbs_tabs_admin_css' );
add_action( 'admin_head-post.php', 'rtbs_tabs_admin_css' );

/* Add the metabox class */
if (!class_exists('drkfr_Meta_Box')) {
    require_once( 'drkfr/custom-meta-boxes.php' );
}

/**
* Define the metabox and field configurations.
*
* @param array $meta_boxes
* @return array
*/
function rtbs_metaboxes( array $meta_boxes ) {
    
    // Add the tab fields
    $fields = array(
        array( 'id' => 'rtbs_content_head', 'desc' => '', 'type' => 'title' ),
        array( 'id' => 'rtbs_title', 'name' => 'Title', 'type' => 'text' ),
        array( 'id' => 'rtbs_content', 'name' => 'Content', 'type' => 'wysiwyg', 'options' => array('textarea_rows' => 9)),
    );
    
    $group_settings = array(
        array( 'id' => 'rtbs_tabs_bg_color', 'name' => 'Main color', 'type' => 'colorpicker', 'default' => '#57c9e0' ),
        array( 'id' => 'rtbs_breakpoint', 'name' => 'Breakpoint', 'type' => 'text', 'desc' => 'Width of the tab container at which it will turn into an accordion (in pixels, but don\'t put the "px" at the end).', 'default' => '600' ),
    );
    
    $group_fields = $fields;
    foreach ( $group_fields as &$field ) {
        $field['id'] = str_replace( 'field', 'gfield', $field['id'] );
    }
    
    $meta_boxes[] = array(
        'title' => 'Managing tabs',
        'pages' => 'rtbs_tabs',
        'fields' => array(
            array(
                'id' => 'rtbs_tabs_head',
                'type' => 'group',
                'repeatable' => true,
                'sortable' => true,
                'fields' => $group_fields,
                'name' => 'Create tabs and drag and drop to reorder.',
            )
        )
    );
    
    $meta_boxes[] = array(
        'title' => 'Settings',
        'pages' => 'rtbs_tabs',
        'context' => 'side',
        'priority' => 'high',
        'fields' => array(
            array(
                'id' => 'rtbs_settings_head',
                'type' => 'group',
                'fields' => $group_settings,
            )
        )
    );
    
    function rtbs_pro_side_meta() {
        return "<p style='font-size:14px; color:#333; font-style:normal;'>This free version is <strong>not</strong> limited and does <strong>not</strong> contain any ad. Check out the <a href='http://wpdarko.com/items/responsive-tabs-pro/'><span style='color:#61d1aa !important;'>PRO version</span></a> for more great features.</p>";
    }
    
     $meta_boxes[] = array(
        'title' => 'Responsive Tabs PRO',
        'pages' => 'rtbs_tabs',
        'context' => 'side',
        'priority' => 'low',
        'fields' => array(
            array(
                'id' => 'rtbs_pro_head',
                'type' => 'group',
                'desc' => rtbs_pro_side_meta(),
            )
        )
    );
    
    return $meta_boxes;
}

add_filter( 'drkfr_meta_boxes', 'rtbs_metaboxes' );

// Add shortcode column
function dkrtbs_custom_columns( $column, $post_id ) {
    switch ( $column ) {
	case 'shortcode' :
		global $post;
		$slug = '' ;
		$slug = $post->post_name;  
        $shortcode = '<span style="border: solid 3px lightgray; background:white; padding:7px; font-size:17px; line-height:40px;">[rtbs name="'.$slug.'"]</strong>';
	    echo $shortcode; 
	    break;
    }
}

add_action( 'manage_rtbs_tabs_posts_custom_column' , 'dkrtbs_custom_columns', 10, 2 );

function add_rtbs_tabs_columns($columns) {
    return array_merge($columns, 
              array('shortcode' => __('Shortcode'),
                    ));
}

add_filter('manage_rtbs_tabs_posts_columns' , 'add_rtbs_tabs_columns');

// Create the Responsive Tabs shortcode
function rtbs_sc($atts) {
	extract(shortcode_atts(array(
		"name" => ''
	), $atts));
	
    global $post;
    $args = array('post_type' => 'rtbs_tabs', 'name' => $name);
    $custom_posts = get_posts($args);
    foreach($custom_posts as $post) : setup_postdata($post);
    
	$entries = get_post_meta( get_the_id(), 'rtbs_tabs_head', false );
    $options = get_post_meta( get_the_id(), 'rtbs_settings_head', false );
  
    foreach ($options as $key => $option) {
        $rtbs_breakpoint = $option['rtbs_breakpoint'];
        $rtbs_color = $option['rtbs_tabs_bg_color'];
    }

    /* Outputing the options in invisible divs */
    $output = '<div class="rtbs rtbs_'.$name.'">';
    $output .= '<div class="rtbs_slug" style="display:none">'.$name.'</div>';
    $output .= '<div class="rtbs_breakpoint" style="display:none">'.$rtbs_breakpoint.'</div>';
    $output .= '<div class="rtbs_color" style="display:none">'.$rtbs_color.'</div>';
    
    $output .= '
        <div class="rtbs_menu">
            <ul>
                <li class="mobile_toggle">&zwnj;</li>';
                foreach ($entries as $key => $tabs) {
                    if ($key == 0){
                    $output .= '<li class="current">';
                    $output .= '<a style="background:'.$rtbs_color.'" class="active '.$name.'-tab-link-'.$key.'" href="#'.$name.'-tab-'.$key.'">';
                    $output .= $tabs['rtbs_title'];
                    $output .= '</a>';
                    $output .= '</li>';
                    } else {
                    $output .= '<li>';
                    $output .= '<a href="#'.$name.'-tab-'.$key.'" class="'.$name.'-tab-link-'.$key.'">';
                    $output .= $tabs['rtbs_title'];
                    $output .= '</a>';
                    $output .= '</li>';
                    }
                }
    $output .= '
            </ul>
        </div>';
    
    foreach ($entries as $key => $tabs) {
        if ($key == 0){
            $output .= '<div style="border-top:7px solid '.$rtbs_color.';" id="'.$name.'-tab-'.$key.'" class="rtbs_content active">';
                $output .= do_shortcode(wpautop($tabs['rtbs_content']));
            $output .= '<div style="margin-top:30px; clear:both;"></div></div>';
        } else {
            $output .= '<div style="border-top:7px solid '.$rtbs_color.';" id="'.$name.'-tab-'.$key.'" class="rtbs_content">';
                $output .= do_shortcode(wpautop($tabs['rtbs_content']));
            $output .= '<div style="margin-top:30px; clear:both;"></div></div>';
        }
    }
    $output .= '
    </div>
    ';

  endforeach; wp_reset_query(); 
  return $output;

}

add_shortcode("rtbs", "rtbs_sc"); 
?>