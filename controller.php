<?php
class WP_Meetup_Controller extends WP_Meetup {
    
    function __construct() {
        parent::__construct();
        foreach ($this->uses as $model_name) {
            $this->import_model($model_name);
        }
    }
    
}