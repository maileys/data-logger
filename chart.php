<?php

// Saxon Mailey 2005
// Australian Information Technologies
// support@auit.com.au

date_default_timezone_set("Australia/Perth");

require 'db.php';

# DEFAULT SQL
$SQLDATE='DATE(datetime) as date';
$SQLGROUP='date';
$DEFAULT_DURATION=28;
#$SQL='SELECT DATE(datetime) as date, ROUND(AVG(value),2) as value FROM data WHERE feed=' . $FEED . ' AND channel="' . $CHANNEL . '" GROUP BY date';

if (isset($_GET['debug'])) {
        $DEBUG = $_GET['debug'];
}

if (isset($_GET['title']))    { $TITLE   = $_GET['title']; }     else { $TITLE=""; }
if (isset($_GET['width']))    { $WIDTH = $_GET['width']; }       else { if (isset($_GET['w']))  { $WIDTH = $_GET['w']; }     else { $WIDTH=800; } }
if (isset($_GET['height']))   { $HEIGHT = $_GET['height']; }     else { if (isset($_GET['h']))  { $HEIGHT = $_GET['h']; }    else { $HEIGHT=400; } }
if (isset($_GET['feed']))     { $FEED = $_GET['feed']; }         else { if (isset($_GET['f']))  { $FEED = $_GET['f']; }      else { die("ERROR : feed not specified\n"); } }
if (isset($_GET['channel']))  { $CHANNEL = $_GET['channel']; }   else { if (isset($_GET['c']))  { $CHANNEL = $_GET['c']; }   else { die("ERROR : channel not specified\n"); } }
if (isset($_GET['math']))     { $MATH = $_GET['math']; }         else { if (isset($_GET['m']))  { $MATH = $_GET['m']; }      else { $MATH='sum'; } }
if (isset($_GET['group']))    { $GROUPBY = $_GET['group']; }     else { if (isset($_GET['g']))  { $GROUPBY = $_GET['g']; }   else { $GROUPBY='date'; } }
if (isset($_GET['start']))    { $DATESTART = $_GET['start']; }   else { if (isset($_GET['s']))  { $DATESTART = $_GET['s']; } else { $DATESTART=''; } }
if (isset($_GET['end']))      { $DATEEND = $_GET['end']; }       else { if (isset($_GET['e']))  { $DATEEND = $_GET['e']; }   else { $DATEEND=''; } }
if (isset($_GET['duration'])) { $DURATION = $_GET['duration']; } else { if (isset($_GET['d']))  { $DURATION = $_GET['d']; }  else { $DURATION=28; } }
if (isset($_GET['type']))     { $TYPE = $_GET['type']; }         else { if (isset($_GET['t']))  { $DURATION = $_GET['t']; }  else { $TYPE="CHART"; } }
if (isset($_GET['weekday']))  { $WEEKDAY = $_GET['weekday']; }   else { if (isset($_GET['wd'])) { $WEEKDAY = $_GET['wd']; }  else { $WEEKDAY=""; } }

$dbcon = mysql_connect($DBHOST,$DBUSER,$DBPASS);
mysql_select_db($DBNAME, $dbcon);


switch ($MATH) {
    case 'max':
        $SQLVALUE='MAX(value) as value';
        $HEAD='Maximum';
        break;
    case 'min':
        $SQLVALUE='MIN(value) as value';
        $HEAD='Minimum';
        break;
    case 'sum':
        $SQLVALUE='SUM(value) as value';
        $HEAD='Total';
        break;
    default:
        $SQLVALUE='ROUND(AVG(value),2) as value';
        $HEAD='Average';
}

switch ($GROUPBY) {
    case 'hour':
        $SQLDATE='HOUR(datetime) as date';
        $SQLGROUP='HOUR(datetime)';
        $SQLORDER="$SQLGROUP";
        break;
    case 'dayhour':
    case 'dh':
        $SQLDATE='HOUR(datetime) as date';
        $SQLGROUP='DATE(datetime), HOUR(datetime)';
        $SQLORDER="$SQLGROUP";
        break;
    case 'weekday':
    case 'dayofweek':
        $SQLDATE='DAYNAME(datetime) as date';
        $SQLGROUP='WEEKDAY(datetime)';
        $SQLORDER="$SQLGROUP";
        break;
    case 'dom':
    case 'dayofmonth':
        $SQLDATE='DAYOFMONTH(datetime) as date';
        $SQLGROUP='DAYOFMONTH(datetime)';
        $SQLORDER="datetime";
        break;
    case 'month':
        $SQLDATE='MONTH(datetime) as date';
        $SQLGROUP='MONTH(datetime)';
        $SQLORDER="$SQLGROUP";
        break;
    default:
        $SQLDATE='DATE(datetime) as date';
        $SQLGROUP='DATE(datetime)';
        $SQLORDER="$SQLGROUP";
}

$SQLWHERE = 'feed=' . $FEED . ' AND channel="' . $CHANNEL . '"';

if ("x$DATESTART" <> "x") {
	$SQLWHERE=$SQLWHERE . ' AND date(datetime) >= ' . "'$DATESTART'";
}
if ("x$DATEEND" <> "x") {
	$SQLWHERE=$SQLWHERE . ' AND date(datetime) <= ' . "'$DATEEND'";
}
if ("x$DATESTART" == "x" && "x$DATEEND" == "x" && is_numeric($DURATION)) {
    if ( $DURATION > 0 ) {
        $SQLWHERE=$SQLWHERE . ' AND (`datetime` > DATE_SUB(now(), INTERVAL ' . $DURATION . ' DAY))';
    }
}
if ( $DURATION > 200 || "x$WEEKDAY" <> "x" ) {
	if ("x$WEEKDAY" == "x") { $WEEKDAY = 'Monday'; }
	$SQLWHERE=$SQLWHERE . ' AND DAYNAME(datetime)="' . $WEEKDAY . '"';
}

$SQL='SELECT description FROM feeds WHERE feed=' . $FEED;
$result = mysql_query($SQL,$dbcon);
$rows=mysql_num_rows($result);
if ($rows > 0) {
		$DESCRIPTION = mysql_result($result,0,"description");
} 

$SQL='SELECT ' . $SQLDATE . ', ' . $SQLVALUE . ' FROM data WHERE ' . $SQLWHERE . ' GROUP BY ' . $SQLGROUP . ' ORDER BY ' . $SQLORDER;
$result = mysql_query($SQL,$dbcon);
$rows=mysql_num_rows($result);
if ($rows > 0) {


	print '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
	print '<html xmlns="http://www.w3.org/1999/xhtml">' . "\n";
	if ("$TYPE" == "CHART") {
		print "\t" . '<head>' . "\n";
		print "\t\t" . '<script type="text/javascript" src="https://www.google.com/jsapi"></script>' . "\n";
		print "\t\t" . '<script type="text/javascript">' . "\n";
		print "\t\t\t" . 'google.load("visualization", "1", {packages:["corechart"]});' . "\n";
		print "\t\t" . '</script>' . "\n";
		print "\t\t" . '<script type="text/javascript">' . "\n";
		print "\t\t\t" . 'function drawVisualization() {' . "\n";
		print "\t\t\t\t" . 'var data = google.visualization.arrayToDataTable([' . "\n";
		print "\t\t\t\t\t" . "['Day', '" . $HEAD . "']," . "\n";
	
		$i=0;
		while ($i < $rows) {
			if ($i > 0) { print ",\n"; }
			print "\t\t\t\t\t['" . mysql_result($result,$i,"date") . "'," . mysql_result($result,$i,"value") . "]";
			$i++;
		}
	
		print "\n\t\t\t\t" . "]);" . "\n\n";
		#print "\t\t\t\t" . "new google.visualization.ColumnChart(document.getElementById('visualization'))." . "\n";
		print "\t\t\t\t" . "new google.visualization.LineChart(document.getElementById('visualization'))." . "\n";
		#print "\t\t\t\t" . 'draw(data, {title:"' . $TITLE . '", width:' . $WIDTH . ', height:' . $HEIGHT . ', hAxis: {title: "Year"}});' . "\n";
		print "\t\t\t\t" . 'draw(data, {title:"' . $TITLE . '", width:' . $WIDTH . ', height:' . $HEIGHT . ', });' . "\n";
		print "\t\t\t" . '}' . "\n";
		print "\t\t\t" . 'google.setOnLoadCallback(drawVisualization);' . "\n";
		print "\t\t\t" . '</script>' . "\n";
		print "\t\t" . '</head>' . "\n";
		print "\t" . '<body>' . "\n";
		#print "\t" . $DESCRIPTION . "<P>\n";
		print "\t\t" . '<div id="visualization" style="width: ' . $WIDTH . 'px; height: ' . $HEIGHT . 'px;"></div>' . "\n";
	} else {
		print "\t" . '<body>' . "\n";
		print "\t\t" . '<TABLE>'  . "\n";
		$i=0;
		while ($i < $rows) {
			print "\t\t\t" . '<TR><TD>' . mysql_result($result,$i,"date") . '</TD><TD>' . mysql_result($result,$i,"value") . '</TD></TR>' . "\n";
			$i++;
		}
		print "\t\t" . '</TABLE>'  . "\n";
	}
} else {
	print "\nNO RECORDS FOUND\n";
}
print "<!-- $SQL -->";
print "\t" . '</body>' . "\n";
print '</html>' . "\n";
?>
