<?php
define ("DO_FACEBOOK",false);

define ("UI","cga");

require("config.inc.php");

session_id("rtg"); session_start();

if (strpos($_SERVER['HTTP_USER_AGENT'],"Firefox")!==FALSE) $htmlclass="ua-ff";
?>
<html class="<?=$htmlclass?>">

<head>
	<title>Your Game Sounds Familiar</title>
	<link rel="stylesheet" href="ui-<?=UI?>.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="jquery-ui.min.js"></script>
	<script src="https://cdn.jsdelivr.net/gh/yeikos/jquery.history/jquery.history.min.js"></script>
	<meta property="fb:app_id" content="2505132236168934">
	<meta property="og:url"           content="http://djab.eu/remember-that-game" />
  	<meta property="og:type"          content="website" />
  	<meta property="og:title"         content="Your Game Sounds Familiar" />
  	<meta property="og:description"   content="How many retro games can you recognize by music?" />
  	<meta property="og:image"         content="http://djab.eu/remember-that-game/img/but-play.gif" />

<!-- Global site tag (gtag.js) - Google Analytics -->
 <script async src="https://www.googletagmanager.com/gtag/js?id=UA-164157835-1"></script>
 <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'UA-164157835-1'); </script>
<!-- -->

</head>

<body>
	
	<?php if (DO_FACEBOOK): ?>
		<div id="fb-root"></div>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v6.0&appId=2505132236168934&autoLogAppEvents=1"></script>
	<?php endif; ?>


	<?php require("ui-".UI.".php"); ?>
	<script src="rtg_game.class.js"></script>
	<script>
	$(() => {
		var GAME = new RTG_GAME()
		if (UI) GAME.registerUI(UI)
		GAME.Init()
	})
	</script>

	<script>
		function urialize(obj) {
			var str = [];
			for (var p in obj)
				if (obj.hasOwnProperty(p)) {
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
				}
			return str.join("&");
		}

		// Paul Irish requestAnimationFrame Polyfill
		// http://www.paulirish.com/2011/requestanimationframe-for-smart-animating/
		window.requestAnimFrame = (function() {
			return window.requestAnimationFrame ||
				window.webkitRequestAnimationFrame ||
				window.mozRequestAnimationFrame ||
				function(callback) {
					window.setTimeout(callback, 1000 / 60);
				};
		})();

	</script>

	<script>
		var INIT_NUM = <?=intval($_REQUEST['q'])?>||null
	</script>

	<script>
		var oldTime = 0

		var Totalscore=0
		var Setscore=0
		var Totalseen=0


		var questionNames = {
			name: "Name of the game",
			fullname: "Full name",
			character: "Character name",
			title: "Song title"
		}
		
		var Subseq_errors = 0

		/// #param score number

		function next_hint() {
			Hints++
			Score++
			show_correct(Score,true)
			$("#hint").html(Score<Q.maxscore-1?"GIVE UP":"GIVE UP")
		}

		/*
		function init_tristates() {
			let $tristates = $(".tristate")
			$tristates.css("opacity","0").wrap("<div class='tristate-wrap'></div>").parent().prepend("<img></img>");
			$(".tristate-wrap").click(function(ev) {
				console.log(ev)
				let $input = $(this).find("input.tristate")
				let v = $input.val()
				v++
				if (v>2) v=0
				$input.val(v).change()
			})
			$(".tristate").change(function() {
				let v = $(this).val()
				let imgs=["cb-0.gif","cb-y.gif","cb-n.gif"]
				$(this).closest(".tristate-wrap").find("img").attr("src",imgs[v]||"xxx.gif")
				window.localStorage.setItem("prefs",JSON.stringify($("#prefform").serializeArray()))
			})
			let prefs = window.localStorage.getItem("prefs")
			if (prefs!=null) prefs=JSON.parse(prefs)
			if (typeof(prefs)=="object")
				for (pref of prefs)
					$("#prefform input[name='"+pref.name+"']").val(pref.value||0)
			$(".tristate").change()
		}
		*/

	</script>
</body>

</html>