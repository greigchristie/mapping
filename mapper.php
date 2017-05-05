<?php
$link = $_SERVER['HTTP_REFERER'];
//$link = ""; // for local testing

$lf = $_GET['lf'];
$loc = $_GET['loc'];
//$lf = "main-3"; // for local testing
//$loc = "main-sho2"; // for local testing
// default identifer is lc_
$classify = "lc_";
// initialize variable.
$holding = "";
//echo $lf; // for local testing
// lf into library identifier and floor.
$flz = explode('-', $lf);
$librarys = $flz[0];
// assign library based on library identifier
$library = $librarys;
if ($librarys == "hub") {
	$library = "main";
}
if ($librarys == "murray_hub") {
	$library = "murray";
}
// use location to set identifier for EAS items. could do this differently
if (preg_match('/eas-/', $loc)) {
	$classify = "eas_";
}
$floor = $flz[1];
// default view value is full
$kiosk = "full";


$matches = "";

	if ($link != "") {
	$mat = "";
	$pm = "";
	// match for ALMA item permalink digits
	if (preg_match('/ie=44UOE_INST:(21\d+)/', $link, $mat)) {
		// check if request has come from an OPAC
		if (preg_match('/KIOSK2/', $link)) {
		$kiosk = "kiosk";
		}

		// build primo permalink
		$pm = "http://discovered.ed.ac.uk/primo_library/libweb/action/dlDisplay.do?vid=44UOE_VU1&search_scope=default_scope&docId=44UOE_ALMA".$mat[1];
	// add switch to show primo normalised xml
	$pnx = $pm . "&showPnx=true";
	$url = $pnx;
	// set up curl call to retrieve item pnx.
	$ch = curl_init();

	echo "<p>".$url."</p>";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	// won't work unless we specifically disable ssl verification
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch,CURLOPT_FAILONERROR,true);
	//curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	$response = curl_exec($ch);
	curl_close($ch);
	if (curl_error($ch))
	{
	    echo 'error:' . curl_error($ch);
	}
	// the MARC $$ causes havoc when parsing xml so I replace them before assigning $response to xml
	$response = str_replace('$$', ';-', $response);


$xml = new SimpleXMLElement($response);
	// asssigning values from xml structure using OO syntax
	$title = (string)$xml->display->title; // item title
	$title = urlencode($title);
	$creator = (string)$xml->display->creator; // item author
	$creator = urlencode($creator);
	$contributor = (string)$xml->display->contributor; // grab contributor information as backup for creator
	$ctz = explode(';', $contributor);
	$contributor = $ctz[0];
	// there isn't a creator use first contributor as item author
	if ($creator == "") {
	$creator = $contributor;
	}
	// enrichment->classificationlcc isn't always set but may be needed if exceptions in availlibrary locations
	$enshelf = (string)$xml->enrichment->classificationlcc;
	
foreach($xml->display->availlibrary as $avail) {
	// using availability info to grab shelfmark based upon location
	if (preg_match("/.*".$loc."*/i", $avail)) {
	$holding = $avail;
	}

 }
	
	if ($holding != "") {
	$itemex = preg_split("/;/",$holding);
	foreach($itemex as $itz) {
	if (preg_match("/^-2/",$itz)) {
		$shelfz = $itz;
		}

	}
	// echo "<p>SZ: ".$shelfz."</p>";
	 $shelfz = str_replace('-2(','',$shelfz);
	 $shelfz = str_replace(')','',$shelfz);
	 $shelfmark2 = $shelfz;
	// if there isn't a matching location in availlibrary then use enrichment->classificationlcc shelfmark value
	} elseif (isset($enshelf)) {
	echo "<h3>enshelf: $enshelf</h3>";
	$shelfz = $enshelf;
	$shelfmark2 = $enshelf;
	}

// shelfmark dewey matches.
 // if shelfmark begins with a dot followed by more than two digits then mank classification dewey
if (preg_match('/^\.\d+/',$shelfz)) {
 $classify = "dewey_";
}
// dewey periodicals
if (preg_match('/^(P\.|P) \.\d+/',$shelfz)) {
	$classify = "dewey_";
}
// dewey folios
if (preg_match('/^(F\.|F) \.\d+/',$shelfz)) {
	$classify = "dewey_";
 }
 // dewey journal indentified
if (preg_match('/^(Per\.|Per) \.\d+/',$shelfz)) {
	$classify = "journal_";
} 
 // redirect user to appropriate map url
$header = "Location: http://www.librarymaps.is.ed.ac.uk/?floor=$floor&library=$library&identifier=$classify$librarys&title=$title&author=$creator&shelfmark=$shelfmark2&view=$kiosk";
//echo "<p>$header</p>";
header($header);
	
	}  elseif (preg_match('/callNumber%3D(.*?)%5D.*/', $link, $mas)) {
	// if coming from item request screen link
	$shelfmarc = $mas[1];
	// because no location information in referer assign all to kiosk view
	$kiosk = "kiosk";
	// dewey checks - may need extended to cover full range
	if (preg_match('/^-2\(\.\d+/',$shelfmarc)) {

	 $classify = "dewey_";
	} else {
		$classify = "lc_";
		}
//	if ($library == "main" && $floor == 1) { $librarys = "hub"; }
	$header = "Location: http://www.librarymaps.is.ed.ac.uk/?floor=$floor&library=$library&identifier=$classify$librarys&shelfmark=$shelfmarc&view=$kiosk";
	header($header);
//	echo "<p>$header</p>";
	}


	}  //$link null
	else
	{
//	echo "NO REFERER";
//	echo "<p>".$_SERVER['HTTP_REFERER']."</p>";
//	echo "<pre>";
//	print_r($_SERVER);
//	echo "</pre>";
//	echo "<p>$link</p>";
	$header = "Location: http://www.librarymaps.is.ed.ac.uk/?floor=$floor&library=$library&identifier=$classify$librarys&shelfmark=&view=kiosk";
	if ($library != "main") {
	$header = "Location: http://www.librarymaps.is.ed.ac.uk/?&library=$library&identifier=$classify$librarys&shelfmark=&view=kiosk";
	}
	header($header);
	
	}
?>