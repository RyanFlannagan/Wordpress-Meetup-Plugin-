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
  `name` text NOT NULL,
  `group_urlname` tinytext NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`(16))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
          
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    function drop_table() {
        $sql = "DROP TABLE `{$this->table_name}`";
        
        $this->wpdb->query($sql);
    }
    
    function get_groups() {
        $results = $this->wpdb->get_results("SELECT * FROM `{$this->table_name}`", "OBJECT");
        return $results;
    }
    
    function get_group_url_names() {
        return $this->wpdb->get_col("SELECT 'group_urlname' FROM `{$this->table_name}`");
    }
    
    function save($group) {
        
        $data = (array) $event;
        
        if ($row = $this->get($group->id)) {
            unset($data['id']);
            $this->wpdb->update($this->table_name, $data, array('id' => $group->id));
        } else {
            $this->wpdb->insert($this->table_name, $data);
        }
        
    }

}