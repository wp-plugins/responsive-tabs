<?php

// Check for previous version data and recover
 
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

?>