<?php
/**
 * Plugin Name: Responsive Tabs
 * Plugin URI: http://wpdarko.com/darko-tools/responsive-pricing-table/
 * Description: A responsive, simple and clean way to display your content. Create new tabs in no-time (custom type) and copy-paste the shortcode into any post/page. Find support and information on the <a href="http://wpdarko.com/darko-tools/responsive-tabs/">plugin's page</a>. This free version is NOT limited and does not contain any ad. Check out the <a href='http://wpdarko.com/darko-tools/responsive-tabs-pro/'>PRO version</a> for more great features.
 * Version: 1.1.1
 * Author: WP Darko
 * Author URI: http://wpdarko.com
 * License: GPL2
 */

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

/* adds stylesheet and script */
add_action( 'wp_enqueue_scripts', 'add_rtbs_scripts' );
function add_rtbs_scripts() {
	wp_enqueue_style( 'rtbs', plugins_url('css/rtbs_custom_style.min.css', __FILE__));
    wp_enqueue_script( 'rtbs', plugins_url('js/rtbs.min.js', __FILE__), array( 'jquery' ));
}

add_action( 'init', 'create_rtbs_tabs_type' );

function create_rtbs_tabs_type() {
  register_post_type( 'rtbs_tabs',
    array(
      'labels' => array(
        'name' => 'Tabs',
        'singular_name' => 'Tabs'
      ),
      'public' => true,
      'has_archive'  => false,
      'hierarchical' => false,
      'supports'     => array( 'title' ),
      'menu_icon'    => 'dashicons-plus',
    )
  );
}

/**
* Define the metabox and field configurations.
*
* @param array $meta_boxes
* @return array
*/
function rtbs_metaboxes( array $meta_boxes ) {
    // Example of all available fields
    $fields = array(
        array( 'id' => 'rtbs_content_head', 'name' => 'TAB CONTENT', 'type' => 'title' ),
        array( 'id' => 'rtbs_title', 'name' => '&#8212; Title', 'type' => 'text' ),
        array( 'id' => 'rtbs_content', 'name' => '&#8212; Content', 'type' => 'wysiwyg', 'options' => array('textarea_rows' => 5)),
    );
    
    $group_settings = array(
        array( 'id' => 'rtbs_breakpoint', 'name' => '&#8212; Breakpoint', 'type' => 'text', 'desc' => 'That\'s the width of the tab container at which it will turn into a dropdown (enter a number of pixels, don\'t put the "px" at the end).', 'default' => '600' ),
        array( 'id' => 'rtbs_tabs_bg_color', 'name' => 'Main color', 'type' => 'colorpicker', 'default' => '#57c9e0' ),
    );
    // Example of repeatable group. Using all fields.
    // For this example, copy fields from $fields, update I
    $group_fields = $fields;
    foreach ( $group_fields as &$field ) {
        $field['id'] = str_replace( 'field', 'gfield', $field['id'] );
    }
    $meta_boxes[] = array(
        'title' => 'Create/remove/sort tabs',
        'pages' => 'rtbs_tabs',
        'fields' => array(
            array(
                'id' => 'rtbs_tabs_head',
                'type' => 'group',
                'repeatable' => true,
                'sortable' => true,
                'fields' => $group_fields,
                'desc' => 'Create new tabs here and drag and drop to reorder.',
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
        return "<p style='font-size:14px; color:#333; font-style:normal;'>This free version is <strong>NOT</strong> limited and does <strong>not</strong> contain any ad. Check out the <a href='http://wpdarko.com/darko-tools/responsive-tabs-pro/'><span style='color:#61d1aa !important;'>PRO version</span></a> for more great features.</p>";
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

if (!class_exists('drkfr_Meta_Box')) {
    require_once( 'drkfr/custom-meta-boxes.php' );
}

//shortcode columns
add_action( 'manage_rtbs_tabs_posts_custom_column' , 'dkrtbs_custom_columns', 10, 2 );

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

function add_rtbs_tabs_columns($columns) {
    return array_merge($columns, 
              array('shortcode' => __('Shortcode'),
                    ));
}
add_filter('manage_rtbs_tabs_posts_columns' , 'add_rtbs_tabs_columns');

//rtbs shortcode
function rtbs_sc($atts) {
	extract(shortcode_atts(array(
		"name" => ''
	), $atts));
	
    query_posts( array( 'post_type' => 'rtbs_tabs', 'name' => $name, ) );
    if ( have_posts() ) : while ( have_posts() ) : the_post();

    global $post;
    
	$entries = get_post_meta( get_the_id(), 'rtbs_tabs_head', false );
    $options = get_post_meta( get_the_id(), 'rtbs_settings_head', false );
  
    foreach ($options as $key => $option) {
        $rtbs_breakpoint = $option['rtbs_breakpoint'];
        $rtbs_bg_color = $option['rtbs_tabs_bg_color'];
        $rtbs_color = $option['rtbs_tabs_color'];
    }

    $output .= '<div class="rtbs rtbs_'.$name.'">';
    $output .= '<div class="rtbs_slug" style="display:none">'.$name.'</div>';
    $output .= '<div class="rtbs_breakpoint" style="display:none">'.$rtbs_breakpoint.'</div>';
    $output .= '<div class="rtbs_bg_color" style="display:none">'.$rtbs_bg_color.'</div>';
    $output .= '<div class="rtbs_color" style="display:none">'.$rtbs_color.'</div>';
    $output .= '
        <div class="rtbs_menu">
            <ul>
                <li class="mobile_toggle">&zwnj;</li>';
                foreach ($entries as $key => $tabs) {
                    if ($key == 0){
                    $output .= '<li class="current">';
                    $output .= '<a style="background:'.$rtbs_bg_color.'" class="active" href="#'.$name.'-tab-'.$key.'">';
                    $output .= $tabs['rtbs_title'];
                    $output .= '</a>';
                    $output .= '</li>';
                    } else {
                    $output .= '<li>';
                    $output .= '<a href="#'.$name.'-tab-'.$key.'">';
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
        $output .= '<div style="border-top:7px solid '.$rtbs_bg_color.';" id="'.$name.'-tab-'.$key.'" class="rtbs_content active">';
        $output .= do_shortcode(wpautop($tabs['rtbs_content']));
        $output .= '</div>';
        } else {
        $output .= '<div style="border-top:7px solid '.$rtbs_bg_color.';" id="'.$name.'-tab-'.$key.'" class="rtbs_content">';
        $output .= do_shortcode(wpautop($tabs['rtbs_content']));
        $output .= '</div>';
        }
    }
    $output .= '
    </div>
    <div style="margin-top:30px;clear:both;"></div>';


  endwhile; endif; wp_reset_query(); 
	
  return $output;

}
add_shortcode("rtbs", "rtbs_sc"); 
?>