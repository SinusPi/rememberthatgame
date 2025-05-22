<?php
require("q.class.php");
Q::$ONLY_TYPE="mp3";

ini_set("DISPLAY_ERRORS", 1);
header("Content-type: application/json");

//include("config.inc.php");
//$db = mysqli_connect(null,$CFG['db_user'],$CFG['db_pass'],$CFG['db_name']);

error_reporting(E_ALL^E_NOTICE);
set_error_handler("json_error");

session_name("ygsf");
session_start();

if ($_REQUEST['reset']??"") $_SESSION=[];

$SEEN = explode(",",$_REQUEST['seen']??"");
$GUESSED = explode(",",$_REQUEST['guessed']??"");

$do_shuffle = ($_REQUEST['shuffle']??0)==1;

$SETS = [
	['slug'=>"all",'label'=>"all",'description'=>"all",'cond'=>function($q) { return true; }]  //default
];
require("config.inc.php");

$SETS_SLUGS = array_reduce($SETS,function($ss,$set) { $ss[$set['slug']]=$set; return $ss; },[]);
$SET = $SETS_SLUGS[$_REQUEST['set']??""]??null; // may be null, that's fine!

if ($_REQUEST['do']=="listsets") {
	$questions = load_all_questions();
	foreach ($SETS as &$set) {
		$questions_in_set = array_filter($questions, function ($q) use ($set) {
			return ($q
				&& (
					($set && $set['cond']($q))
					//||
					//(empty($_SESSION['prefs']) || count(array_intersect($q['pf'], $_SESSION['prefs'])) > 0)) // at least one pf_y is present in pf
				)
				//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
			);
		});
		$q_in_set=array_column($questions_in_set,'num');
		$set['_count']=count($q_in_set);
		if (isset($_REQUEST["seen"])) {
			$seen = array_intersect($SEEN,$q_in_set);
			$set['_seen']=count($seen);
		}
		if (isset($_REQUEST['guessed'])) {
			$guessed = array_intersect($GUESSED,$q_in_set);
			$set['_score']=count($guessed);
		}
	}
	die(json_encode($SETS));
}

$unseen = [];
if ($SET) {
	if (!isset($_SESSION['set_questions'][$_REQUEST['set']])) {
		// load matching questions
		$all_questions = load_all_questions();

		if (!count($all_questions)) throw new Exception("No questions available");

		$_SESSION['question_count'] = count($all_questions);

		$settings = @json_decode(@file_get_contents("data/settings.json"),true);
		if ($settings) {
			foreach ((array)$settings['limits'] as &$limit) {
				if ($limit['type']=="max-in-group") {
					$limit['_chosen']=array_values(array_intersect_key($limit['group'],array_flip(array_rand($limit['group'],2))));
					shuffle($limit['_chosen']);
				}
			}
			unset($limit);
		}

		// throw away mismatched
		$all_questions = array_filter($all_questions, function ($q) use ($SET) {
			return $SET['cond']($q);
			//(empty($_SESSION['prefs']) || count(array_intersect($q['pf'], $_SESSION['prefs'])) > 0)) // at least one pf_y is present in pf
			//&& (!empty($pf_n) || count(array_intersect($q['pf'], $pf_n)) != count($q['pf'])) // not all of pf is in pf_n
		});

		// apply special rules
		if ($settings && isset($settings['sets'])) {
			$questions = array_filter($all_questions, function ($q) use ($settings) {
				foreach ($settings['limits'] as &$limit) {
					if ($limit['type']=="max-in-group" && in_array($q['num'],$limit['group']))
						return in_array($q['num'],$limit['_chosen']);
				}
				return true;
			});
		}

		$_SESSION['set_questions'][$_REQUEST['set']] = array_column($all_questions,'num');
	}

	$QUESTIONS = $_SESSION['set_questions'][$_REQUEST['set']];

	if ($do_shuffle)
		shuffle($QUESTIONS);

	// throw away seen
	$unseen = array_values(array_diff($QUESTIONS,$SEEN));
	$seen_set = array_intersect($SEEN,$QUESTIONS);
}

// pick a specific question, seen or not ; or, pick from unseen
$qnum = ($_REQUEST['q']??0) ?: $unseen[0] ?? 0;

$Q = [];
try {
	if ($qnum) $Q = Q::load_question_num($qnum); // =================================================
} catch (Exception $err) {
	die(json_encode(['err'=>$err->getMessage(),'errcase'=>"loading q",'errq'=>$qnum]));
}

if (!$Q && !$SET) die(json_encode(['err'=>"no q, no set"]));
if (!$Q) die(json_encode(['err'=>"no q in set ".$_REQUEST['set']." !?"]));


//$Q['n']=$num;
//$Q['f']=$f;

$RET['total'] = $_SESSION['question_count'];
if ($SET) {
	$RET['match'] = count($QUESTIONS);
	$RET['match_arr'] = $QUESTIONS; // debug
	$RET['unseen'] = count($unseen);
	//$RET['seen'] = count($SEEN);
	//$RET['seen_arr'] = $SEEN;
	$RET['seen_set'] = count($seen_set);
	$RET['seen_set_arr'] = $seen_set;
	//$RET['totalscore']=count($GUESSED);
	//$RET['guessed_arr']=$GUESSED;
	$RET['score_arr']=array_intersect($GUESSED,$QUESTIONS); // score for THIS set
	$RET['score']=count($RET['score_arr']);
	//$RET['set_arr']=$_SESSION['matched'];
	$RET['set'] = $SET;
}
$RET['err'] = $err ? $err->getMessage() : null;
$RET['q']=$Q ? $Q->getValues() : null;

die(json_encode($RET));


function load_all_questions() {
	// read ALL QUESTIONS into $QS
	$fs = Q::glob_all_datafiles("data/");
	$questions = [];
	foreach ($fs as $f) {
		try {
			$q_obj = Q::read_q($f);
			if ($q_obj) $questions[]=$q_obj->getValues();
		} catch (Exception $e) {
			header("X-rtg-q-error: ".$f." ".$e->getMessage(),false);
		}
	}
	return $questions;
}

function json_error($type,$text,$file,$line) {
	if ($type & error_reporting()) die(json_encode(['err'=>$text,'errline'=>$line,'errtype'=>$type]));
}