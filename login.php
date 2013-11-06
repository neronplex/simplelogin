<?php
session_start();

// CSRF対策トークン生成関数
function settoken(){
	# 現在時刻を元にした乱数からsha1ハッシュを生成
	$token = sha1(uniqid(mt_rand(),true));
	$_SESSION[token] = $token;
}

// CSRF対策トークンチェック関数
function checktoken(){
	# トークンが空かもしくはPOSTされたトークンと値が違う
	if(empty($_SESSION[token]) || ($_SESSION[token] != $_POST[token]))
	{
	# メッセージを表示してスクリプトの処理を終了
	print('不正な投稿が行われました。');
	exit;
	}
}

// リクエストメソッドがPOSTかそれ以外かで条件分岐
if($_SERVER[REQUEST_METHOD] != 'POST'){
	# トークンをセット
	settoken();
	# 読み込み時にセッション名[me]がある場合
	if(isset($_SESSION[me])){
	# メッセージを表示してスクリプトの処理を終了
	echo('Hello World!');
	exit;
	}
	}
	else{
	# トークンをチェック
	checktoken();

	// ログイン処理のための変数
	# SQLインジェクション対策のエスケープ処理を施した上で変数に格納
	$email = mysql_real_escape_string($_POST[email]);
	$password = mysql_real_escape_string($_POST[password]);
	# sha1としてパスワードが保存されているため、こちらも照合するために変換
	$hash = sha1($password);

	// エラーチェック用の連想配列
	$err = array();

		# メールアドレスの形式が不正
		if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
		$err[email] = 'メールアドレスの形式が正しくありません。';
		}
	
		# メールアドレスが空？
		if($email == ''){
		$err[email] = 'メールアドレスが入力されていません。';
		}
	
		# パスワードが空？
		if($password == ''){
		$err[password] = 'パスワードが入力されていません。';
		}

	// エラーがなにもなかった場合
	if(empty($err)){

	// MySQLの接続情報
	$link = mysql_connect('localhost','logindb','logindb');
	mysql_select_db('logindb',$link);

	// クエリーとして投げるSQL文
	# メールアドレスをキーとして検索
	$sql = "SELECT * FROM users WHERE email = "."'".$email."'";
	$query = mysql_query($sql);
	# 検索条件に一致するレコードを連想配列として取り出す
	$row = mysql_fetch_assoc($query);

		// メールアドレスとパスワードが一致した場合
		if($row[email] == $email && $row[password] == $hash){
		# セッション・ハイジャック対策
		session_regenerate_id(true);
		# 条件分岐用のセッション変数を用意
		$_SESSION[me] = $email;
		# セッションチェックの処理に飛ばすためリロード
		header('Location: ');
		exit;
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<!- 文字化け対策の文字コード ->
<meta charset="UTF-8">
<title>ログインフォーム</title>
</head>

<body>
<!- タイトル ->
<h1>ログインフォーム</h1>

<!- 入力フォーム ->
<form action="" method="POST">

<!- CSRF対策トークン ->
<input type="hidden" name="token" value="<?php echo $_SESSION[token]; ?>">

<!- メールアドレスとパスワードの入力 ->
<p>メールアドレス <input type="text" name="email" value="<?php echo $email; ?>"> <?php echo $err[email]; ?></p>
<p>パスワード <input type="password" name="password" value=""> <?php echo $err[password]; ?></p>

<- submitボタン ->
<input type="submit" value="ログイン">
</form>
</body>
</html>
