<?php
class WP_Meetup_Groups extends WP_Meetup_Model {

    private $wpdb;
    
    function __construct() {
        parent::__construct();
        global $wpdb;
        $this->wpdb = &$wpdb;
        $this->table_name = $this->table_prefix . "groups";
    }
    
    function create_table() {
        $sql = "CREATE TABLE `{$this->table_name}` (
  `id` tinytext NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `name` text NOT NULL,
  `description` longtext NOT NULL,
  `visibility` tinytext NOT NULL,
  `status` tinytext NOT NULL,
  `time` int(11) NOT NULL,
  `utc_offset` int(10) NOT NULL,
  `event_url` varchar(255) NOT NULL,
  `venue` longtext,
  `rsvp_limit` int(11) DEFAULT NULL,
  `yes_rsvp_count` int(11) NOT NULL,
  `maybe_rsvp_count` int(11) NOT NULL,
  PRIMARY KEY (`id`(16))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
          
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    function drop_table() {
        $sql = "DROP TABLE `{$this->table_name}`";
        
        $this->wpdb->query($sql);
    }

}