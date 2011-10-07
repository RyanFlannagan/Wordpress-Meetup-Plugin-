<?php
$heading_cells = "";

foreach ($headings as $heading)
    $heading_cells .= "<th>{$heading}</th>";
    
$table_contents = "
<thead>
    <tr>
        {$heading_cells}
    </tr>
</thead>
<tfoot>
    <tr>
        {$heading_cells}
    </tr>
</tfoot>
<tbody>";

foreach($rows as $row) {
    $row_contents = "";
    foreach ($row as $cell) {
        $row_contents .= $this->element('td', $cell);
    }
    $table_contents .= $this->element('tr', $row_contents);
}

$table_contents .= "</tbody>";

echo $this->element('table', $table_contents, array_merge(array('class' => 'widefat'), $table_attributes));
?>