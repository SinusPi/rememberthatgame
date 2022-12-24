<?php
require("q.class.php");
Q::$ONLY_TYPE="mp3";

ini_set("DISPLAY_ERRORS", 1);
//include("config.inc.php");
//$db = mysqli_connect(null,$CFG['db_user'],$CFG['db_pass'],$CFG['db_name']);

session_id("rtg"); session_start();

settype($_SESSION['seen'],"array");
settype($_SESSION['guessed'],"array");

settype($_REQUEST['seen'],"int");
settype($_REQUEST['guessed'],"int");
settype($_REQUEST['q'],"int");

if (isset($_REQUEST['reset_seen'])) unset($_SESSION['seen']);
elseif ($_REQUEST['seen']) mark_seen($_REQUEST['seen']);

if ($_REQUEST['reset_score']) unset($_SESSION['guessed']);
elseif ($_REQUEST['guessed'] && !in_array($_REQUEST['guessed'],$_SESSION['guessed'])) $_SESSION['guessed'][] = $_REQUEST['guessed'];

if (isset($_REQUEST['pf'])) {
	if ($_REQUEST['pf'][0]=="all") $_REQUEST['pf']=[];
	$_SESSION['prefs']=(array)$_REQUEST['pf'];
	unset($_SESSION['matched']);
}

$do_shuffle = $_REQUEST['shuffle'];

if (!isset($_SESSION['matched'])) {
	// load matching questions
	$fs = Q::glob_all_datafiles("data/");

	// read ALL QUESTIONS into $QS
	$questions = [];
	foreach ($fs as $f) {
		try {
			$q_obj = Q::read_q($f);
			if ($q_obj) $questions[]=$q_obj->getValues();
		} catch (Exception $e) {
			header("X-rtg-q-error: ".$f." ".$e->getMessage(),false);
		}
	}

	$_SESSION['total'] = count($questions);

	$settings = @json_decode(file_get_contents("data/settings.json"),true);
	if ($settings) {
		foreach ($settings['sets'] as &$set) {
			if ($set['type']=="max-in-set") {
				$set['_chosen']=array_values(array_intersect_key($set['set'],array_flip(array_rand($set['set'],2))));
				shuffle($set['_chosen']);
			}
		}
		unset($set);
	}

	// throw away mismatched
	$questions = array_filter($questions, function ($q) {
		return ($q
			&& (empty($_SESSION['prefs']) || count(array_intersect($q['pf'], $_SESSION['prefs'])) > 0) // at least one pf_y is present in pf
			//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
		);
	});

	// apply special rules
	if ($settings && isset($settings['sets'])) {
		$questions = array_filter($questions, function ($q) use ($settings) {
			foreach ($settings['sets'] as &$set) {
				if ($set['type']=="max-in-set" && in_array($q['num'],$set['set']))
					return in_array($q['num'],$set['_chosen']);
			}
			return true;
		});
	}

	$_SESSION['matched']=array_column($questions,'num');
	$do_shuffle=true;
}

if ($do_shuffle)
	shuffle($_SESSION['matched']);

// throw away seen
$unseen = array_values(array_diff($_SESSION['matched'],$_SESSION['seen']));


if ($_REQUEST['q']) {
	// pick a specific question, seen or not
	$qnum = $_REQUEST['q'];
} else {
	// pick from unseen
	$qnum = $unseen[0];
}

try {
	$Q = Q::load_question_num($qnum); // =================================================
} catch (Exception $err) {
	die(json_encode(['err'=>$err->getMessage()]));
}



//$Q['n']=$num;
//$Q['f']=$f;

$RET['total'] = $_SESSION['total'];
$RET['match'] = count($_SESSION['matched']);
$RET['match_arr'] = $_SESSION['matched']; // debug
$RET['unseen'] = count($unseen);
$RET['seen'] = count($_SESSION['seen']);
$RET['seen_arr'] = $_SESSION['seen'];
$RET['totalscore']=count($_SESSION['guessed']);
$RET['guessed_arr']=$_SESSION['guessed'];
$RET['score_arr']=array_intersect($_SESSION['guessed'],$_SESSION['matched']);
$RET['score']=count($RET['score_arr']);
//$RET['set_arr']=$_SESSION['matched'];
$RET['prefs']=(array)$_SESSION['prefs'];
$RET['err'] = $err ? $err->getMessage() : null;
$RET['q']=$Q ? $Q->getValues() : null;


header("Content-type: application/json");
die(json_encode($RET));



function mark_seen($qnum) {
	if (!in_array($qnum,$_SESSION['seen'])) $_SESSION['seen'][]=$qnum;
}
