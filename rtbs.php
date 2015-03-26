<?php
/*
Plugin Name: Responsive Tabs
Plugin URI: http://wpdarko.com/support/documentation/get-started-responsive-tabs/
Description: A responsive, simple and clean way to display your content. Create new tabs in no-time (custom type) and copy-paste the shortcode into any post/page. Find support and information on the <a href="http://wpdarko.com/responsive-tabs/">plugin's page</a>. This free version is NOT limited and does not contain any ad. Check out the <a href='http://wpdarko.com/items/responsive-tabs-pro/'>PRO version</a> for more great features.
Version: 3.0.2
Author: WP Darko
Author URI: http://wpdarko.com
License: GPL2
 */

add_action( 'init', 'process_post' );

function process_post() {
    
    if(!get_option('rtbs_is_updated_yn')){
    
        global $post;
        $args = array(
            'post_type' => 'rtbs_tabs',
        );
    
        $get_old = get_posts( $args );
        foreach ( $get_old as $post ) : setup_postdata( $post );
    
            $current_id = get_the_id();
            $old_data_tabs = get_post_meta( $current_id, 'rtbs_tabs_head', false );
    
            $i = 0;
            foreach ($old_data_tabs as $key => $odata) {
                $num = count($key);
                $num = $num +1;
    
                $test_man[$key]['_rtbs_title'] = $odata['rtbs_title'];
                $test_man[$key]['_rtbs_content'] = $odata['rtbs_content'];
    
                update_post_meta($current_id, '_rtbs_tabs_head', $test_man);
    
            }
    
            $old_data_settings = get_post_meta( $current_id, 'rtbs_settings_head', false );
    
            $i = 0;
            foreach ($old_data_settings as $key => $odata) {
                $num = count($key);
                $num = $num +1;
    
                $var1 = $odata['rtbs_tabs_bg_color'];
                $var2 = $odata['rtbs_breakpoint'];
    
                update_post_meta($current_id, '_rtbs_tabs_bg_color', $var1);
                update_post_meta($current_id, '_rtbs_breakpoint', $var2);
    
            }
    
        endforeach;
        
        update_option('rtbs_is_updated_yn', 'old_data_recovered');
            
    }
        
}
     


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
	wp_enqueue_style( 'rtbs', plugins_url('css/rtbs_style.min.css', __FILE__));
    wp_enqueue_script( 'rtbs', plugins_url('js/rtbs.min.js', __FILE__), array( 'jquery' ));
}

add_action( 'wp_enqueue_scripts', 'add_rtbs_scripts', 99 );

/* Enqueue admin styles */
add_action( 'admin_enqueue_scripts', 'add_admin_rtbs_style' );

function add_admin_rtbs_style() {
	wp_enqueue_style( 'rtbs', plugins_url('css/admin_de_style.min.css', __FILE__));
}

// Create Tabs custom type
function create_rtbs_tabs_type() {
  register_post_type( 'rtbs_tabs',
    array(
      'labels' => array(
        'name' => 'Tab Sets',
        'singular_name' => 'Tab Set'
      ),
      'public' => true,
      'has_archive' => false,
      'hierarchical' => false,
      'supports'           => array( 'title' ),
      'menu_icon'    => 'dashicons-plus',
    )
  );
}

add_action( 'init', 'create_rtbs_tabs_type' );

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

// Adding the CMB2 Metabox class
if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

// Registering Tabs metaboxes
function rtbs_register_tab_group_metabox( ) {
    
    $prefix = '_rtbs_';
   
    // Tables group
    $main_group = new_cmb2_box( array(
        'id' => $prefix . 'tab_metabox',
        'title' => '<span class="dashicons dashicons-welcome-add-page"></span> Manage Tabs <span style="color:#8a7463; font-weight:400; float:right; padding-right:14px;"><span class="dashicons dashicons-lock"></span> Free version</span>',
        'object_types' => array( 'rtbs_tabs' ),
    ));
    
        $rtbs_tab_group = $main_group->add_field( array(
            'id' => $prefix . 'tabs_head',
            'type' => 'group',
            'options' => array(
                'group_title' => 'Tab {#}',
                'add_button' => 'Add another tab',
                'remove_button' => 'Remove tab',
                'sortable' => true,
                'single' => false,
            ),
        ));

            $main_group->add_group_field( $rtbs_tab_group, array(
                'name' => '<span class="dashicons dashicons-edit"></span> Title',
                'id' => $prefix . 'title',
                'type' => 'text',
                'row_classes' => 'de_first de_hundred de_text de_input',
            ));
    
            $main_group->add_group_field( $rtbs_tab_group, array(
                'id' => $prefix . 'icon', 
                'name' => '<span style="color:#8a7463;"><span style="position:relative; top:-2px;" class="dashicons dashicons-lock"></span> PRO Icon (font-awesome)</span>', 
                'type' => 'text',
                'row_classes' => 'de_first de_hundred de_text de_input',
                'attributes'  => array(
                    'placeholder' => 'This feature allows you to choose from many icons for your tabs',
                ),
            ));
    
            $main_group->add_group_field( $rtbs_tab_group, array(
                'name' => '<span class="dashicons dashicons-edit"></span> Content',
				'id' => $prefix . 'content',
				'type' => 'textarea',
                'attributes'  => array(
                    'rows' => 8,
                ),
                'row_classes' => 'de_first de_seventy de_textarea de_input',
            ));
            
            $main_group->add_group_field( $rtbs_tab_group, array(
                'name' => 'Tips & Tricks',
                'desc' => '<span class="dashicons dashicons-yes"></span> Shortcodes (not recommended)<br/><span style="color:#bbb;">[your_shorcode]</span><br/><br/><span class="dashicons dashicons-yes"></span> Titles (H tags)<br/><span style="color:#bbb;">&lt;h1&gt;&lt;h2&gt;&lt;h3&gt;&lt;h4&gt;...</span></span><br/><br/><span class="dashicons dashicons-yes"></span> HTML allowed<br/><span style="color:#bbb;">&lt;img&gt;&lt;a&gt;&lt;br\&gt;&lt;p&gt;...</span></span>',
                'id'   => $prefix . 'content_desc',
                'type' => 'title',
                'row_classes' => 'de_thirty de_info',
            ));
    
            $main_group->add_group_field( $rtbs_tab_group, array(
                'name' => '',
                'id'   => $prefix . 'sep_header',
                'type' => 'title',
            ));
    
    // Settings group
    $side_group = new_cmb2_box( array(
        'id' => $prefix . 'settings_head',
        'title' => '<span class="dashicons dashicons-admin-tools"></span> Tab Set Settings',
        'object_types' => array( 'rtbs_tabs' ),
        'context' => 'side',
        'priority' => 'high',
        'closed' => true,
    ));
        
        $side_group->add_field( array(
            'name' => 'General settings',
            'id'   => $prefix . 'other_settings_desc',
            'type' => 'title',
            'row_classes' => 'de_hundred_side de_heading_side',
        ));
    
        $side_group->add_field( array(
            'id' => $prefix . 'tabs_bg_color', 
            'name' => '<span class="dashicons dashicons-admin-appearance"></span> Main color', 
            'type' => 'colorpicker', 
            'default' => '#57c9e0',
            'row_classes' => 'de_hundred_side de_color_side',
        ));
    
        $side_group->add_field( array(
            'id' => $prefix . 'breakpoint', 
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Breakpoint', 
            'type' => 'text', 
            'desc' => 'Width of the tab container at which it will turn into an accordion (in pixels, but don\'t put the "px" at the end).', 
            'default' => '600',
            'row_classes' => 'de_hundred_side de_text_side de_input',
        ));
    
        $side_group->add_field( array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Force original fonts',
            'desc' => 'By default this plugin will use your theme\'s font, check this to force the use of the plugin\'s original fonts.',
		    'id'   => $prefix . 'original_font',
		    'type' => 'checkbox',
            'row_classes' => 'de_hundred_side de_checkbox_side',
            'default' => false,
        ));
    
        $side_group->add_field( array(
            'id'      => $prefix . 'border', 
            'name'    => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Container borders</span>', 
            'type'    => 'select',
            'options' => array(
                'rtbs_notround' => 'Round or squared',
            ),
            'row_classes' => 'de_hundred_side de_text_side de_input',
        ));
    
        $side_group->add_field( array(
            'id'      => $prefix . 'cbg', 
            'name'    => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Container background </span>', 
            'type'    => 'select',
            'options' => array(
                'whitesmoke' => 'Choose among different shades',
            ),
            'row_classes' => 'de_hundred_side de_text_side de_input',
        ));
    
        $side_group->add_field( array(
            'id'      => $prefix . 'arrows', 
            'name'    => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Button style </span>', 
            'type'    => 'select',
            'options' => array(
                'whitesmoke' => 'Add a small arrow to your tabs',
            ),
            'default' => 'rtbs_notarrows',
            'row_classes' => 'de_hundred_side de_text_side de_input',
        ));
    
    // Help group
    $help_group = new_cmb2_box( array(
        'id' => $prefix . 'help_metabox',
        'title' => '<span class="dashicons dashicons-sos"></span> Help & Support',
        'object_types' => array( 'rtbs_tabs' ),
        'context' => 'side',
        'priority' => 'high',
        'closed' => true,
        'row_classes' => 'de_hundred de_heading',
    ));
    
        $help_group->add_field( array(
            'name' => '',
                'desc' => 'Find help at WPdarko.com<br/><br/><a target="_blank" href="http://wpdarko.com/support/forum/plugins/responsive-tabs/"><span class="dashicons dashicons-arrow-right-alt2"></span> Support forum</a><br/><a target="_blank" href="http://wpdarko.com/support/documentation/get-started-responsive-tabs/"><span class="dashicons dashicons-arrow-right-alt2"></span> Documentation</a>',
                'id'   => $prefix . 'help_desc',
                'type' => 'title',
                'row_classes' => 'de_hundred de_info de_info_side',
        ));
    
    // PRO group
    $pro_group = new_cmb2_box( array(
        'id' => $prefix . 'pro_metabox',
        'title' => '<span class="dashicons dashicons-awards"></span> PRO version',
        'object_types' => array( 'rtbs_tabs' ),
        'context' => 'side',
        'priority' => 'high',
        'closed' => true,
        'row_classes' => 'de_hundred de_heading',
    ));
    
        $pro_group->add_field( array(
            'name' => '',
                'desc' => 'This free version is <strong>not</strong> limited and does <strong>not</strong> contain any ad. Check out the PRO version for more great features.<br/><br/><a target="_blank" href="http://wpdarko.com/items/responsive-tabs-pro"><span class="dashicons dashicons-arrow-right-alt2"></span> See plugin\'s page</a>',
                'id'   => $prefix . 'pro_desc',
                'type' => 'title',
                'row_classes' => 'de_hundred de_info de_info_side',
        ));
    
    // Shortcode group
    $show_group = new_cmb2_box( array(
        'id' => $prefix . 'shortcode_metabox',
        'title' => '<span class="dashicons dashicons-visibility"></span> Display my Tabs',
        'object_types' => array( 'rtbs_tabs' ),
        'context' => 'side',
        'priority' => 'low',
        'closed' => false,
        'row_classes' => 'de_hundred de_heading',
    ));
    
        $show_group->add_field( array(
            'name' => '',
            'desc' => 'To display your Tabs on your site, copy-paste the Tab Set\'s [Shortcode] in your post/page. <br/><br/>You can find this shortcode by clicking on the "Tab Sets" tab in the menu on the left.',
            'id'   => $prefix . 'short_desc',
            'type' => 'title',
            'row_classes' => 'de_hundred de_info de_info_side',
        ));
    
}

add_action( 'cmb2_init', 'rtbs_register_tab_group_metabox' );

// Add shortcode column
function dkrtbs_custom_columns( $column, $post_id ) {
    switch ( $column ) {
	case 'shortcode' :
		global $post;
		$slug = '' ;
		$slug = $post->post_name;  
        $shortcode = '<span style="border: solid 3px lightgray; background:white; padding:2px 7px 5px; font-size:18px; line-height:40px;">[rtbs name="'.$slug.'"]</strong>';
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
    
	$entries = get_post_meta( $post->ID, '_rtbs_tabs_head', true );
    $options = get_post_meta( $post->ID, '_rtbs_settings_head', true );
    
    // Forcing original fonts?
    $original_font = get_post_meta( $post->ID, '_rtbs_original_font', true );
    if ($original_font == true){
        $ori_f = 'rtbs_tab_ori';
    } else {
        $ori_f = '';
    }
  
    $rtbs_breakpoint = get_post_meta( $post->ID, '_rtbs_breakpoint', true );
    $rtbs_color = get_post_meta( $post->ID, '_rtbs_tabs_bg_color', true );

    /* Outputing the options in invisible divs */
    $output = '<div class="rtbs '.$ori_f.' rtbs_'.$name.'">';
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
                    $output .= $tabs['_rtbs_title'];
                    $output .= '</a>';
                    $output .= '</li>';
                    } else {
                    $output .= '<li>';
                    $output .= '<a href="#'.$name.'-tab-'.$key.'" class="'.$name.'-tab-link-'.$key.'">';
                    $output .= $tabs['_rtbs_title'];
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
                $output .= do_shortcode(wpautop($tabs['_rtbs_content']));
            $output .= '<div style="margin-top:30px; clear:both;"></div></div>';
        } else {
            $output .= '<div style="border-top:7px solid '.$rtbs_color.';" id="'.$name.'-tab-'.$key.'" class="rtbs_content">';
                $output .= do_shortcode(wpautop($tabs['_rtbs_content']));
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