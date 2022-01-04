/*
		 MM  MM  MMMMMM
		 MM  MM    MM
		 MM  MM    MM
		 MM  MM    MM
		  MMMM   MMMMMM
		*/
class UI_CGA {

	// called by GAME: when engine initialization is complete
	Init() {
		this.init_checkboxes()
		this.init_spectrum()
		this.tutorials_load();

		this.InitAudioButtons(GAME.Audio)
		this.InitAudioSlider(GAME.Audio)
		this.FreqAnalyserSetup()

		let this_ui = this
		$('#input').keydown(function (e) { if (e.keyCode == 13) { e.preventDefault(); this_ui.OnAnswer(); return false } })
		$("#ok").click(function (e) { e.preventDefault(); this_ui.OnAnswer(); })
		$('#qform').submit(function (e) { e.preventDefault(); this_ui.OnAnswer(); return false })

		$("#prefbut").show().click(() => { $("#prefs").slideToggle(); $("#prefbut").toggleClass("open"); return false });
		$("#prefform .apply").click(() => { $("#prefs").slideUp(), $("#prefbut").removeClass("open"); if (GAME.Started) GAME.load_question(); return false })
		$("#prefform .reset_seen").click(() => { $("#prefs").slideUp(), $("#prefbut").removeClass("open"), $.get("q.php?reset_seen=1", function () { GAME.Q = null; if (GAME.Started) GAME.load_question() }); return false });
		$("#prefform .reset_score").click(() => { $("#prefs").slideUp(), $("#prefbut").removeClass("open"), $.get("q.php?reset_score=1", function () { GAME.Q = null; if (GAME.Started) GAME.load_question() }); return false });
		$("#prefform .reset_tut").click(() => { $("#prefs").slideUp(), $("#prefbut").removeClass("open"), tutorials_reset(), GAME.Start(); return false });

		$("#start").click(() => { GAME.Start(); return false })
		$("#next").click(() => { GAME.load_question(); return false })
		$("#play").click(() => { GAME.StartAudio(); $('#input').focus(); return false })
		$("#pause").click(() => { GAME.Audio.player.pause(); $('#input').focus(); return false })

		$("#container").show()
		$("#maincontainer").hide()
		$("#start").show().focus()

		$("#play").show()
		$("#pause").hide()

		this.ShowMessage("intro")
	}

	OnIntro() {
	}

	// called by GAME: needed?
	OnStart() {
	}

	dont_save_prefs = false

	// called by GAME: when user prefs arrive
	OnPrefsChanged(prefs) {
		$("#prefform input[name='pf[]']").prop("checked", false)
		for (let p = 0; p < prefs.length; p++)
			$("#prefform input[name='pf[]'][value='" + prefs[p] + "']").prop("checked", 1)
		$(".cga-checkbox").trigger("update")
		this.update_pref_all()
	}
	// called by GAME: when number of matched questions changes
	OnMatchedChanged(match) {
		$("#prefmatch").html("Matching questions: " + match)
	}

	// called by GAME: when engine starts loading the next question
	OnQuestionLoading() {
		$("#input").val("")
		$("#commentsbox").hide()
	}

	init_checkboxes() {
		let $checkboxes = $(".cga-checkbox")
		$checkboxes.css("opacity", "0").wrap("<div class='cga-checkbox-wrap'></div>").parent().prepend("<img></img>");
		let this_ui = this
		$(".cga-checkbox").on("update", function () {
			$(this).closest(".cga-checkbox-wrap").find("img").attr("src", "img/" + ($(this).prop("checked") ? "cb-y.gif" : "cb-0.gif"))
		}).change(function () {
			if ($(this).val() == "all" && $(this).prop("checked")) {
				$("#prefform input[name='pf[]']").prop("checked", false).find("img").css("opacity", 0.5)
			}
			this_ui.update_pref_all()
			this_ui.save_prefs()
		})
		this.update_pref_all()
	}

	update_pref_all() {
		let any_checked = $("input.platcb").not("[value=all]").is(":checked")
		if (!any_checked) $("input.platcb[value=all]").prop("checked", true)
		else $("input.platcb[value=all]").prop("checked", false)
		$("#prefform input[name='pf[]']").closest(".cga-checkbox-wrap").find("img").css("opacity", any_checked ? "" : "50%")
		$(".cga-checkbox:first-child()").trigger("update")
		//let platforms = $("input.platcb").toArray().reduce(((tot,cb)=>{tot[cb.value]=cb.checked; return tot}),{})
		//let not_all = 
	}
	save_prefs() {
		//console.log("platforms",platforms)
		GAME.SavePrefs($("#prefform").serializeArray().reduce((o, kv) => ([...o, kv.value]), []))
	}


	// called by GAME: when score data arrives
	ShowScore(data) {  // use .totalscore, .score, .seen, .match
		$("#scorepane")
			.find(".score").html(data.score).end()
			.find(".remaining").html("(remaining: " + data.unseen + " of " + data.match + ")")
	}

	OnGameAutoplayFailed() { // if user didn't interact with page, but page tried to play audio

	}

	// called by GAME: to show a message
	ShowMessage(type, err) {
		$("#questionpane,#leftpane,#scorepane").hide()
		$("#messages")
			.show()
			.find("[data-message]").hide().end()
			.find(`[data-message='${type}']`).show()
		if (type == "error" && err) $("#messages #error").html(err).show()
		$("#start").show()
	}

	// internal: when user types an answer
	OnAnswer() {
		let guess = $("#input").val().toLowerCase()
		GAME.OnAnswer(guess)
	}

	// called by GAME: bad answer
	ShowIncorrect(guess) {
		$("#input").effect("shake", {
			direction: "left"
		})
	}

	// called by GAME: correct answer
	ShowCorrect(score, hint = false) {
		let color = ""
		let $score = $("#score" + score)

		let Q = GAME.Q

		let score_index = score-1
		if (Q.scores[score_index].answer) $score.find(".answer").text(Q.scores[score_index].answer);
		if (!hint) {
			let $img = $score.find("img")
			if (score == 1) color = "m";
			else if (score == 2) color = "c";
			else if (score == 3) color = "w";
			//$img.attr("src","")
			setTimeout(() => $img.attr("src", "img/star-" + color + "a.gif"));
		}

		$("#input").val("").focus()

		//GAME.Score=score			

		if (score == Q.maxscore) {

			if (!hint) {
				GAME.SaveGuessed()
			}

			//let a = Q.answer
			//if (platform) a += " (" + platform + ")"
			//if (year) a += " (" + year + ")"
			if (!hint) $("#correct1").show()
			//$("#correct2").show().html(a)
			
			//trivia
			let $c3 = $("#correct3")
			$c3.empty()
			if (Q.url) $c3.append("<p><a target='_blank' href='" + Q.url + "'>Click here</a> for the full track.</p>")
			if (Q.composer) $c3.append(`<p>Composer: ${Q.composer}</p>`)
			if (Q.publisher) $c3.append(`<p>Publisher: ${Q.publisher}</p>`)
			if (Q.trivia) $c3.append(`<p><b>Trivia<b></p>${Q.trivia.map(s=>"<p>"+s+"</p>").join("")}</p>`)
			$c3.toggle($c3[0].childNodes.length>0)

			$("#qform").hide()
			$("#hint").hide()

			$("#commentsbox").show()
			$(".fb-share-button").click(function (e) {
				FB.ui({
					method: 'share',
					href: $(this).attr("data-href"),
					quote: Hints ? "Can you recognize this game? I didn't!" : "Can you recognize this game? I did!"
				}, function (response) { console.log(response) });
			})

			setTimeout(() => $("#next").html("NEXT").focus(), 10)
		}
	}



	// called by GAME: to show a question
	ShowQuestion(q) {
		INIT_NUM = 0

		// sanity check!

		if (q.num > 0) {

			$("#messages,#start").hide()
			$("#maincontainer,#leftpane,#scorepane").show()

			let location_raw = window.location.toString().replace(/\/\d+$/,"")
			$("#qnum").attr("href", location_raw + "/" + q.num).find("[data-val]").html(q.num)

			$("#questionpane").css("visibility", "visible").show()
			$("#correct1,#correct2,#correct3").hide()
			$("#qform").show()
			$("#input").focus()
			$("#next").show().html("SKIP")
			$("#hint").show().html(q.maxscore > 1 ? "SHOW ANSWER" : "SHOW ANSWER")
			this.tutorials_update()

			let details = ""
			if (q.year) details += "Year: " + q.year + "<br>"
			if (q.remix) details += "Remix!";
			$("#leftpane").html(details)

			/*
			if (s=data.scores[1]) {
				$q.append("<div class='question' id='score1'><img src='star-0.gif'><span class='answer'>"+questions.name+"</span></div>")
			}
			*/
			let qta = document.querySelector("template[data-name=question]").content
			let $b = $("#questions").empty().append(qta.cloneNode(true)).children(":last").find("tbody")
			let qtb = qta.querySelector("template[data-name=question-a]").content
			q.maxscore = 0
			let s
			//let qt = document.querySelector("template[data-name=question]").content.cloneNode(true)
			for (let s of q.scores) {
				console.log("Score",s)
				q.maxscore++
				let $a = $b.append(qtb.cloneNode(true)).children(":last")
				$a
					.attr("id","score"+q.maxscore)
					.find(".answer").html(questionNames[s.name])
				
				//$b.append("<tr class='question' id='score" + i + "'><td class=star><img src='img/star-0.gif'></td><td class='answer'>" + questions[s.tag] + "</td></tr>")
			}

			if (q.type == "mp3") {
				$("#playercontrols").show()
				$("#imageframe").hide()
				$(GAME.Audio.player).attr("src", q.file)
				console.log("Loading " + q.file)
				GAME.StartAudio()
			} else if (q.type == "png" || q.type == "gif") {
				$("#playercontrols").hide()
				$("#imageframe").show()
				$("#imageframe #image").bind("load", function () { $(this).show() }).hide().attr("src", q.file)
			}

			let current_url = window.location //"http://" + location.hostname + "/remember-that-game/" + q.num
			$(".fb-comments").attr("data-href", current_url)
			$(".fb-share-button").attr("data-href", current_url)
			if (FB && FB.XFBML) {
				FB.XFBML.parse()
			}

		} else if (data.unseen === 0) {
			this.ShowMessage("end")
		} else {
			this.ShowMessage("error", "Oops! Something crashed!")
		}
	}



	UpdateAudioSlider(Audio) {
		if (!Audio.player.paused && !Audio.player.ended && Audio.player.currentTime != oldTime)
			$("#slider").slider("value", Audio.player.currentTime / Audio.player.duration * 100)
	}

	InitAudioButtons(Audio) {
		$(Audio.player).on("playing", function (e) {
			console.log("playing!")
			$("#pause").show()
			$("#play").hide()
			$("#playercontrols").show()
		}).on("pause", function () {
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
			create: function () { },
			slide: function (event, ui) { },
			start: () => Audio.player.pause(),
			stop: () => {
				Audio.player.currentTime = Audio.player.duration * $("#slider").slider("value") / 100
				GAME.StartAudio()
				$('#input').focus();
			}
		})
		setInterval(() => this.UpdateAudioSlider(Audio), 100)
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
		window.requestAnimFrame(() => this.FreqAnalyserFrame()) //refire

		let now = Date.now()
		let elapsed = now - this.FreqThen
		if (elapsed < this.FreqDelay) return
		this.FreqThen = now - (elapsed % this.FreqDelay)

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
			let average = 0
			for (var j = 0; j < bin_size; j += 1) {
				average = Math.max(average, data[(i * bin_size) + j]);
			}

			let bar_height = (average / 225) // should be 256, but I WANT it oversized to account for the 0.1 cutoff below.
			bar_height = bar_height - (bar_height % 0.1)
			if (bar_height < 0.5) {
				canvas_context.fillStyle = "#ff00ff"
				canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -bar_height * canvas.height);
			} else if (bar_height < 0.9) {
				canvas_context.fillStyle = "#ff00ff"
				canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -0.5 * canvas.height);
				canvas_context.fillStyle = "#00ffff"
				canvas_context.fillRect(i * bar_width, 0.5 * canvas.height, bar_width - 2, -(bar_height - 0.5) * canvas.height);
			} else {
				canvas_context.fillStyle = "#ff00ff"
				canvas_context.fillRect(i * bar_width, canvas.height, bar_width - 2, -0.5 * canvas.height);
				canvas_context.fillStyle = "#00ffff"
				canvas_context.fillRect(i * bar_width, 0.5 * canvas.height, bar_width - 2, -0.4 * canvas.height);
				canvas_context.fillStyle = "#ffffff"
				canvas_context.fillRect(i * bar_width, 0.1 * canvas.height, bar_width - 2, -(bar_height - 0.9) * canvas.height);
			}
		}
		canvas_context.strokeStyle = "black"
		canvas_context.lineWidth = "2"
		for (i = 0; i < 1; i += 0.1) {
			let h = Math.floor(i * canvas.height)
			canvas_context.beginPath()
			canvas_context.moveTo(0, h)
			canvas_context.lineTo(canvas.width, h)
			canvas_context.stroke()
		}

		if (Audio.player.paused) return; // keep drawing freq when paused, let frequency die down slowly. Stop here and don't draw Oscilloscope when paused!

		Audio.analyser.getByteTimeDomainData(data)
		canvas_context.strokeStyle = "white"
		canvas_context.lineWidth = "6"
		bar_width = canvas.width / (num_bars)
		for (var i = 0; i < num_bars; i += 1) {
			sum = 0;
			for (var j = 0; j < bin_size; j += 1) {
				sum += data[(i * bin_size) + j];
			}
			average = sum / bin_size;
			average = average - average % 15

			/*
			// lightning oscilloscope
			if (i==0) {
				canvas_context.beginPath()
				canvas_context.moveTo(0, (average/256) * canvas.height);
			} else canvas_context.lineTo(i * bar_width, (average/256) * canvas.height);
			*/

			let y = (average / 256) * canvas.height
			canvas_context.beginPath()
			canvas_context.moveTo(i * bar_width, y)
			canvas_context.lineTo((i + 0.85) * bar_width, y)
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
		let this_ui = this
		$(".tutorial").each(function () {
			let tutfor = $(this).data("tutfor")
			$(this).toggle(($("#" + tutfor).is(":visible")) && !this_ui.Tutshidden[tutfor])
		})
	}
	tutorials_save() {
		window.localStorage.setItem("tutshidden", JSON.stringify(this.Tutshidden))
	}
	tutorials_reset() {
		this.Tutshidden = {}
		tutorials_save()
	}
	tutorials_load() {
		this.Tutshidden = JSON.parse(window.localStorage.getItem("tutshidden")) || {}
		$(".tutorial").append("<div class='close mini'>x</div>")
		let this_ui = this
		$(".close").click(function () { this_ui.Tutshidden[$(this).parent().data("tutfor")] = true; this_ui.tutorials_save(); $(this).parent().hide() })
		this.tutorials_update()
	}

	OnError(err) {
		this.ShowMessage("error", err)
	}
}