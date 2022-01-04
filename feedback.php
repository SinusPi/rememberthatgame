<?php
$q=intval($_REQUEST['q']);
$fb=preg_replace("/[^\w]/","",$_REQUEST['fb']);
if ($q)
	mail("sinus@sinpi.net","R-T-G feedback","$q = $fb");
header("Content-type: application/json");
die(json_encode(['ok'=>true]));
