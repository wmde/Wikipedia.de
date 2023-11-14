<?php
// censorship //////////////////////////////////////
$blockedPages = array(
	"August von Goethe Literaturverlag",
	"Frankfurter Verlagsgruppe",
	"Fouqué Literaturverlag",
	"August von Goethe Literaturverlag",
	"Frankfurter Taschenbuchverlag",
	"August Goethe Verlag",
	"Cornelia Goethe Verlag",
	"Frankfurter Literaturverlag",
	"Oliver Petszokat",
	"Oli Petszokat",
	"Stella Deetjen",
	"Dirk Bavendamm",
	"Helge Schneider",
	"Santander Consumer Bank \(Deutschland\)",
	"Ulrich Marseille",
	"(stella )?deetjen",
	"helge schneider",
	"(dirk )?bavendamm",
	"oll?i\\.? ?p\\.?",
	"(oli(ver)? )?petszokat",
	"loriot?"
);

$blockedPages = str_replace( ' ', '[_ ]', $blockedPages );
$blockedPages = '#(^'. join('$)|(^', $blockedPages) .'$)#iAu';
