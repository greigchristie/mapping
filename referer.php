<?php
$link = $_SERVER['HTTP_REFERER'];
$uid = $_SERVER['REMOTE_USER'];
$matches = "";
//echo $_SERVER['HTTP_REFERER'];
	echo "<pre>";
	print_r($_SERVER);
	echo "</pre>";
	//echo "<p>$link</p>";
	if ($link != "") {
	$mat = "";
	$pm = "";
	$concatenated_message = "";
	preg_match('/rft_dat=(.*?),/', $link, $mat);
//				echo "<pre>";
//		print_r($mat);
//			echo "</pre>";
	$t = $mat[1];
	if (preg_match('/ie=44UOE_INST/', $t)){
//	echo "\$t: <pre>$t</pre>";
			//$matches = "";
		preg_match('/(511\d+)/',$t,$matches);
//			echo "<pre>";
//		print_r($matches);
//			echo "</pre>";
//			echo "<pre>";
//		var_dump($matchex);
//			echo "</pre>";
		$pm = "http://ed-primo-sb.hosted.exlibrisgroup.com/44UOE_VU1:default_scope:44UOE_ALMA".$matches[1];
	//echo "<h4><a href='$pm'>$pm</a><h4>";
	//echo "<h4>$uid</h4>";
//		echo "<pre>";
	//print_r($_SERVER);
//	echo "</pre>";
	} else {
		$match = "";
		preg_match('/%3C(.*)%3E(.*)%3C/',$t,$match);
		$pq = $match[1].$match[2];
		$pm = "http://ed-primo-sb.hosted.exlibrisgroup.com/44UOE_VU1:default_scope:TN_$pq";
		//echo "<h4><a href='http://discovered.ed.ac.uk/44UOE_VU1:default_scope:TN_$pq'>http://discovered.ed.ac.uk/44UOE_VU1:default_scope:TN_$pq</a></h4>";
		//echo "<h4>$uid</h4>";
//			echo "<pre>";
	//print_r($_SERVER);
//	echo "</pre>";
	}


// get email using alma user web service	
$ch = curl_init();

$user_id = urlencode($uid);
$url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/users/".$user_id;
//$templateParamNames = array('gandrew1');
//$templateParamValues = array(urlencode('gandrew1'));
//$url = str_replace($templateParamNames, $templateParamValues, $url);
$queryParams = '?' . urlencode('user_id_type') . '=' . urlencode('all_unique') . '&' . urlencode('view') . '=' . urlencode('brief') . '&' . urlencode('expand') . '=' . urlencode('none') . '&' . urlencode('apikey') . '=' . urlencode('l7xxe19ebbcd33314d808f88671f1ededb53');
$fullurl = $url.$queryParams;
//echo "<p>".$fullurl."</p>";
curl_setopt($ch, CURLOPT_URL, $fullurl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// won't work unless we specifically disable ssl verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
$response = curl_exec($ch);
curl_close($ch);

//var_dump($response);
//echo "<pre>";
//print_r($response);
//echo "</pre>";

$xml = new SimpleXMLElement($response);
//echo "<pre>";
//print_r($xml);
//echo "</pre>";
$email_address = (string)$xml->contact_info->emails->email[0]->email_address[0]; // item location
	
	if ($pm != "") {
	$concatenated_message = "<h4>Reported E-Resource Access Problem</h4>\n";
	$concatenated_message = $concatenated_message . "Permalink: <a href='$pm'>$pm</a><br>\n";
	$concatenated_message = $concatenated_message . "User UUN: $email_address<br>\n";
		$recipients = "g.andrew@ed.ac.uk";
		$headers = "Content-Type: text/html; charset=UTF-8"."\r\n";
		$headers = $headers . "From: greig.christie@ed.ac.uk";


		$subjectline = "Discovered Reported Access Problem";
//	echo $concatenated_message;
		mail($recipients, $subjectline, $concatenated_message, $headers);
		
		echo "<h2>Thank You!</h2>";
		echo "This is much appreciated";
	}
	
	} //$link null
	else
	{
	echo "NO REFERER";
	echo "<p>".$_SERVER['HTTP_REFERER']."</p>";
	echo "<pre>";
	print_r($_SERVER);
	echo "</pre>";
	echo "<p>$link</p>";
	}
?>
