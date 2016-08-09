(function($) {
    function getTemplate() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            data: {
                action: 'getTemplate'
            }
        
        });
    };

   function deleteTemplate(name) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            data: {
                action: 'deleteTemplate',
                params: {
                    template: name
                }
            }
        });
        
    }

    function init() {
        index.transshow('hide', $('.templateinfo'));
        //index.transshow('hide', $('.wrap'));
        $('.templateinfo tbody tr').remove();
        getTemplate().done(function(data) {

            $.each(data, function(index,value){
                var tr = $('<tr>');
                var btn = $('<button>').attr({class: 'btn btn-danger tempAction'}).text('刪除');
                tr.append('<td class="name">'+value.name+'</td>');
                tr.append($('<td>').append(btn));
                $('.templateinfo table tbody').append(tr);
            });

            index.transshow('show', $('.templateinfo'));
        });



    }

    $(function () {
        $('#templateinfo').click(function() {
            init();
        });
        
        
        $('.templateinfo').on('click', '.tempAction', function() {
            console.dir('text'); 
            var name = $(this).closest('tr').find('.name').text();
            var tr = $(this).closest('tr');

            $(this).replaceWith('<div class="loading-action"></div>');
            deleteTemplate(name).done(function(data) {
                if(data.status == 'success') {
                    toastr['success']('刪除模板成功', '成功');
                    tr.remove();
                }
            });

        });

        $('#input-img').fileupload({
            url: 'upload_img.php',
            dataType: 'json',
            add: function(e,data) {
                
                $('#input-img').closest('.input-group').find('.fileinput-upload').click(function() {
                    
                    $('#progress-img').attr({value: 0});
                    data.submit();
                    $(this).off('click');
                });
            },

            progress: function(e,data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress-img').attr({value: progress});
            },

            done: function(e,data) {
                if(data.result.status == 'error') {
                    toastr['error']('失敗', '失敗');
                } else {

                    toastr['success']('成功', '成功');
                    init();
                }
            },
            fail: function(e,data) {
               
                toastr['error']('上傳失敗', '失敗');
            },
 
        
        });
    });
})(jQuery);
