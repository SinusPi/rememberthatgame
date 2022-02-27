<?php
require("q.class.php");

session_start();

header("Content-type: text/html; charset=utf-8");
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<style>
@font-face {
  font-family: "WebPlus_IBM_CGA";
  src: url("WebPlus_IBM_CGA.woff") format("woff");
  font-weight: normal;
  font-style: normal;
}
	body {
		font-family: "WebPlus_IBM_CGA";
	}
</style>
<?php

if ($_REQUEST['adminpass']=="rtg789@@") $_SESSION['admin']=true;
if (!$_SESSION['admin']) {
	?>
	<form action=""><input type="password" name="adminpass"><input type="submit"></form>
	<?php
	die();
}

$fs = Q::glob_all_datafiles("data/");
foreach ($fs as $f) {
	$q = Q::read_q($f)->getValues();
	if (!$q)
		header("X-rtg-q-error: ".$f,false);
	else {
		$scores_by_type = [];
		foreach (array_merge((array)$q['scores'],(array)$q['multiple'],[]) as $s) $scores_by_type[$s['name']]=$s;
		$q['_name']=
			(isset($scores_by_type['fullname'])?$scores_by_type['fullname']['answer']:null) ?:
			(isset($scores_by_type['name'])?$scores_by_type['name']['answer']:null) ?:
			basename($f,".txt");
		$QS[$q['num']]=$q;
	}
}

?>
<h1>Remember That Game: Admin</h1>

<table id="gamelist">
	<tr><th>#<th>game<th>platform<th>type</tr>

	<?php foreach ($QS as $q): ?>
		<tr class='game'>
			<td align="right"><?=$q['num']?>&nbsp;
			<td><?=$q['_name']?>
			<td><?=implode(", ",$q['pf'])?>
			<td><div class="type <?=$q['type']?>"><?=$q['type']?></div>
			<td>
				<a href="#" onclick="return preview(<?=$q['num']?>)">preview</a>
				<a href="?edit=<?=$q['num']?>">edit</a>
	<?php endforeach; ?>
</table>

<div id="previewcontainer" style="display:none;">
	<div id=closer onclick="document.querySelector('#preview').contentWindow.GAME.Audio.player.pause(); document.querySelector('#previewcontainer').style.display='none'">X</div>
	<iframe id="preview" style="width:1280px; height:960px;"></iframe>
</div>

<style>
	body { position:relative; width:100vw; height:100vh; }
	#previewcontainer {
		background:white; border:2px solid black;
		position:fixed;
		bottom:0px; right:0px; width:640px; height:480px;		
	}
	#previewcontainer #closer {
		text-align:right;
	}
	.type {
		text-transform: uppercase; font-size:0.7em;
	}
	.type.mp3 {
		color:red;
	}
	.type.png,.type.gif,.type.jpg {
		color:green;
	}
	#preview {
		border:0;
	   -ms-zoom: 0.5;
        -moz-transform: scale(0.5);
        -moz-transform-origin: 0 0;
        -o-transform: scale(0.5);
        -o-transform-origin: 0 0;
        -webkit-transform: scale(0.5);
        -webkit-transform-origin: 0 0;
    }		
	#gamelist .game:hover {
		background:silver;
	}
</style>
<script>
function preview(qnum) {
	document.querySelector("#preview").src=window.location.toString().replace(/\/[^\/]*$/,"")+"/"+qnum
	document.querySelector('#previewcontainer').style.display='block'
	return false
}
$(()=>{
	//$("#gamelist .game")
})
</script>