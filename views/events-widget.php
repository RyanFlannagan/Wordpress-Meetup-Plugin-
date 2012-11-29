<?php
if (count($events) > 0) {
    $ul_contents = '';
    foreach ($events as $event) {
        $event_adjusted_time = $event->time + $event->utc_offset;
        $date_display = $this->element("div", "<span class=\"month\">".date('M', $event_adjusted_time)."</span> <span class=\"date\">".date('d', $event_adjusted_time)."</span>", array('style' => "background-color: {$event->group->color}"));
        $event_post_link = $this->element('a', $event->name, array('href' => get_permalink($event->post->ID)));
        $ul_contents .= $this->element('li', $date_display . $event_post_link . (($this->groups->count() > 1)?"<br />" . $event->group->name:""), array('id' => 'wp-meetup-event-'.$event->id));
    }
    echo $this->element('ul', $ul_contents, array('id' => 'wp-meetup-upcoming-events-list'));
} else {
    echo $this->element('p', __('No upcoming events.'));
}
if ($this->options->get('show_nm_link')) {
	echo "<p align=\"right\"><a href=\"href=\"http://nuancedmedia.com/\" title=\"Powered by Nuanced Media\"><img src=\"" . $this->plugin_url . "images/NM_logo_mini.png\"></a></p>";
}
?>