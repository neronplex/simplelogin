<?php
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