<?php
class WP_Meetup_Groups extends WP_Meetup_Model {

    private $wpdb;
    
    function __construct() {
        parent::__construct();
        global $wpdb;
        $this->wpdb = &$wpdb;
        $this->table_name = $this->table_prefix . "groups";
        $this->create_table();
    }
    
    function create_table() {
        $sql = "CREATE TABLE {$this->table_name} (
  id tinytext NOT NULL,
  name text NOT NULL,
  url_name tinytext NOT NULL,
  link varchar(255) NOT NULL,
  color varchar(7) NOT NULL DEFAULT '#E51937',
  PRIMARY KEY  (`id`(16))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
          
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    function drop_table() {
        $sql = "DROP TABLE `{$this->table_name}`";
        
        $this->wpdb->query($sql);
    }
    
    function get($group_id) {
        $result = $this->wpdb->get_row("SELECT * FROM `{$this->table_name}` WHERE `id` = {$group_id}");
        return $result;
    }
    
    function get_all() {
        $results = $this->wpdb->get_results("SELECT * FROM `{$this->table_name}`", "OBJECT");
        //$this->pr($results);
        /*foreach ($results as $key=>$result) {
            if(empty($result->color))
                $results[$key]->color = '#E51937';
        }*/
        return $results;
    }
    
    function get_url_names() {
        return $this->wpdb->get_col("SELECT `url_name` FROM `{$this->table_name}`");
    }
    
    function count() {
        return $this->wpdb->get_var($this->wpdb->prepare("SELECT COUNT(*) FROM `{$this->table_name}`"));
    }
    
    function save($group) {
        
        $data = (array) $group;
        //$this->pr($data, $this->table_name);
        
        //$this->wpdb->show_errors();
        if ($row = $this->get($group['id'])) {
            unset($data['id']);
            $this->wpdb->update($this->table_name, $data, array('id' => $group['id']));
        } else {
            //$this->pr('wtf');
            $this->wpdb->insert($this->table_name, $data);
            //$this->pr($this->wpdb->insert_id);
        }
        //$this->wpdb->print_error();
    }
    
    function remove($group_id) {
        $this->import_model('events');
        $this->events->remove_by_group_id($group_id);
        $this->wpdb->query($this->wpdb->prepare("DELETE FROM {$this->table_name} WHERE `id` = %d", array($group_id)));
    }

}