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
		<link type="text/css" rel="stylesheet" href="bootstrap/css/bootstrap.css"/>
		<link type="text/css" rel="stylesheet" href="css/login.css"/>
		<script type="text/javascript" src="js/jquery-1.12.2.js"></script>
		<script type="text/javascript" src="js/login.js"></script>
</head>
<body>
    <form action="" method="post">
        <div class="login-page">
            <div class="form-group">
                <label for="uid">帳號:</label>
                <input type="text" id="uid" class="form-control" placeholder="Please input your account"/>
            </div>
            <div class="form-group">
                <label for="passwd">密碼:</label>
                <input type="password" id="passwd" class="form-control" placeholder="Please input your password"/>
            </div>
            <button id="login-submit" class="btn btn-primary">提交</button>
        </div>
    </form>
</body>
</html>

