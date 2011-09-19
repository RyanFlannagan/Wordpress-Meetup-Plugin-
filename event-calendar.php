<?php
//pr($events);

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

function element($tag_name, $content = '', $attributes = NULL) {
    if ($attributes) {
        $html_string = "<$tag_name";
        foreach ($attributes as $key => $value) {
            if ($value != '')
                $html_string .= " {$key}=\"{$value}\"";
        }
        $html_string .= ">";
    } else {
        $html_string = "<$tag_name>";
    }
    $html_string .= $content;
    $html_string .= "</$tag_name>";
    return $html_string;
}

$number_of_months = 2;
$today = mktime(0, 0, 0, date('n'), date('j'), date('Y'));

$div_contents = "";
for ($m = 0; $m < $number_of_months; $m++) {
    $current_month = date('n') + $m;
    $first_of_the_month = mktime(0, 0, 0, $current_month, 1, date('Y'));
    $date_start = mktime(0, 0, 0, $current_month, 1-date('w', $first_of_the_month), date('Y'));
    $end_date = mktime(0, 0, 0, $current_month+1, -1, date('Y'));
    
    $theadrow_contents = '';
    foreach (array('Su', 'M', 'T', 'W', 'Th', 'F', 'Sa') as $day) {
        $theadrow_contents .= element('th', $day);
    }
    $thead_contents = element('tr', $theadrow_contents);
    
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
            
            $td_contents = date('j', $current_date);
            $date_key = date('Y-m-d', $current_date);
            if (array_key_exists($date_key, $events_by_date)) {
                $ul_contents = "";
                foreach ($events_by_date[$date_key] as $event) {
                    $ul_contents .= element('li',
                        element('a',
                            element('span', date("g:i A", $event->time + $event->utc_offset/1000)) . $event->name,
                            array('href' => get_permalink($event->post->ID))
                        )
                    );
                }
                $td_contents .= element('ul', $ul_contents);
            }
            
            if (date('n', $current_date) == $current_month) {
                $tr_contents .= element('td', $td_contents, array('class' => implode(' ', $td_classes)));
            } else {
                $tr_contents .= element('td', "", array('class' => 'out-of-range'));
            }
            
        }
        
        $tbody_contents .= element('tr', $tr_contents);
        $i++;
    }
    
    $div_contents .= element('h2', date('F Y', $first_of_the_month));
    $table_class = $current_month - date('n') == 0 ? "current-month" : "next-month";
    $div_contents .= element('table', element('thead', $thead_contents) . element('tbody', $tbody_contents), array('class' => $table_class,'cellpadding' => 0, 'cellspacing' => 0));
     
}

echo element('div', $div_contents, array('id' => 'wp-meetup-calendar'));


?>