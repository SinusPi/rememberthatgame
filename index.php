<?php
define ("DO_FACEBOOK",false);

define ("UI","console");

require("config.inc.php");

$htmlclass = (strpos($_SERVER['HTTP_USER_AGENT'],"Firefox")!==FALSE) ? "ua-ff" : "";
?>
<html class="<?=$htmlclass?>">

<head>
	<title>Your Game Sounds Familiar</title>
	<link rel="stylesheet" href="dist/ui-<?=UI?>.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<!link rel="stylesheet" href="dist/fancyInput.css">
	<meta property="fb:app_id" content="2505132236168934">
	<meta property="og:url"           content="https://djab.eu/remember-that-game" />
  	<meta property="og:type"          content="website" />
  	<meta property="og:title"         content="Your Game Sounds Familiar" />
  	<meta property="og:description"   content="How many retro games can you recognize by music?" />
  	<meta property="og:image"         content="http://djab.eu/remember-that-game/img/but-play.gif" />

<!-- Global site tag (gtag.js) - Google Analytics -->
 <script async src="https://www.googletagmanager.com/gtag/js?id=UA-164157835-1"></script>
 <script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag('js', new Date()); gtag('config', 'UA-164157835-1'); </script>
<!-- -->
	<style>
		body { }
		#console { font-family:monospace; position:absolute; left:0px; top:0px; width:100vw; height:100vh; }
	</style>

</head>

<body>
	
	<?php if (DO_FACEBOOK): ?>
		<div id="fb-root"></div>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v6.0&appId=2505132236168934&autoLogAppEvents=1"></script>
	<?php endif; ?>


	<div id="console">
	</div>

	<audio id="audio" src="" type="audio/mpeg"></audio>

	<script>
		function urialize(obj) {
			var str = [];
			for (var p in obj)
				if (obj.hasOwnProperty(p)) {
				str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
				}
			return str.join("&");
		}
	</script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="dist/jquery-ui.min.js"></script>
	<script src="https://cdn.jsdelivr.net/gh/yeikos/jquery.history/jquery.history.min.js"></script>

	<div id="game">
	</div>
</body>

<?php @include("ui-".UI.".php"); ?>

<script>
	var INIT_NUM = <?=json_encode(intval($_REQUEST['q']??null))?>;

	function zerolog(s,...args) {
		$("#console").append("<p>"+s+"</p>")
		console.log(s,...args)
	}

	zerolog("Booting YGSF OS v0.1.")
</script>

<script src="dist/bundle.js"></script>

	<script>
		/// #param score number

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

</html>