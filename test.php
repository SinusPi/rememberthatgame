<?php
require("q.class.php");
require("Quiz.class.php");

error_reporting(E_ALL);

function assert_true(bool $condition, string $message): void {
	if (!$condition) {
		throw new Exception($message);
	}
}

function assert_same($expected, $actual, string $message): void {
	if ($expected !== $actual) {
		throw new Exception($message." Expected: ".var_export($expected, true)." Actual: ".var_export($actual, true));
	}
}

function assert_has_key(string $key, array $array, string $message): void {
	if (!array_key_exists($key, $array)) {
		throw new Exception($message." Missing key: ".$key);
	}
}

function run_php_script(string $script): string {
	$tempFile = tempnam(sys_get_temp_dir(), 'rtg-test-');
	assert_true($tempFile !== false, "Failed to create temporary PHP script");
	file_put_contents($tempFile, $script);
	try {
		$output = shell_exec('php '.escapeshellarg($tempFile));
	} finally {
		@unlink($tempFile);
	}
	assert_true($output !== null, "Failed to execute q.php in subprocess");
	return $output;
}

function run_quiz_php(string $expression): array {
	$output = run_php_script("<?php\nrequire 'q.class.php';\nrequire 'Quiz.class.php';\n".$expression."\n");
	$data = json_decode($output, true);
	assert_true(is_array($data), "Quiz subprocess did not return valid JSON: ".$output);
	return $data;
}

function test_load_question_by_number(): void {
	$q = Q::load_question_num(17);
	assert_same(17, $q->num, "Question number should match requested file");
	assert_true(count($q->scores) >= 2, "Question 17 should expose multiple expert answers");
	assert_true(count($q->multiple) >= 2, "Question 17 should expose multiple easy-mode answers");
}

function test_clean_json_hides_answers_and_hints(): void {
	$q = Q::load_question_num(17);
	$clean = $q->get_json(true);
	$full = $q->get_json(false);

	assert_has_key('scores', $clean, "Clean JSON should keep score slots");
	assert_has_key('multiple', $clean, "Clean JSON should keep multiple-choice slots");
	assert_true(!isset($clean['scores'][0]['re']), "Clean JSON should hide expert regex hints");
	assert_true(!isset($clean['scores'][0]['answer']), "Clean JSON should hide expert answers");
	assert_true(!isset($clean['multiple'][0]['correct']), "Clean JSON should hide correct multiple-choice answer");
	assert_true(!isset($clean['multiple'][0]['wrongs']), "Clean JSON should hide wrong-answer pool");
	assert_true(isset($clean['multiple'][0]['choices']), "Clean JSON should still expose multiple-choice options");
	assert_true(in_array("King's Quest", $clean['multiple'][0]['choices'], true), "Choices should include the correct easy-mode answer");
	assert_same('kq|king.*quest', $full['scores'][0]->re, "Full JSON should retain expert regex hints");
	assert_same("King's Quest", $full['multiple'][0]->correct, "Full JSON should retain full multiple-choice data");
}

function test_expert_mode_matches_regex_answers(): void {
	$q = Q::load_question_num(17);
	$matches = $q->check_text_answer("King's Quest V");

	assert_same(2, count($matches), "Expert mode should allow one answer to satisfy both broad and precise prompts");
	assert_same('name', $matches[0]['name'], "First match should be the broad game-name answer");
	assert_same('fullname', $matches[1]['name'], "Second match should be the precise full-title answer");

	$regexDriven = Q::load_question_num(2)->check_text_answer("Sands of Time");
	assert_same(1, count($regexDriven), "Expert mode should honor regex alternatives from question data");
	assert_same("Prince of Persia: Sands of Time", $regexDriven[0]['answer'], "Regex alternative should resolve to the configured answer");
}

function test_easy_mode_checks_multiple_choice_sequence(): void {
	$q = Q::load_question_num(17);
	$firstCorrect = $q->check_multiple_choice(1, "King's Quest");
	$firstWrong = $q->check_multiple_choice(1, "Space Quest");
	$secondCorrect = $q->check_multiple_choice(2, "King's Quest V");

	assert_same('name', $firstCorrect['name'], "First easy-mode prompt should accept the correct broad title");
	assert_same([], $firstWrong, "Wrong easy-mode answer should return an empty result");
	assert_same('fullname', $secondCorrect['name'], "Second easy-mode prompt should accept the precise title");
}

function test_quiz_fetch_and_check_answer(): void {
	$fetched = Quiz::fetch_q(17);
	assert_same(17, $fetched['num'], "Quiz fetch should return the requested question");
	assert_true(!isset($fetched['scores'][0]['answer']), "Quiz fetch should return cleaned expert data");
	assert_true(!isset($fetched['multiple'][0]['correct']), "Quiz fetch should return cleaned multiple-choice data");

	$expertResult = Quiz::check_answer(17, "King's Quest V");
	assert_same(2, count($expertResult), "Quiz answer check should return all expert-mode matches");

	$easyResult = Quiz::check_answer(17, "King's Quest V", 2);
	assert_same('fullname', $easyResult['name'], "Quiz answer check should validate easy-mode answers by choice index");

	$easyWrong = Quiz::check_answer(17, "King's Quest VI", 2);
	assert_same([], $easyWrong, "Quiz answer check should reject wrong easy-mode answers");
}

$tests = [
	'test_load_question_by_number',
	'test_clean_json_hides_answers_and_hints',
	'test_expert_mode_matches_regex_answers',
	'test_easy_mode_checks_multiple_choice_sequence',
	'test_quiz_fetch_and_check_answer',
];

$failures = [];
foreach ($tests as $test) {
	try {
		$test();
		print "PASS ".$test.PHP_EOL;
	} catch (Throwable $err) {
		$failures[] = $test.": ".$err->getMessage();
		print "FAIL ".$test.": ".$err->getMessage().PHP_EOL;
	}
}

if ($failures) {
	exit(1);
}

print "All tests passed".PHP_EOL;

