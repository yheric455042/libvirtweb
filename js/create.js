(function($) {
    var create = {};
    
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

    create.gethostCount = function() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            async: false,
            data: {
                action: 'hostCount'
            }
        });
    
    }


    $(function () {
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
        
       
        $('.btn-default').click(function() {
            if($(this).val() == 'create_submit') {
                var name = $('#Inputname').val();    
                var vcpu = $('#Inputvcpu').val();    
                var mem = $('#Inputmem').val();    
                var template = $('#Inputtemplate').val();    
                var host = $('#Inputhost').val();
                var button = $(this);
                create.sendData(name, vcpu, mem, template, host).done(function (data) {
                   
                    console.dir(data);
                });



            
            }
        }); 

    });

})(jQuery);
