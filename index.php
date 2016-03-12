<?php
session_start();
if(!isset($_SESSION['uid'])) {
	header('Location: ./login.php');
}

?>
<html>
<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>虛擬機管理</title>
		<link type="text/css" rel="stylesheet" href="bootstrap/css/bootstrap.min.css"/>
		<link type="text/css" rel="stylesheet" href="css/style.css"/>
		<link type="text/css" rel="stylesheet" href="css/index.css"/>
		<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
		<script type="text/javascript" src="js/index.js"></script>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <div class="nav-collapse collapse">
                <ul class="nav navbar-nav">
					<li  id="logout" class="navbar-right"><a href="#logout">登出</a></li>
					
                </ul>

            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>

<div class="loading-list"></div>

<div class="wrap">
	<div class="info">
		<button id="create_vm" class="btn btn-primary"><i class="icon-plus icon-white"></i>添加虛擬機</button>
	</div>

	<div class="list">
		<table class="table table-bordered">
		<thead>
			<tr>
				<th>虛擬機名稱</th>
                <th>CPU#</th>
                <th>記憶體</th>
                <th>硬碟</th>
                <th>系统</th>
                <th>狀態</th>
                <th>操作</th>
			</tr>	
		</thead>
		<tbody>
		</tbody>
		</table>	
	</div>
</div>
</body>
</html>
