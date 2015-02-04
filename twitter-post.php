<?php
date_default_timezone_set('GMT');
//ツイート投稿
if(isset($_GET["twi-data"])){
	$twi_data = $_GET["twi-data"];
	echo "ツイートした内容:". $twi_data."<br>";
	
}else{
	$twi_data = "正常にデータ取得できませんでしたがツイート機能は正常です";
	echo $twi_data;
}



$api_key = "jEUrplLym5Lb5QjSqdwM4YpqF";
$api_secret = "fJiZcYc9R0wXNDWQfJvIRkdSStVqnusQxiOeUExA1Rr8YH8Knr";

$access_token = "2826502824-AtUD8JFu0iPJkGXjTiHrt52hIBRUpJ5j2Ruc3jL";
$access_secret = "f0EkzXqa1A57jICbU9kEoXCyXDPnA2h3vWmcdqT03dNpe";

$request_method = "POST";
$request_url = "https://api.twitter.com/1.1/statuses/update.json";

$signature_key = rawurlencode($api_secret)."&".rawurlencode($access_secret);



$params_a = array(
	"status" => $twi_data. "\r\n" . date("Y/m/d H:i", strtotime("+9 hour")),
	"display_coordinates" => FALSE,
	"trim_user" => true,
);

$params_b = array(
	"oauth_consumer_key" => $api_key,
	"oauth_token" => $access_token,
	"oauth_nonce" => microtime(),
	"oauth_signature_method" => "HMAC-SHA1",
	"oauth_timestamp" => time(),
	"oauth_version" => "1.0",
);

$signature_key = rawurlencode($api_secret)."&".rawurlencode($access_secret);

$params_c = array_merge($params_a,$params_b);

ksort($params_c);

$signature_params = str_replace(array("+","%7E"),array("%20","~"),http_build_query($params_c,"","&"));

$signature_params = rawurlencode($signature_params);

$encoded_request_method = rawurlencode($request_method);

$encoded_request_url = rawurlencode($request_url);

$signature_data = "{$encoded_request_method}&{$encoded_request_url}&{$signature_params}";

$hash = hash_hmac("sha1",$signature_data,$signature_key,TRUE);

$signature = base64_encode($hash);

/**********************
   リクエスト
**********************/
//[$params_c]に、作成した署名を加える
$params_c["oauth_signature"] = $signature;

//[$params_c]を[キー=値,キー=値,...]の文字列に変換する(ヘッダー用)
$header_params = http_build_query($params_c,"",",");

//[$params_a]を、リクエストボディに付けるデータに変換する
//[例] ?status=Hello,possibly_sensitive=true...
$body_params = http_build_query($params_a,"","&");

//TwitterにPOSTリクエストを送る [$json]にTwitterから返ってきたJSONが格納される

$json = @file_get_contents(
   $request_url,	//[第1引数：リクエストURL]
   false,		//[第2引数：リクエストURLは相対パスか？(違うのでfalse)]
   stream_context_create(	//[第3引数：stream_context_create()でメソッドとヘッダーを指定]
      array(
         "http" => array(
            "method" => $request_method, //リクエストメソッド
            "header" => array(          //カスタムヘッダー
               "Authorization: OAuth ".$header_params,
            ),
            "content" => $body_params, //リクエストボディ
         )
      )
   )
);

//JSONを取得できなかった場合のメッセージ
if(!$json){
   //ヘッダー
   echo <<<"EOT"
<html>
   <head>
      <meta charset="UTF-8"/>
      <meta name="robots" content="noindex,nofollow">
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
      <title>エラー</title>
   </head>
<body>
EOT;

   //エラーメッセージ
   echo "<h1>エラー</h1>";
   echo "<p>リクエストに失敗しました…。ディスプレイを叩く前に設定項目を再度、確認してみて下さい。</p>";

   //レスポンスヘッダーの内容を表示する
   echo "<h2>レスポンスヘッダーの内容</h2>";
   echo "<p>Twitterからの応答です。エラー原因のヒントがこの中に含まれている場合があります。</p>";
   echo "<pre>".print_r($http_response_header,1)."</pre>";

   //終了
   echo "</body>";
   exit;
}

//検証用にJSONを出力
echo $json;


?>
<html>
    <head>
        <title>test</title>
    </head>
    <body>
        <form action="twitter-post.php" method="GET">
            <input type="text" name="twi-data" />
            <input type="submit" value="ついーとする"/>
        </form>
    </body>
</html>