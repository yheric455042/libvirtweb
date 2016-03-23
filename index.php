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
		<script type="text/javascript" src="js/create.js"></script>
		<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
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
		<button id="create_vm" class="btn btn-info btn-lg" data-toggle="modal" data-target="#create_modal"><i class="icon-plus icon-white"></i>添加虛擬機</button>

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
    
    <div class='modal fade' id="create_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">創建虛擬機</h4>
                </div>
                
                <div class="modal-body">
                    <div class="control-group">
                        <label class="control-label" for="Inputname">虛擬機名稱</label>
                        <div class="controls">
                            <input type="text" id="Inputname">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="Inputmem">記憶體</label>
                        <div class="controls">
                            <select id="Inputmem">
                                <option value="1">1G</option>
                                <option value="2">2G</option>
                                <option value="3">3G</option>
                            </select>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label" for="Inputvcpu">CPU個數</label>
                        <div class="controls">
                            <select id="Inputvcpu">
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="Inputtemplate">模板</label>
                        <div class="controls">
                            <select id="Inputtemplate">
                                <option value="0">Win7</option>
                                <option value="1">CentOS7</option>
                                <option value="2">Ubuntu14.04</option>
                            </select>

                        </div>
                    </div>
                    <div class="control-group isadmin">
                        <label class="control-label" for="Inputhost">實體主機</label>
                        <div class="controls">
                            <select id="Inputhost">
                            </select>

                        </div>
                    </div>

                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-default"  value="create_submit">提交</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" value="cancel">關閉</button>
                </div>

            </div>
        </div>
    </div>

</div>
</body>
</html>
