<?php
require_once 'functions.php';
$dbh = new PDO("mysql:host=localhost;dbname=logindb", "logindb", "logindb");
session_start();

// リクエストメソッドがPOSTかそれ以外かで条件分岐
if($_SERVER[REQUEST_METHOD] != 'POST') {
	# トークンをセット
	settoken();
	}
	else {
	# トークンをチェック
	checktoken();

	// 登録処理のための変数
	# SQLインジェクション対策のエスケープ処理を施した上で変数に格納
	$email = $_POST[email];
	# 変数を2つ用意してパスワードの誤入力チェックを行う
	$passworda = $_POST[passworda];
	$passwordb = $_POST[passwordb];

	// エラーチェック用の連想配列
	$err = array();

		// メールアドレスの形式が不正
		if(!filter_var($email,FILTER_VALIDATE_EMAIL)) {
		$err[email] = 'メールアドレスの形式が正しくありません。';
		}

		// メールアドレスが空？
		if($email == '') {
		$err[email] = 'メールアドレスが入力されていません。';
		}

		// すでに登録されているメールアドレス
		# クエリーとして投げるSQL文
		# メールアドレスをキーとして検索
		$sql = "SELECT * FROM users WHERE email = :email";
		# クエリの送信準備
		$stmt = $dbh->prepare($sql);
		# プレースホルダーの置換方法を指定
		$params = array(":email" => $email);
		# ここでクエリがDBに送信される
		$stmt->execute($params);
		# 検索条件に一致するレコードを連想配列として取り出す
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		# メールアドレスが既存のレコードと一致した場合
		if($email == $row[email]) {
		$err[email] = 'そのメールアドレスはすでに登録されています。';
		}

		// パスワードが空？
		if($passworda == '' || $passwordb == '') {
		$err[password] = 'パスワードが入力されていません。';
		}

		// パスワードが一致しない
		if($passworda !== $passwordb) {
		$err[password] = '入力されたパスワードが一致しません。';
		} else {
			# パスワードを保存用にsha1ハッシュ化
			$hash = sha1($passworda);
		}

	// エラーがなにもなかった場合
	if(empty($err)) {
		try {
			# クエリーとして投げるSQL文
			# 入力されたメールアドレスと、パスワードのハッシュ値を登録
			$sql = "insert into users (email,password) values (:email,:hash)";
			$stmt = $dbh->prepare($sql);
			$params = array(":email" => $email,":hash" => $hash);
			$stmt->execute($params);
		} catch (PDOException $e) {
			# エラーメッセージの取得
			var_dump($e->getMessage());
		}

		# 成功時と失敗時のシステムメッセージ
		if(empty($e)) {
		$message = '会員登録が完了しました。';
		}
		else {
		$message = '会員登録が正常に完了出来ませんでした。管理者に連絡してください。';
		}
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

<!- submitボタン ->
<input type="submit" value="ログイン">
</form>
<center><p><?php echo $message; ?></p></center>
</body>
</html>