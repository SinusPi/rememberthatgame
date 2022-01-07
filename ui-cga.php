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
				<?php foreach ($PLATFORMS as $pf=>$pfl):?>
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
				<p>Correct guesses will, in some cases, be rewarded with various interesting tidbits about the game.</p>
				<p>Use the menu button to select your favourite game platforms.</p>
			</div>
			<div data-message="about">
				<p>Remember That Game? is a quiz for gamers, in which you'll have to guess game titles by fragments of their music, sound effects, bits of graphics or trivia.</p>
				<p>Correct guesses will, in some cases, be rewarded with various interesting tidbits about the game.</p>
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
					<a id="qnum">Question: #<span data-val></span></a>
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
					<div id='correct3'></div>
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

	<script src="ui-cga.class.js"></script>
	<script>
		var UI = new UI_CGA()
	</script>

<template data-name="question">
	<table>
		<colgroup><col><col width='100%'></colgroup>
		<tbody>
			<template data-name="question-a">
				<tr class='question'>
					<td class=star><img src='img/star-0.gif'></td>
					<td class='answer'></td>
				</tr>
			</template>
		</tbody>
	</table>
</template>
