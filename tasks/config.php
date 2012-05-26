<?php

include "xml2ary.php";
$file = 'config.xml';
$user_name = "user";
$user_pass = "password";

function identify ($user, $pass) {
	if (($user == $user_name) && ($pass == $user_pass)) {
		return true;
	} else {
		return false;
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head><title>Tasks Verwaltung</title>
</head><body>
<?
if(!isset($_POST['vname']) || !identify($_POST['vname'],$_POST['vpass'])){
?>
<div class="box" style="width:23em;">
	<form action="<? __FILE__ ?>" method="post">
	<div style="text-align:right;padding:1em;">
	Loginname:&nbsp;&nbsp;<input type="text" name="vname" size='25' class='text'><br>
	Passwort:&nbsp;&nbsp;<input type="password" name="vpass" size='25' class='text'>
	</div>
	<input type="submit" name="valider" value="Login">
	</form>
</div>
<?
} else {
	if (isset($_POST['save'])) {
		$fp = fopen($file, "w");
		fputs($fp,"<".chr(63)."xml version='1.0' encoding='ISO-8859-1'".chr(63).">\n");
		fputs($fp,"<!DOCTYPE webseiten SYSTEM 'sites.dtd'>");
		fputs($fp,"<sites>\n");
		$names = $_POST['name'];
		$urls = $_POST['url'];
		$cats = $_POST['cat'];
		$anz = count($names);
		for ($i=0; $i<$anz; $i++) {
			if ($names[$i] != "") {
				fputs($fp,"\t<site>\n");
				fputs($fp,"\t\t<name>".$names[$i]."</name>\n");
				fputs($fp,"\t\t<url>".$urls[$i]."</url>\n");
				fputs($fp,"\t\t<category>".$cats[$i]."</category>\n");
				if ($_POST['mon_'.$i] == "mon") { fputs($fp,"\t\t<mon>X</mon>\n"); }
				if ($_POST['tue_'.$i] == "tue") { fputs($fp,"\t\t<tue>X</tue>\n"); }
				if ($_POST['wed_'.$i] == "wed") { fputs($fp,"\t\t<wed>X</wed>\n"); }
				if ($_POST['thu_'.$i] == "thu") { fputs($fp,"\t\t<thu>X</thu>\n"); }
				if ($_POST['fri_'.$i] == "fri") { fputs($fp,"\t\t<fri>X</fri>\n"); }
				if ($_POST['sat_'.$i] == "sat") { fputs($fp,"\t\t<sat>X</sat>\n"); }
				if ($_POST['sun_'.$i] == "sun") { fputs($fp,"\t\t<sun>X</sun>\n"); }
				fputs($fp,"\t</site>\n");
			}
		}
		fputs($fp,"</sites>\n");
		fputs($fp,"\n");
		fclose($fp);
		echo "Tasks gespeichert<br /><br />";
	}
		// daten aus xml datei auslesen
		$xml=xml2ary(file_get_contents($file));
?>
<form action='<? __FILE__ ?>' method='post'>
<? echo "<input type='hidden' name='vname' value='".$_POST['vname']."'><input type='hidden' name='vpass' value='".$_POST['vpass']."'>" ?>
<h1>Tasks</h1>
<table>
	<thead>
		<tr>
			<td>Name</td>
			<td>URL</td>
			<td>Kategorie</td>
			<td>Mon</td>
			<td>Tue</td>
			<td>Wed</td>
			<td>Thu</td>
			<td>Fri</td>
			<td>Sat</td>
			<td>Sun</td>
		</tr>
	</thead>
<?
		foreach ($xml[sites][_c][site] as $seite) {
			$eintrag[$seite[_c][name][_v]][name] = $seite[_c][name][_v];
			$eintrag[$seite[_c][name][_v]][url] = $seite[_c][url][_v];
			$eintrag[$seite[_c][name][_v]][cat] = $seite[_c][category][_v];
			if ($seite[_c][mon][_v] == "X") { $eintrag[$seite[_c][name][_v]][mon] = TRUE; }
			if ($seite[_c][tue][_v] == "X") { $eintrag[$seite[_c][name][_v]][tue] = TRUE; }
			if ($seite[_c][wed][_v] == "X") { $eintrag[$seite[_c][name][_v]][wed] = TRUE; }
			if ($seite[_c][thu][_v] == "X") { $eintrag[$seite[_c][name][_v]][thu] = TRUE; }
			if ($seite[_c][fri][_v] == "X") { $eintrag[$seite[_c][name][_v]][fri] = TRUE; }
			if ($seite[_c][sat][_v] == "X") { $eintrag[$seite[_c][name][_v]][sat] = TRUE; }
			if ($seite[_c][sun][_v] == "X") { $eintrag[$seite[_c][name][_v]][sun] = TRUE; }
			$i++;
		}
		ksort($eintrag, SORT_STRING);
		for ($i=1; $i<=5; $i++) {
			$eintrag[] = "";
		}
		$i = 0;
		foreach ($eintrag as $entry) {
			echo "\t<tr>\n";
			echo "\t\t<td><input name='name[]' type='text' size='20' maxlength='30' value='".$entry[name]."'></td>\n";
			echo "\t\t<td><input name='url[]' type='text' size='100' value='".$entry[url]."'></td>\n";
			echo "\t\t<td><input name='cat[]' type='text' size='10' maxlength='30' value='".$entry[cat]."'></td>\n";
			echo "\t\t<td><input type='checkbox' name='mon_".$i."' value='mon'";
			if ($entry[mon] == TRUE) { echo " checked='checked'"; }
			echo "></td>\n";
			echo "\t\t<td><input type='checkbox' name='tue_".$i."' value='tue'";
			if ($entry[tue] == TRUE) { echo " checked='checked'"; }
			echo "></td>\n";
			echo "\t\t<td><input type='checkbox' name='wed_".$i."' value='wed'";
			if ($entry[wed] == TRUE) { echo " checked='checked'"; }
			echo "></td>\n";
			echo "\t\t<td><input type='checkbox' name='thu_".$i."' value='thu'";
			if ($entry[thu] == TRUE) { echo " checked='checked'"; }
			echo "></td>\n";
			echo "\t\t<td><input type='checkbox' name='fri_".$i."' value='fri'";
			if ($entry[fri] == TRUE) { echo " checked='checked'"; }
			echo "></td>\n";
			echo "\t\t<td><input type='checkbox' name='sat_".$i."' value='sat'";
			if ($entry[sat] == TRUE) { echo " checked='checked'"; }
			echo "></td>\n";
			echo "\t\t<td><input type='checkbox' name='sun_".$i."' value='sun'";
			if ($entry[sun] == TRUE) { echo " checked='checked'"; }
			echo "></td>\n";
			echo "\t</tr>\n";
			$i++;
		}
?>
</table>
<br />
<input type="submit" name="save" value="Speichern">
</form>
<?
}
?>
</body></html>
