<?php
class Quiz {
	/**
	 * Fetch a question by its number, cleaned of correct answers, in JSON format. 
	 *
	 * @param int $num The question number to fetch.
	 * @param bool $full Whether to fetch the full question data, including correct answers.
	 * @return array The question data in JSON format.
	 */
	public static function fetch_q(int $num,bool $full=false):array {
		try {
			$q = Q::load_question_num($num); // =================================================
			die(json_encode($q->get_json(!$full)));
		} catch (Exception $err) {
			die(json_encode(['err'=>$err->getMessage(),'errcase'=>"loading q",'errq'=>$num]));
		}
	}

	public static function check_answer(int $num, string $guess, int $multinum=0):array {
		try {
			$q = Q::load_question_num($num);
			if ($multinum>0) {
				die(json_encode($q->check_multiple_choice($multinum,$guess)));
			} else {
				die(json_encode($q->check_text_answer($guess)));
			}
		} catch (Exception $err) {
			die(json_encode(['err'=>$err->getMessage(),'errcase'=>"loading q",'errq'=>$num]));
		}
	}
}