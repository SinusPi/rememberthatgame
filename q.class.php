<?php
class Q {
	static $folder = "data";
	static $convert_to_json = true;

	public string $type = "";
	public string $file = "";

	/** Text answers for the question
	 * @var TextAnswer[]
	 */
	public array $scores = [];

	/** Multiple choice answers for the question
	 * @var MultipleChoice[]
	 */
	public array $multiple = [];

	/** Platforms
	 * @var string[]
	 */
	public array $pf = [];

	/** Platforms with years
	 * @var array<string,int>
	 */
	public array $pf_y = [];

	/** Source platform
	 * @var string
	 */
	public string $source_pf = "";

	public string $title = "";
	public int $year = 0;
	public string $url = "";
	public string $composer = "";
	public string $publisher = "";
	public string $producer = "";
	public bool $remix = false;
	public array $trivia = [];
	public array $tags = [];

	function __construct(
		public int $num,
		public array $data
	) { }

	static function load_question_num(int $num) {
		$files = glob(self::$folder."/".sprintf("%05d",$num)." - *.txt");
		if (!$files) throw new Exception("Question $num not found");
		return self::read_q($files[0]);
	}

	static function read_q(string $filename):Q {
		preg_match("/(\\d+) \\- (.*)/", $filename, $ms);
		if (!$ms) throw new Exception("No question number in filename $filename");
		
		$qnum = intval($ms[1]);
		$q = new Q($qnum,[]);

		foreach (["mp3","png","gif"] as $ext) {
			$cluefile = str_replace(".txt", ".$ext", $filename);
			if (file_exists($cluefile)) {
				$q->type=$ext;
				$q->file=$cluefile;
				break;
			}
		}
		if (!$q->type) throw new Exception("No data file found for $filename.");
	
		// if it's JSON, use it and bail.
		$qfile = file_get_contents($filename);
		$json = @json_decode($qfile,true);
		if ($json) {
			$json = Q::fix_json($json);
			$q->set_from_array($json);
		} else {
			// if it's an old-style question, read it and convert.
			$q->read_from_old_file($filename);
		}

		return $q;
	}

	/**
	 * @deprecated Use Q::load_question_num() instead.
	 */
	function read_from_old_file($filename) {
		$meta = file($filename);
		foreach ($meta as $m) {
			$m = trim($m);
			$c = substr($m, 0, 1);
			$r = substr($m, 1);
			if (false) { }
			elseif ($c == "=") { }//$q['scores'][1]['answer'] = $r;
			elseif (preg_match("/^([a-z]+)=(.*)/", $m, $ms)) {
				$key=$ms[1]; $val=$ms[2];
				if (!isset($this->$key)) {
					if (!property_exists($this,$key)) throw new Exception("Unknown property $key");
					if (gettype($this->$key)=="array") $val=explode(",",$val);
					elseif (gettype($this->$key)=="int") $val=intval($val);
					$this->$key = $val;
				} else
					$this->$key = array_merge((array)$this->$key,(array)$val);
			}
			elseif (preg_match("/^(\\-+)(.*)/", $m, $ms)) {
				//$score=1/(strlen($ms[1])+1);
				//$this['scores'][]=['score'=>$score,'re'=>$ms[2]];
			} elseif /* + */ (preg_match("/^\\+([a-z]+|\".*?\")=(.*)=(.*)/", $m, $ms)) { // text name: +name=go.*od=Good
				list($_,$name,$re,$answer) = $ms;
				$qs = (array)$this->scores;
				$qs[] = ['name' => $name, 're' => $re, 'answer' => $answer];
				$this->scores=$qs;
			} elseif /* * */ (preg_match("/^\\*([a-z]+|\".*?\")=(.*)\\s*\\|\\s*(.*)/", $m, $ms)) { // multiple choice: *name=Good=Bad,Bad,Bad
				$name = $ms[1];
				$answer = trim($ms[2]);
				$wrongs = preg_split("/\\s*,\\s*/",$ms[3]);
				$qm = (array)$this->multiple;
				$qm[] = ['name' => $name, 'answer' => $answer, 'wrongs' => $wrongs];
				$this->multiple=$qm;
			} else {
				// legacy
				$re = $m;
				unset($answer);
				$this->scores[1]['tag']="name";
				$this->scores[1]['re']=$re;
			}
		}
		if (!$this->pf) $this->pf=["PC"];
	
		if (isset($this->trivia)) settype($this->trivia,"array");
	

		if (count((array)$this->scores)==0 && count((array)$this->multiple)==0) throw new Exception("bad q ".$this->num); // bad question

		// save converted!
		if (self::$convert_to_json) $this->save_as_json($filename);
	}

	/**
	 * @deprecated Saving no longer needed.
	 */
	function save_as_json(string $filename) {
		// remove default fields
		$vals = $this->data;
		$json = json_encode($vals,JSON_PRETTY_PRINT);
		if ($json) file_put_contents($filename,$json);
	}
	
	/**
	 * Fix JSON data for a question. Turn comma-separated strings into arrays, etc.
	 * @param array $json The JSON data to fix.
	 */
	static function fix_json(array $json) {
		if (gettype($json['tags']??0)=="string") $json['tags']=explode(",",$json['tags']);
		if (gettype($json['pf']??0)=="string") $json['pf']=explode(",",$json['pf']);
		if (gettype($json['year']??0)=="string") $json['year']=intval($json['year']);
		if (gettype($json['trivia']??0)=="string") $json['trivia']=[$json['trivia']];
		return $json; // no postprocessing for now
	}

	function set_from_array(array $arr) {
		foreach ($arr as $k=>$v) {
			if (!property_exists($this,$k)) throw new Exception("Unknown property $k");
			if ($k=="cmp") $k="composer"; // legacy
			if ($k=="scores") {
				$this->scores=[];
				foreach ((array)$v as $score) {
					$this->scores[] = new TextAnswer(
						$score['name']??"",
						$score['re']??"",
						$score['answer']??"",
						intval($score['score']??0)
					);
				}
			} elseif ($k=="multiple") {
				$this->multiple=[];
				foreach ((array)$v as $mc) {
					$this->multiple[] = new MultipleChoice(
						$mc['name']??"",
						$mc['answer']??"",
						(array)$mc['wrongs']
					);
				}
			} elseif ($k=="trivia") { // arrayify if single string
				$this->trivia=(array)$v;
			} elseif (gettype($this->$k)=="array" && gettype($v)!="array") { // explode if string
				$this->$k=explode(",",$v);
			} elseif (gettype($this->$k)=="int") {
				$this->$k=intval($v);
			} elseif (gettype($this->$k)=="string") {
				$this->$k=trim($v);
			} else {
				$this->$k=$v;
			}
		}
		$this->sanitize();
	}

	function sanitize() {
		if ($this->type=="") $this->type="mp3";
		if ($this->title=="") $this->title=array_filter($this->scores,fn($s)=>$s->name=="name")[0]?->answer??"";
		if ($this->year && $this->pf) {
			// reindex pf to have year as value, not just keys
			$newpf = [];
			foreach ($this->pf as $pf) $newpf[$pf] = $this->year;
			$this->pf_y = $newpf;
		}
	}

	static function glob_all_datafiles(string $dir) {
		return glob($dir . "????? - *.txt");
	}

	static function load_questions(?callable $filter=null) {
		$files = self::glob_all_datafiles(self::$folder."/");
		$questions = [];
		foreach ($files as $f) {
			$qnum = intval(basename($f));
			try {
				$q = self::read_q($f);
				if ($filter && !$filter($q)) continue;
				$questions[$qnum] = $q;
			} catch (Exception $e) {
				header("X-rtg-q-error: ".$f." ".$e->getMessage(), false);
			}
		}
		return $questions;
	}

	public function get_json(bool $cleaned=false):array {
		$data = get_object_vars($this);

		if ($cleaned) {
			// clean answers for JSON output
			$data['scores'] = array_map(fn($score)=>$score->get_json(true),$this->scores);
			$data['multiple'] = array_map(fn($mc)=>$mc->get_json(true),$this->multiple);
			unset($data['trivia'],$data['name']);
		}
		return $data;
	}

	function is_pf(string|array $pf):bool {
		if (is_string($pf)) $pf=[$pf];
		return !!array_intersect($pf,$this->pf);
	}

	function check_text_answer(string $guess):array {
		return array_map(fn($score)=>$score->get_json(false), array_filter($this->scores, fn($score)=>$score->is_correct($guess)));
	}
	/**
	 * Check a multiple choice answer for this question.
	 * @param int $choicenum The 1-based index of the multiple choice question to check.
	 * @param string $guess The user's guess.
	 * @return array The result of the check: full multiple choice data if correct, empty array if incorrect.
	 * @throws Exception If the multiple choice question number is invalid.
	 */
	function check_multiple_choice(int $choicenum, string $guess):array {
		$choicenum--; // convert to 0-based index
		if (!isset($this->multiple[$choicenum])) throw new Exception("No multiple choice question #$choicenum for question ".$this->num);
		$mult = $this->multiple[$choicenum];
		if ($mult->is_correct($guess)) return $mult->get_json(false);
		else return [];
	}
	
}

class TextAnswer implements CleanableJSON {
	public string $name;
	public string $re;
	public string $answer;
	public int $score;

	function __construct(string $name, string $re, string $answer, int $score) {
		$this->name = $name;
		$this->re = $re;
		$this->answer = $answer;
		$this->score = $score;
	}
	
	function is_correct(string $guess):bool {
		return preg_match("/".$this->re."/i",$guess) === 1;
	}
	
	function clean_json(array &$data) {
		unset($data['re'],$data['answer']);
	}

	function get_json(bool $cleaned=false):array {
		$data = get_object_vars($this);
		if ($cleaned) $this->clean_json($data);
		return $data;
	}
}

class MultipleChoice implements CleanableJSON {
	public string $name;
	public string $correct;
	public array $wrongs;
	public array $choices;

	function __construct(string $name, string $correct, array $wrongs) {
		$this->name = $name;
		$this->correct = $correct;
		$this->wrongs = $wrongs;
		$this->choices = array_merge($wrongs, [$correct]);
		shuffle($this->choices);
	}

	function is_correct(string $guess):bool {
		return $guess == $this->correct;
	}

	function clean_json(array &$data) {
		unset($data['correct'],$data['wrongs']);
	}

	function get_json(bool $cleaned=false):array {
		$data = get_object_vars($this);
		if ($cleaned) $this->clean_json($data);
		return $data;
	}
}

interface CleanableJSON {
	function clean_json(array &$data);
	function get_json(bool $cleaned=false):array;
}