<?php
ini_set("DISPLAY_ERRORS",1);
//include("config.inc.php");
//$db = mysqli_connect(null,$CFG['db_user'],$CFG['db_pass'],$CFG['db_name']);

$dir="data/";
$fs = glob($dir."*.txt");
do {
	$num=$_REQUEST['q'] ? intval($_REQUEST['q'])-1 : rand(0,count($fs)-1);
	if ($num>=count($fs)) die("{}");
	$f=$fs[$num];
	$meta=file($f);
	$mp3=str_replace(".txt",".mp3",$f);
	if (!file_exists($mp3)) continue;
	$q = [];
	preg_match("/^(\\d+) \\- (.*)/",basename($f),$ms);
	$q['num']=intval($ms[1]);
	$q['total']=count($fs);
	$q['mp3']=$mp3;
	foreach ($meta as $m) {
		$m=trim($m);
		$c=substr($m,0,1);
		$r=substr($m,1);
		if ($c=="=") $q['answer']=$r;
		elseif (preg_match("/%([a-z]+)=(.*)/",$m,$ms)) $q[$ms[1]]=$ms[2];
		elseif (preg_match("/^(\\-+)(.*)/",$m,$ms)) {
			$score=1/(strlen($ms[1])+1);
			$q['scores'][]=['score'=>$score,'re'=>$ms[2]];
		} elseif (preg_match("/^(\\++)(.*)/",$m,$ms)) {
			$score=strlen($ms[1])+1;
			$q['scores'][]=['score'=>$score,'re'=>$ms[2]];
			$q['maxscore']=max($q['maxscore'],$score);
		} else
			$q['scores'][]=['score'=>1,'re'=>$m];
	}
	header("Content-type: application/json");
	die(json_encode($q));
} while(true);
?>