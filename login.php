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
	print('不正な投稿が行われました。');
	exit;
	}
}

if($_SERVER[REQUEST_METHOD] != 'POST')
	{
	settoken();
		if(isset($_SESSION[me])){
		echo('Hello World!');
		exit;
		}
	}
	else{
	checktoken();

	$email = mysql_real_escape_string($_POST[email]);
	$password = mysql_real_escape_string($_POST[password]);
	$hash = sha1($password);

	// エラーチェック
	$err = array();

	// メールアドレスの形式が不正
	if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
	$err[email] = 'メールアドレスの形式が正しくありません。';
	}

	// メールアドレスが空？
	if($email == ''){
	$err[email] = 'メールアドレスが入力されていません。';
	}

	// パスワードが空？
	if($password == ''){
	$err[password] = 'パスワードが入力されていません。';
	}

	// エラーがなにもなかった場合
	if(empty($err)){
	
	$link = mysql_connect('localhost','logindb','logindb');
	mysql_select_db('logindb',$link);

	$sql = "SELECT * FROM users WHERE email = "."'".$email."'";
	$query = mysql_query($sql);
	$row = mysql_fetch_assoc($query);

		if($row[email] == $email || $row[password] == $hash){
		// セッション・ハイジャック対策
		session_regenerate_id(true);
		$_SESSION[me] = $email;
		header('Location: ');
		exit;
		}
	}
}
?>

<!DOCTYPE html>
<html>
<head>
<title>ログインフォーム</title>
</head>

<body>
<p><?php echo $me; ?></p>
<h1>ログインフォーム</h1>
<form action="" method="POST">
<input type="hidden" name="token" value="<?php echo $_SESSION[token]; ?>">
<p>メールアドレス <input type="text" name="email" value="<?php echo $email; ?>"> <?php echo $err[email]; ?></p>
<p>パスワード <input type="password" name="password" value=""> <?php echo $err[password]; ?></p>
<input type="submit" value="ログイン">
</form>
</body>
</html>
