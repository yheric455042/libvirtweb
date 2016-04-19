(function($) {
    var user = {};
    
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
            async: false,
            dataType: 'json',
            data: {
                action: 'getAlluser',
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


    $(function () {

        user.getAlluser().done(function(data) {
            $('.user-button').click(function() {
                var btn = $(this);

                if(btn.val() === 'user_submit') {
                    var uid = $('#Inputuid').val();
                    var displayname = $('#Inputdisplayname').val();
                    var password = $('#Inputpassword').val();
                    var email = $('#Inputemail').val();

                    user.Createuser(uid, password, displayname, email).done(function(status) {

                        if(status == 'error') {
                            alert('error');

                        } else {
                            var tr = $('<tr>');

                            tr.append($('<td>').append(uid));
                            tr.append($('<td>').append(displayname));
                            tr.append($('<td>').append(email));
                            
                            $('.userinfo tbody').append(tr);
                            
                            alert('success');
                        }

                    });
                
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
