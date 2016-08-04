(function($) {

    $(function () {
        $('#templateinfo').click(function() {
            index.transshow('hide',$('.templateinfo'));

            index.transshow('show', $('.templateinfo'));
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
                }
            },
            fail: function(e,data) {
               
                toastr['error']('上傳失敗', '失敗');
            },
 
        
        });
    });
})(jQuery);
