import { type } from "os"
import {GameUI} from "./gameui.class"

interface Set {
	slug:string
	label:string
	description:string
	_count?:number
	_seen?:number
	_score?:number
}

class YGSF_GAME {
	Started = false
	Q:{[key:string]:any} = {} // current question
	Prefs:object = {}
	Audio:{player?:HTMLAudioElement,context?:AudioContext,analyser?:AnalyserNode,source?:MediaElementAudioSourceNode} = {}

	State = {guessed:[] as number[],seen:[] as number[],set:"" as string|null,diff:"" as string|null}
	Set:Set
	
	UI:GameUI
	Subseq_errors=0
	SetScore=0
	SetSize=0
	AllSets: Record<string,Set>

	questionNames = {
		name: "Name of the game",
		fullname: "Full name",
		character: "Character name",
		title: "Song title"
	}

	llog (s:string, ...attrs:any[]) {
		$("#console").append("<p>"+s+"</p>")
		console.log(s,...attrs)
	}
	idlog (id:string,s:string, ...attrs:any[]) {
		let $block = $("#console").find(`#${id}`)
		if ($block.length==0)
			$("#console").append(`<div id="${id}">${s}</div>`)
		else
			$block.html(s)
		console.log(s,...attrs)
	}

	async sleep(n:number) {
		return new Promise(f=>setTimeout(f,n))
	}

	async Init() {
		await this.SetupConsole() // first this, to be able to display errors
		await this.sleep(200)
		this.idlog("initchk","<pre>GAME powering up:\n[ ] UI\n[ ] Audio\n[ ] Preferences</pre>")
		await this.SetupUI() // first this, to be able to display errors
		await this.sleep(200)
		let uichk = this.UI ? "X" : "-"
		this.idlog("initchk",`<pre>GAME powering up:\n[${uichk}] UI\n[ ] Audio\n[ ] Preferences</pre>`)
		await this.SetupAudio()
		await this.sleep(200)
		this.idlog("initchk",`<pre>GAME powering up:\n[${uichk}] UI\n[X] Audio\n[ ] Preferences</pre>`)
		await this.LoadPreferences()
		await this.sleep(200)
		this.idlog("initchk",`<pre>GAME powering up:\n[${uichk}] UI\n[X] Audio\n[X] Preferences</pre>`)
		this.OnInited()
	}

	async SetupConsole() {

	}

	async LoadPreferences() {
		this.LoadState()
		/*
		return this.API({"do":"init"}).then(data => {
			console.log("GAME initing")
			this.get_status_from_response(data)
		})
		*/
	}

	SetupUI() {
		if (window['YGSF_UI']) this.registerUI(window['YGSF_UI'])
		this.UI?.Init(this)
	}

	OnInited() {
		this.UI?.OnReady()
	}

	SetupAudio() {
		let Audio = this.Audio || {}
		Audio.player = $("#audio")[0] as HTMLAudioElement

		if (!Audio.player) throw "No audio player found."

		console.groupCollapsed("Game/audio initing...")
		// analyser stuff
		var AudioContext = window.AudioContext// || ("webkitAudioContext" in window ? window.webkitAudioContext : null);
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

		this.UI?.OnAudioReady()
	}

	async StartAudio() {
		return new Promise(resolve=>{
			console.log("Audio: resuming context")
			this.Audio.context?.resume();
			console.log("Audio: starting player")
			this.Audio.player?.play()
			.then(resolve)
			.catch((e) => {
				if ((e instanceof DOMException) && e.toString().match(/user didn't interact/)) {
					console.warn("GAME.StartAudio failed to autoplay; fallback to request interaction")
					this.UI?.OnAudioAutoplayFailed()
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

	OnError(msg:string,data?:Record<string,string>) {
		this.UI?.OnError(msg,data||{})
	}

	registerUI(UI:GameUI) {
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

	SaveState() {
		window.localStorage.setItem("ygsf_state",JSON.stringify(this.State))
	}

	LoadState() {
		let state = JSON.parse(window.localStorage.getItem("ygsf_state") || "null") || {}
		if (typeof state != "object") state={}
		let newstate:{[key:string]:any}={}
		this.State={
			set:state?.set?.toString?.(),
			diff:state?.diff?.toString?.(),
			guessed:state.guessed || [],
			seen:state.seen || [],
		}
	}

	NextQuestion(num:string|null=null, dontpush = false, skip = false) {
		this.Audio.player?.pause()
		this.UI.OnQuestionLoading()
		let query:{[key:string]:string} = {do:"q",set:this.State.set}
		if (num) query.q=num // load specific q
		//+ "&" + urialize(this.Prefs) // save prefs
		if (this.Q?.num && skip) this.MarkSeen(this.Q.num) //mark Q seen
		query.seen = this.State.seen.join(",")
		console.log("NextQuestion",num,dontpush,query)
		this.API(query).then(data => {
				this.OnQuestionReceived(data, dontpush)
		})
	}

	MarkSeen(num) {
		console.log(`Logging question ${num} as seen.`)
		if (!this.State.seen.includes(num)) this.State.seen.push(num)
		this.SaveState()
	}

	OnQuestionReceived(data, dontpush) {
		this.get_status_from_response(data)

		if (data.unseen==0) return this.UI.ShowEnd()

		let q = data.q

		let ok = this.verify_question(q)
		if (!ok) {
			this.Subseq_errors++
			if (this.Subseq_errors > 3) {
				this.UI.ShowMessage("error", {msg:"Too many consecutive broken questions. Reload?"})
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
		if (data.score != null)
			this.SetScore = data.score
		if (data.match != null)
			this.SetSize = data.match
		if (data.set != null)
			this.Set = {...data.set}
		
		if (data.match!=null) this.UI.OnMatchedChanged(data.match,data.unseen)

		this.UI.ShowScore({setscore:this.SetScore,setsize:this.SetSize,setseen:data.seen_set as number,totalscore:this.State.guessed.length})
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
		/*
		return this.API({guessed:(num || this.Q.num)}).then(data => {
			console.log("saved guessed:", data)
			this.get_status_from_response(data)
		})
		*/
		if (!this.Q?.num) return
		if (this.State.guessed.includes(this.Q.num)) this.State.guessed.push(this.Q.num)
		this.SaveState()
	}

	Reset(what) {
		let fields={}
		for (let field in what) fields["reset_"+field]=1
		return this.API(fields)
	}

	ListSets() {
		return this.API({do:"listsets",guessed:this.State.guessed.join(","),seen:this.State.seen.join(",")}).then(data=>{
			if (isIterable(data)) {
				this.AllSets = {}
				for (let set of data) {
					this.AllSets[set.slug]={...set} as Set
				}
			}
		})
	}

	API(query?:Record<string,string>) {
		let queryString = new URLSearchParams(query).toString()
		let url = "q.php?"+queryString
		console.log("API: "+url)
		return $.get(url)
		.then((data,status,xhr)=>{
			console.log("q.php called as '%s', sends data:",url, data)
			if (!data) return this.OnError(null)
			if (data.err) return this.OnError(data.err,data)
			return data
		})
	}

	/** @deprecated */
	
	
	next_hint() {
		//Hints++
		//Score++
		//show_correct(Score,true)
		//$("#hint").html(Score<Q.maxscore-1?"GIVE UP":"GIVE UP")
	}


}

export { YGSF_GAME }

function isIterable(obj) {
	// checks for null and undefined
	if (obj == null) {
	  return false;
	}
	return typeof obj[Symbol.iterator] === 'function';
  }