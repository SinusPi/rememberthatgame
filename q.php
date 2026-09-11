<?php
require("q.class.php");
require("set.class.php");

ini_set("DISPLAY_ERRORS", 1);
header("Content-type: application/json");

//include("config.inc.php");
//$db = mysqli_connect(null,$CFG['db_user'],$CFG['db_pass'],$CFG['db_name']);

error_reporting(E_ALL^E_NOTICE);
set_error_handler("json_error");

require("config.inc.php");
$QSETS = Set::get_sets_from_config($SETS);

if (isset($_REQUEST['listsets'])) {
	$do_scores = ($_REQUEST['seen']??[]) || ($_REQUEST['guessed']??[]);
	$all_questions = Q::load_questions();
	foreach ($QSETS as $set) {
		$setvals = get_object_vars($set);

	
		$set->get_valid_questions_from($all_questions);
		$setvals['count']=count($set->questions);
		
		$scores = $set->get_scores($_REQUEST['seen']??"",$_REQUEST['guessed']??"");
		$scores = array_map("count",$scores);
		$setvals = array_merge($setvals,$scores);

		unset($setvals['condition'],$setvals['questions']);

		$ret[] = $setvals;
	}
	die(json_encode($ret));
}

if (isset($_REQUEST['set'])) {
	$set = $QSETS[$_REQUEST['set']];
	if (!$set) die(json_encode(['error'=>"no_such_set"]));
	
	$all_questions = Q::load_questions();
	$set->get_valid_questions_from($all_questions);

	$SEED = $_REQUEST['seed']??0;
	if ($SEED==-1) $SEED=rand(1,9999);
	if ($SEED>0) {
		srand($SEED);
		shuffle($set->questions);
	}

	$retset = [];
	$retset['questions']=array_keys($set->questions);

	$scores = $set->get_scores($_REQUEST['seen']??"",$_REQUEST['guessed']??"");
	$retset = array_merge($retset,$scores);

	die(json_encode($retset));
}

if (isset($_REQUEST['settings'])) {
	die(@json_encode(@json_decode(@file_get_contents("data/settings.json"),true)));
}

		/*
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

	$QUESTIONS = $_SESSION['set_questions'][$_REQUEST['set']];

	if ($do_shuffle)
		shuffle($QUESTIONS);

	// throw away seen
	$unseen = array_values(array_diff($QUESTIONS,$SEEN));
	$seen_set = array_intersect($SEEN,$QUESTIONS);
		*/

if (isset($_REQUEST['q'])) {
	try {
		$q = Q::load_question_num(intval($_REQUEST['q']??0)); // =================================================
		$qvals = get_object_vars($q);
		die(json_encode($qvals));
	} catch (Exception $err) {
		die(json_encode(['err'=>$err->getMessage(),'errcase'=>"loading q",'errq'=>$qnum]));
	}
}
/*
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
*/

function json_error($type,$text,$file,$line) {
	if ($type & error_reporting()) die(json_encode(['err'=>$text,'errline'=>$line,'errtype'=>$type]));
}