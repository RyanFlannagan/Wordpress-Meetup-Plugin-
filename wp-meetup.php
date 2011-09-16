<?php
/*
Plugin Name: WP Meetup
Plugin URI: http://nuancedmedia.com/
Description: Pulls events from Meetup.com onto your blog
Version: 1.0
Author: Nuanced Media
Author URI: http://nuancedmedia.com/
*/
?>

<?php
$meetup = new WP_Meetup();

class WP_Meetup {
    
    private $dir;
    private $api_key;
    private $admin_page_url;
    private $mu_api;
    
    function WP_Meetup() {
        
        $this->dir = WP_PLUGIN_DIR . "/wp-meetup/";
        $this->api_key = get_option('wp_meetup_api_key') ? get_option('wp_meetup_api_key') : FALSE;
        $this->admin_page_url = admin_url("options-general.php?page=wp_meetup");
        
        add_action('admin_menu', array($this, 'admin_menu'));
        
    }
    
    function admin_menu() {
        add_options_page('WP Meetup Options', 'WP Meetup', 'manage_options', 'wp_meetup', array($this, 'admin_options'));
    }
    
    function admin_options() {
        if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
        
        if (!empty($_POST)){
            if (array_key_exists('api_key', $_POST)) {
                update_option('wp_meetup_api_key', $_POST['api_key']);
            }
            $this->api_key = get_option('wp_meetup_api_key') ? get_option('wp_meetup_api_key') : FALSE;
        }
        
        $data = array();
        $data['has_api_key'] = !empty($this->api_key);
        
        echo $this->get_include_contents($this->dir . "options-page.php", $data);
        
    }
    
    function get_include_contents($filename, $vars = array()) {
        if (is_file($filename)) {
            ob_start();
            foreach ($vars as $name => $value) {
                $$name = $value;
            }
            include $filename;
            return ob_get_clean();
        }
        return false;
    }
    
}

?>