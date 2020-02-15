<?php
ini_set("DISPLAY_ERRORS",1);
//include("config.inc.php");
//$db = mysqli_connect(null,$CFG['db_user'],$CFG['db_pass'],$CFG['db_name']);

$fs = glob("data/*.txt");
$f=$fs[rand(0,count($fs)-1)];
$meta=file($f);
$mp3=str_replace(".txt",".mp3",$f);
if (file_exists($mp3)) {
	?>
	<audio controls><source src="<?=$mp3?>" type="audio/mpeg"></audio>
	<script>
	var regexps=[]
	<?php
	foreach ($meta as $m) {
		if (substr($m,0,1)=="=") echo "var answer='".addslashes(substr($m,1))."'\n";
		elseif (substr($m,0,1)=="@") echo "var platform='".addslashes(substr($m,1))."'\n";
		elseif (substr($m,0,1)==":") echo "var year='".addslashes(substr($m,1))."'\n";
		else echo "regexps.push('".addslashes($m)."')\n";
	}
	?>
	function test() {
		console.log(this)
	}
	</script>
	<input type="text" onchange="test">
	<?php
	
} else echo $mp3." doesn't exist";
?>
