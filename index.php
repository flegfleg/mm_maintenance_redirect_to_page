<?php
/*
 * Plugin Name: MM Maintenance redirect to page
 * Version: 1.2
 * Plugin URI: https://github.com/flegfleg/mm_maintenance_redirect_to_page/
 * Description: Redirect all visitors to a page of your choosing.
 * Author: Florian Egermann
 * Author URI: http://www.fleg.de/
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mm-maintenance-redirect
 * Requires at least: 4.0
 * Tested up to: 4.5
 *
 *
 * @package WordPress
 * @author Florian Egermann
 * @since 1.0.0
 */

/* * * * * * * * * * * * * *
 * Localization
 * * * * * * * * * * * * * *
 */

add_action('plugins_loaded', 'mm_maintenance_redirect_textdomain');

function mm_maintenance_redirect_textdomain() {
    load_plugin_textdomain( 'mm-maintenance-redirect', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}


/* * * * * * * * * * * * * *
 * Settings API
 * * * * * * * * * * * * * *
 */

add_action( 'admin_menu', 'mm_maintenance_redirect_add_admin_menu' );
add_action( 'admin_init', 'mm_maintenance_redirect_settings_init' );

function mm_maintenance_redirect_add_admin_menu(  ) { 

    add_options_page( 'Maintenance Redirect', 'Maintenance Redirect', 'manage_options', 'mm_maintenance_redirect', 'mm_maintenance_redirect_options_page' );
}


function mm_maintenance_redirect_settings_init(  ) { 

    register_setting( 'pluginPage', 'mm_maintenance_redirect_settings' );

    add_settings_section(
        'mm_maintenance_redirect_pluginPage_section', 
        '', 
        'mm_maintenance_redirect_settings_section_callback', 
        'pluginPage'
    );

    add_settings_field( 
        'redirect_checkbox', 
        __( 'Enable redirect', 'mm-maintenance-redirect' ), 
        'mm_maintenance_redirect_checkbox_enabled_render', 
        'pluginPage', 
        'mm_maintenance_redirect_pluginPage_section' 
    );

    add_settings_field( 
        'select_page', 
        __( 'Redirect to:', 'mm-maintenance-redirect' ), 
        'mm_maintenance_redirect_select_page_render', 
        'pluginPage', 
        'mm_maintenance_redirect_pluginPage_section' 
    );


}


function mm_maintenance_redirect_checkbox_enabled_render(  ) { 

    $options = get_option( 'mm_maintenance_redirect_settings' );
    $checked = ( isset ( $options['redirect_checkbox'] ) ) ? 'checked' : '';
    ?>
    <input type='checkbox' name='mm_maintenance_redirect_settings[redirect_checkbox]' <?php echo $checked; ?> value='1'>
    <?php

}


function mm_maintenance_redirect_select_page_render(  ) { 

    $options = get_option( 'mm_maintenance_redirect_settings' );
    ?>
    <select name='mm_maintenance_redirect_settings[select_page]'>
       <?php mm_maintenance_redirect_render_options( $options ); ?>   
    </select>

<?php

}


function mm_maintenance_redirect_settings_section_callback(  ) { 

    echo __( 'Select the page you want to redirect your visitors to and click the checkbox.', 'mm-maintenance-redirect' );

}


function mm_maintenance_redirect_options_page(  ) { 

    ?>
    <form action='options.php' method='post'>
        
        <h2>Maintenance Redirect</h2>
        
        <?php
        settings_fields( 'pluginPage' );
        do_settings_sections( 'pluginPage' );
        submit_button();
        ?>
        
    </form>
    <?php

}

function mm_maintenance_redirect_render_options( $options ) {
    $pages = get_pages();
    foreach ($pages as $page ) { ?>
            <option value='<?php echo $page->ID ?>' <?php selected( $options['select_page'], $page->ID ); ?>><?php echo $page->post_title ?></option>
            <?php
        }
    }

/* * * * * * * * * * * * * *
 * The actual redirect
 * * * * * * * * * * * * * *
 */

add_action( 'template_redirect', 'mm_maintance_mode_redirect' );

function mm_maintance_mode_redirect( ) {

    $options = get_option( 'mm_maintenance_redirect_settings' );

    if ( ! is_page( $options['select_page'] ) && isset ( $options['redirect_checkbox'] ) && ! ( current_user_can( 'manage_options' ) ) ) {
        wp_redirect( get_permalink( $options['select_page'] ) );
        exit;
    }
}
?>