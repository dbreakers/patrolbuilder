<?php

$authorize_url = "https://www.onlinescoutmanager.co.uk/oauth/authorize";
$token_url = "https://www.onlinescoutmanager.co.uk/oauth/token";

//	callback URL specified when the application was defined--has to match what the application says
// REplace this with your URL for this file
$callback_uri = "https://osmr.elementfx.com/osmrelay_patrolbuilder.php";

$test_api_url = "https://www.onlinescoutmanager.co.uk/oauth/resource";
$parameters = $_GET;
if (!isset($parameters["authorization_code"])) {
if (!isset($parameters['code'])) {
if ($parameters['captchakey']=="") {
    echo "You need to use the Recaptcha to access OSMR"; exit;
}
}
}
if (!isset($parameters["authorization_code"])) {
if (!isset($parameters['code'])) {
if (!isset($parameters['captchakey'])) {
    echo "You need to use the Recaptcha to access OSMR"; exit;
}
}
}

//Ypu'll need to setup Recaptcha from Google https://www.google.com/recaptcha/admin/
if (isset($parameters['captchakey'])) {
    $recaptcha1 = $parameters['captchakey'];
    $secret_key = '';   // REplace this with your Recaptcha Secret Key 
       
 //  echo $recaptcha1;

   $ch = curl_init();
//echo $_SERVER['REMOTE_ADDR'];
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => [
        'secret' => $secret_key,
        'response' => $recaptcha1
   //     'remoteip' => $_SERVER['REMOTE_ADDR']
    ],
    CURLOPT_RETURNTRANSFER => true
]);
    
$output = curl_exec($ch);
curl_close($ch);

   
   
  $output = json_decode($output);
    if ($output -> success == true) { 
  //      echo 'Google reCAPTACHA verified'; 
    } 
    else { 
        echo 'Error in Google reCAPTACHA: '; 
        echo json_encode($output);
        exit;
    } 
}




// These need to come from OSM and will be unique to you

$client_id = "IwV....";
$client_secret = "8zW...."; 


if ($_POST["authorization_code"]) {
 
//  echo("POST");
	//	what to do if there's an authorization code
    $tokens =  getAccessToken($_POST["authorization_code"]);
    $access_token = $tokens->access_token;
//	$access_token = getAccessToken($_POST["authorization_code"]);
//	$resource = getResource($access_token);
//	echo $resource;
} elseif ($_GET["code"]) {
   // echo var_dump($_GET);
	$access_token = getAccessToken($_GET["code"]);
//	$resource = getResource($access_token->access_token);
//	echo ($resource);
 
	
	?>
    <script>
      function click_and_return(access,refresh) {
              window.opener.postMessage({access,refresh});
              window.close(); 
      }    
    </script><span style="font-size:14pt">
    <?php if ($access_token!="") { ?>    
      You have successfully logged onto OSM<br>  
    
	  <button style="width:200pt; height: 40pt" onclick="click_and_return('<?php echo $access_token->access_token; ?>','<?php echo $access_token->refresh_token; ?>') ; " type="button">Return to original window</button></span>
	<?php } else  {echo "OSMR is currently unavailable, please try again later"; } ?>
	<?php  
}  elseif ($_GET["refresh"]) {
   $token = getRefreshToken($_GET["refresh"],$_GET["oauthset"]);


} else {
	//	what to do if there's no authorization code
	$parameters = $_GET;
	getAuthorizationCode($parameters['scope'],$parameters['oauthset']);
}



//	step A - simulate a request from a browser on the authorize_url
//		will return an authorization code after the user is prompted for credentials
function getAuthorizationCode($scope,$st) {
	global $authorize_url, $client_id, $callback_uri;
	 $scope = "section:member:write section:event:read";

    //  $scope = "section:administration:read";
	$authorization_redirect_url = $authorize_url . "?response_type=code&client_id=" . $client_id ."&client_secret=".$client_secret ."&redirect_uri=" . $callback_uri . "&scope=".$scope.'&state='.$st; //section:member:read section:flexirecord:read section:event:read section:badge:read section:quartermaster:read";

	header("Location: " . $authorization_redirect_url);

	//	if you don't want to redirect
	// echo "Go <a href='$authorization_redirect_url'>here</a>, copy the code, and paste it into the box below.<br /><form action=" . $_SERVER["PHP_SELF"] . " method = 'post'><input type='text' name='authorization_code' /><br /><input type='submit'></form>";
}

//	step I, J - turn the authorization code into an access token, etc.
function getAccessToken($authorization_code) {
	global $token_url, $client_id, $client_secret, $callback_uri;

	$authorization = base64_encode("$client_id:$client_secret");
    $header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
	$content = "grant_type=authorization_code&code=$authorization_code&redirect_uri=$callback_uri";
//echo
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $token_url,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $content
	));
	$response = curl_exec($curl);
  //  echo $resource;
	curl_close($curl);
//	echo("L:L:L:");
 
    if (json_decode($response)==null ) {echo "Access currently blocked by OSM this sometimes means too many people are using OSMR<br>";}
	if ($response === false) {
		echo "Failed";
		echo curl_error($curl);
		echo "Failed";
	} elseif (json_decode($response)->error) {
		echo "Error:<br />";
		echo $authorization_code;
		echo $response;
	}
  // echo(var_dump($response));//access token and refresh_token
//	return json_decode($response)->access_token;
return json_decode($response);
}

//	we can now use the access_token as much as we want to access protected resources
function getResource($access_token) {
	global $test_api_url;

	$header = array("Authorization: Bearer {$access_token}");

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $test_api_url,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true
	));
	$response = curl_exec($curl);
	curl_close($curl);
 //   echo $test_api_url;
  echo $response;
	return json_decode($response, true);
}


function getRefreshToken($authorization_code,$access_token) {
	global $token_url, $client_id, $client_secret, $callback_uri;
   
	$authorization = base64_encode("$client_id:$client_secret");
	$header = array("Authorization: Basic {$authorization}","Content-Type: application/x-www-form-urlencoded");
    //	$header = array("Content-Type: application/x-www-form-urlencoded");
	$content = "grant_type=refresh_token&refresh_token=".$authorization_code."&client_id=".$client_id."&client_secret=".$client_secret;
		$content = "grant_type=refresh_token&refresh_token=$authorization_code&client_id=$client_id&client_secret=$client_secret";
   // echo($content);
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $token_url,
		CURLOPT_HTTPHEADER => $header,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => $content
	));
	$response = curl_exec($curl);
	curl_close($curl);
//	echo("L:L:L:");
 
	if ($response === false) {
		echo "Failed";
		echo curl_error($curl);
		echo "Failed";
	} elseif (json_decode($response)->error) {
		echo "Error:<br />";
		echo $authorization_code;
		echo $response;
	}
  echo($response); //access token and refresh_token
//	return json_decode($response)->access_token;
return json_decode($response);
}


?>