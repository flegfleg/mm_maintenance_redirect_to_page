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

	add_settings_field( 
        'exclude_pages', 
        __( 'Exclude Pages:', 'mm-maintenance-redirect' ), 
        'mm_maintenance_redirect_exclude_pages_render', 
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

function mm_maintenance_redirect_exclude_pages_render(  ) { 

    $options = get_option( 'mm_maintenance_redirect_settings' );
	$excluded_pages = maybe_unserialize( (isset ( $options['excluded_pages'] )) ? $options['excluded_pages'] : array() );
	
	$args = array(
		'post_type'              => array( 'page' ),
		'post_status'            => array( 'Publish' ),
		'nopaging'               => true,
		'posts_per_page'         => '-1',
	);

	$query = new WP_Query( $args );
	
	_e('If you want some pages to be visible without being redirected to maintenance page, check them here.','mm-maintenance-redirect');
	
	if ( $query->have_posts() ) {
		?> <ul> <?php
		while ( $query->have_posts() ) {
			$query->the_post();
			
			$postID = get_the_ID(); // da modificare per compatibilità con WPML usando icl_object_id(ID, type, return_original_if_missing,language_code)
			$checked = ( in_array($postID, $excluded_pages) ) ? 'checked' : '';
			
			?>
			<li>
				<label for ='page_<?php echo $postID; ?>'>
					<input type='checkbox' id='page_<?php echo $postID; ?>' name='mm_maintenance_redirect_settings[excluded_pages][]' <?php echo $checked; ?> value='<?php echo $postID; ?>'> 
					<?php echo get_the_title(); ?>  <small><?php edit_post_link(); ?> - <a href='<?php the_permalink(); ?>'><?php _e('View') ?></a></small>
				</label>
			</li>
			
			<?php
			
			
		}
		?> </ul> <?php
	}

	// Restore original Post Data
	wp_reset_postdata();

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
	$excluded_pages = maybe_unserialize( (isset ( $options['excluded_pages'] )) ? $options['excluded_pages'] : array() );
	array_push($excluded_pages,$options['select_page']);
	
    if ( ! is_page( $excluded_pages ) && isset ( $options['redirect_checkbox'] ) && ! ( current_user_can( 'manage_options' ) ) ) {
		
		echo 'redirect';
		
        exit;
    }
}
?>