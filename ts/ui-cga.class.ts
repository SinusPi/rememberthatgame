import {YGSF_GAME} from "./rtg_game.class"
import {GameUI} from "./gameui.class"

/*
		 MM  MM  MMMMMM
		 MM  MM    MM
		 MM  MM    MM
		 MM  MM    MM
		  MMMM   MMMMMM
*/

class UI_CGA implements GameUI {

	GAME:YGSF_GAME
	freq_canvas: HTMLCanvasElement
	freq_canvas_context: CanvasRenderingContext2D | null

	// called by GAME: when engine initialization starts. Only "static" initialization here.
	Init(GAME:YGSF_GAME) {
		GAME.llog("CGA UI initing...")
		this.GAME = GAME

		this.init_checkboxes()
		this.tutorials_load();

		this.SetupHistory()
		
		let this_ui = this
		$('#input').keydown(function (e) { if (e.keyCode == 13) { e.preventDefault(); this_ui.OnAnswer(); return false } })
		$("[data-onclick=guess]").click(function (e) { e.preventDefault(); this_ui.OnAnswer(); })
		$('#qform').submit(function (e) { e.preventDefault(); this_ui.OnAnswer(); return false })

		$("#prefbut").show().click(_=>{ /*this.update_pref_all();*/ $("#prefs").slideToggle(); $("#prefbut").toggleClass("open"); return false });
		//$("#prefform .apply").click(_=>{ this.Close_Menu(); if (GAME.Started) GAME.NextQuestion(); return false })
		$("[data-onclick=new_set]").click(_=>{ this.Close_Menu(), this.Go(""); return false });
		$("[data-onclick=reset_seen]").click(_=>{ this.Close_Menu(), GAME.Reset({seen:1}, _=>GAME.NextQuestion() ); return false });
		//$("[data-onclick=reset_score]").click(_=>{ this.Close_Menu(), GAME.Reset({score:1}, _=>GAME.NextQuestion() ); return false });
		$("[data-onclick=reset_tut]").click(_=>{ this.Close_Menu(), GAME.NextQuestion(); return false });
		$("[data-onclick=reset_all]").click(_=>{ this.Close_Menu(), GAME.Reset({all:1}, _=>this.Go("") ); return false });

		$("[data-onclick=start]").click(_=>{ GAME.NextQuestion(); return false })
		$("[data-onclick=show_selection]").click(_=>{ this.Go("selection"); return false })
		$("[data-onclick=show_share]").click(_=>{ this.ShowPopup("share"); return false })
		$("[data-onclick=pick_set]").click(function(e) { this_ui.GAME.State.set=$(this).data("set"); this_ui.GAME.SaveState(); this_ui.Go("difficulty"); return false })
		$("[data-onclick=pick_diff]").click(function(e) { this_ui.GAME.State.diff=$(this).data("diff"); this_ui.GAME.SaveState(); this_ui.Route(`start=${this_ui.GAME.State.set}/${this_ui.GAME.State.diff}`); return false })
		$("[data-onclick=next]").click(e=>{ GAME.NextQuestion(null,false,true); e.preventDefault(); return false })
		$("[data-onclick=play]").click(_=>{ GAME.StartAudio().then(_=>$('#input').focus()); return false })
		$("[data-onclick=pause]").click(_=>{ GAME.Audio.player.pause(); $('#input').focus(); return false })

		$("#container").show()
		$("#maincontainer").hide()
		$("#start").show().focus()

		$("#play").show()
		$("#pause").hide()

		GAME.llog("CGA UI inited done, ready to show stuff in all cyan and magenta.")
	}

	SetupHistory() {
		/*
		window.addEventListener('popstate', (event) => {
			if (event.state && event.state.q) this.NextQuestion(event.state.q, true, false);
		})
		$("a[data-href]").each(function(i) {
			$(this).attr("href",$(this).data("href"))
		})
		*/

		// setup jQuery-history
		$.history.on('load change push pushed', (event, url, type) => {
			if (event.type=="change" || event.type=="load") {
				console.log("History event:",event.type,url,location.hash)
				this.Route(url || location.hash || "intro")
			} else {
				//alert(event.type + ': ' + url);
			}
		})

		let thisUI = this
		$('body').on('click', 'a.hash', function(event) {
			//alert(event.type)
			thisUI.Go($(this).attr('href'))
			event.preventDefault()
		});
	}

	OnAudioReady() {
		this.SetupAudioUI()
	}

	OnReady() {
		$.history.listen('hash') // possibly fire this.Route immediately
		if (!location.hash) this.Route("")
		if ("INIT_NUM" in window) this.GAME.NextQuestion()
	}

	Close_Menu() {
		$("#prefs").slideUp()
		$("#prefbut").removeClass("open")
	}

	Go(href) {
		console.log("Pushing:",href)
		$.history.push(href)
		this.Route(href)
	}

	Route(name:string,arg?:string) {
		if (name.indexOf("=")>-1) [name,arg]=name.split("=")
		
		console.log("Route:",name,arg)
		if (name=="" || name=="intro") {
			this.ShowMessage("intro")
		} else if (name=="selection") {
			this.ShowSets()
		} else if (name=="difficulty") {
			this.ShowMessage("difficulty")
		} else if (name=="start") {
			let set,diff,q
			if (arg && arg.indexOf("/")>-1) [set,diff,q]=arg.split("/")
			this.GAME.State.set=set
			this.GAME.State.diff=diff
			//this.GAME.SavePrefs({set:set,diff:diff}).then(_=>this.Route(q?`q=${q}`:"next"))
			this.Route(q?`q=${q}`:"next")
		} else if (name=="q") {
			this.GAME.NextQuestion(arg)
		} else if (name=="next") {
			this.GAME.NextQuestion()
		} else {
			alert(name+": "+arg)
		}
	}

	OnIntro() {
	}

	// called by GAME: needed?
	OnStart() {
	}

	SetupAudioUI() {
		this.InitAudioButtons(this.GAME.Audio)
		this.InitAudioSlider(this.GAME.Audio)
		this.InitFreqAnalyser()
		console.log("CGA audioplayer visuals are ready.")
	}

	dont_save_prefs = false

	// called by GAME: when user prefs arrive
	OnPrefsChanged(prefs) {
		$("#prefform input[name='pf[]']").prop("checked", false)
		for (let p = 0; p < prefs.length; p++)
			$("#prefform input[name='pf[]'][value='" + prefs[p] + "']").prop("checked", 1)
		$(".cga-checkbox").trigger("update")
		//this.update_pref_all()
	}
	// called by GAME: when number of matched questions changes
	OnMatchedChanged(match,unseen) {
		$("#prefmatch").fillAllTemplates({match:match,unseen:unseen})
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
		return
		let any_checked = $("input.platcb").not("[value=all]").is(":checked")
		if (!any_checked) $("input.platcb[value=all]").prop("checked", true)
		else $("input.platcb[value=all]").prop("checked", false)
		$("#prefform input[name='pf[]']").closest(".cga-checkbox-wrap").find("img").css("opacity", any_checked ? "" : "50%")
		$(".cga-checkbox:first-child()").trigger("update")
		//let platforms = $("input.platcb").toArray().reduce(((tot,cb)=>{tot[cb.value]=cb.checked; return tot}),{})
		//let not_all = 

		let base_loc = location.toString().replace(/#.*____/,"").replace(/\/$/,"") // DELETE THE ____ !!!!
		$("[data-share=game]").attr("href",base_loc)
		$("[data-share=set]").toggle(!!(this.GAME.State.set && this.GAME.State.diff)).attr("href",base_loc+`/#start=${this.GAME.State.set}/${this.GAME.State.diff}`)
		$("[data-share=question]").toggle(!!(this.GAME.Q?.num)).attr("href",base_loc+`/#start=${this.GAME.State.set}/${this.GAME.State.diff}/${this.GAME.Q?.num}`)
	}

	save_prefs() {
		return
		//console.log("platforms",platforms)
		//this.GAME.SavePrefs($("#prefform").serializeArray().reduce((o, kv) => [...o, kv.value], [])) //.filter(n=>n)
	}


	// called by GAME: when score data arrives
	ShowScore(data) {  // use .totalscore, .score, .seen, .match; also .set.*
		if (data.set)
			$("#scorepane .section").fillAllTemplates({...data,setlabel:data?.set?.label || "?",setseenplus:data.setseen+1})
		else
			$("#scorepane").fillAllTemplates({setlabel:"(none)",totalscore:this.GAME.State.guessed.length,score:"0",setseenplus:1,setsize:1})
		/*
		$("#scorepane")
			.find(".score").html(data.score).end()
			.find(".remaining").html("(remaining: " + data.unseen + " of " + data.match + ")")
		$("#currentset")
			.find(".label").html(data.set.label).end()
		*/

	}

	OnAudioAutoplayFailed() { // if user didn't interact with page, but page tried to play audio

	}

	// called by GAME: to show a message
	ShowMessage(type: string, data?: Record<string,string>) {
		$("#questionpane,#leftpane,#scorepane").hide()
		let $msg = $("#messages")
			.show()
			.find("[data-message]").hide().end()
			.find(`[data-message='${type}']`).show()
		$msg.find(`[data-ifvar]`).add(`[data-var]`).hide()
		console.log("ERR:" ,data)
		if (data) for (let field in data) $msg.find(`[data-ifvar=${field}]`).show(),$msg.find(`[data-var=${field}]`).show().html(data[field])
		$("#footer").toggle(type=="intro")
		$("#start").show()
		this.GAME.Audio.player?.pause()
	}

	// internal: when user types an answer
	OnAnswer() {
		let guess = $("#input").val()?.toString().toLowerCase()
		this.GAME.OnAnswer(guess)
	}

	// called by GAME: bad answer
	ShowIncorrect(guess) {
		$("#input").effect("shake", {
			direction: "left"
		})
	}

	// called by GAME: correct answer
	ShowCorrect(score, hint = false) {
		console.log("Showing correct:",score)
		let color = ""
		let $score = $("#score" + score)

		let Q = this.GAME.Q

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
				this.GAME.SaveGuessed()
			}
			

			//let a = Q.answer
			//if (platform) a += " (" + platform + ")"
			//if (year) a += " (" + year + ")"
			if (!hint) $("#correct1").show()
			//$("#correct2").show().html(a)
			
			//trivia
			let $c3 = $("#correct3")
			$c3.empty()
			if (Q.url) $c3.append("<h3>Full track</h3><p><a target='_blank' href='" + Q.url + "'>Click here</a> to listen.</p>")
			if (Q.composer) $c3.append(`<h3>Composer:</h3><p>${Q.composer}</p>`)
			if (Q.publisher) $c3.append(`<h3>Publisher:</h3>${Q.publisher}</p>`)
			if (Q.trivia) $c3.append(`<h3>Trivia:</h3>${Q.trivia.map(s=>"<p>"+s+"</p>").join("")}</p>`)
			$c3.toggle($c3[0].childNodes.length>0)

			$("#qform").hide()
			$("#hint").hide()

			/*
			$("#commentsbox").show()
			$(".fb-share-button").click(function (e) {
				FB && (FB as unknown as any).ui({
					method: 'share',
					href: $(this).attr("data-href"),
					quote: Hints ? "Can you recognize this game? I didn't!" : "Can you recognize this game? I did!"
				}, function (response) { console.log(response) });
			})
			*/

			if (this.GAME.State.set)
				setTimeout(() => $("#next").html("NEXT").data("onclick","next").trigger("focus"), 10)
			else
				setTimeout(() => $("#next").html("NEW GAME").data("onclick","new_set").trigger("focus"), 10)
		}
	}



	// called by GAME: to show a question
	async ShowQuestion(q) {
		(window as unknown as Record<string,number>).INIT_NUM = 0

		// sanity check!

		if (q.num > 0) {

			$("#messages,#footer,#start").hide()
			$("#maincontainer,#leftpane,#scorepane").show()

			//let location_raw = window.location.toString().replace(/\/\d+$/,"")
			$("#qnum").attr("href", "#q=" + q.num).find("[data-val]").html(q.num)

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
			let qta = (document.querySelector("template[data-name=question]") as HTMLTemplateElement).content
			let $b = $("#questions").empty().append(qta.cloneNode(true) as HTMLElement).children(":last").find("tbody")
			let qtb = (qta.querySelector("template[data-name=question-a]") as HTMLTemplateElement).content
			q.maxscore = 0
			let s
			//let qt = document.querySelector("template[data-name=question]").content.cloneNode(true)
			for (let s of q.scores) {
				console.log("Score",s)
				q.maxscore++
				let $a = $b.append(qtb.cloneNode(true) as HTMLElement).children(":last")
				$a
					.attr("id","score"+q.maxscore)
					.find(".answer").html(this.GAME.questionNames[s.name])
				
				//$b.append("<tr class='question' id='score" + i + "'><td class=star><img src='img/star-0.gif'></td><td class='answer'>" + questions[s.tag] + "</td></tr>")
			}

			if (q.type == "mp3") {
				$("#playercontrols").show()
				$("#imageframe").hide()
				if (this.GAME.Audio.player) $(this.GAME.Audio.player).attr("src", q.file)
				console.log("Loading " + q.file)
				await this.GAME.StartAudio()
			} else if (q.type == "png" || q.type == "gif") {
				$("#playercontrols").hide()
				$("#imageframe").show()
				$("#imageframe #image").bind("load", function () { $(this).show() }).hide().attr("src", q.file)
			}

			$.history.push("q="+q.num)

			
			let current_url = window.location.toString() //"http://" + location.hostname + "/remember-that-game/" + q.num
			
			/*
			$(".fb-comments").attr("data-href", current_url)
			$(".fb-share-button").attr("data-href", current_url)
			if ("XFBML" in FB) {
				FB.XFBML.parse()
			}
			*/

		} else {
			this.ShowMessage("error", {error:"Oops! Something crashed!"})
		}
	}

	ShowEnd() {
		this.ShowMessage("end")
	}

	ShowSets() {
		this.ShowMessage("selection")
		this.GAME.ListSets().then(_=>this.ShowSetScores())
	}
	ShowSetScores() {
		let thisUI=this
		$("#select-set [data-forset]").each(function(e) {
			let setslug=$(this).data("forset")
			let set=thisUI.GAME.AllSets[setslug]
			$(this).fillTemplate({num:set._count,seen:set._seen,score:set._score})
		})
	}


	UpdateAudioSlider(Audio) {
		if (!Audio.player.paused && !Audio.player.ended) //  && Audio.player.currentTime != oldTime
			$("#slider").slider("value", Audio.player.currentTime / Audio.player.duration * 100)
	}

	InitAudioButtons(Audio) {
		$(Audio.player).on("playing", function (e) {
			console.log("Audio buttons: audio reports: \u25b6\ufe0f playing")
			$("#pause").show()
			$("#play").hide()
			$("#playercontrols").show()
		}).on("pause", function () {
			console.log("Audio buttons: audio reports: \u23f8\ufe0f pause")
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
				this.GAME.StartAudio()
				.then(_=>$('#input').focus())
			}
		})
		setInterval(() => this.UpdateAudioSlider(Audio), 100)
	}


	FreqDelay = 20
	FreqThen = 0

	InitFreqAnalyser() {
		// canvas stuff
		this.freq_canvas = document.getElementById('c') as HTMLCanvasElement
		if (!this.freq_canvas) throw new Error("Couldn't init freq analyser: no #c element!")
		this.freq_canvas_context = this.freq_canvas.getContext('2d');
		this.FreqAnalyserFrame()
	}

	FreqAnalyserFrame() {
		let now = Date.now()
		let elapsed = now - this.FreqThen
		if (elapsed < this.FreqDelay) return
		this.FreqThen = now - (elapsed % this.FreqDelay)

		let Audio = this.GAME.Audio

		// draw the analyser to the canvas
		var sum;
		var average;
		var scaled_average;
		var num_bars = 30;
		var data = new Uint8Array(2048);

		let canvas = this.freq_canvas
		let canvas_context = this.freq_canvas_context

		if (!canvas_context) return

		// clear canvas
		canvas_context.clearRect(0, 0, canvas.width, canvas.height);

		Audio.analyser?.getByteFrequencyData(data);
		let bin_size = Math.floor(data.length / num_bars / 4);
		let bar_width = Math.floor(canvas.width / num_bars);

		for (var x = 0; x <= num_bars; x += 1) {
			for (var y=0;y<=1;y+=0.1) {
				if (y<0.1)canvas_context.fillStyle = "white"
				else if (y<0.5) canvas_context.fillStyle = "cyan"
				else canvas_context.fillStyle = "magenta"
				canvas_context.fillRect(Math.floor((x+0.5) * bar_width-2), Math.floor((y+0.05)*canvas.height-1.5), 4, 2);
			}
		}

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

		// draw black horizontal bars
		
		canvas_context.strokeStyle = "black"
		canvas_context.lineWidth = 2
		for (i = 0; i < 1; i += 0.1) {
			let h = Math.floor(i * canvas.height)
			canvas_context.beginPath()
			canvas_context.moveTo(0, h)
			canvas_context.lineTo(canvas.width, h)
			canvas_context.stroke()
		}
		

		if (Audio.player?.paused) return; // keep drawing freq when paused, let frequency die down slowly. Stop here and don't draw Oscilloscope when paused!

		Audio.analyser?.getByteTimeDomainData(data)
		canvas_context.strokeStyle = "white"
		canvas_context.lineWidth = 6
		bar_width = Math.floor(canvas.width / num_bars)
		canvas_context.fillStyle = "white"
		for (var i = 0; i <= num_bars; i += 1) {
			sum = 0;
			for (var j = 0; j < bin_size; j += 1) {
				sum += data[(i * bin_size) + j];
			}
			average = sum / bin_size;
			average = average - average % 20

			/*
			// lightning oscilloscope
			if (i==0) {
				canvas_context.beginPath()
				canvas_context.moveTo(0, (average/256) * canvas.height);
			} else canvas_context.lineTo(i * bar_width, (average/256) * canvas.height);
			*/

			/*
			let y = (average / 256) * canvas.height
			canvas_context.beginPath()
			canvas_context.moveTo(i * bar_width, y)
			canvas_context.lineTo((i + 0.85) * bar_width, y)
			canvas_context.stroke()
			*/
			let y = (average / 256)
			canvas_context.fillRect(Math.floor((i+0.5) * bar_width-2)-4, Math.floor((y+0.05)*canvas.height-1.5)+1, 11, 8);


		}
		//canvas_context.stroke()

		window.requestAnimationFrame(() => this.FreqAnalyserFrame()) //refire
	}

	// Spectrum / Waveform



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
		this.tutorials_save()
	}
	tutorials_load() {
		this.Tutshidden = {}
		try {
			this.Tutshidden = JSON.parse(window.localStorage.getItem("tutshidden") || "{}")
		} catch (e) {
		}
		$(".tutorial").append("<div class='close mini'>x</div>")
		let this_ui = this
		$(".close").click(function () { this_ui.Tutshidden[$(this).parent().data("tutfor")] = true; this_ui.tutorials_save(); $(this).parent().hide() })
		this.tutorials_update()
	}

	OnError(error: string,data: Record<string, string> | null | undefined) {
		console.error("UI showing error:",error,data)
		this.ShowMessage("error", {error,...data})
	}

	ShowPopup(v) {
	}

}

function Template(s,vars) {
	return s.replace(/\{([^}]+)\}/g, (_,key)=>{
		return vars[key]==null ? "" : vars[key]
	});
}

$(_=>{
	$.fn.fillTemplate = function(vars):JQuery {
		if (this.data("template")) return this.html(Template(this.data("template"),vars)); else return this
	};

	$.fn.fillAllTemplates = function(vars):JQuery {
		return this.find("[data-template]").each(function() { $(this).fillTemplate(vars) });
	}
	//$('.inp input#input').fancyInput()[0].focus();
})

// make the plugin available outside this file
declare global {
    interface JQuery {
        fillTemplate(vars: any): this
        fillAllTemplates(vars: any): this
		slider(arg1:any,arg2?:any):number
		effect(arg1:any,arg2?:any):this
    }
	interface JQueryStatic {
		history:{on(i:any,ev:Function):any,listen(i:any):any,push(i:any):any}
	}
}

/*
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
*/

if (window) {
	window['YGSF_UI'] = window['YGSF_UI'] || {}
	window['YGSF_UI']['cga'] = new UI_CGA()
}

export {UI_CGA}