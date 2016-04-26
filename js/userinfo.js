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

    user.getAlluser = function() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            async: false,
            data: {
                action: 'userList',
            }
        });
    }


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
        user.getAlluser().done(function(data) {
            $('.user-btn').click(function() {
                var btn = $(this);

                if(btn.val() === 'user_submit') {
                    var uid = $('#Inputuid').val();
                    var displayname = $('#Inputdisplayname').val();
                    var password = $('#Inputpassword').val();
                    var email = $('#Inputemail').val();
                    var tr = $('<tr>');
                    var loading_td = $('<td>').append($('<div>').attr({class: 'loading-action'}));
                    var tds = [loading_td.clone(), loading_td.clone(), loading_td.clone()];

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
                                var out = [uid, displayname, email]
                                value.find('div').replaceWith(out[index]);
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

            $('#Inputuid').keyup(function() {
                
                var input = $(this);
                input.closest('div').find('small').remove();
                $.each(data, function(index, value) {
                    if(value.uid == input.val()) {
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

            $('#Inputemail').keyup(function() {
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

                    tr.append($('<td>').append(user.uid));
                    tr.append($('<td>').append(user.displayname));
                    tr.append($('<td>').append(user.email));
                    
                    $('.userinfo tbody').append(tr);

                });
                index.transshow('show', $('.userinfo'));
            });
        });

    
    });

})(jQuery);
