<?php
$CFG['db_name']="lichoart_rtg";
$CFG['db_user']="lichoart_rtg";
$CFG['db_pass']="X0fRD1rY5w";

$PLATFORMS = ['all'=>"All", 'ZX'=>"ZX Spectrum",'Atari'=>"Atari",'C64'=>"C-64",'Amiga'=>"Amiga",'ST'=>"Atari ST",'PC'=>"PC",'NES'=>"NES, SNES",'PSX'=>"PlayStation",'Arcade'=>"Arcade"];

$SETS = [
['slug'=>"zx",'label'=>"ZX Spectrum",'description'=>"The J key was always the first to be pressed, for the glory of Sir Clive Sinclair.",'cond'=>function($q) { return in_array("ZX",$q['pf']); }],
['slug'=>"atari",'label'=>"Atari XL/XE",'description'=>"Hold OPTION while booting up for state of the art audio experience.",'cond'=>function($q) { return in_array("Atari",$q['pf']); }],
['slug'=>"c64",'label'=>"Commodore 64",'description'=>"Behold the washed-out... I mean, realistic color palette.",'cond'=>function($q) { return in_array("C64",$q['pf']); }],
['slug'=>"nes",'label'=>"NES/Famicom",'description'=>"Jump and hit the ceiling with your head and a star will fall out, trust me.",'cond'=>function($q) { return in_array("NES",$q['pf']); }],
['slug'=>"8bit",'label'=>"8-Bit Classics",'description'=>"Eight bits should be enough for everyone. ZX, XL/XE, C64 and NES.",'cond'=>function($q) { return array_intersect($q['pf'],["Atari","ZX","C64","NES"]); }],

['slug'=>"amiga",'label'=>"Amiga",'description'=>"Agnus, Paula and Denise serving your 16-bit cravings.",'cond'=>function($q) { return in_array("Amiga",$q['pf']); }],
['slug'=>"st",'label'=>"Atari ST",'description'=>"So ST wasn't just for DTP and music?",'cond'=>function($q) { return in_array("ST",$q['pf']); }],
['slug'=>"pc",'label'=>"PC '80-'90",'description'=>"How many kilobytes of base memory did YOU squeeze out of your config.sys?",'cond'=>function($q) { return in_array("PC",$q['pf']) && $q['year']<2000; }],
['slug'=>"16bit",'label'=>"16-Bit Power",'description'=>"The nineties, the age of floppies. Amiga, ST and PC.",'cond'=>function($q) { return array_intersect($q['pf'],["Amiga","ST","PC"]) && $q['year']<2000; }],

['slug'=>"arcade",'label'=>"Arcade Glory",'description'=>"Why play at home when you can chuck all your allowance into a machine at a local bar?",'cond'=>function($q) { return in_array("Arcade",$q['pf']); }],
['slug'=>"consoles",'label'=>"Consoles",'description'=>"Keyboards are for nerds, gamers use gamepads! Xbox, Playstation and Nintendo across the ages.",'cond'=>function($q) { return array_intersect($q['pf'],["Xbox","PS","NES"]); }],

['slug'=>"all",'label'=>"Everything",'description'=>"Every game we have on file",'cond'=>function($q) { return true; }],

/*
['slug'=>"80s",'label'=>"'80s",'description'=>"'80s",'cond'=>function($q) { return $q['year']<1990; }],
['slug'=>"90s",'label'=>"'90s",'description'=>"'90s'",'cond'=>function($q) { return $q['year']>=1990 && $q['year']<2000; }],
['slug'=>"00s",'label'=>"'00s",'description'=>"2000",'cond'=>function($q) { return $q['year']>=2000 && $q['year']<2010; }],
['slug'=>"10s",'label'=>"'10s",'description'=>"2010",'cond'=>function($q) { return $q['year']>=2010 && $q['year']<2020; }],
*/
];
