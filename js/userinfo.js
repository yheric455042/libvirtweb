(function($) {
    var user = {
        uidstate: true,
        emailstate: true,
        //false is lock true is unlock
    };
    
    user.loaduser = function() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            data: {
                action: 'userList'
            }
        }); 
    };

    user.modifyPassword = function(oldpass, newpass) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            data: {
                action: 'modifyPassword',
                params: {
                    oldpass: oldpass,
                    newpass: newpass
                }
            }
        });
    };
    

    user.Createuser = function(uid, password, displayname, email) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            data: {
                action: 'userCreate',
                params: {
                    uid: uid,
                    password: password,
                    displayname: displayname,
                    email: email
                }
            }
        }); 
    };
    
    user.adminAction = function(uid, admin) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            data: {
                action: 'modifyAdmin',
                params: {
                    user: uid,
                    admin: admin
                }
            }
        });
    
    }

    user.deleteUser = function(uid) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            data: {
                action: 'removeUser',
                params: {
                   user: uid 
                }
            }
        });
    };
    
    function resetInput(elementArray) {
        reset(elementArray);
        $.each(elementArray, function(index, value) {
            value.removeClass('error');
        });
        
        $('.user_submit').attr({'disabled': true});
    }

    function validateEmail(Email) {
        var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
        if (filter.test(Email)) {
            return true;
        }
        else {
            return false;
        }
    }
    $(function () {
        resetInput([$('#Inputuid'), $('#Inputdisplayname'), $('#Inputpassword'), $('#Inputemail')]);
        $('.user-btn').click(function() {
            var btn = $(this);

            if(btn.val() === 'user_submit') {
                var uid = $('#Inputuid').val();
                var displayname = $('#Inputdisplayname').val();
                var password = $('#Inputpassword').val();
                var email = $('#Inputemail').val();
                var tr = $('<tr>');
                var loading_td = $('<td>').append($('<div>').attr({class: 'loading-action'}));
                var tds = [loading_td.clone(), loading_td.clone(), loading_td.clone(), loading_td.clone()];

                $.each(tds, function(index, value) {
                    tr.append(value);
                });

                $('.userinfo tbody').append(tr);

                user.Createuser(uid, password, displayname, email).done(function(status) {

                    if(status == 'error') {
                        tr.remove();
                        toastr['error']('新增使用者失敗','失敗');

                    } else {
                        
                        $.each(tds, function(index, value) {
                            var div = $('<div>').attr({class: 'userAction'});
                            var adminBtn = $('<button>').attr({class: 'adminAction btn btn-success', value: '0'});
                            
                            adminBtn.text('增加管理者');
                            div.append($('<button class="deleteUser btn btn-danger">').text('刪除'));
                            div.append(adminBtn);

                            var out = [uid, displayname, email, div];
                            value.find('.loading-action').replaceWith(out[index]);
                        });
                        
                        toastr['success']('新增使用者成功','成功');
                    }
                    
                    resetInput([$('#Inputuid'), $('#Inputdisplayname'), $('#Inputpassword'), $('#Inputemail')]);
                });

            }
            
        });

        $('.user_close').click(function() {
                $('.form-group').find('small').remove();
                resetInput([$('#Inputuid'), $('#Inputdisplayname'), $('#Inputpassword'), $('#Inputemail')]);
        });

        $('#Inputuid').on('input', function() {
            
            var input = $(this);
            input.closest('div').find('small').remove();
            $('.userinfo tr').each(function(index, value) {
                if($(value).find('td:first').text() == input.val()) {
                    input.addClass('error');
                    input.after($('<small>').text('使用者名稱已重複'));
                    user.uidstate = false;
                    $('.user_submit').attr({'disabled': true});
                    return false;
                } else {
                    input.removeClass('error');
                    user.uidstate = true;
                    
                    user.emailstate && $('.user_submit').attr({'disabled': false});

                }
            });
       
        }); 

        $('#Inputemail').on('input', function() {
            var input = $(this);
            
            input.closest('div').find('small').remove();
            if(!validateEmail(input.val())) {
                input.addClass('error');
                input.after($('<small>').text('Email 格式不符'));
                user.emailstate = false;
                $('.user_submit').attr({'disabled': true});

            } else {
                input.removeClass('error');
                user.emailstate = true;
                user.uidstate && $('.user_submit').attr({'disabled': false});

            }
        }); 



        $('#userinfo').click(function() {
            index.transshow('hide', $('.userinfo'));
            $('.userinfo tbody tr').remove();

            user.loaduser().done(function(users) {
                if(users == 'notadmin') {
                    alert('error');
                    window.location.href = "./index.php"; 
                }
                $.each(users, function(index, user) {
                    var tr = $('<tr>');
                    var div = $('<div>').attr({class: 'userAction'});
                    var adminBtn = $('<button>').attr({class: user.isadmin == '1' ? 'adminAction btn btn-warning' : 'adminAction btn btn-success', value: user.isadmin});
                    adminBtn.text(user.isadmin == '1' ? '刪除管理者' : '增加管理者');

                    tr.append($('<td id="uid">').append(user.uid));
                    tr.append($('<td>').append(user.displayname));
                    tr.append($('<td>').append(user.email));
                    user.uid != 'admin' && div.append($('<button class="deleteUser btn btn-danger">').text('刪除'));
                    user.uid != 'admin' && div.append(adminBtn);
                    
                    tr.append($('<td>').append(div));
                    
                    $('.userinfo tbody').append(tr);

                });
                index.transshow('show', $('.userinfo'));
            });
        });

        $('.userinfo').on('click', '.deleteUser', function () {
            var uid = $(this).closest('tr').find('#uid').text();
            var tr = $(this).closest('tr');
            
            user.deleteUser(uid).done(function (msg) {
                if(msg === 'success') {
                    tr.remove();
                    toastr['success']('刪除使用者成功','成功');
                } else {
                    console.dir(msg); 
                    toastr['error']('刪除使用者失敗','失敗');
                }
            });

        
        });

        $('.userinfo').on('click', '.adminAction', function () {
            var uid = $(this).closest('tr').find('#uid').text();
            var btn = $(this);
            var admin = $(this).val(); 
            user.adminAction(uid, admin == '1' ? '0' : '1' ).done(function () {
                var before = admin == '1' ? 'btn-warning' : 'btn-success';
                var after = admin == '1' ? 'btn-success' : 'btn-warning';
                btn.removeClass(before).addClass(after);
                btn.text(admin == '1' ? '增加管理者' : '刪除管理者');
                btn.val(admin == '1' ? '0' : '1');
            });

        
        });

        $('#modifyPassword').click(function () {
            resetInput([$('#oldpassword'), $('#newpassword'), $('#confirmpassword')]);
            index.transshow('show', $('.modifyPassword'));
            $('.modify_submit').attr({'disabled':true});
        
        });

        $('.password').on('input', function() {
            $('.password').closest('div').find('small').remove();
            $('.password').removeClass('error');
            $('.password').each(function(index, value) {
                if($(value).val() == '') {
                    $(value).addClass('error');
                    $(value).after($('<small>').text('密碼不得為空'));
                    $('.modify_submit').attr({'disabled':true});
                    return false;
                } else {
                    if($('#newpassword').val() != $('#confirmpassword').val()) {
                        $('#confirmpassword').addClass('error');
                        $('#confirmpassword').after($('<small>').text('密碼不同'));
                        $('.modify_submit').attr({'disabled':true});
                        return false;
                    } 
                    
                    $('.modify_submit').attr({'disabled':false});

                }
            });
        });

        $('.modify_submit').click(function() {
            var oldpass = $('#oldpassword').val();
            var newpass = $('#newpassword').val();
            var $button = $(this);

            $button.button('loading');
            user.modifyPassword(oldpass, newpass).done(function(data) {
                if(data == 'success') {
                    toastr['success']('修改密碼成功','成功');
                } else {
                    toastr['error']('修改密碼失敗', '失敗');
                }
                 
                $button.button('reset');
                resetInput([$('#oldpassword'), $('#newpassword'), $('#confirmpassword')]);
            });


        });

        $('.fileinput-remove').on('click',function() {

            
            $('progress').attr({value: 0});
        });
    
        $('#input-file').fileupload({
            url : 'upload.php',
            dataType: 'json',
            add: function(e,data) {
                
                $('#input-file').closest('.input-group').find('.fileinput-upload').click(function() {
                    
                    $('#progress-file').attr({value: 0});
                    data.submit();
                    $(this).off('click');
                });
            },
            progress: function(e,data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress-file').attr({value: progress});
            },
            done:function(e,data) {
                if(data.result.status == 'error') {
                    toastr['error']('新增使用者失敗', '失敗');
                } else {
                    $.each(data.result.data, function(index, value) {
                        var tr = $('<tr>');
                        var div = $('<div>').attr({class: 'userAction'});
                        var adminBtn = $('<button>').attr({class: 'adminAction btn btn-success',value: '0'});
                        adminBtn.text('增加管理者');
                        div.append($('<button class="deleteUser btn btn-danger">').text('刪除'));
                        div.append(adminBtn);

                        tr.append($('<td>').append(value[0]));
                        tr.append($('<td>').append(value[2]));
                        tr.append($('<td>').append(value[3]));
                        tr.append($('<td> ').append(div));
                        $('.userinfo tbody').append(tr);
                    }); 


                    toastr['success']('新增使用者成功', '成功');
                }
            },
            fail: function(e,data) {
                
               
                toastr['error']('上傳失敗', '失敗');
            },
            stop: function(e) {
            },
        }); 
    
    });

})(jQuery);
