<?php
ini_set("DISPLAY_ERRORS", 1);
//include("config.inc.php");
//$db = mysqli_connect(null,$CFG['db_user'],$CFG['db_pass'],$CFG['db_name']);

session_start();
if ($_REQUEST['reset_seen']) unset($_SESSION['seen']);
elseif ($_REQUEST['seen']) {
	$_SESSION['seen'][] = intval($_REQUEST['seen']);
	$_SESSION['seen']=array_unique($_SESSION['seen']);
}

if ($_REQUEST['do']=="q" || $_REQUEST['do']=="pf")
	$_SESSION['prefs']=(array)$_REQUEST['pf'];

if ($_REQUEST['reset_score']) unset($_SESSION['guessed']);
elseif ($_REQUEST['guessed']) {
	$_SESSION['guessed'][]=intval($_REQUEST['guessed']);
	$_SESSION['guessed']=array_unique($_SESSION['guessed']);
}

session_write_close();


$dir = "data/";
$fs = glob($dir . "????? - *.txt");

foreach ($fs as $f) {
	$q = readit($f);
	if (!$q) header("X-rtg-q-error: ".$f,false);
	$QS[$q['num']]=$q;
}

if ($_REQUEST['q']) {
	$Q = $QS[intval($_REQUEST['q'])];
	if (!$Q) unset($_REQUEST['q']);
}

$total = count($QS);
// throw away mismatched
$QS = array_filter($QS, function ($q) {
	return ($q
		&& (empty($_SESSION['prefs']) || count(array_intersect($q['pf'], $_SESSION['prefs'])) > 0) // at least one pf_y is present in pf
		//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
	);
});
$match = count($QS);
$matchnums = [];
array_walk($QS,function($q) use (&$matchnums) { $matchnums[]=$q['num']; });

// throw away seen
$QS = array_filter($QS, function ($q) {
	return ($q
		&& !in_array($q['num'], (array)$_SESSION['seen']) // num wasn't seen
		//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
	);
});
$unseen = count($QS);


if ($_REQUEST['do']=="q" && !$_REQUEST['q']) {
	$QS = array_values($QS); //compact
	$num = rand(0, count($QS) - 1);
	$Q = $QS[$num];
}


if ($Q) $unseen--;


//$Q['n']=$num;
//$Q['f']=$f;

$RET['total'] = intval($total);
$RET['match'] = intval($match);
$RET['unseen'] = intval($unseen);
$RET['seen'] = count((array)$_SESSION['seen']);
$RET['totalscore']=count((array)$_SESSION['guessed']);
$RET['score']=count(array_intersect((array)$_SESSION['guessed'],$matchnums));
$RET['guessed_arr']=(array)$_SESSION['guessed'];
$RET['score_arr']=array_intersect((array)$_SESSION['guessed'],$matchnums);
$RET['set_arr']=$matchnums;
$RET['prefs']=(array)$_SESSION['prefs'];
$RET['q']=$Q;



header("Content-type: application/json");
die(json_encode($RET));



function read_json($json) {
	return $json; // no postprocessing for now
}

function save_json($q,$f) {
	// remove default fields
	unset($q['num'],$q['file'],$q['type']);
	$json = json_encode($q,JSON_PRETTY_PRINT);
	if ($json) file_put_contents($f,$json);
}

function readit($f) {
	$found=false;
	foreach (["mp3","png","gif"] as $ext) {
		$file = str_replace(".txt", ".".$ext, $f);
		if (file_exists($file)) { $found=true; break; }
	}
	if (!$found) return false;

	$q = [];
	preg_match("/^(\\d+) \\- (.*)/", basename($f), $ms);
	$q['num'] = intval($ms[1]);
	$q['file'] = $file;
	$q['type'] = $ext;

	// if it's JSON, use it and bail.
	$file = file_get_contents($f);
	$json = @json_decode($file,true);
	if ($json) {
		return array_merge($q,read_json($json));
	}

	$meta = file($f);
	foreach ($meta as $m) {
		$m = trim($m);
		$c = substr($m, 0, 1);
		$r = substr($m, 1);
		if (false) { }
		elseif ($c == "=") { }//$q['scores'][1]['answer'] = $r;
		elseif (preg_match("/^([a-z]+)=(.*)/", $m, $ms)) {
			$key=$ms[1]; $val=$ms[2];
			if (!isset($q[$key]))
				$q[$key] = $val;
			else
				$q[$key] = array_merge((array)$q[$key],(array)$val);
		}
		elseif (preg_match("/^(\\-+)(.*)/", $m, $ms)) {
			//$score=1/(strlen($ms[1])+1);
			//$q['scores'][]=['score'=>$score,'re'=>$ms[2]];
		} elseif /* + */ (preg_match("/^\\+([a-z]+|\".*?\")=(.*)=(.*)/", $m, $ms)) { // text name 
			$name = $ms[1];
			$re = $ms[2];
			$answer = $ms[3];
			settype($q['scores'],"array");
			$q['scores'][] = ['name' => $name, 're' => $re, 'answer' => $answer];
		} elseif /* * */ (preg_match("/^\\*([a-z]+|\".*?\")=(.*)\\s*\\|\\s*(.*)/", $m, $ms)) { // multiple choice
			$name = $ms[1];
			$answer = trim($ms[2]);
			$wrongs = preg_split("/\\s*,\\s*/",$ms[3]);
			settype($q['multiple'],"array");
			$q['multiple'][] = ['name' => $name, 'answer' => $answer, 'wrongs' => $wrongs];
		} else {
			// legacy
			$re = $m;
			unset($answer);
			$q['scores'][1]['tag']="name";
			$q['scores'][1]['re']=$re;
		}
	}
	$q['pf'] = explode(",", $q['pf']);

	if (isset($q['trivia'])) settype($q['trivia'],"array");

	// save converted!
	save_json($q,$f);

	return $q;
}
