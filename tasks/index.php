<?
//##########################################################
//## small PHP-Script I created for browsing webcomics    ##
//## needs http://mysrc.blogspot.de/2007/02/php-xml-to-array-and-backwards.html
//##   written 2007 by Jan Rauschenbach                   ##
//##   contact: coding@jan-rauschenbach.de                ##
//##########################################################

include "xml2ary.php";

if (isset($_GET['date'])) {
	$datum = date_create($_GET['date']);
} else {
	$datum = date_create("now");
}

$datum_sicher = new DateTime(date_format($datum,"c"));
// wochentage von heute rückwärts
for ($i=1; $i<=7; $i++) {
	$wd[$i] = strtolower(date_format($datum,"D"));
	date_modify($datum,"-1 day");
}
$wd[8] = "xxx";
$max = 0;

// daten aus xml datei auslesen
$xml=xml2ary(file_get_contents('config.xml'));

// daten in array schreiben
foreach ($xml[sites][_c][site] as $seite) {
	for ($i=1; $i<=8; $i++) {
		if ($seite[_c][$wd[$i]][_v] == 'X') {
			$tage[$i-1]["wd"] = $wd[$i];
			break;
		} 
	}
	if ($i <=7){
		$i--;
		$tage[$i]["name"][] = $seite[_c][name][_v];
		$tage[$i]["url"][] = replaceDate($seite[_c][url][_v],$datum_sicher,$i);
		$tage[$i]["cat"][] = $seite[_c][category][_v];
		if ($max < count($tage[$i]["name"])) { $max = count($tage[$i]["name"]); }
	}
}

// nach kategorie sortieren
for ($i=0;$i<=6;$i++){
	for($x=0;$x<$max;$x++){
		if ($tage[$i]["del"][$x] != "X" && $tage[$i]["name"][$x] != ""){
			$tage2[$i]["wd"] = $tage[$i]["wd"];
			$tage2[$i]["date"] = replaceDate("%d.%m.%Y",$datum_sicher,$i);
			$tage2[$i]["name"][] = $tage[$i]["name"][$x];
			$tage2[$i]["url"][] = $tage[$i]["url"][$x];
			$tage2[$i]["cat"][] = $tage[$i]["cat"][$x];
			if ($tage[$i]["cat"][$x] != "") { 
				for($y=$x+1;$y<$max;$y++) {
					if ($tage[$i]["cat"][$y] == $tage[$i]["cat"][$x]) {
						$tage2[$i]["name"][] = $tage[$i]["name"][$y];
						$tage2[$i]["url"][] = $tage[$i]["url"][$y];
						$tage2[$i]["cat"][] = $tage[$i]["cat"][$y];
						$tage[$i]["del"][$y] = "X";
					}
				}
			}
		}
	}
}

// html ausgeben
echo "<html>\n";
echo "<head>\n";
echo "\t<link rel='stylesheet' type='text/css' href='style.css'>\n";
echo "\t<link rel='top' href='?'>\n";
echo "\t<link rel='start' href='?'>\n";
echo "\t<link rel='index' href='?'>\n";
echo "\t<link rel='home' href='?'>\n";
echo "\t<link rel='next' href='?date=".replaceDate("%Y-%m-%d",$datum_sicher,-1)."'>\n";
echo "\t<link rel='prev' href='?date=".replaceDate("%Y-%m-%d",$datum_sicher,1)."'>\n";
echo "</head>\n";
echo "<body>\n";
echo "<div id='nav'><a href='?date=".replaceDate("%Y-%m-%d",$datum_sicher,1)."'>&laquo; Pref</a>";
//if (date_format($datum_sicher,"Y-m-d") != date("Y-m-d")) {
	echo "|<a href='?'>Today</a>|<a href='?date=".replaceDate("%Y-%m-%d",$datum_sicher,-1)."'>Next &raquo;</a>";
//}
echo "</div>\n";
echo "<div id='timedate'>Zuletzt aktualisiert: ".td()."</div>\n";
foreach ($tage2 as $tag){
	echo "<div id='".$tag["wd"]."' class='wd'>\n";
	echo "\t<h2>".wd($tag["wd"])." - ".$tag["date"]."</h2>\n";
	for($x=0;$x<count($tag["name"]);$x++){
		echo "\t<div class='".$tag["cat"][$x]."'>\n";
		echo "\t\t<a href='".$tag["url"][$x]."'>".$tag["name"][$x]."</a>\n";
		echo "\t</div>\n";
	}
	echo "</div>\n";
}
echo "</body></html>";

function replaceDate($string,$date,$int){
	$dateTemp = new DateTime(date_format($date,"c"));
	date_modify($dateTemp,"-".$int." day");
	$out = str_replace("%Y",date_format($dateTemp,"Y"),$string);
	$out = str_replace("%y",date_format($dateTemp,"y"),$out);
	$out = str_replace("%m",date_format($dateTemp,"m"),$out);
	$out = str_replace("%d",date_format($dateTemp,"d"),$out);
	return $out;
}

function wd($str){
	if (!strcasecmp($str,"mon")) { return "Montag"; }
	elseif (!strcasecmp($str,"tue")) { return "Dienstag"; }
	elseif (!strcasecmp($str,"wed")) { return "Mittwoch"; }
	elseif (!strcasecmp($str,"thu")) { return "Donnerstag"; }
	elseif (!strcasecmp($str,"fri")) { return "Freitag"; }
	elseif (!strcasecmp($str,"sat")) { return "Samstag"; }
	elseif (!strcasecmp($str,"sun")) { return "Sonntag"; }
	return "";
}

function month($month) {
    if (!strcasecmp($month,"january")) { return "Januar"; }
	elseif (!strcasecmp($month,"february")) { return "Februar"; }
	elseif (!strcasecmp($month,"march")) { return "M&auml;rz"; }
	elseif (!strcasecmp($month,"april")) { return "April"; }
	elseif (!strcasecmp($month,"may")) { return "Mai"; }
	elseif (!strcasecmp($month,"june")) { return "Juni"; }
	elseif (!strcasecmp($month,"july")) { return "Juli"; }
	elseif (!strcasecmp($month,"august")) { return "August"; }
	elseif (!strcasecmp($month,"september")) { return "September"; }
	elseif (!strcasecmp($month,"october")) { return "Oktober"; }
	elseif (!strcasecmp($month,"november")) { return "November"; }
	elseif (!strcasecmp($month,"december")) { return "Dezember"; }
	return "";
}

function td(){
	$return = wd(date(D))." / ".date(j).". ".month(date(F))." ".date(Y);
	$return .= " - ";
	$return .= date(G).":".date(i).":".date(s);
	return $return;
}

?>
