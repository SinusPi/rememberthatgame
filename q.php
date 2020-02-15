<?php
ini_set("DISPLAY_ERRORS", 1);
//include("config.inc.php");
//$db = mysqli_connect(null,$CFG['db_user'],$CFG['db_pass'],$CFG['db_name']);

session_start();
if ($_REQUEST['reset']) unset($_SESSION['seen']);
elseif ($_REQUEST['seen']) $_SESSION['seen'][] = intval($_REQUEST['seen']);
session_write_close();

$dir = "data/";
$fs = glob($dir . "*.txt");

foreach ($fs as $f)
	$QS[] = readit($f);

$pf=(array)$_REQUEST['pf'];


if ($_REQUEST['q']) {
	$num = max(min(intval($_REQUEST['q']) - 1, count($fs) - 1), 0);
	$Q = $QS[$num];
}


$total = count($QS);
// throw away mismatched
$QS = array_filter($QS, function ($q) use ($pf) {
	return ($q
		&& (empty($pf) || count(array_intersect($q['pf'], $pf)) > 0) // at least one pf_y is present in pf
		//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
	);
});
$match = count($QS);
// throw away seen
$QS = array_filter($QS, function ($q) use ($pf) {
	return ($q
		&& !in_array($q['num'], $_SESSION['seen']) // num wasn't seen
		//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
	);
});
$unseen = count($QS);


if (!$_REQUEST['q']) {
	$QS = array_values($QS); //compact
	$num = rand(0, count($QS) - 1);
	$Q = $QS[$num];
}


$Q['n']=$num;
$Q['f']=$f;
$Q['total'] = $total;
$Q['match'] = $match;
$Q['unseen'] = $unseen;

header("Content-type: application/json");
die(json_encode($Q));

function readit($f) {
	$meta = file($f);
	$mp3 = str_replace(".txt", ".mp3", $f);
	if (!file_exists($mp3)) return;
	$q = [];
	preg_match("/^(\\d+) \\- (.*)/", basename($f), $ms);
	$q['num'] = intval($ms[1]);
	$q['mp3'] = $mp3;
	foreach ($meta as $m) {
		$m = trim($m);
		$c = substr($m, 0, 1);
		$r = substr($m, 1);
		if (false) { }
		elseif ($c == "=") { }//$q['scores'][1]['answer'] = $r;
		elseif (preg_match("/^([a-z]+)=(.*)/", $m, $ms)) $q[$ms[1]] = $ms[2];
		elseif (preg_match("/^(\\-+)(.*)/", $m, $ms)) {
			//$score=1/(strlen($ms[1])+1);
			//$q['scores'][]=['score'=>$score,'re'=>$ms[2]];
		} elseif (preg_match("/^\\+([a-z]+|\".*?\")=(.*)=(.*)/", $m, $ms)) {
			$score = count($q['scores']) + 1;
			$tag = $ms[1];
			$re = $ms[2];
			$answer = $ms[3];
			$q['scores'][$score] = ['tag' => $tag, 're' => $re, 'answer' => $answer];
		} else {
			// legacy
			$re = $m;
			unset($answer);
			$q['scores'][1]['tag']="name";
			$q['scores'][1]['re']=$re;
		}
	}
	$q['pf'] = explode(",", $q['pf']);
	return $q;
}
