<?php
$heading_cells = "";
foreach ($headings as $heading)
    $heading_cells += "<th>{$heading}</th>";
?>
<table class="widefat">
<thead>
    <tr>
        <?php echo $heading_cells; ?>
    </tr>
</thead>
<tfoot>
    <tr>
        <?php echo $heading_cells; ?>
    </tr>
</tfoot>
<tbody>

<?php foreach($rows as $row): ?>
<tr>
    <?php
    foreach ($row as $cell) {
        echo $this->element('td', $cell);
    }
    ?>
</tr>
<?php endforeach; ?>
   
</tbody>
</table>