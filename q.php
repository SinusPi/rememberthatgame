<?php
require("q.class.php");

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

if (!isset($_SESSION['matched'])) {
	// load matching questions
	$fs = Q::glob_all_datafiles("data/");

	// read ALL QUESTIONS into $QS
	$questions_all = [];
	foreach ($fs as $f) {
		$q = Q::read_q($f)->getValues();
		if (!$q)
			header("X-rtg-q-error: ".$f,false);
		else
			$questions_all[]=$q;
	}

	// throw away mismatched
	$questions_matched = array_filter($questions_all, function ($q) {
		return ($q
			&& (empty($_SESSION['prefs']) || count(array_intersect($q['pf'], $_SESSION['prefs'])) > 0) // at least one pf_y is present in pf
			//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
		);
	});

	$_SESSION['total'] = count($questions_all);
	$_SESSION['matched']=array_map(function($q) { return $q['num']; },$questions_matched);
}

// throw away seen
$unseen = array_filter($_SESSION['matched'], function ($qn) {
	return (!in_array($qn, $_SESSION['seen']) // num wasn't seen
	);
});

// pick a specific question
if ($_REQUEST['q']) {
	$qnum = $_REQUEST['q'];
} else {
	// pick from unseen
	if (count($unseen)) {
		$qnum = $unseen[array_rand($unseen)];
	} else {
		$qnum=0;
	}
}
try {
	$Q = Q::load_question_num($qnum); // =================================================
} catch (Exception $err) {

}



//$Q['n']=$num;
//$Q['f']=$f;

$RET['total'] = $_SESSION['total'];
$RET['match'] = count($_SESSION['matched']);
$RET['unseen'] = count($unseen);
$RET['seen'] = count($_SESSION['seen']);
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
