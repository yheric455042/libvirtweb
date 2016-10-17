function reset (elementArray) {
    $.each(elementArray ,function(index, element) {
        
        element.is('select') ? element.find('option').first().attr({'selected': true}) : element.val(""); 

    });

}

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

    create.getTemplate = function() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            async: false,
            data: {
                action: 'getTemplate'
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
        reset(elementArray);
        $('#Inputname').removeClass('error');
        $('.create_submit').attr({'disabled': true});
    };

    $(function () {
        resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate'), $('#Inputhost')]); 

        if(!index.isadmin) {
            $('.isadmin').hide();
            $('#create_vm').append('申請虛擬機');
        } else {
            
            $('#create_vm').append('添加虛擬機');
        }
        
        $('#create_vm').click(function() {
            create.getTemplate().done(function(data) {
                $('#Inputtemplate').find('option').remove();
                $.each(data, function(index, value) {
                  $('#Inputtemplate').append('<option value="'+index+'">'+value.name+'</option>');  
                }); 
            });

            create.gethostCount().done(function(count) {
                 
                for(var i=0; i<count;i++) {
                    $('#Inputhost').append('<option value="'+i+'">'+(i+1)+'</option>');
                }
            }); 
        });


        $('.btn-default').click(function() {
            var button = $(this);

            if(button.val() == 'create_submit') {
                var name = $('#Inputname').val();
                var vcpu = $('#Inputvcpu').val();    
                var mem = $('#Inputmem').val(); 
                var template = $('#Inputtemplate').val();    
                var host = $('#Inputhost').val();
                
                create.sendData(name, vcpu, mem, template, host).done(function (data) {
                    if(index.isadmin) {
                        resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate'), $('#Inputhost')]);
                        toastr['success']('創建虛擬機器成功','成功');
                        window.location.href = './index.php';
                    } else {
                        resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate')]);
                        toastr['success']('申請虛擬機器成功','成功');
                    
                    }
                });
            }
             
        });

        $('.create_close').click(function() {
            $('.form-group').find('small').remove();
            $('#Inputhost').find('option').remove();
            $('#Inputtemplate').find('option').remove();
            resetInput([$('#Inputname'), $('#Inputvcpu'), $('#Inputmem'), $('#Inputtemplate'), $('#Inputhost')]); 
        });

        $('#Inputname').on('input', function() {
            var name = $(this).val();
            var input = $(this);
            input.closest('div').find('small').remove();
            name != '' && $('.create_submit').attr({'disabled': false});
            $('.wrap tr').each(function(index, value) {
                if(($(value).find('#name').text() == name || name == '') && (index.isadmin ? $(value).find('#uid').text() == index.uid : 1)) {
                    input.addClass('error');
                    input.after($('<small>').text('虛擬機器名稱已重複'));
                    $('.create_submit').attr({'disabled': true});
                    return false;
                } else {
                    input.removeClass('error');
                    $('.create_submit').attr({'disabled': false});
                }
            });

        });

    });

})(jQuery);
