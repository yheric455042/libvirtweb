var index = {
		uid: '',
		isadmin: '',
        shutdownButton:  $('<button>').attr({class: 'vmAction btn btn-danger' , id: 'shutdown'}),
	    startButton: $('<button>').attr({class: 'vmAction btn btn-success' , id: 'start'}),
	    deleteButton: $('<button>').attr({class: 'vmAction btn btn-danger' , id: 'delete'}),
		forceoffButton: $('<button>').attr({class: 'vmAction btn btn-warning' , id: 'forceoff'}),
		VNCButton: $('<button>').attr({class: 'vmAction btn btn-warning' , id: 'VNC'}),

};


(function ($) {
	
	    
    index.init = function() {
        index.startButton.text('開機');
        index.shutdownButton.text('關機');
        index.deleteButton.text('刪除');
        index.forceoffButton.text('強制關機');
        index.VNCButton.text('VNC');
        $('.wrap').hide();
        $('.loading-list').show();
    };
    
    

	index.getVMList = function(user) {
		return $.ajax({
			type: 'POST',
			url: 'base.php',
			dataType: 'json',
			data: {
				action: 'getVMList',
				params: {
					uid: user,
                    isadmin: index.isadmin
				}
			}
		});
	};

    index.stateControl = function (currstate, appendWith, uuid) {
    
        var shutdown = index.shutdownButton.clone().attr({value: uuid});
        var start = index.startButton.clone().attr({value: uuid});
        var del = index.deleteButton.clone().attr({value: uuid});
        var forceoff = index.forceoffButton.clone().attr({value: uuid});
        var vnc = index.VNCButton.clone().attr({value: uuid});
        
	    
        if(currstate == 'running') {
            appendWith.append(shutdown);
            appendWith.append(forceoff);
        } else {
            appendWith.append(start);
            appendWith.append(del);
        }

        return appendWith;
    };

	index.logout = function() {
		return $.ajax({
			type: 'POST',
			url: 'base.php',
			async: false,
			data: {action: 'logout'}
		});
	};
		
	index.getCurrentUid = function () {
		return $.ajax({
			type: 'POST',
			url: 'base.php',
			dataType: 'json',
			async: false,
			data: {
				action: 'getuid',
			}
		});
	};

    index.vmcontrol = function(action, uuid, host) {
        return $.ajax({
            type: 'POST',
            url: 'base.php',
            dataType: 'json',
            data: {
                action: 'domainControl',
                params: {
                    action: action,
                    uuid: uuid,
                    host: host
                }
            }
        
        });
    
    };

	$(function () {
        
        index.init();
        
		index.getCurrentUid().done(function (data) {
			var li = $('#uid');
			var p = $('<p>').attr({class: 'navbar-text'});
			p.text('歡迎 '+data.uid);
			li.append(p);
	
			index.uid = data.uid;
			index.isadmin = data.isadmin === '1' ? true : false;
		});
		
		index.getVMList(index.uid).done(function (data) {
            $('.wrap').hide();
            $('.loading-list').show();
            index.isadmin ? $('table .isadmin').show() : $('table .isadmin').hide();
			data.forEach(function (vm) {
				var tr = $('<tr>');
				var action  = $('<td>');
                var action_td = $('<td>').attr({id: 'vmControl'});
                var div = $('<div>');
                if(vm.name != null) {
                    index.isadmin && tr.append($('<td id="uid">').text(vm.uid));
                    tr.append($('<td id="name">').text(vm.name));
                    index.isadmin && tr.append($('<td id="host">').text(vm.host));
                    tr.append($('<td id="vcpu">').text(vm.vcpu));
                    tr.append($('<td id="mem">').text(vm.mem));
                    tr.append($('<td id="disk">').text(vm.disk));
                    tr.append($('<td id="arch">').text(vm.arch));	
                    tr.append($('<td id="state">').append($('<div>').append(vm.state)));
                    tr.append(index.stateControl(vm.state, div, vm.uuid));
                    action_td.append(div);
                    tr.append(action_td);
                    $('.list tbody').append(tr);
                }
            });
            $('.wrap').show();
            $('.loading-list').hide();


		});
	    
        $('table').on('click', '.vmAction' ,function() {
            var action = $(this).attr('id');
            var uuid  = $(this).val();
            var state = $(this).closest('tr').find('#state');
            var loading_action = $('<div>').attr({class: 'loading-action'});
            var action_td = $(this).closest('td');
            var curr_tr = $(this).closest('tr');

            curr_tr.find('#vmControl div').replaceWith(loading_action.clone());
            curr_tr.find('#state div').replaceWith(loading_action.clone());
            
            index.vmcontrol(action, uuid, 0).done(function (data) {

                if(data.msg == 'success') {
                
                curr_tr.find('#vmControl div').replaceWith(index.stateControl(data.state, $('<div>'), uuid));
                curr_tr.find('#state div').replaceWith($('<div>').append(data.state)); 


                } else if(data.msg == 'success_delete') {
                    //init view
                    $('.wrap').hide();
                    $('.loading-list').show();
                    
                    curr_tr.remove();

                    $('.wrap').show();
                    $('.loading-list').hide();

                } else {
                    alert('error');
                    window.location.href = './index.php';

                }
           
            });
        }); 


		$('#logout').click(function() {
			index.logout().done(function(data){
				if(data == 'success') {
					window.location.href = './login.php';
				}

			});
		});


	});

})(jQuery);
