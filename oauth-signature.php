<?php
////////////////////
//OAuth1.0のkeyを生成
////////////////////
$oauth_consumer_secret = "bbbbbb";
$encode_consumer_secret = rawurlencode($oauth_consumer_secret);

$oauth_token_secret ="dddddd";
$encode_token_secret = rawurlencode($oauth_token_secret);


$signature_key = "{$oauth_consumer_secret}&{$oauth_token_secret}";

echo("signature_key=" . $signature_key . "<br>");


////////////////////
//OAuth1.0のdata生成
////////////////////
$request_method = rawurlencode("POST");

$request_url = rawurlencode("http://localhost/twitter-test/oauth-signature.php");

//連想配列でパラメータを設定
$params = array(
	"title"=>"AAA",
	"name"=>"BBB",
	"text"=>"CCC",
);

//連想配列のキーをアルファベット順に並び変える
ksort($params);

//連想配列の中身をurlエンコード
foreach($params as $key => $value){
	$params[$key] = rawurlencode($value);
}

$request_params = http_build_query($params,"","&");

$request_params = rawurlencode($request_params);

$signature_data = "{$request_method}&{$request_url}&{$request_params}";

echo("signature_data=" . $signature_data . "<br>");

////////////////////
//keyとdataを署名変換
////////////////////
$hash = hash_hmac("sha1",$signature_data,$signature_key,TRUE);

$signature = base64_encode($hash);

echo("signature=" . $signature);
?>