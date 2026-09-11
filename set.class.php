<?php
class Set {
	public array $questions = [];
	public ?Closure $condition = null;

	public function __construct(
		public string $slug = "all",
		public string $label = "all",
		public string $description = "all",
		Closure $condition
	) {
		$this->condition = $condition;
	}

	static function build_default() {
		return new self(condition:fn()=>true);
	}

	public function get_valid_questions_from(array $all_questions) {
		return $this->questions = array_filter($all_questions,$this->condition);
	}

	public static function get_sets_from_config(array $inputs) {
		$inputs_slugs = array_column($inputs,null,'slug');
		return array_map(fn($input)=>new Set($input['slug'],$input['label'],$input['description'],$input['condition']), $inputs_slugs);
	}

	public function get_scores($seenlist,$guessedlist) {
		$SEEN = array_map("intval",explode(",",$seenlist));
		$GUESSED = array_map("intval",explode(",",$guessedlist));
		
		$qnums = array_keys($this->questions);
		$ret=[];
		if ($SEEN) {
			$seen = array_intersect($SEEN,$qnums);
			$ret['seen']=$seen;
		}
		if ($GUESSED) {
			$guessed = array_intersect($GUESSED,$qnums);
			$ret['guessed']=$guessed;
		}
		return $ret;
	}
}