<?php
//##########################################################
//## small PHP-Script I created for searching the LDAP-   ##
//## server at my former university                       ##
//## needs http://www.kryogenix.org/code/browser/sorttable/
//##   written 2009 by Jan Rauschenbach                   ##
//##   contact: coding@jan-rauschenbach.de                ##
//##########################################################

$ldapurl = "ldaps://ldap1.your-domain.de";
$searchpath = "ou=people,dc=your-domain,dc=de";

>
<?php
echo '<'.'?xml version="1.0" encoding="utf-8"?'.'>';
echo "\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <title>FHA LDAP Search</title>
    <style type="text/css">
      <!--
        body {
          font-family: Tahoma, Verdana, sans-serif;
        }
        table {
          width: 100%;
        }
        td {
          vertical-align: top;
          border-top: 1px dotted grey;
        }
        .center {
          text-align: center;
        }
        .right {
          text-align: right;
        }
        .rborder {
          border-right: 1px dotted grey;
        }
        .hl {
          background-color: yellow;
          font-style: normal;
        }
      -->
    </style>
    <script type="text/javascript" src="sorttable.js"></script>
  </head>
  <body>
    <div class="center">
      <form action="">
        <?php
        if ($_GET['searchstring'] != "") {
          echo "<input type=\"text\" id=\"searchstring\" name=\"searchstring\" value=\"".$_GET['searchstring']."\" />";
        } else {
          echo "<input type=\"text\" id=\"searchstring\" name=\"searchstring\" />";
        }
        ?>
        <input type="submit" value="Suchen">
      </form>
    </div>

<?php
function firstbig($str)
{
  $first = substr($str, 0,1);
  $string = strtoupper($first).substr($str, 1);
  return $string;
}
function mark($input, $marker) {
  $marker = trim($marker);
  $input = trim($input);
  $suchmuster = "/(".str_replace("*", ".*?", $marker).")/i";
  $ersatz = '<em class="hl">\\1</em>';
  $out = preg_replace($suchmuster, $ersatz, $input);
  if (strpos($out, "<em class") == FALSE) {
    if (strpos($marker, " ") != FALSE) {
      $words = array_unique(explode(" ", $marker));
      foreach ($words as $word) {
        $suchmuster = "/(".$word.")/i";
        $out = preg_replace($suchmuster, $ersatz, $out);
      }
    }
  }
  return $out;
}
if ($_GET['searchstring'] != "") {
  if ((strpos($_GET['searchstring'], "(") === false) && (strpos($_GET['searchstring'], ")") === false)) {
    $ds=ldap_connect($ldapurl);
    if ($ds) {
      $search = $_GET['searchstring'];
      $justthese = array( "ou", "sn", "cn", "uid", "mailLocalAddress", "employeeType", "givenName", "homeDirectory", "createTimestamp");
      $r=ldap_bind($ds);
      $filter = "(|";
      $filter .= "(cn=".$search.")";
      $filter .= "(sn=".$search.")";
      $filter .= "(givenName=".$search.")";
      $filter .= "(mailLocalAddress=".$search.")";
      $filter .= "(uid=".$search.")";
      $filter .= ")";
      $sr=ldap_search($ds, $searchpath, $filter, $justthese);

      echo "<hr />";
      $anz = ldap_count_entries($ds,$sr);
      echo "Gefundene Einträge: ".$anz."<hr />";
      
      if ($anz > 0) {
        $info = ldap_get_entries($ds, $sr);
        echo "<table class=\"sortable\">\n";
        echo "\t<tr>\n";
        echo "\t\t<th class=\"rborder\" abbr=\"ignore_case\" title=\"nach Vorname sortieren\">Vorname</th>\n";
        echo "\t\t<th class=\"rborder\" abbr=\"ignore_case\" title=\"nach Nachname sortieren\">Nachname</th>\n";
        echo "\t\t<th class=\"rborder\" abbr=\"ignore_case\" title=\"nach E-Mail Addresse sortieren\">E-Mail</th>\n";
        echo "\t\t<th class=\"rborder\" title=\"nach Benutzername sortieren\">Benutzer</th>\n";
        echo "\t\t<th class=\"rborder\" abbr=\"ignore_case\" title=\"nach Home Directory sortieren\">Home</th>\n";
        echo "\t\t<th class=\"rborder\" abbr=\"ignore_case\" title=\"nach Fakultät sortieren\">Fakultäten</th>\n";
        echo "\t\t<th class=\"rborder\" abbr=\"ignore_case\" title=\"nach Typ sortieren\">Typ</th>\n";
        echo "\t\t<th abbr=\"ignore_case\">Vorhanden seit</th>\n";
        echo "\t</tr>\n";
        for ($i=0; $i<$info["count"]; $i++) {
          echo "\t<tr>\n\t\t<td class=\"rborder\">";
          for ($cnt=0; $cnt<$info[$i]["givenname"]["count"]; $cnt++) {
            echo mark($info[$i]["givenname"][$cnt], $search);
          }
          echo "</td>\n\t\t<td class=\"rborder\">";
          for ($cnt=0; $cnt<$info[$i]["sn"]["count"]; $cnt++) {
            echo mark($info[$i]["sn"][$cnt], $search);
          }
          echo "</td>\n\t\t<td class=\"right rborder\">";
          for ($cnt=0; $cnt<$info[$i]["maillocaladdress"]["count"]; $cnt++) {
            echo "<a href='mailto:".$info[$i]["maillocaladdress"][$cnt]."'>";
            echo mark($info[$i]["maillocaladdress"][$cnt], $search);
            echo "</a><br />";
          }
          echo "</td>\n\t\t<td class=\"right rborder\">";
          for ($cnt=0; $cnt<$info[$i]["uid"]["count"]; $cnt++) {
            echo "<a href='http://www.fh-augsburg.de/~".$info[$i]["uid"][$cnt]."'>";
            echo mark($info[$i]["uid"][$cnt], $search);
            echo "</a>";
          }
          echo "</td>\n\t\t<td class=\"rborder\">";
          for ($cnt=0; $cnt<$info[$i]["homedirectory"]["count"]; $cnt++) {
            echo mark($info[$i]["homedirectory"][$cnt], $search);
          }
          echo "</td>\n\t\t<td class=\"rborder\">";
          for ($cnt=0; $cnt<$info[$i]["ou"]["count"]; $cnt++) {
            echo $info[$i]["ou"][$cnt]."<br />";
          }
          echo "</td>\n\t\t<td class=\"rborder\">";
          for ($cnt=0; $cnt<$info[$i]["employeetype"]["count"]; $cnt++) {
            echo $info[$i]["employeetype"][$cnt]." ";
          }
          echo "</td>\n\t\t<td>";
          for ($cnt=0; $cnt<$info[$i]["createtimestamp"]["count"]; $cnt++) {
            echo str_replace("Z", "", $info[$i]["createtimestamp"][$cnt]);
            echo " ";
          }
          echo "</td>\n\t</tr>\n";
        }
        echo "</table><hr />";
        ldap_close($ds);
      }
    } else {
      echo "<h4>Verbindung zum LDAP Server nicht möglich</h4>";
    }
  } else {
    echo "<h4>Ungültige Eingabe</h4>";
  }
}
?>

  </body>
</html>
