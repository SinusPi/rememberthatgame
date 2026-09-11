<?php
class Q {
	static $folder = "data";
	static $convert_to_json = true;

	static $ONLY_TYPE;

	public string $type = "";
	public string $file = "";

	public array $scores = [];
	public array $pf = [];
	public array $multiple = [];
	public string $fullname = "";
	public string $title = "";
	public int $year = 0;
	public string $url = "";
	public string $composer = "";
	public string $publisher = "";
	public string $producer = "";
	public string $cmp = "";
	public bool $remix = false;
	public array $trivia = [];
	public array $tags = [];

	function __construct(
		public int $num,
		public array $data
	) { }

	static function load_question_num($num) {
		$files = glob(self::$folder."/".sprintf("%05d",$num)." - *.txt");
		if (!$files) throw new Exception("Question $num not found");
		return self::read_q($files[0]);
	}

	static function read_q($qfilename):Q {
		preg_match("/(\\d+) \\- (.*)/", $qfilename, $ms);
		if (!$ms) throw new Exception("No question number in filename $qfilename");
		
		$q = new Q(intval($ms[1]),[]);

		foreach (["mp3","png","gif"] as $ext) {
			$cluefile = str_replace(".txt", ".$ext", $qfilename);
			if (file_exists($cluefile)) {
				$q->type=$ext;
				$q->file=$cluefile;
				break;
			}
		}
		if (!$q->type) throw new Exception("No data file found for $qfilename.");
	
		// if it's JSON, use it and bail.
		$qfile = file_get_contents($qfilename);
		$json = @json_decode($qfile,true);
		if ($json) {
			$json = Q::read_json($json);
			foreach ($json as $k=>$v) {
				$q->$k = $v;
			}
		} else {
			// if it's an old-style question, read it and convert.
			$q->read_from_old_file($qfilename);
		}

		return $q;
	}

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
				if (!isset($q[$key]))
					$this[$key] = $val;
				else
					$this[$key] = array_merge((array)$this[$key],(array)$val);
			}
			elseif (preg_match("/^(\\-+)(.*)/", $m, $ms)) {
				//$score=1/(strlen($ms[1])+1);
				//$this['scores'][]=['score'=>$score,'re'=>$ms[2]];
			} elseif /* + */ (preg_match("/^\\+([a-z]+|\".*?\")=(.*)=(.*)/", $m, $ms)) { // text name: +name=go.*od=Good
				list($_,$name,$re,$answer) = $ms;
				$qs = (array)$this['scores'];
				$qs[] = ['name' => $name, 're' => $re, 'answer' => $answer];
				$this['scores']=$qs;
			} elseif /* * */ (preg_match("/^\\*([a-z]+|\".*?\")=(.*)\\s*\\|\\s*(.*)/", $m, $ms)) { // multiple choice: *name=Good=Bad,Bad,Bad
				$name = $ms[1];
				$answer = trim($ms[2]);
				$wrongs = preg_split("/\\s*,\\s*/",$ms[3]);
				$qm = (array)$this['multiple'];
				$qm[] = ['name' => $name, 'answer' => $answer, 'wrongs' => $wrongs];
				$this['multiple']=$qm;
			} else {
				// legacy
				$re = $m;
				unset($answer);
				$this['scores'][1]['tag']="name";
				$this['scores'][1]['re']=$re;
			}
		}
		if (!$this['pf']) $this['pf']="PC";
		$this['pf'] = explode(",", $this['pf']);  
	
		if (isset($this['trivia'])) settype($this['trivia'],"array");
	

		if (count((array)$this['scores'])==0 && count((array)$this['multiple'])==0) throw new Exception("bad q ".$this['num']); // bad question

		// save converted!
		if (self::$convert_to_json) $this->save_json($filename);
	}

	function save_json($f) {
		// remove default fields
		$vals = $this->data;
		$json = json_encode($vals,JSON_PRETTY_PRINT);
		if ($json) file_put_contents($f,$json);
	}
	
	static function read_json($json) {
		if (gettype($json['tags']??0)=="string") $json['tags']=explode(",",$json['tags']);
		if (gettype($json['year']??0)=="string") $json['year']=intval($json['year']);
		if (gettype($json['trivia']??0)=="string") $json['trivia']=[$json['trivia']];
		return $json; // no postprocessing for now
	}

	static function glob_all_datafiles($dir)	{
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
	
}