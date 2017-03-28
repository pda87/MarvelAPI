<?php
//ini_set('display_errors', 1);

//Insert Marvel API keys here
$PRIV_KEY = "MyPrivateKey";
$PUBLIC_KEY = "MyPublicKey";

$time = time();

//The Marvel API specifically asks for this format
$hash = md5("$time$PRIV_KEY$PUBLIC_KEY");

$url = "http://gateway.marvel.com:80/v1/public/characters?limit=1&ts=$time&apikey=$PUBLIC_KEY&hash=$hash";

$response = file_get_contents($url);

$marveljson = json_decode($response);

//There were 1485 characters available on the API last time I checked
$totalcharactersavailable = $marveljson->data->total;

//Build an array ready to store all of the Marvel character information
$allcharactersjson = array(
"attributionHTML" => $marveljson->attributionHTML,
"attributionText" => $marveljson->attributionText,
"code" => $marveljson->code,
"data" => array(
"results" => array (
),
"total" => $totalcharactersavailable
),
"status" => "Ok"
);

//Get all of the "Characters" JSON if the "all" parameter is included in the request, otherwise
//just get the name and ID of each character - the ID is needed for other API calls	
for ($count = 0; $count<$totalcharactersavailable;$count+=100) {

	$limit = 100;
	$offset = $count;

	$url = "http://gateway.marvel.com:80/v1/public/characters?limit=$limit&offset=$offset&ts=$time&apikey=$PUBLIC_KEY&hash=$hash";

	$response = file_get_contents($url);

	$marveljson = json_decode($response);

	for($i = 0; $i < $limit; $i++) {

	if($marveljson->data->results[$i] == NULL) {
		continue;
	}
	else {
		if(isset($_GET['full']) && !empty($_GET['full'])){
			//Full Character JSON download
			array_push($allcharactersjson['data']['results'], $marveljson->data->results[$i]);
		} else {
			//Get only the Character names and Character IDs
			array_push($allcharactersjson['data']['results'], array("id" => $marveljson->data->results[$i]->id, "name" => $marveljson->data->results[$i]->name));
		}
	}
	}
}

echo json_encode($allcharactersjson);
return;
?>
