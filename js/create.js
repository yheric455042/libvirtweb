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
        } else {
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

                create.sendData(name, vcpu, mem, template, host).done(function (data) {
                   console.dir(data); 
                
                });



                $(this).attr({'data-dismiss': 'modal'});
            
            }
        }); 

    });

})(jQuery);
