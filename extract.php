<?php

// Saxon Mailey 2005
// Australian Information Technologies
// support@auit.com.au

date_default_timezone_set("Australia/Perth");

require 'db_stats.php';

if (isset($_GET['debug'])) {
	$DEBUG = $_GET['debug']; 
}

if (isset($_GET['feed'])) {
	$FEED = $_GET['feed']; 
} else { 
	if (isset($_GET['f'])) {
		$FEED = $_GET['f'];
	} else {
		die("ERROR : feed not specified\n");
	}
}
if (isset($_GET['channel'])) {
	$CHANNEL = $_GET['channel'];
} else {
	if (isset($_GET['c'])) {
		$CHANNEL = $_GET['c'];
	} else {
		die("ERROR : channel not specified\n");
	}
}
if (isset($_GET['math'])) {
	$MATH = $_GET['math'];
} else {
	if (isset($_GET['m'])) {
		$MATH = $_GET['m'];
	} else {
		die("ERROR : math not specified\n");
	}
}

//$FEED=1300136;
//$CHANNEL='flow';

$dbcon = mysql_connect($DBHOST,$DBUSER,$DBPASS);
mysql_select_db($DBNAME, $dbcon);

switch ($MATH) {
    case "average":
        $SQL='SELECT DATE(datetime) as date, ROUND(AVG(value),2) as value FROM data WHERE feed=' . $FEED . ' AND channel="' . $CHANNEL . '" GROUP BY date';
        $HEAD='Average';
        break;
    case "max":
        $SQL='SELECT DATE(datetime) as date, MAX(value) as value FROM data WHERE feed=' . $FEED . ' AND channel="' . $CHANNEL . '" GROUP BY date';
        $HEAD='Maximum';
        break;
    case "min":
        $SQL='SELECT DATE(datetime) as date, MIN(value) as value FROM data WHERE feed=' . $FEED . ' AND channel="' . $CHANNEL . '" GROUP BY date';
        $HEAD='Minimum';
        break;
    case "all":
        $SQL='SELECT datetime as date, value FROM data  WHERE feed=' . $FEED . ' AND channel="' . $CHANNEL . '"';
        $HEAD='Value';
        break;
    default:
        $SQL='SELECT DATE(datetime) as date, SUM(value) as value FROM data WHERE feed=' . $FEED . ' AND channel="' . $CHANNEL . '" GROUP BY date';
        $HEAD='Total';
}

if ( $debug == 1 ) {
    print "\n$SQL\n";
}
$result = mysql_query($SQL,$dbcon);

$rows=mysql_num_rows($result);

if ($rows > 0) {
	print "Date, " . $HEAD . "<BR>\n";
	$i=0;
	while ($i < $rows) {
		print mysql_result($result,$i,"date") . "," . mysql_result($result,$i,"value") . "<BR>\n";
		$i++;
	}
} else {
	print "\nNO RECORDS FOUND\n";
}
	

?>
