<?php
	session_start();
	if(isset($_SESSION['uid'])) {
		header('Location: ./index.php');
	}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>虛擬機管理</title>
		<link type="text/css" rel="stylesheet" href="bootstrap/css/bootstrap.min.css"/>
		<link type="text/css" rel="stylesheet" href="css/login.css"/>
		<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="js/login.js"></script>
</head>
<body>
    <form action="" method="post">
	<div class="login-page">
	<div class="control-group">
		<label class="control-label" for="uid">帳號</label>
			<div class="controls">
				<input type="text" id="uid">
			</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="passwd">密碼</label>
		<div class="controls">
			<input type="password" id="passwd">
		</div>
	</div>
	<div class="controls">
		<button id="login-submit" class="btn btn-primary">提交</button>
	</div>
	</div>
    </form>
</body>
</html>

