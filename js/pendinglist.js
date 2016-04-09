(function($) {
    list = {};
    list.getData = function() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            data: {
                action : 'pendingList'
            }
        });    
    
    };

    list.gethostCount = function() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            async: false,
            data: {
                action: 'hostCount'
            }
        });
    
    };

    list.sendRequest = function() {
        
    
    };
    

    btnGroup = function(id) {
        var okbtn = $('<button>').attr({class: 'btn btn-success pendingAction', id: id});
        var cancelbtn = $('<button>').attr({class: 'btn btn-danger pendingAction', id: id});
        var div = $('<div>');
        okbtn.text('確認');
        cancelbtn.text('取消');

        div.append(okbtn.clone());
        div.append(cancelbtn.clone());

        return div.clone();
    
    }

    $(function() {
        var hostcount;
        


        list.gethostCount().done(function (data){
            hostcount = data; 
        });

        $('#pendinglist').click(function() {
            index.transshow('hide', $('.pending'));
            $('.pending table tbody tr').remove();

            list.getData().done(function(data) {
                var isadmin = data['isadmin'] == "1" ? true : false;
                isadmin ? $('table .isadmin').show() : $('table .isadmin').hide();

                delete data.isadmin;

                $.map(data, function(value){ return [value]}).forEach(function (list) {
                    var tr = $('<tr>').attr({id: list.id});
                    var select = $('<select>').attr({class: 'form-control'});

                    for(var i=0;i<hostcount;i++) {
                        select.append('<option value="'+i+'">'+(i+1)+'</option>');
                    }

                    isadmin && tr.append($('<td>').text(list.uid));
                    tr.append($('<td>').text(list.name));
                    tr.append($('<td>').text(list.vcpu));
                    tr.append($('<td>').text(list.mem + 'GB'));
                    tr.append($('<td align="center">').text(list.template));
                    tr.append($('<td>').append(select));
                    tr.append($('<td>').append(btnGroup(list.id)));
                    $('.pending tbody').append(tr); 
                
                });
                index.transshow('show', $('.pending'));
            });
       
        }); 


    });
})(jQuery);
