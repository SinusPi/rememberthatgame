<?php
define ("DO_FACEBOOK",true);
?>
<html>

<head>
	<title>Remember That Game?</title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="jquery-ui.min.js"></script>
	<meta property="og:description" content="Test your knowledge of retro game music. Re-live those 8-bit earworms!">
	<meta property="fb:app_id" content="2505132236168934">
	<style>
	</style>
</head>

<body>
	
	<?php if (DO_FACEBOOK): ?>
		<div id="fb-root"></div>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v6.0&appId=2505132236168934&autoLogAppEvents=1"></script>
	<?php endif; ?>

	<div id="container" style="display:none;">
		<h2 id="title">
			Remember That Game?
			<div id="prefbut">==</div>
		</h2>

		<div id="prefs">
			<form id="prefform">
				<table>
				<?php foreach (['ZX'=>"ZX Spectrum",'Atari'=>"Atari",'C64'=>"C-64",'Amiga'=>"Amiga",'ST'=>"Atari ST",'PC'=>"PC",'NES'=>"NES, SNES",'PSX'=>"PlayStation",'Arcade'=>"Arcade"] as $pf=>$pfl):?>
					<tr><td><input type="checkbox" id="pf_<?=$pf?>" name="pf[]" value="<?=$pf?>" class="cga-checkbox"></td><td><label for='pf_<?=$pf?>'><?=$pfl?></label></td></tr>
				<?php endforeach; ?>
				</table>
				<div id="prefmatch"></div>
				<a href="#" class="cyanbut apply">apply</a>
				<br><br>
				<a href="#" class="reset">reset history</a>
			</form>
		</div>

		<div id="intro">
			<p>How well do you remember those earworm tunes?</p>
			<p>Relive the nostalgia and try to guess to which game each music belongs.</p>
			<p>Use the Preferences panel to select your favourite game platforms.</p>
		</div>
		<p><a id="start" href="#" onclick="start(); return false">START</a></p>

		<a id="qnum"></a>
		<div id="qstats"></div>
		<audio id="audio">
			<source src="" type="audio/mpeg">
		</audio>
		<div id="playercontrols">
			<canvas id="c"></canvas>
			<div id="slider">
				<div id="custom-handle" class="ui-slider-handle"></div>
			</div>
			<div id="control">
				<img id="play" class="playpause" src="img/but-play.gif" onclick="Player.play(); return false">
				<img id="pause" class="playpause" src="img/but-pause.gif" onclick="Player.pause(); return false">
			</div>
			<!--
				<button onclick="Player.volume+=0.1">Vol +</button>
				<button onclick="Player.volume-=0.1">Vol -</button>
			-->
		</div>
		<div id="questions"></div>
		<p><input id="input" type="text"></p>
		<h2 id='correct1'>Correct!</h2>
		<h1 id='correct2'></h1>
		<h3 id='correct3'></h3>
		<p><a id="next" href="#" onclick="load_question(); return false">NEXT</a></p>
		<p><a id="hint" href="#" onclick="next_hint(); return false">GIVE UP</a></p>
		<div id="commentsbox">
			<div class="fb-comments" data-colorscheme="dark" data-href="https://djab.eu/remember-that-game/" data-width="600" data-numposts="5"></div>			
		</div>
		<br>&nbsp;
	</div>
	<script>
		var Q = []

		var init_num = <?=intval($_REQUEST['q'])?>

		function init() {
			$("#container").show()
			$("#audio,#input,#correct1,#correct2,#next,#hint,#playercontrols").hide()
			$("#start").show().focus()
		}

		function init_player() {
			let $player = $("audio")
			Player = $player[0]
			$player.on("playing", function(e) {
				$("#pause").show()
				$("#play").hide()
				$("#playercontrols").show()
			}).on("pause", function() {
				$("#pause").hide()
				$("#play").show()
			})
			setInterval(player_position, 100);

			var handle = $("#custom-handle");
			$("#slider").slider({
				min: 0,
				max: 100,
				range: "min",
				create: function() {},
				slide: function(event, ui) {},
				start: () => Player.pause(),
				stop: () => {
					Player.currentTime = Player.duration * $("#slider").slider("value") / 100
					Player.play()
				}
			});
		}

		var oldTime = 0

		var Started=false
		var Score=0
		var Hints=0


		function player_position() {
			if (!Player.paused && !Player.ended && Player.currentTime != oldTime)
				$("#slider").slider("value", Player.currentTime / Player.duration * 100)
		}

		function start() {
			$("#start,#intro").hide()
			load_question(init_num)
			Started=true
		}

		var Player = null;

		var questions = {
			name: "Name of the game",
			fullname: "Full name",
			character: "Character name",
			title: "Song title"
		}
		function load_question(num=null,dontpush=false) {
			$("#input").val("")
			$("#commentsbox").hide()
			$.get({
				url:"q.php?_"+(num?"&q="+num:"")+"&"+($("#prefform").serialize())+(Q.num?"&seen="+Q.num:""),
				success:function(data, status, jqhxr) {
					Score=0
					init_num=0
					console.log("Got q:",data)
					if (data.num) {
						$("#qnum").html("Question: #"+data.num).attr("href","/remember-that-game/"+data.num)
						$("#qstats").html("(selected: "+data.match+", unseen: "+data.unseen+", total: "+data.total+")")
						$("#correct1,#correct2,#correct3").hide()
						$("#audio,#input,#hint,#next").show()

						let $q=$("#questions")
						$q.empty()
						/*
						if (s=data.scores[1]) {
							$q.append("<div class='question' id='score1'><img src='star-0.gif'><span class='answer'>"+questions.name+"</span></div>")
						}
						*/
						data.maxscore=0
						for (i=1;i<5;i++)
							if (s=data.scores[i]) {
								data.maxscore++
								$q.append("<div class='question' id='score"+i+"'><img src='img/star-0.gif'><span class='answer'>"+questions[s.tag]+"</span></div>")
							}

						Q=data

						if (!dontpush) history.pushState({q:data.num},"Remember That Game? Question #"+data.num,"/remember-that-game/"+data.num)
						
						$(Player).attr("src", data.mp3)[0].play()
						startAudio()
						
						$("#input").focus()
						$("#next").html("SKIP")
						$("#hint").html(Q.maxscore>1?"GIVE UP":"GIVE UP")
						
						$(".fb-comments").attr("data-href",location.hostname+"/remember-that-game/"+data.num)
						if (FB && FB.XFBML) FB.XFBML.parse()
					}
				},
				error:function() {
					console.log("ERROR")
				}
			})
		}

		window.addEventListener('popstate',(event)=>{
			if (event.state && event.state.q) load_question(event.state.q,true);
		})

		function test() {
			let m=0
			for (s in Q.scores) {
				let score=Q.scores[s]
				if ($("#input").val().toLowerCase().match(score.re)) {
					if (s>m) {
						m=s
					}
				}
			}
			if (m>0) {
				for (i=1;i<=m;i++)
					show_correct(i)
			} else {
				show_incorrect()
			}
		}

		/// #param score number

		function show_correct(score,hint=false) {
			let color=""
			let $score = $("#score"+score)

			if (Q.scores[score].answer) $score.find(".answer").text(Q.scores[score].answer);
			if (!hint) {
				let $img = $score.find("img")
				if (score==1) color="m";
				else if (score==2) color="c";
				else if (score==3) color="w";
				//$img.attr("src","")
				setTimeout(()=>$img.attr("src","img/star-"+color+"a.gif"));
			}

			$("#input").val("").focus()

			Score=score			

			if (score==Q.maxscore) {
				//let a = Q.answer
				//if (platform) a += " (" + platform + ")"
				//if (year) a += " (" + year + ")"
				if (!hint) $("#correct1").show()
				//$("#correct2").show().html(a)
				if (Q.url) $("#correct3").show().html("<a href='"+Q.url+"'>more...</a>")
				$("#input").hide()
				$("#hint").hide()
				$("#commentsbox").show()
				setTimeout(()=>$("#next").html("NEXT").focus(),10)
			}
		}
		function next_hint() {
			Score++
			show_correct(Score,true)
			$("#hint").html(Score<Q.maxscore-1?"GIVE UP":"GIVE UP")
		}

		function show_incorrect() {
			console.log("BAD")
			$("#input").effect("shake", {
				direction: "left"
			})
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
		function init_checkboxes() {
			let $checkboxes = $(".cga-checkbox")
			$checkboxes.css("opacity","0").wrap("<div class='cga-checkbox-wrap'></div>").parent().prepend("<img></img>");
			$(".cga-checkbox-wrap").click(function(ev) {
				let $input = $(this).find("input.cga-checkbox")
				$input.prop("checked",!$input.prop("checked")).change()
			})
			let dont_update_pref_match=false
			$(".cga-checkbox").change(function() {
				$(this).closest(".cga-checkbox-wrap").find("img").attr("src","img/"+($(this).prop("checked")?"cb-y.gif":"cb-0.gif"))
				window.localStorage.setItem("prefs",JSON.stringify($("#prefform").serializeArray()))
				if (!dont_update_pref_match) 
					$.get("q.php?q=1&"+$("#prefform").serialize(),function(data) {
						console.log(data)
						if (data.match) $("#prefmatch").html("Matching questions: "+data.match)
					})
			})
			let prefs = window.localStorage.getItem("prefs")
			if (prefs!=null) prefs=JSON.parse(prefs)
			if (typeof(prefs)=="object")
				for (pref of prefs)
					$("#prefform input[name='"+pref.name+"'][value='"+pref.value+"']").prop("checked",1)
			dont_update_pref_match=true
			$(".cga-checkbox").change()
			dont_update_pref_match=false
			$(".cga-checkbox:first-child()").change()
		}

		$(() => {
			init()
			init_player()
			//init_tristates()
			init_checkboxes()
			$('#input').keydown(function(e) {
				if (e.keyCode == 13) {
					test()
				}
			})
			$("#prefbut").click(()=>{$("#prefform").slideToggle(); return false});
			$("#prefform .apply").click(()=>{$("#prefform").slideUp(); if (Started) load_question(); return false})
			$("#prefform .reset").click(()=>{$("#prefform").slideUp(),$.get("q.php?reset=1",function() { if (Started) load_question() }); return false});
		})

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


		// Spectrum / Waveform
		{

			// canvas stuff
			var canvas = document.getElementById('c');
			var canvas_context = canvas.getContext('2d');

			// audio stuff
			var audio = $("#audio")[0];

			var analyser = null
			var context = null
			var source = null

			// analyser stuff
			var AudioContext = window.AudioContext || window.webkitAudioContext;
			context = new AudioContext()
			console.log("context", context)

			analyser = context.createAnalyser();
			analyser.fftSize = 2048;
			console.log("analyser", analyser)

			// connect the stuff up to eachother
			source = context.createMediaElementSource(audio);
			source.connect(analyser);
			analyser.connect(context.destination);

			function startAudio() {
				context.resume();
				console.log("starting")
				freqAnalyser();
				audio.play()
			}

			// draw the analyser to the canvas
			function freqAnalyser() {
				window.requestAnimFrame(freqAnalyser);
				var sum;
				var average;
				var scaled_average;
				var num_bars = 30;
				var data = new Uint8Array(2048);
				
				// clear canvas
				canvas_context.clearRect(0, 0, canvas.width, canvas.height);

				analyser.getByteFrequencyData(data);
				let bin_size = Math.floor(data.length / num_bars / 4);
				let bar_width = canvas.width / num_bars;

				for (var i = 0; i < num_bars; i += 1) {

					/*
					sum = 0;
					for (var j = 0; j < bin_size; j += 1) {
						sum += data[(i * bin_size) + j];
					}
					average = sum / bin_size;
					*/
					let average=0
					for (var j = 0; j < bin_size; j += 1) {
						average = Math.max(average,data[(i * bin_size) + j]);
					}

					scaled_average = (average / 256) ;
					if (scaled_average<0.5) {
						canvas_context.fillStyle="#ff00ff"
						canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -scaled_average * canvas.height);
					} else if (scaled_average<0.9) {
						canvas_context.fillStyle="#ff00ff"
						canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -0.5 * canvas.height);
						canvas_context.fillStyle="#00ffff"
						canvas_context.fillRect(i * bar_width, 0.5 * canvas.height, bar_width - 2, -(scaled_average-0.5) * canvas.height);
					} else {
						canvas_context.fillStyle="#ff00ff"
						canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -0.5 * canvas.height);
						canvas_context.fillStyle="#00ffff"
						canvas_context.fillRect(i * bar_width, 0.5 * canvas.height, bar_width - 2, -0.4 * canvas.height);
						canvas_context.fillStyle="#ffffff"
						canvas_context.fillRect(i * bar_width, 0.1 * canvas.height, bar_width - 2, -(scaled_average-0.9) * canvas.height);
					}
				}
				canvas_context.strokeStyle="black"
				canvas_context.lineWidth="2"
				for (i=0;i<1;i+=0.1) {
					let h = Math.floor(i*canvas.height)
					canvas_context.beginPath()
					canvas_context.moveTo(0,h)
					canvas_context.lineTo(canvas.width,h)
					canvas_context.stroke()
				}


				if (audio.paused) return;

				analyser.getByteTimeDomainData(data)
				canvas_context.strokeStyle="white"
				canvas_context.lineWidth="4"
				bar_width=canvas.width/(num_bars-1)
				for (var i = 0; i < num_bars; i += 1) {
					sum = 0;
					for (var j = 0; j < bin_size; j += 1) {
						sum += data[(i * bin_size) + j];
					}
					average = sum / bin_size;
					average = average - average%15

					if (i==0) {
						canvas_context.beginPath()
						canvas_context.moveTo(0, (average/256) * canvas.height);
					} else canvas_context.lineTo(i * bar_width, (average/256) * canvas.height);
				}
				canvas_context.stroke()
			}
		}
	</script>
</body>

</html>