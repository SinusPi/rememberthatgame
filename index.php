<?php
define ("DO_FACEBOOK",true);

if (strpos($_SERVER['HTTP_USER_AGENT'],"Firefox")!==FALSE) $htmlclass="ua-ff";
?>
<html class="<?=$htmlclass?>">

<head>
	<title>Remember That Game?</title>
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="jquery-ui.min.js"></script>
	<meta property="fb:app_id" content="2505132236168934">
	<meta property="og:url"           content="http://djab.eu/remember-that-game" />
  	<meta property="og:type"          content="website" />
  	<meta property="og:title"         content="Remember That Game?" />
  	<meta property="og:description"   content="How many retro games can you recognize by music or by a piece of graphics?" />
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

	<div id="container" style="display:none;">
		<h2 id="title">
			<a class='titlelink' href="index.php">Remember That Game?</a>
			<a href="#" id="prefbut">
			<svg width="100%" height="100%" viewBox="0 0 50 50"  preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
 <g id="Layer_1">
  <rect height="8.06248" width="40" y="7.18743" x="5" fill="#00ffff"/>
  <rect height="8.06248" width="40" y="21.1874" x="5" fill="#00ffff"/>
  <rect height="8.06248" width="40" y="35.3123" x="5" fill="#00ffff"/>
 </g>
</svg>
			</a>
		</h2>

		<div id="prefs">
			<p class="caption">Select your old gaming platform(s):</p>
			<form id="prefform">
				<div class="platforms">
				<?php foreach (['all'=>"All", 'ZX'=>"ZX Spectrum",'Atari'=>"Atari",'C64'=>"C-64",'Amiga'=>"Amiga",'ST'=>"Atari ST",'PC'=>"PC",'NES'=>"NES, SNES",'PSX'=>"PlayStation",'Arcade'=>"Arcade"] as $pf=>$pfl):?>
					<div class="plat"><label for='pf_<?=$pf?>'><input type="checkbox" id="pf_<?=$pf?>" name=<?=$pf=="all"?'"pfall_discard"':'pf[]'?> value="<?=$pf?>" class="platcb cga-checkbox"></label></td><td><label for='pf_<?=$pf?>'><?=$pfl?></label></div>
				<?php endforeach; ?>
				</div>
				<div id="prefmatch"></div>
				<a href="#" class="cyanbut apply">apply</a>
				<br>
				
				<div class="resets">
					<a href="#" class="cyanbut reset_seen">reset seen</a>
					<a href="#" class="cyanbut reset_score">reset score</a>
					<a href="#" class="cyanbut reset_tut">reset tutorials</a>
				</div>
			</form>
		</div>

		<div id="messages">
			<div data-message="intro">
				<p>How well do you remember those old games?</p>
				<p>Relive the nostalgia and try to guess the game by music or graphics.</p>
				<p>Use the menu button to select your favourite game platforms.</p>
			</div>
			<div data-message="end">
				<p>That's all, folks!</p>
				<p>Thank you for playing! Sadly, there are no more questions for your chosen platform.</p>
				<p>Try a different gaming platform, or reset your progress and go challenge a friend!</p>
			</div>
			<div data-message="error">
				<p>Oops.</p>
				<p>Something isn't working right. Please reload me.</p>
			</div>
		</div>
		<p><a class="cyanbut" id="start" href="#">START</a></p>

		<div id="maincontainer">
			<div id="leftpane"></div>
			<div id="questionpane" style="visibility:hidden;">
				<div id="question">
					<a id="qnum"></a>
					<div id="qstats"></div>
					<audio id="audio" src="" type="audio/mpeg"></audio>
					<div id="subject">
						<div id="playercontrols">
							<canvas id="c"></canvas>
							<div id="slider">
								<div id="custom-handle" class="ui-slider-handle"></div>
							</div>
							<div id="control">
								<img id="play" class="playpause" src="img/but-play.gif">
								<img id="pause" class="playpause" src="img/but-pause.gif">
							</div>
							<!--
								<button onclick="Player.volume+=0.1">Vol +</button>
								<button onclick="Player.volume-=0.1">Vol -</button>
							-->
						</div>
						<div id="imageframe">
							<img id="image">
						</div>
					</div>
					<div id="questions"></div>
					<form id="qform"><img id="gt" src="img/gt-c.gif"><input id="input" type="text"><a id="ok" class="cyanbut">OK</a></form>
					<h2 id='correct1'>Correct!</h2>
					<h1 id='correct2'></h1>
					<h3 id='correct3'></h3>
					<p><a id="next" class="cyanbut" href="#">NEXT</a></p>
					<div class="tutorial" data-tutfor="next">Skip: Move on to the next question.</div>
					<!--
						<p><a id="hint" class="cyanbut" href="#" onclick="next_hint(); return false">SHOW ANSWER</a></p>
						<div class="tutorial" data-tutfor="hint">Give Up: Reveal answer to this question.</div>
					-->
					<div id="commentsbox">
						<!-- Your share button code -->
						<div class="fb-share-button" 
						data-href="http://djab.eu/remember-that-game/" 
						data-layout="button"></div>
						<div class="fb-comments" data-colorscheme="dark" data-href="http://djab.eu/remember-that-game/" data-width="600" data-numposts="5"></div>
					</div>
				</div>
			</div>
			<div id="scorepane">
				<div id="scorep">Score: <span class="score">0</span></div>
				<div class="remaining"></div>
			</div>
		</div>
		<br>&nbsp;
	</div>

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

		class RTG_GAME {
			Started = false
			Score = 0
			Q = [] // current question
			Prefs = {}
			Audio = {}

			Init() {
				this.SetupHistory()
				this.SetupAudio()
				this.InitPrefs(()=>this.OnInited())
			}

			InitPrefs(done) {
				let this_game=this
				$.get("q.php?init",function(data) {
					console.log("init",data)
					this_game.get_status_from_response(data)
					done()
				})
			}
			
			OnInited() {
				this.UI.Init()
			}

			SetupHistory() {
				window.addEventListener('popstate',(event)=>{
					if (event.state && event.state.q) this.load_question(event.state.q,true);
				})
			}

			SetupAudio() {
				this.Audio.player = $("#audio")[0]

				// analyser stuff
				var AudioContext = window.AudioContext || window.webkitAudioContext;
				this.Audio.context = new AudioContext()
				console.log("AudioContext created", this.Audio.context)
			}

			StartAudio() {
				try {
					console.log("resuming")
					this.Audio.context.resume();
					console.log("starting")
					this.Audio.player.play().catch((e)=>{
						console.error("failed to play",e)
						GAME.OnError("failed to play:"+e)
					})
				} catch (e) {
					console.error(e);
				}
			}

			OnError(msg) {
				this.UI.OnError(msg)
			}

			Start() {
				this.UI.OnStart && this.UI.OnStart()
				this.load_question(INIT_NUM,true)
				this.Started=true
			}

			registerUI(UI) {
				this.UI=UI
			}

			verify_question(q) {
				try {
					for (let s in q.scores) {
						let score=q.scores[s]
						if (!score.answer.toLowerCase().match(score.re)) {
							console.error("Bad answer "+s+": "+score.re+" doesn't match "+score.answer)
							throw "badanswer"+s;
						}
					}
					return true
				} catch (e) {
					$.ajax("/remember-that-game/feedback.php?q="+q.num+"&fb="+e) // report problem
					console.error("Failed verifying question "+q.num+":",e)
				}
			}

			SavePrefs(prefs) {
				console.log("GAME.SavePrefs",prefs)
				this.prefs = prefs
				let this_game=this
				$.get("q.php?do=pf&"+prefs.map(s=>`pf[]=${s}`).join("&"),function(data) {
					console.log("prefs saved:",data)
					this_game.get_status_from_response(data)
				})
			}


			load_question(num=null,dontpush=false) {
				this.Audio.player.pause()
				this.UI.OnQuestionLoading()
				let this_game=this
				$.get({
					url:"q.php?do=q"
						+(num?"&q="+num:"") // load specific q
						+"&"+urialize(this.Prefs) // save prefs
						+((this.Q && this.Q.num)?"&seen="+this.Q.num:""), //mark Q seen
					success:function(data, status, jqhxr) {
						console.log("q.php sends data:",data)
						this_game.OnQuestionReceived(data,dontpush)
					},
					error:function() {
						console.log("ERROR")
					}
				})
			}

			OnQuestionReceived(data,dontpush) {
				this.get_status_from_response(data)

				let q = data.q
						
				let ok = this.verify_question(q)
				if (!ok) {
					this.Subseq_errors++
					if (this.Subseq_errors>3) {
						this.UI.ShowMessage("error","Too many consecutive broken questions. Reload?")
						return
					}
					setTimeout(()=>this.load_question(),1000)
					return
				}
				this.Subseq_errors = 0

				console.log("Loaded and verified question "+q.num)

				if (!dontpush) {
					history.pushState({q:q.num},"Remember That Game? Question #"+q.num,"/remember-that-game/"+q.num)
					console.log("pushed state "+q.num)
				}

				this.Q = q
				this.UI.display_question(this.Q)
			}

			get_status_from_response(data) {
				if (data.prefs) {
					this.Prefs = data.prefs
					this.UI.OnPrefsChanged(this.Prefs)
				}
				
				if (data.totalscore!=null)
					this.Totalscore=data.totalscore
				if (data.score!=null)
					this.Setscore=data.score
				if (data.seen!=null)
					this.Totalseen=data.seen

				if (data.match) this.UI.OnMatchedChanged(data.match)

				this.UI.ShowScore(data)
			}

			OnAnswer(guess) {
				let m=0
				for (let s in this.Q.scores) {
					let score=this.Q.scores[s]
					if (guess.match(score.re)) {
						if (s>m) {
							m=s
						}
					}
				}
				if (m>0) {
					for (let i=1;i<=m;i++)
						this.UI.OnCorrect && this.UI.OnCorrect(i) // all correct up to current
					return true
				} else {
					this.UI.OnIncorrect && this.UI.OnIncorrect(guess)
					return false
				}
			}

			SaveGuessed(num=null) {
				let this_game=this
				$.get("q.php?guessed="+(num||this.Q.num),function(data) {
					console.log("saved guessed:",data)
					this_game.get_status_from_response(data)
				})
			}
		}

		var GAME = new RTG_GAME()

	</script>
	<script>
		/*
		 MM  MM  MMMMMM
		 MM  MM    MM
		 MM  MM    MM
		 MM  MM    MM
		  MMMM   MMMMMM
		*/
		class UI_CGA {

			Init() {
				this.init_checkboxes()
				this.init_spectrum()
				this.tutorials_load();

				this.InitAudioButtons(GAME.Audio)
				this.InitAudioSlider(GAME.Audio)
				this.FreqAnalyserSetup()

				let this_ui=this
				$('#input').keydown(function(e) { if (e.keyCode == 13) { e.preventDefault(); this_ui.OnAnswer(); return false } })
				$("#ok").click(function(e) { e.preventDefault(); this_ui.OnAnswer(); })
				$('#qform').submit(function(e) { e.preventDefault(); this_ui.OnAnswer(); return false })

				$("#prefbut").show().click(()=>{$("#prefs").slideToggle(); $("#prefbut").toggleClass("open"); return false});
				$("#prefform .apply").click(()=>{$("#prefs").slideUp(),$("#prefbut").removeClass("open"); if (GAME.Started) GAME.load_question(); return false})
				$("#prefform .reset_seen").click(()=>{$("#prefs").slideUp(),$("#prefbut").removeClass("open"),$.get("q.php?reset_seen=1",function() { GAME.Q=null; if (GAME.Started) GAME.load_question() }); return false});
				$("#prefform .reset_score").click(()=>{$("#prefs").slideUp(),$("#prefbut").removeClass("open"),$.get("q.php?reset_score=1",function() { GAME.Q=null; if (GAME.Started) GAME.load_question() }); return false});
				$("#prefform .reset_tut").click(()=>{$("#prefs").slideUp(),$("#prefbut").removeClass("open"),tutorials_reset(), GAME.Start(); return false});

				$("#start").click(()=>{ GAME.Start(); return false })
				$("#next").click(()=>{ GAME.load_question(); return false })
				$("#play").click(()=>{ GAME.StartAudio(); $('#input').focus(); return false})
				$("#pause").click(()=>{ GAME.Audio.player.pause(); $('#input').focus(); return false})

				$("#container").show()
				$("#maincontainer").hide()
				$("#start").show().focus()
				
				$("#play").show()
				$("#pause").hide()

				this.ShowMessage("intro")
			}

			OnIntro() {
			}

			OnStart() {
				$("#start,#messages").hide()
				$("#maincontainer,#leftpane,#scorepane").show()
			}

			dont_save_prefs=false

			OnPrefsChanged(prefs) {
				$("#prefform input[name='pf[]']").prop("checked",false)
				for (let p=0;p<prefs.length;p++)
					$("#prefform input[name='pf[]'][value='"+prefs[p]+"']").prop("checked",1)
				$(".cga-checkbox").trigger("update")
				this.update_pref_all()
			}
			OnMatchedChanged(match) {
				$("#prefmatch").html("Matching questions: "+match)
			}

			OnQuestionLoading() {
				$("#input").val("")
				$("#commentsbox").hide()
			}

			init_checkboxes() {
				let $checkboxes = $(".cga-checkbox")
				$checkboxes.css("opacity","0").wrap("<div class='cga-checkbox-wrap'></div>").parent().prepend("<img></img>");
				let this_ui=this
				$(".cga-checkbox").on("update",function() {
					$(this).closest(".cga-checkbox-wrap").find("img").attr("src","img/"+($(this).prop("checked")?"cb-y.gif":"cb-0.gif"))
				}).change(function() {
					if ($(this).val()=="all" && $(this).prop("checked")) {
						$("#prefform input[name='pf[]']").prop("checked",false).find("img").css("opacity",0.5)
					}
					this_ui.update_pref_all()
					this_ui.save_prefs()
				})
				this.update_pref_all()
			}

			update_pref_all() {
				let any_checked = $("input.platcb").not("[value=all]").is(":checked")
				if (!any_checked) $("input.platcb[value=all]").prop("checked",true)
				else $("input.platcb[value=all]").prop("checked",false)
				$("#prefform input[name='pf[]']").closest(".cga-checkbox-wrap").find("img").css("opacity",any_checked?"":"50%")
				$(".cga-checkbox:first-child()").trigger("update")
				//let platforms = $("input.platcb").toArray().reduce(((tot,cb)=>{tot[cb.value]=cb.checked; return tot}),{})
				//let not_all = 
			}
			save_prefs() {
				//console.log("platforms",platforms)
				GAME.SavePrefs($("#prefform").serializeArray().reduce((o,kv) => ([...o, kv.value]), []))
			}


			ShowScore(data) {  // use .totalscore, .score, .seen, .match
				$("#scorepane")
					.find(".score").html(data.score).end()
					.find(".remaining").html("(remaining: "+data.unseen+" of "+data.match+")")
			}

			OnIncorrect(guess) {
				console.log("BAD: ",guess)
				$("#input").effect("shake", {
					direction: "left"
				})
			}

			OnAnswer() {
				let guess=$("#input").val().toLowerCase()
				GAME.OnAnswer(guess)
			}

			display_question(q) {
				INIT_NUM=0

				// sanity check!

				if (q.num>0) {

					$("#messages").hide()
					$("#start").hide()

					$("#qnum").html("Question: #"+q.num).attr("href","/remember-that-game/"+q.num)
					
					$("#questionpane").css("visibility","visible").show()
					$("#correct1,#correct2,#correct3").hide()
					$("#qform").show()
					$("#input").focus()
					$("#next").show().html("SKIP")
					$("#hint").show().html(q.maxscore>1?"SHOW ANSWER":"SHOW ANSWER")
					this.tutorials_update()

					let details = ""
					if (q.year) details+="Year: "+q.year+"<br>"
					if (q.remix) details+="Remix!";
					$("#leftpane").html(details)

					/*
					if (s=data.scores[1]) {
						$q.append("<div class='question' id='score1'><img src='star-0.gif'><span class='answer'>"+questions.name+"</span></div>")
					}
					*/
					let $t=$("<table><colgroup><col><col width='100%'></colgroup><tbody></tbody></table>")
					let $b=$t.find("tbody")
					q.maxscore=0
					let s
					for (i=1;i<5;i++)
						if (s=q.scores[i]) {
							q.maxscore++
							$b.append("<tr class='question' id='score"+i+"'><td class=star><img src='img/star-0.gif'></td><td class='answer'>"+questions[s.tag]+"</td></tr>")
						}
					$("#questions").empty().append($t);

					if (q.type=="mp3") {
						$("#playercontrols").show()
						$("#imageframe").hide()
						$(GAME.Audio.player).attr("src", q.file)
						console.log("Loading "+q.file)
						GAME.StartAudio()
					} else if (q.type=="png" || q.type=="gif") {
						$("#playercontrols").hide()
						$("#imageframe").show()
						$("#imageframe #image").bind("load",function() { $(this).show() }).hide().attr("src",q.file)
					}
					
					let current_url = "http://"+location.hostname+"/remember-that-game/"+q.num
					$(".fb-comments").attr("data-href",current_url)
					$(".fb-share-button").attr("data-href",current_url)
					if (FB && FB.XFBML) {
						FB.XFBML.parse()
					}

				} else if (data.unseen === 0) {
					this.ShowMessage("end")
				} else {
					this.ShowMessage("error","Oops! Something crashed!")
				}
			}

			ShowMessage(type,err) {
				$("#questionpane,#leftpane,#scorepane").hide()
				$("#messages")
					.show()
					.find("[data-message]").hide().end()
					.find(`[data-message='${type}']`).show()
				if (type=="error" && err) $("#messages #error").html(err).show()
				$("#start").show()
			}

			OnCorrect(score,hint=false) {
				let color=""
				let $score = $("#score"+score)

				let Q = GAME.Q

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

				//GAME.Score=score			

				if (score==Q.maxscore) {

					if (!hint) {
						GAME.SaveGuessed()
					}
					
					//let a = Q.answer
					//if (platform) a += " (" + platform + ")"
					//if (year) a += " (" + year + ")"
					if (!hint) $("#correct1").show()
					//$("#correct2").show().html(a)
					if (Q.url) $("#correct3").show().html("<a target='_blank' href='"+Q.url+"'>more...</a>")
					$("#qform").hide()
					$("#hint").hide()

					$("#commentsbox").show()
					$(".fb-share-button").click(function(e) {
						FB.ui({
							method: 'share',
							href: $(this).attr("data-href"),
							quote: Hints ? "Can you recognize this game? I didn't!" : "Can you recognize this game? I did!"
						}, function(response){ console.log(response) });
					})
					
					setTimeout(()=>$("#next").html("NEXT").focus(),10)
				}
			}




			UpdateAudioSlider(Audio) {
				if (!Audio.player.paused && !Audio.player.ended && Audio.player.currentTime != oldTime)
					$("#slider").slider("value", Audio.player.currentTime / Audio.player.duration * 100)
			}

			InitAudioButtons(Audio) {
				$(Audio.player).on("playing", function(e) {
					console.log("playing!")
					$("#pause").show()
					$("#play").hide()
					$("#playercontrols").show()
				}).on("pause", function() {
					console.log("pause!")
					$("#pause").hide()
					$("#play").show()
				})
			}
			InitAudioSlider(Audio) {
				var handle = $("#custom-handle");
				$("#slider").slider({
					min: 0,
					max: 100,
					range: "min",
					create: function() {},
					slide: function(event, ui) {},
					start: () => Audio.player.pause(),
					stop: () => {
						Audio.player.currentTime = Audio.player.duration * $("#slider").slider("value") / 100
						GAME.StartAudio()
						$('#input').focus(); 
					}
				})
				setInterval(()=>this.UpdateAudioSlider(Audio), 100)
			}


			FreqDelay = 20
			FreqThen = 0
		
			FreqAnalyserSetup() {
				// canvas stuff
				this.freq_canvas = document.getElementById('c');
				this.freq_canvas_context = this.freq_canvas.getContext('2d');
				this.FreqAnalyserFrame()
			}

			FreqAnalyserFrame() {
				window.requestAnimFrame(()=>this.FreqAnalyserFrame()) //refire

				let now = Date.now()
				let elapsed = now - this.FreqThen
				if (elapsed<this.FreqDelay) return
				this.FreqThen=now-(elapsed%this.FreqDelay)
				
				let Audio = GAME.Audio

				// draw the analyser to the canvas
				var sum;
				var average;
				var scaled_average;
				var num_bars = 30;
				var data = new Uint8Array(2048);

				let canvas = this.freq_canvas
				let canvas_context = this.freq_canvas_context
				
				// clear canvas
				canvas_context.clearRect(0, 0, canvas.width, canvas.height);

				Audio.analyser.getByteFrequencyData(data);
				let bin_size = Math.floor(data.length / num_bars / 4);
				let bar_width = canvas.width / num_bars;

				// draw full bars, then draw a grid on top. Pixels, schmixels...
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

					let bar_height = (average / 225) // should be 256, but I WANT it oversized to account for the 0.1 cutoff below.
					bar_height = bar_height - (bar_height % 0.1)
					if (bar_height<0.5) {
						canvas_context.fillStyle="#ff00ff"
						canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -bar_height * canvas.height);
					} else if (bar_height<0.9) {
						canvas_context.fillStyle="#ff00ff"
						canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -0.5 * canvas.height);
						canvas_context.fillStyle="#00ffff"
						canvas_context.fillRect(i * bar_width, 0.5 * canvas.height, bar_width - 2, -(bar_height-0.5) * canvas.height);
					} else {
						canvas_context.fillStyle="#ff00ff"
						canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -0.5 * canvas.height);
						canvas_context.fillStyle="#00ffff"
						canvas_context.fillRect(i * bar_width, 0.5 * canvas.height, bar_width - 2, -0.4 * canvas.height);
						canvas_context.fillStyle="#ffffff"
						canvas_context.fillRect(i * bar_width, 0.1 * canvas.height, bar_width - 2, -(bar_height-0.9) * canvas.height);
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

				if (Audio.player.paused) return; // keep drawing freq when paused, let frequency die down slowly. Stop here and don't draw Oscilloscope when paused!

				Audio.analyser.getByteTimeDomainData(data)
				canvas_context.strokeStyle="white"
				canvas_context.lineWidth="6"
				bar_width=canvas.width/(num_bars)
				for (var i = 0; i < num_bars; i += 1) {
					sum = 0;
					for (var j = 0; j < bin_size; j += 1) {
						sum += data[(i * bin_size) + j];
					}
					average = sum / bin_size;
					average = average - average%15

					/*
					// lightning oscilloscope
					if (i==0) {
						canvas_context.beginPath()
						canvas_context.moveTo(0, (average/256) * canvas.height);
					} else canvas_context.lineTo(i * bar_width, (average/256) * canvas.height);
					*/

					let y = (average/256) * canvas.height
					canvas_context.beginPath()
					canvas_context.moveTo(i * bar_width, y)
					canvas_context.lineTo((i+0.85) * bar_width, y)
					canvas_context.stroke()

				}
				//canvas_context.stroke()
			}

			// Spectrum / Waveform
			init_spectrum() {
				let Audio = GAME.Audio

				Audio.analyser = Audio.context.createAnalyser()
				Audio.analyser.fftSize = 2048
				console.log("Analyser created", Audio.analyser)

				// connect the stuff up to eachother
				Audio.source = Audio.context.createMediaElementSource(Audio.player)
				Audio.source.connect(Audio.analyser);
				Audio.analyser.connect(Audio.context.destination);
			}



			// === TUTORIALS ===
				Tutshidden = {}

				tutorials_update() {
					let this_ui=this
					$(".tutorial").each(function() {
						let tutfor = $(this).data("tutfor")
						$(this).toggle(($("#"+tutfor).is(":visible")) && !this_ui.Tutshidden[tutfor])
					})
				}
				tutorials_save() {
					window.localStorage.setItem("tutshidden",JSON.stringify(this.Tutshidden))
				}
				tutorials_reset() {
					this.Tutshidden = {}
					tutorials_save()
				}
				tutorials_load() {
					this.Tutshidden = JSON.parse(window.localStorage.getItem("tutshidden")) || {}
					$(".tutorial").append("<div class='close mini'>x</div>")
					let this_ui=this
					$(".close").click(function() { this_ui.Tutshidden[$(this).parent().data("tutfor")]=true; this_ui.tutorials_save(); $(this).parent().hide() })
					this.tutorials_update()
				}

				OnError(err) {
					this.ShowMessage("error",err)
				}


		}

		var UI = new UI_CGA()
		GAME.registerUI(UI)

	</script>
	
	<script>
		var oldTime = 0

		var Totalscore=0
		var Setscore=0
		var Totalseen=0


		var questions = {
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

		$(() => {
			GAME.Init()
		})

	</script>
</body>

</html>