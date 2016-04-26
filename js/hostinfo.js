(function($) {
    
    function gethostInfo() {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            data: {
                action : 'hostInfo'
            }
        });
    }



    $(function() {
         
        $('#hostinfo').click(function() {
            index.transshow('hide', $('.hostinfo'));
            $('.hostinfo').find('div').remove();
            gethostInfo().done(function (result) {
                
                $.each(result, function(index, host) {
                    var div = $('<div>');
                    var progress_cpu = $('<div>').attr({id: 'Progress'});  
                    var progress_mem = $('<div>').attr({id: 'Progress'});  
                    var bar_cpu = $('<div>').attr({id: 'Bar'});
                    var bar_mem = $('<div>').attr({id: 'Bar'});
                    var hostname = $('<h4>').text('HOST'+(index + 1));
                    var cpu = $('<h5>').text('CPU用量');
                    var mem = $('<h5>').text('記憶體用量');
                    var cpu_used = (host.vcpu_used/host.vcpu_max*100) <= 100 ? (host.vcpu_used/host.vcpu_max*100) : 100;
                    var mem_used = (host.mem_used/host.mem_max*100) <= 100 ? (host.mem_used/host.mem_max*100) : 100;

                    bar_cpu.css('width', cpu_used+'%');
                    bar_mem.css('width', mem_used+'%');
                    div.append(hostname);
                    div.append(cpu);
                    progress_cpu.append(bar_cpu);
                    div.append(progress_cpu);
                    div.append(host.vcpu_used+' / '+host.vcpu_max);
                    div.append(mem);
                    progress_mem.append(bar_mem);
                    div.append(progress_mem);
                    div.append(host.mem_used+'GB / '+host.mem_max+'GB');
                    div.after($('<hr>'));

                    $('.hostinfo').append(div);

                    
 
                });

                index.transshow('show', $('.hostinfo')); 
            });
        });

    });


})(jQuery)
