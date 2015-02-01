<?php
//アクセストークン作成テスト

////////////////
//署名の作成
////////////////
//API key と　API secretを指定
$api_key = "jEUrplLym5Lb5QjSqdwM4YpqF";
$api_secret = "fJiZcYc9R0wXNDWQfJvIRkdSStVqnusQxiOeUExA1Rr8YH8Knr";

//Access token secretを指定（今回はなし）
$access_token_secret = "";

//callback URLを指定
$callback_url = "http://127.0.0.1/twitter-test/oauth-accses-token.php/";

//リクエストURLを指定 twitterの場合は固定
$request_url = "https://api.twitter.com/oauth/request_token";

//リクエストメソッドを指定Twitterは基本POST
$request_method = "POST";

//「連携アプリを認証」をクリックして帰ってきた時
if(isset($_GET["oauth_token"]) && !empty($_GET["oauth_token"]) && isset($_GET["oauth_verifier"]) && !empty($_GET["oauth_verifier"])){
	//アクセストークンを取得するための処理
	//[リクエストトークン・シークレット]をセッションから呼び出す
	session_start();
	$request_token_secret = $_SESSION["oauth_token_secret"];
	
	//リクエストURL
	$request_url = "https://api.twitter.com/oauth/access_token";
	
	//署名鍵の作成
	$signature_key = rawurlencode($api_secret). "&". rawurlencode($request_token_secret);
	
	//署名データの作成
	$params = array(
		"oauth_consumer_key" => $api_key,
		"oauth_token" => $_GET["oauth_token"],
		"oauth_signature_method" => "HMAC-SHA1",
		"oauth_timestamp" => time(),
		"oauth_verifier" => $_GET["oauth_verifier"],
		"oauth_nonce" => microtime(),
		"oauth_version" => "1.0",
	);
	
	//上記連想配列のエンコード
	foreach($params as $key => $value){
		$params[$key] = rawurlencode($value);
	}
	
	ksort($params);
	
	$request_params = http_build_query($params,"","&");
	
	$request_params = rawurlencode($request_params);
	
	$encoded_request_method = rawurlencode($request_method);
	
	$encoded_request_url = rawurlencode($request_url);
	
	$signature_data = "{$encoded_request_method}&[$encoded_request_url]&{$request_params}";
	
	//署名作成
	$hash = hash_hmac("sha1", $signature_data, $signature_key,TRUE);
	$signature = base64_encode($hash);
	
	//アクセストークンの取得
	$params["oauth_signature"] = $signature;
	
	$header_params = http_build_query($params,"",",");
	
	$response = @file_get_contents(
		$request_url,
		false,
		stream_context_create(
			array(
				"http" => array(
					"method" => $request_method,
					"header" => array(
						"Authorization: OAuth ".$header_params,
					),
				)
			)
		)
	);
//リクエストが成功しなかった場合
if(!isset($response) || empty($response)){
   $html = '<p>アクセストークンを取得できませんでした…。[$api_key]と[$callback_url]、そしてTwitterのアプリケーションに設定している[Callback URL]を確認して下さい。</p>';
}else{
   //文字列を[&]で区切る
   $parameters = explode("&",$response);

   //HTMLの変数
   $html = "";

   //それぞれの値を格納する配列
   $query = array();

   //[$parameters]をループ処理
   foreach($parameters as $parameter){
      //文字列を[=]で区切る
      $pair = explode("=",$parameter);

      //配列に格納する
      $query[$pair[0]] = $pair[1];
   }

   //変数の整理
   $oauth_token = $query["oauth_token"];	//アクセストークン
   $oauth_token_secret = $query["oauth_token_secret"];	//アクセストークン・シークレット
   $user_id = $query["user_id"];	//ユーザーID
   $screen_name = $query["screen_name"];	//スクリーンネーム

   //HTMLの作成
   $html = <<<"EOT"
<dl>
   <dt>アクセストークン</dt>
   <dd>{$oauth_token}</dd>
   <dt>アクセストークン・シークレット</dt>
   <dd>{$oauth_token_secret}</dd>
   <dt>ユーザーID</dt>
   <dd>{$user_id}</dd>
   <dt>スクリーンネーム</dt>
   <dd>@{$screen_name}</dd>
</dl>
EOT;

}

//取得したアクセストークンを表示する
echo <<<"EOT"
<html>
<head>
   <title>アクセストークンを取得するサンプルデモ</title>
</head>
<body>
<h1>アクセストークンを取得するサンプルデモ</h1>
<p>Twitter APIで、アクセストークンを取得するサンプルデモです。</p>
<p>(解説：<a href="http://syncer.jp/twitter-api-how-to-get-access-token" target="_blank" rel="nofollow">Syncer</a>)</p>

<h2>取得した結果</h2>
{$html}

<h2>取得した値</h2>
<textarea style="width:95%;height:300px">{$response}</textarea>
</body>
</html>
EOT;
	
	
	echo '<html><head><title>Twitter APIでアプリ連携を許可した場合のサンプルデモ</title></head><body>';
	echo "<p>アプリケーションの連携が許可されました！現在のURLアドレス(パラメータ)を確認してみて下さい。</p>";	
	echo "<p>パラメータ: {$_SERVER['QUERY_STRING']}</p>";
	echo "</body></html>";
	exit;
   


//「キャンセル」をクリックして帰ってきた時
}elseif(isset($_GET["denied"]) && !empty($_GET["denied"])){

//エラーメッセージを出力して終了
	echo '<html><head><title>Twitter APIでアプリ連携を拒否した場合のサンプルデモ</title></head><body>';
	echo "<p>アプリケーションの連携は許可されませんでした…。</p>";
	echo "<p>パラメータ: {$_SERVER['QUERY_STRING']}</p>";
	echo "</body></html>";
	exit;

}

//署名鍵（signature key）の作成
$signature_key = rawurlencode($api_secret) . "&" . rawurlencode($access_token_secret);


//署名データの作成
$params = array(
	"oauth_callback" => $callback_url,
	"oauth_consumer_key" => $api_key,
	"oauth_signature_method" => "HMAC-SHA1",
	"oauth_timestamp" => time(),
	"oauth_nonce" => microtime(),
	"oauth_version" => "1.0"
);


//連想配列の中身をurlエンコード ただしコールバックURLはエンコードしない
foreach($params as $key => $value){
	if($key == "oauth_callback"){
		continue;
	}
	$params[$key] = rawurlencode($value);
}

//連想配列のキーをアルファベット順に並び変える
ksort($params);

$request_params = http_build_query($params,"","&");

$request_params = rawurlencode($request_params);

//リクエストメソッドをURLエンコードする
$encoded_request_method = rawurlencode($request_method);
 
//リクエストURLをURLエンコードする
$encoded_request_url = rawurlencode($request_url);

$signature_data = "{$encoded_request_method}&{$encoded_request_url}&{$request_params}";

$hash = hash_hmac("sha1",$signature_data,$signature_key,TRUE);

//署名の作成　base64でエンコード
$signature = base64_encode($hash);


////////////////
//リクエストトークンの取得
////////////////
$params["oauth_signature"] = $signature;

$heder_params = http_build_query($params,"",",");


//何してるか不明
$response = @file_get_contents(
	$request_url,
	false,
	stream_context_create(
		array(
			"http" => array(
				"method" => $request_method,
				"header" => array(
					"Authorization: OAuth ".$heder_params,
				),
			)
		)
	)
);

//リクエストが成功しなかった場合
if(!isset($response) || empty($response)){
   $html = '<p>リクエストトークンを取得できませんでした…。[$api_key]と[$callback_url]、そしてTwitterのアプリケーションに設定している[Callback URL]を確認して下さい。</p>';
}else{
   //文字列を[&]で区切る
   $parameters = explode("&",$response);

   //HTMLの変数
   $html = "";

   //それぞれの値を格納する配列
   $query = array();

   //[$parameters]をループ処理
   foreach($parameters as $parameter){
      //文字列を[=]で区切る
      $pair = explode("=",$parameter);

      //HTMLの書き出し
      $html .= "<dt style=\"margin-top:1em\"><b>{$pair[0]}</b></dt>";
      $html .= "<dd>{$pair[1]}</dd>";

      //配列に格納する
      $query[$pair[0]] = $pair[1];
   }

   //DLタグ
   $html = "<dl>{$html}</dl>";
}

//戻り先の指定を行うセッション
session_start();
session_regenerate_id(true);
$_SESSION["oauth_token_secret"] = $data["oauth_token_secret"];
 
//ユーザーを認証画面に飛ばす
header("Location: https://api.twitter.com/oauth/authenticate?oauth_token={$query['oauth_token']}");
  
?>