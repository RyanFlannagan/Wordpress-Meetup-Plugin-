<?php
class WP_Meetup_Model extends WP_Meetup {
    
    public $table_prefix;
    public $table_name;
    
    function __construct() {
        parent::__construct();
        global $wpdb;
	$this->table_prefix = $wpdb->prefix . "wpmeetup_";
    }
    
}