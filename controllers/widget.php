<?php
class WP_Meetup_Calendar_Widget extends WP_Widget {
    
    private $core;
    
    function __construct() {
        parent::WP_Widget( 'wp_meetup_calendar_widget', $name = 'WP Meetup Calendar Widget', array('description' => 'Displays Meetup.com events in the current month'));
        //$this->events = new WP_Meetup_Events;
        $this->core = new WP_Meetup_Events_Controller();
    }
    
    function form($instance) {
        $title = $instance ? esc_attr($instance['title']) : __('Meetup Events', 'wp-meetup');
        echo '<label for="' . $this->get_field_id('title') . '">' . _e('Title:') . '</label>'; 
	echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" />';
    }
    
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }
    
    function widget($args, $instance) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        
        echo $this->core->render('widget_view.php', array('events' => $this->core->events->get_all()));
        
        echo $after_widget;
    }
    
}