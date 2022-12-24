class RTG_GAME {
	Started = false
	Score = 0
	Q = [] // current question
	Prefs = {}
	Audio = {}

	async Init() {
		console.log("GAME powering up: UI...")
		this.SetupUI() // first this, to be able to display errors
		console.log("GAME powering up: audio...")
		this.SetupAudio()
		console.log("GAME loading preferences...")
		await this.LoadPreferences()
		this.OnInited()
	}

	async LoadPreferences() {
		return new Promise(resolve=>{
			return this.API({"do":"init"}, data => {
				console.log("GAME initing")
				this.get_status_from_response(data)
				resolve()
			})
		})
	}

	SetupUI() {
		this.UI.Init(this)
	}

	OnInited() {
		this.UI.OnReady()
	}

	SetupAudio() {
		let Audio = this.Audio
		Audio.player = $("#audio")[0]

		console.groupCollapsed("Game/audio initing...")
		// analyser stuff
		var AudioContext = window.AudioContext || window.webkitAudioContext;
		Audio.context = new AudioContext()
		console.log("AudioContext created",Audio.context)

		Audio.analyser = Audio.context.createAnalyser()
		Audio.analyser.fftSize = 2048
		console.log("Spectrum Analyser created", Audio.analyser)

		// connect the stuff up to eachother
		Audio.source = Audio.context.createMediaElementSource(Audio.player)
		Audio.source.connect(Audio.analyser);
		Audio.analyser.connect(Audio.context.destination);
		console.log("All connected.")
		console.groupEnd()
		console.log("Game/audio inited. ✔")


		this.UI.OnAudioReady()
	}

	async StartAudio() {
		return new Promise(resolve=>{
			console.log("Audio: resuming context")
			this.Audio.context.resume();
			console.log("Audio: starting player")
			this.Audio.player.play()
			.then(resolve)
			.catch((e) => {
				if ((e instanceof DOMException) && e.toString().match(/user didn't interact/)) {
					console.warn("GAME.StartAudio failed to autoplay; fallback to request interaction")
					this.UI.OnAudioAutoplayFailed()
				} else {
					console.error("failed to play", e)
					this.OnError("failed to play:" + e)
				}
			})
		})
	}

	location_raw() {
		return window.location.toString().replace(/\/\d+$/,"")
	}	

	OnError(msg) {
		this.UI.OnError(msg)
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

	SavePrefs(prefs,callback) {
		console.log("GAME.SavePrefs", prefs)
		this.prefs = prefs
		prefs = {do:"prefs",...prefs}
		this.API(prefs, data => {
			console.log("prefs saved:", data)
			this.get_status_from_response(data)
			callback?.()
		})
	}


	NextQuestion(num = null, dontpush = false, skip = false) {
		this.Audio.player.pause()
		this.UI.OnQuestionLoading()
		let query = {"do":"q"} 
		if (num) query.q=num // load specific q
		//+ "&" + urialize(this.Prefs) // save prefs
		if (this.Q && this.Q.num && skip) query.seen=this.Q.num //mark Q seen
		console.log("NextQuestion",num,dontpush,query)
		this.API(query,
			data => {
				
				this.OnQuestionReceived(data, dontpush)
			},
		)
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
			setTimeout(() => this.NextQuestion(null,false,true), 1000)
			return
		}
		this.Subseq_errors = 0

		console.log("Loaded and verified question " + q.num)

		if (!dontpush) {
			//history.pushState({ q: q.num }, "Remember That Game? Question #" + q.num, this.location_raw() + "/" + q.num)
			//$.history.push("q="+q.num)
			//console.log("pushed state " + q.num)
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

		if (data.match!=null) this.UI.OnMatchedChanged(data.match,data.unseen)

		this.UI.ShowScore(data)
	}

	OnAnswer(guess) {
		let max_sc = 0
		for (let s=0;s<this.Q.scores.length;s++) {
			let score = this.Q.scores[s]
			if (guess.match(score.re)) {
				if (s+1 > max_sc) {
					max_sc = (s+1)
				}
			}
		}
		if (max_sc > 0) {
			console.log("GOOD:", guess, "num", max_sc)
			for (let i = 1; i <= max_sc; i++)
				this.UI.ShowCorrect?.(i) // all correct up to current
			return true
		} else {
			console.log("BAD: ", guess)
			this.UI.ShowIncorrect?.(guess)
			return false
		}
	}

	SaveGuessed(num = null) {
		this.API({guessed:(num || this.Q.num)}, data => {
			console.log("saved guessed:", data)
			this.get_status_from_response(data)
		})
	}

	Reset(what,callback) {
		let fields={}
		for (let field in what) fields["reset_"+field]=1
		this.API(fields,callback)
	}

	API(query,callback) {
		if (typeof query == "object") query = new URLSearchParams(query).toString()
		return $.get("q.php?"+query, (data,status,xhr)=>{
			if (!data) return this.OnError()
			if (data.err) return this.OnError(data.err)
			console.log("q.php called as '%s', sends data:",query, data)
			callback(data)
		}).fail(_=>this.OnError())
	}
}