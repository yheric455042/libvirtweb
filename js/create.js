(function($) {
    var create = {
        userVms: ''
    };
    
    create.sendData = function(name, vcpu, mem, template, host) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            data:{
                action: 'pendingCreate',
                params: {
                    uid: index.uid,
                    isadmin: index.isadmin,
                    name: name,
                    vcpu: vcpu,
                    mem: mem,
                    template: template,
                    host: host ? host : ''
                }
            }
        
        });
    
    };
    
    create.getAllvmName = function(uid) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            async: false,
            dataType: 'json',
            data: {
                action: 'getAllvmName',
                params: {
                    uid: uid
                }
            }
        });
    }

    create.gethostCount = function() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            async: false,
            data: {
                action: 'hostCount'
            }
        });
    
    };
    
    function resetInput(elementArray) {
        $.each(elementArray ,function(index, element) {
            
            element.is('select') ? element.find('option').first().attr({'selected': true}) : element.val(""); 

        });
        $('#Inputname').removeClass('error');
        $('.submit').attr({'disabled': true});
        

    };

    $(function () {

        resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate'), $('#Inputhost')]); 

        if(!index.isadmin) {
            $('.isadmin').hide();
            $('#create_vm').append('申請虛擬機');
        } else {
            
            $('#create_vm').append('添加虛擬機');
            create.gethostCount().done(function(count) {
                 
                for(var i=0; i<count;i++) {
                    $('#Inputhost').append('<option value="'+i+'">'+(i+1)+'</option>');
                }
            }); 
        
        }

        create.getAllvmName(index.uid).done(function(data) {

            $('.btn-default').click(function() {
                var button = $(this);

                if(button.val() == 'create_submit') {
                    var name = $('#Inputname').val();
                    var vcpu = $('#Inputvcpu').val();    
                    var mem = $('#Inputmem').val();    
                    var template = $('#Inputtemplate').val();    
                    var host = $('#Inputhost').val();

                    create.sendData(name, vcpu, mem, template, host).done(function (data) {
                       
                        resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate'), $('#Inputhost')]); 
                    });
                
                } else if(button.val() == 'cancel') {
                    resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate'), $('#Inputhost')]); 
                    
                }
            });

            $('.close').click(function() {
                
                resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate'), $('#Inputhost')]); 
            });

            $('#Inputname').keyup(function() {
                var name = $(this).val();
                var input = $(this);
                var checked = false;
                
                $.each(data, function(index, value) {
                    if(name == value.name || name == '') {
                        input.addClass('error');
                        $('.submit').attr({'disabled': true});
                        return false;
                    } else {
                        input.removeClass('error');
                        $('.submit').attr({'disabled': false});
                    }
                });

            });

        });

       
        

    });

})(jQuery);
