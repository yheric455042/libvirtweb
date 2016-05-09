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
        <link href="css/toastr.css" rel="stylesheet" type="text/css" />
		<link type="text/css" rel="stylesheet" href="css/index.css"/>
		<script type="text/javascript" src="js/jquery-1.12.2.min.js"></script>
        <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
        <script src="js/toastr.js"></script>
		<script type="text/javascript" src="js/index.js"></script>
		<script type="text/javascript" src="js/create.js"></script>
		<script type="text/javascript" src="js/pendinglist.js"></script>
		<script type="text/javascript" src="js/userinfo.js"></script>
		<script type="text/javascript" src="js/hostinfo.js"></script>
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
      <a id="vmlist" class="navbar-brand" href="#">虛擬機管理</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav">
        <li id="pendinglist"><a href="#pendinglist">審核列表</a></li>
        <li id="hostinfo" class="isadmin"><a href="#hostinfo">主機資訊</a></li>
        <li id="userinfo" class="isadmin"><a href="#userinfo">使用者列表</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li id="uid"></li>
        <li id="modifyPassword"><a href="#userinfo">修改密碼</a></li>
        <li><a id="logout" href="#logout">登出</a></li>
      </ul>
    </div><!--/.nav-collapse -->
  </div>
</nav>

<div class="container">
    <div class="loading-list"></div>

    <div class="wrap">
        <div>
        <h3>虛擬機列表</h3>
        <button id="create_vm" class="btn btn-primary" data-toggle="modal" data-target="#create_modal" style="font-size: 18px;"></button>
            <span class="glyphicons glyphicons-plus"></span>
        </button>
        </div>
        <hr>
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

    <div class="pending">
        <h3>審核列表</h4>
        <hr>
        <div class="list">
            <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="isadmin">帳號</th>
                    <th>虛擬機名稱</th>
                    <th>CPU#</th>
                    <th>記憶體</th>
                    <th>模板</th>
                    <th class="isadmin">主機</th>
                    <th class="isadmin">核准</th>
                </tr>	
            </thead>
            <tbody>
            </tbody>
            </table>	
        </div>
    </div>

    <div class="hostinfo">
        
    </div>

    <div class="userinfo">
        <h3>使用者列表</h3>
        <button id="create_vm" class="btn btn-primary" data-toggle="modal" data-target="#user_modal" style="font-size: 18px;">創建使用者</button>
        <hr>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>帳號</th>
                    <th>使用者名稱</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <div class="modifyPassword">
        <h3>修改密碼</h3>
        <hr>
        <div class="form-group">
            <label for="oldpassword">舊密碼</label>
            <input type="password" id="oldpassword" class="form-control password">
        </div>

        <div class="form-group">
            <label for="newpassword">新密碼</label>
            <input type="password" id="newpassword" class="form-control password">
        </div>

        <div class="form-group">
            <label for="confrirmpassword">確認新密碼</label>
            <input type="password" id="confirmpassword" class="form-control password">
        </div>

        <button type="button" data-loading-text="等待中....." class="btn btn-default modify_submit" value="modify_submit">送出</button>

    </div>

</div>
<div class='modal fade' id="create_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close create_close" data-dismiss="modal">&times;</button>
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
                <button type="button" class="btn btn-default create_submit" data-dismiss="modal" value="create_submit">提交</button>
                <button type="button" class="btn btn-default create_close" data-dismiss="modal" value="cancel">關閉</button>
            </div>

        </div>
    </div>
</div>

<div class='modal fade' id="user_modal" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close user_close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">創建使用者</h4>
            </div>
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="Inputuid">使用者帳號</label>
                    <input type="text" id="Inputuid" class="form-control">
                </div>
                <div class="form-group">
                    <label  for="Inputdisplayname">使用者名稱</label>
                    <input type="text" id="Inputdisplayname" class="form-control">
                </div>

                <div class="form-group">
                    <label for="Inputpassword">密碼</label>
                    <input type="password" id="Inputpassword" class="form-control">
                </div>
                <div class="form-group">
                    <label for="Inputemail">Email</label>
                    <input type="email" id="Inputemail" class="form-control">
                </div>
                
            </div>
                
            <div class="modal-footer">
                <button type="button" class="user-btn btn btn-default user_submit" data-dismiss="modal" value="user_submit">提交</button>
                <button type="button" class="user-btn btn btn-default user_close" data-dismiss="modal" value="cancel">關閉</button>
            </div>

        </div>
    </div>
</div>

</body>
</html>
