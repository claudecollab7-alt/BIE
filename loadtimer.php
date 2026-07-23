<?php
ob_start();
session_start();

$now = time();
$timeSince = $now - $_SESSION['timer'];
$remainingSeconds = abs(1440 - $timeSince);
$mins = round($remainingSeconds /60);       

echo "<span id='session_timer' class='text-right'>There are $mins Minutes remaining.</span>";
?>