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

	static function read_q($f) {
		$found=false;
		foreach (["mp3","png","gif"] as $ext) {
			$file = str_replace(".txt", ".".$ext, $f);
			if (file_exists($file)) { $found=true; break; }
		}
		if (!$found) throw new Exception("Data file not found for $f.");
		
		$q = new Q();

		preg_match("/(\\d+) \\- (.*)/", $f, $ms);
		$q['num'] = intval($ms[1]);
		$q['file'] = "get.php?q=".$q['num'];
		$q['type'] = $ext;
	
		// if it's JSON, use it and bail.
		$file = file_get_contents($f);
		$json = @json_decode($file,true);
		if ($json) {
			$q->setValues(array_merge($q->getValues(),Q::read_json($json)));
			return $q;
		}
	
		$meta = file($f);
		foreach ($meta as $m) {
			$m = trim($m);
			$c = substr($m, 0, 1);
			$r = substr($m, 1);
			if (false) { }
			elseif ($c == "=") { }//$q['scores'][1]['answer'] = $r;
			elseif (preg_match("/^([a-z]+)=(.*)/", $m, $ms)) {
				$key=$ms[1]; $val=$ms[2];
				if (!isset($q[$key]))
					$q[$key] = $val;
				else
					$q[$key] = array_merge((array)$q[$key],(array)$val);
			}
			elseif (preg_match("/^(\\-+)(.*)/", $m, $ms)) {
				//$score=1/(strlen($ms[1])+1);
				//$q['scores'][]=['score'=>$score,'re'=>$ms[2]];
			} elseif /* + */ (preg_match("/^\\+([a-z]+|\".*?\")=(.*)=(.*)/", $m, $ms)) { // text name 
				$name = $ms[1];
				$re = $ms[2];
				$answer = $ms[3];
				settype($q['scores'],"array");
				$q['scores'][] = ['name' => $name, 're' => $re, 'answer' => $answer];
			} elseif /* * */ (preg_match("/^\\*([a-z]+|\".*?\")=(.*)\\s*\\|\\s*(.*)/", $m, $ms)) { // multiple choice
				$name = $ms[1];
				$answer = trim($ms[2]);
				$wrongs = preg_split("/\\s*,\\s*/",$ms[3]);
				settype($q['multiple'],"array");
				$q['multiple'][] = ['name' => $name, 'answer' => $answer, 'wrongs' => $wrongs];
			} else {
				// legacy
				$re = $m;
				unset($answer);
				$q['scores'][1]['tag']="name";
				$q['scores'][1]['re']=$re;
			}
		}
		$q['pf'] = explode(",", $q['pf']);
	
		if (isset($q['trivia'])) settype($q['trivia'],"array");
	
		// save converted!
		if (self::$convert_to_json) $q->save_json($f);

		return $q;
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