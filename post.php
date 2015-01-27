<?php

// Saxon Mailey 2005
// Australian Information Technologies
// support@auit.com.au

date_default_timezone_set("Australia/Perth");

require 'db.php';

$TABLE="data";
$CONNECT=1;

if (isset($_GET['feed']))    { $FEED    = $_GET['feed'];    } else { die("ERROR : feed not specified\n"); }
if (isset($_GET['channel'])) { $CHANNEL = $_GET['channel']; } else { die("ERROR : channel not specified\n"); }
if (isset($_GET['value']))   { $VALUE   = $_GET['value'];   } else { die("ERROR : value not specified\n"); }
if (isset($_GET['date']))    { $DATE    = $_GET['date'];    } else { $DATE=date(DATE_ATOM); }

if ( "x$CONNECT" == "x1" ) {
	$dbcon = mysql_connect($DBHOST,$DBUSER,$DBPASS);
	mysql_select_db($DBNAME, $dbcon);

	$SQL="INSERT INTO $TABLE SET datetime=\"$DATE\",feed=$FEED,channel=\"$CHANNEL\",value=$VALUE";
	$result = mysql_query("$SQL",$dbcon);
	
	//print "$SQL\n";
	print "$result\n";
}

?>
