<?php
$PLATFORMS = ['all'=>"All", 'ZX'=>"ZX Spectrum",'Atari'=>"Atari",'C64'=>"C-64",'Amiga'=>"Amiga",'ST'=>"Atari ST",'PC'=>"PC",'NES'=>"NES, SNES",'PSX'=>"PlayStation",'Arcade'=>"Arcade"];

$SETS = [
['slug'=>"zx",'label'=>"ZX Spectrum",'description'=>"The J key was always the first to be pressed, for the glory of Sir Clive Sinclair.",'condition'=>fn($q)=>$q->is_pf("ZX") ],
['slug'=>"atari",'label'=>"Atari XL/XE",'description'=>"Hold OPTION while booting up for state of the art audio experience.",'condition'=>fn($q)=>$q->is_pf("Atari") ],
['slug'=>"c64",'label'=>"Commodore 64",'description'=>"Behold the washed-out... I mean, realistic color palette.",'condition'=>fn($q)=>$q->is_pf("C64") ],
['slug'=>"nes",'label'=>"NES/Famicom",'description'=>"Jump and hit the ceiling with your head and a star will fall out, trust me.",'condition'=>fn($q)=>$q->is_pf("NES") ],
['slug'=>"8bit",'label'=>"8-Bit Classics",'description'=>"Eight bits should be enough for everyone. ZX, XL/XE, C64 and NES.",'condition'=>fn($q)=>$q->is_pf(["Atari","ZX","C64","NES"]) ],

['slug'=>"amiga",'label'=>"Amiga",'description'=>"Agnus, Paula and Denise serving your 16-bit cravings.",'condition'=>fn($q)=>$q->is_pf("Amiga") ],
['slug'=>"st",'label'=>"Atari ST",'description'=>"So ST wasn't just for DTP and music?",'condition'=>fn($q)=>$q->is_pf("ST") ],
['slug'=>"pc",'label'=>"PC '80-'90",'description'=>"How many kilobytes of base memory did YOU squeeze out of your config.sys?",'condition'=>fn($q)=>$q->is_pf("PC") && $q->year<2000 ],
['slug'=>"16bit",'label'=>"16-Bit Power",'description'=>"The nineties, the age of floppies. Amiga, ST and PC.",'condition'=>fn($q)=>$q->is_pf(["Amiga","ST","PC"]) && $q->year<2000 ],

['slug'=>"arcade",'label'=>"Arcade Glory",'description'=>"Why play at home when you can chuck all your allowance into a machine at a local bar?",'condition'=>fn($q)=>$q->is_pf("Arcade") ],
['slug'=>"consoles",'label'=>"Consoles",'description'=>"Keyboards are for nerds, gamers use gamepads! Xbox, Playstation and Nintendo across the ages.",'condition'=>fn($q)=>$q->is_pf(["Xbox","PS","NES"]) ],

['slug'=>"all",'label'=>"Everything",'description'=>"Every game we have on file",'condition'=>fn($q)=>true ],

/*
['slug'=>"80s",'label'=>"'80s",'description'=>"'80s",'cond'=>function($q) { return $q->year<1990; }],
['slug'=>"90s",'label'=>"'90s",'description'=>"'90s'",'cond'=>function($q) { return $q->year>=1990 && $q->year<2000; }],
['slug'=>"00s",'label'=>"'00s",'description'=>"2000",'cond'=>function($q) { return $q->year>=2000 && $q->year<2010; }],
['slug'=>"10s",'label'=>"'10s",'description'=>"2010",'cond'=>function($q) { return $q->year>=2010 && $q->year<2020; }],
*/
];
