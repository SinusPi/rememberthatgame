<div id="container" style="display:none;">
		<h2 id="title">
			<a class='titlelink' href="index.php"></a>
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
			<form id="prefform">
				<?php /*
				<div class="platforms">
				<?php foreach ($PLATFORMS as $pf=>$pfl):?>
					<div class="plat"><label for='pf_<?=$pf?>'><input type="checkbox" id="pf_<?=$pf?>" name=<?=$pf=="all"?'"pfall_discard"':'pf[]'?> value="<?=$pf?>" class="platcb cga-checkbox"></label></td><td><label for='pf_<?=$pf?>'><?=$pfl?></label></div>
				<?php endforeach; ?>
				</div>
				<a href="#" class="cyanbut apply">apply</a>
				*/ ?>
				<div class="resets">
					<div class="resetline"><a href="#" class="cyanbut" data-onclick="new_set">new game</a><div class="resetdesc">Play another set.<br>Your scores are saved.</div></div>
					<div class="resetline"><a href="#" class="cyanbut" data-onclick="reset_seen">restart</a><div class="resetdesc">Retry skipped questions.</div></div>
					<div class="resetline"><a href="#" class="cyanbut" data-onclick="reset_all">reset game</a><div class="resetdesc">Reset all your scores.</div></div>
				</div>
				<?php /*
				<div class="resets">
					<div class="resetline"><a href="#" class="cyanbut" data-onclick="reset_tut">reset tutorials</a><div class="resetdesc"></div></div>
				</div>
				*/ ?>
			</form>
		</div>

		<div id="messages">

			<div data-message="intro">
				<div class="logo">
					<img src="img/title.gif" width=800 alt="Your Game Sounds Familiar"/>
				</div>
				<h2>- The Game Music Quiz -</h2>

				<p><a class="cyanbut hash" id="start" href="selection">START</a></p>
			</div>

			<div data-message="selection">
				<div class="logo-mini">
					<img src="img/title.gif" width=400 alt="Your Game Sounds Familiar"/>
				</div>
				
				<h2>Pick a set:</h2>
				<div id="select-set">
					<?php foreach ($SETS as $set):?>
						<a class="set hash" href="start=<?=$set['slug']?>">
							<div class="label"><?=$set['label']?></div>
							<div class="description"><?=$set['description']?></div>
						</a>
					<?php endforeach; ?>
				</div>
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
				<p>The game has crashed!</p>
				<p data-var="error"></p>
			</div>
		</div>
		
		<div id="footer">
			<p class="small">
				<b>Contributors:</b> Sinus.Pi, FatalBomb
			</p>
		</div>

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
								<img id="play" class="playpause" src="img/but-play.gif" data-onclick="play">
								<img id="pause" class="playpause" src="img/but-pause.gif" data-onclick="pause">
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
					<form id="qform"><img id="gt" src="img/gt-c.gif"><input id="input" type="text"><a id="ok" class="cyanbut" data-onclick="guess">GUESS</a></form>
					<h2 id='correct1'>Correct!</h2>
					<h1 id='correct2'></h1>
					<div id='correct3'></div>
					<p><a id="next" class="cyanbut" href="#" data-onclick="next">NEXT</a></p>
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
				<div class="section">
					<div class="head set">Set</div>
					<div class="label set" data-template="{set-label}"></div>
				</div>
				<div class="section">
					<div class="head score">Score</div>
					<div class="label score" data-template="{score}">0</span></div>
					<div class="remaining" data-template="(remaining: {unseen} of {match})"></div>
				</div>
			</div>
		</div>
		<br>&nbsp;
	</div>

	<script src="ui-cga.class.js"></script>

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
