class RTG_GAME {
	Started = false
	Score = 0
	Q = [] // current question
	Prefs = {}
	Audio = {}

	Init() {
		this.SetupHistory()
		this.SetupAudio()
		this.InitPrefs(() => this.OnInited())
	}

	InitPrefs(done) {
		let this_game = this
		$.get("q.php?init", function (data) {
			console.log("init", data)
			this_game.get_status_from_response(data)
			done()
		})
	}

	OnInited() {
		this.UI.Init()

		if (INIT_NUM) this.Start()
	}

	SetupHistory() {
		window.addEventListener('popstate', (event) => {
			if (event.state && event.state.q) this.load_question(event.state.q, true);
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
			this.Audio.player.play().catch((e) => {
				if ((e instanceof DOMException) && e.toString().match(/user didn't interact/)) {
					console.log("GAME.StartAudio failed to autoplay; fallback to request interaction")
					this.UI.OnGameAutoplayFailed()
				} else {
					console.error("failed to play", e)
					GAME.OnError("failed to play:" + e)
				}
			})
		} catch (e) {
			console.error(e);
		}
	}

	location_raw() {
		return window.location.toString().replace(/\/\d+$/,"")
	}	

	OnError(msg) {
		this.UI.OnError(msg)
	}

	Start() {
		this.UI.OnStart && this.UI.OnStart()
		this.load_question(INIT_NUM, true)
		this.Started = true
	}

	registerUI(UI) {
		this.UI = UI
	}

	verify_question(q) {
		try {
			if (!q.scores) throw "no scores";
			for (let s in q.scores) {
				let score = q.scores[s]
				if (!score.answer.toLowerCase().match(score.re)) {
					console.error("Bad answer " + (s+1) + ": " + score.re + " doesn't match " + score.answer)
					throw "badanswer" + s;
				}
			}
			return true
		} catch (e) {
			$.ajax(this.location_raw()+"feedback.php?q=" + q.num + "&fb=" + e) // report problem
			console.error("Failed verifying question " + q.num + ":", e)
		}
	}

	SavePrefs(prefs) {
		console.log("GAME.SavePrefs", prefs)
		this.prefs = prefs
		let this_game = this
		$.get("q.php?" + prefs.map(s => `pf[]=${s}`).join("&"), function (data) {
			console.log("prefs saved:", data)
			this_game.get_status_from_response(data)
		})
	}


	load_question(num = null, dontpush = false) {
		this.Audio.player.pause()
		this.UI.OnQuestionLoading()
		let this_game = this
		$.get({
			url: "q.php?do=q"
				+ (num ? "&q=" + num : "") // load specific q
				//+ "&" + urialize(this.Prefs) // save prefs
				+ ((this.Q && this.Q.num) ? "&seen=" + this.Q.num : ""), //mark Q seen
			success: function (data, status, jqhxr) {
				console.log("q.php sends data:", data)
				this_game.OnQuestionReceived(data, dontpush)
			},
			error: function () {
				console.log("ERROR")
			}
		})
	}

	OnQuestionReceived(data, dontpush) {
		this.get_status_from_response(data)

		if (data.unseen==0) return this.UI.ShowEnd()

		let q = data.q

		let ok = this.verify_question(q)
		if (!ok) {
			this.Subseq_errors++
			if (this.Subseq_errors > 3) {
				this.UI.ShowMessage("error", "Too many consecutive broken questions. Reload?")
				return
			}
			setTimeout(() => this.load_question(), 1000)
			return
		}
		this.Subseq_errors = 0

		console.log("Loaded and verified question " + q.num)

		if (!dontpush) {
			history.pushState({ q: q.num }, "Remember That Game? Question #" + q.num, this.location_raw() + "/" + q.num)
			console.log("pushed state " + q.num)
		}

		this.Q = q
		this.UI.ShowQuestion(this.Q)
	}

	get_status_from_response(data) {
		if (data.prefs) {
			this.Prefs = data.prefs
			this.UI.OnPrefsChanged(this.Prefs)
		}

		if (data.totalscore != null)
			this.Totalscore = data.totalscore
		if (data.score != null)
			this.Setscore = data.score
		if (data.seen != null)
			this.Totalseen = data.seen

		if (data.match!=null) this.UI.OnMatchedChanged(data.match)

		this.UI.ShowScore(data)
	}

	OnAnswer(guess) {
		let m = 0
		for (let s in this.Q.scores) {
			let score = this.Q.scores[s]
			if (guess.match(score.re)) {
				if (s+1 > m) {
					m = (s+1)
				}
			}
		}
		if (m > 0) {
			console.log("GOOD:", guess, "num", m)
			for (let i = 1; i <= m; i++)
				this.UI.ShowCorrect && this.UI.ShowCorrect(i) // all correct up to current
			return true
		} else {
			console.log("BAD: ", guess)
			this.UI.ShowIncorrect && this.UI.ShowIncorrect(guess)
			return false
		}
	}

	SaveGuessed(num = null) {
		let this_game = this
		$.get("q.php?guessed=" + (num || this.Q.num), function (data) {
			console.log("saved guessed:", data)
			this_game.get_status_from_response(data)
		})
	}
}