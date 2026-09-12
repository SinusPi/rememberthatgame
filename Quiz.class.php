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
			return($q->get_json(!$full));
		} catch (Exception $err) {
			return(['err'=>$err->getMessage(),'errcase'=>"loading q",'errq'=>$num]);
		}
	}

	/**
	 * Check an answer, either for an expert-mode text answer or an easy-mode multiple-choice answer when $multinum is provided.
	 *
	 * @param int $num The question number to check.
	 * @param string $guess The user's answer guess.
	 * @param int $multinum The multiple-choice index for easy-mode answers.
	 * @return array An array of matching answers or an empty array if incorrect.
	 */
	public static function check_answer(int $num, string $guess, int $multinum=0):array {
		try {
			$q = Q::load_question_num($num);
			if ($multinum>0) {
				return($q->check_multiple_choice($multinum,$guess));
			} else {
				return($q->check_text_answer($guess));
			}
		} catch (Exception $err) {
			return(['err'=>$err->getMessage(),'errcase'=>"loading q",'errq'=>$num]);
		}
	}
}