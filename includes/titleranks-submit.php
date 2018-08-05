<?php
# investigate if we can put multiple input boxes and loop through them to insert into the database (see includes/titleranks-add.php)
# https://stackoverflow.com/questions/34469482/how-to-insert-multiple-inputs-into-the-database-using-the-power-of-php

$stmtstins = $con->prepare("INSERT INTO gwsubtitles (titlenameid, stname, stpoints, strank) VALUES (?, ?, ?, ?)");
$stmtstins->bind_param("isii", $_POST['titlenameid'], $_POST['titlerankname'], $_POST['titlepoints'], $_POST['titlerank']);
$stmtstins->execute();
$stmtstins->close();
$_SESSION['title'] = "repeat";
$_SESSION['tid'] = $_POST['titlenameid'];
echo 'Title rank added, redirecting!';
header ("Refresh:1; url=titlemanager.php");
?>