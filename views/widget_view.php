<?php

//print_r($events);
$events_by_date = array();
foreach ($events as $event) {
    $event_time = $event->time + $event->utc_offset/1000;
    $event_date = mktime(0, 0, 0, date('n', $event_time), date('j', $event_time), date('Y', $event_time));
    $date_key = date('Y-m-d', $event_date);
    
    if (!array_key_exists($date_key, $events_by_date))
        $events_by_date[$date_key] = array();
    
    $events_by_date[$date_key][] = $event;
}

unset($events);

//pr($events_by_date);

if (count($events_by_date) > 0) {

    $today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
    
    $div_contents = "";

    $current_month = date('n');
    $first_of_the_month = mktime(0, 0, 0, $current_month, 1, date('Y'));
    $date_start = mktime(0, 0, 0, $current_month, 1-date('w', $first_of_the_month), date('Y'));
    $end_date = mktime(0, 0, 0, $current_month+1, -1, date('Y'));
    
    $theadrow_contents = '';
    foreach (array('S', 'M', 'T', 'W', 'T', 'F', 'S') as $day) {
        $theadrow_contents .= $this->element('th', $day);
    }
    $thead_contents = $this->element('tr', $theadrow_contents);
    
    $tbody_contents = '';
    $current_date = $date_start;
    $i = 0;
    while ($current_date < $end_date) {
    
        $tr_contents = '';
        
        for ($j = 0; $j < 7; $j++) {
            $current_date = strtotime('+' . (($i*7)+$j) . 'days', $date_start);
            
            $td_classes = array();
            if (date('w', $current_date) == 0 || date('w', $current_date) == 6) {
                $td_classes[] = 'weekend';
            }
            
            if ($current_date == $today)
                $td_classes[] = 'today';
            
            $td_contents = "";
            $date_key = date('Y-m-d', $current_date);
            if (array_key_exists($date_key, $events_by_date)) {
                /*$ul_contents = "";
                foreach ($events_by_date[$date_key] as $event) {
                    $ul_contents .= $this->element('li',
                        $this->element('a',
                            $this->element('span', date("g:i A", $event->time + $event->utc_offset/1000)) . $event->name,
                            array('href' => get_permalink($event->post->ID))
                        ),
                        array('class' => $event->status)
                    );
                }
                $td_contents .= $this->element('ul', $ul_contents);*/
                $event = $events_by_date[$date_key][0];
                $td_contents = $this->element('a', date('j', $current_date), array(
                    'href' => get_permalink($event->post->ID),
                    'title' => $event->name
                ));
                
            } else {
                $td_contents = date('j', $current_date);
            }
            
            if (date('n', $current_date) == $current_month) {
                $tr_contents .= $this->element('td', $td_contents, array('class' => implode(' ', $td_classes)));
            } else {
                $tr_contents .= $this->element('td', "", array('class' => 'out-of-range'));
            }
            
        }
        
        $tbody_contents .= $this->element('tr', $tr_contents);
        $i++;
    }
    
    $table_caption = $this->element('caption', date('F Y', $first_of_the_month));
    $div_contents .= $this->element('table', $table_caption . $this->element('thead', $thead_contents) . $this->element('tbody', $tbody_contents), array('cellpadding' => 0, 'cellspacing' => 0));
    
    echo $this->element('div', $div_contents, array('id' => 'wp-meetup-widget-calendar'));
} else {
    echo $this->element('p', "No events listed.");
}

?>