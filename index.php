<?php
session_start();
if(!isset($_SESSION['uid'])) {
	header('Location: ./login.php');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
        
		<meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>虛擬機管理</title>
		<meta name="description" content="">
        <meta name="author" content="">
        <link type="text/css" rel="stylesheet" href="bootstrap/css/bootstrap.min.css"/>
		<link type="text/css" rel="stylesheet" href="css/index.css"/>
        <link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-1.12.2.js"></script>
		<script type="text/javascript" src="js/index.js"></script>
		<script type="text/javascript" src="js/create.js"></script>
		<script  type="text/javascript" src="js/ie-emulation-modes-warning.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>

<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">虛擬機管理</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <li id="pendinglist"><a href="#pendinglist">審核列表</a></li>
        <li id="hostinfo"><a href="#hostinfo">主機資訊</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li id="uid"></li>
        <li><a id="logout" href="#logout">登出</a></li>
      </ul>
    </div><!--/.nav-collapse -->
  </div>
</nav>
<div class="container">
    <div class="loading-list"></div>

    <div class="wrap">
        <button id="create_vm" class="btn btn-info btn-lg" data-toggle="modal" data-target="#create_modal"><i class="icon-plus icon-white"></i></button>

        <div class="list">
            <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="isadmin">帳號</th>
                    <th>虛擬機名稱</th>
                    <th class="isadmin">主機</th>
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
</div>
<div class='modal fade' id="create_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">創建虛擬機</h4>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="Inputname">虛擬機名稱</label>
                    <input type="text" id="Inputname" class="form-control">
                </div>
                <div class="form-group">
                    <label  for="Inputmem">記憶體</label>
                    <select id="Inputmem" class="form-control">
                        <option value="1">1G</option>
                        <option value="2">2G</option>
                        <option value="3">3G</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="Inputvcpu">CPU個數</label>
                    <select id="Inputvcpu" class="form-control">
                        <option value="1">1</option>
                        <option value="2">2</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="Inputtemplate">模板</label>
                    <select id="Inputtemplate" class="form-control">
                        <option value="0">Win7</option>
                        <option value="1">CentOS7</option>
                        <option value="2">Ubuntu14.04</option>
                    </select>
                </div>
                <div class="form-group isadmin">
                    <label for="Inputhost">實體主機</label>
                        <select id="Inputhost" class="form-control">
                        </select>
                </div>

            </div>
                
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" value="create_submit">提交</button>
                <button type="button" class="btn btn-default" data-dismiss="modal" value="cancel">關閉</button>
            </div>

        </div>
    </div>
</div>
</body>
</html>
