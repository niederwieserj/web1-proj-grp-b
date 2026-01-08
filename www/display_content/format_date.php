<?php
function format_date($date_str): string {
    return date_format(date_create_from_format("Y-m-d H:i:s", $date_str), "M j, Y");
}
?>