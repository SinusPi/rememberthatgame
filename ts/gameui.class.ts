import {YGSF_GAME} from "./rtg_game.class"

abstract class GameUI {
	ShowScore(data: { setscore: number; setsize: number; setseen: number; totalscore: number }) {}
	ShowSetScores(sets_slugs: Record<string, string>) {}
	ShowIncorrect(guess: any) {}
	ShowCorrect(i: number) {}
	OnMatchedChanged(match: any, unseen: any) {}
	ShowQuestion(Q: { [key: string]: any }) {}
	ShowMessage(type: string, data?: Record<string, string>) {}
	ShowEnd() {}
	OnQuestionLoading() {}
	OnError(msg: any, data?: Record<string, any>) {}
	OnAudioAutoplayFailed() {}
	OnAudioReady(): void {}
	OnReady(): void {}
	Init(game: YGSF_GAME): void {}
}

export { GameUI }

