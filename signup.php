<?php
session_start();

// CSRF対策トークン生成関数
function settoken(){
	$token = sha1(uniqid(mt_rand(),true));
	$_SESSION[token] = $token;
}

// トークンチェック関数
function checktoken(){
	if(empty($_SESSION[token]) || ($_SESSION[token] != $_POST[token]))
	{
	# メッセージを表示してスクリプトの処理を終了
	print('不正な投稿が行われました。');
	exit;
	}
}

if($_SERVER[REQUEST_METHOD] != 'POST')
	{
	# トークンをセット
	settoken();
	}
	else{
	# トークンをチェック
	checktoken();

	// 登録処理のための変数
	# SQLインジェクション対策のエスケープ処理を施した上で変数に格納
	$email = mysql_real_escape_string($_POST[email]);
	# 変数を2つ用意してパスワードの誤入力チェックを行う
	$passworda = mysql_real_escape_string($_POST[passworda]);
	$passwordb = mysql_real_escape_string($_POST[passwordb]);
	# sha1ハッシュ化した上でパスワードを保存
	$hash = sha1($passworda);

	// エラーチェック用の連想配列
	$err = array();

		// メールアドレスの形式が不正
		if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
		$err[email] = 'メールアドレスの形式が正しくありません。';
		}

		// メールアドレスが空？
		if($email == ''){
		$err[email] = 'メールアドレスが入力されていません。';
		}

		// すでに登録されているメールアドレス
		# MySQLの接続情報
		$link = mysql_connect('localhost','logindb','logindb');
		mysql_select_db('logindb',$link);

		# クエリーとして投げるSQL文
		# メールアドレスをキーとして検索
		$sql = "SELECT * FROM users WHERE email = "."'".$email."'";
		$query = mysql_query($sql);

		# 検索条件に一致するレコードを連想配列として取り出す
		$row = mysql_fetch_assoc($query);

		# メールアドレスが既存のレコードと一致した場合
		if($email == $row[email]){
		$err[email] = 'そのメールアドレスはすでに登録されています。';
		mysql_close($link);
		}

		// パスワードが空？
		if($passworda == '' || $passwordb == ''){
		$err[password] = 'パスワードが入力されていません。';
		}

		// パスワードが一致しない
		if($passworda !== $passwordb){
		$err[password] = '入力されたパスワードが一致しません。';
		}

	// エラーがなにもなかった場合
	if(empty($err)){

	# MySQLの接続情報
	$link = mysql_connect('localhost','logindb','logindb');
	mysql_select_db('logindb',$link);

	# クエリーとして投げるSQL文
	# 入力されたメールアドレスと、パスワードのハッシュ値を登録
	$sql = "insert into users (email,password) value ("."'".$email."'".","."'".$hash."'".")";
	$query = mysql_query($sql);

		# 成功時と失敗時のシステムメッセージ
		if($query == true){
		$message = '会員登録が完了しました。';
		mysql_close($link);
		}
		else{
		$message = '会員登録が正常に完了出来ませんでした。管理者に連絡してください。';
		mysql_close($link);
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>新規ユーザー登録フォーム</title>
</head>

<body>
<!- タイトル ->
<h1>新規ユーザー登録フォーム</h1>

<!- 入力フォーム ->
<form action="" method="POST">

<!- CSRF対策トークン ->
<input type="hidden" name="token" value="<?php echo $_SESSION[token]; ?>">

<!- メールアドレスとパスワードの入力 ->
<p>メールアドレス <input type="text" name="email" value="<?php echo $email; ?>"> <?php echo $err[email]; ?></p>
<p>パスワード <input type="password" name="passworda" value=""> <?php echo $err[password]; ?></p>
<p>パスワード <input type="password" name="passwordb" value=""> <?php echo $err[password]; ?></p>

<- submitボタン ->
<input type="submit" value="ログイン">
</form>
<center><p><?php echo $message; ?></p></center>
</body>
</html>
