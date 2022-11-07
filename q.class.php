<?php
class Q implements ArrayAccess {
	static $folder = "data";
	static $convert_to_json = true;

	private $container = array();

	function offsetSet($offset,$value):void {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    function offsetExists($offset):bool {
        return isset($this->container[$offset]);
    }

    function offsetUnset($offset):void {
        unset($this->container[$offset]);
    }

    function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

	function __construct($a=null)
	{
		$this->setValues($a ?: []);
	}

	function setValues($a) {
		$this->container = $a;
	}

	function getValues() {
		return $this->container;
	}


	static function load_question_num($num) {
		$files = glob(self::$folder."/".sprintf("%05d",$num)." - *.txt");
		if (!$files) throw new Exception("Question $num not found");
		return self::read_q($files[0]);
	}

	static function read_q($qfilename) {
		$q = new Q();

		preg_match("/(\\d+) \\- (.*)/", $qfilename, $ms);
		$q['num'] = intval($ms[1]);

		foreach (["mp3","png","gif"] as $ext) {
			$cluefile = str_replace(".txt", ".$ext", $qfilename);
			if (file_exists($cluefile)) {
				$q['type']=$ext;
				$q['file']=$cluefile;
				break;
			}
		}
		if (!$q['type']) throw new Exception("No data file found for $qfilename.");
	
		// if it's JSON, use it and bail.
		$qfile = file_get_contents($qfilename);
		$json = @json_decode($qfile,true);
		if ($json) {
			$q->setValues(array_merge($q->getValues(),Q::read_json($json)));
			return $q;
		}
	
		// if it's an old-style question, read it and convert.
		$q->read_from_old_file($qfilename);

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
		$vals = $this->getValues();
		unset($vals['num'],$vals['file'],$vals['type']);
		$json = json_encode($vals,JSON_PRETTY_PRINT);
		if ($json) file_put_contents($f,$json);
	}
	
	static function read_json($json) {
		return $json; // no postprocessing for now
	}

	static function glob_all_datafiles($dir)	{
		return glob($dir . "????? - *.txt");
	}
}