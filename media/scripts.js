var PDT = {
	showLoading: function(){
		$('#status_bar').fadeIn(150);
	},
	
	hideLoading: function(){
		$('#status_bar').fadeOut(300);
	},
	
	loadHandler: function(hdl, args, cb){
		$.post("index.php", {handler:hdl, args:args, shell:true}, function(d){
			if(d.substr(0,1)=='{'||d.substr(0,1)=='['){
				if(typeof cb == 'function'){
					cb($.parseJSON(d));
				}
			}else{
				if(cb != 'silent'){
					PDT.showMessage(d);
				}
			}
		});
	},
	
	focusOnPage: function(page){	// Просто выносит страницу на передний план
		$('.menu_link').removeClass('active');
		$('.menu_'+page).addClass('active');
		
		$('.page').hide();
		$('#page_'+page).show();
	},
	
	showMessage: function(msg){
		$('<div>', {class: 'message'}).html(msg).appendTo("#message_bar").delay(4000).fadeOut();
	},
	
	runScript: function(scr){
		$.post("index.php", {handler:'runScript', script:scr});
		PDT.loadScriptList();
	},
	
	stopScript: function(scr){
		PDT.showLoading();
		PDT.loadHandler('stopScript', {script:scr}, function(d){
			if(parseInt(d) == 1){
				PDT.showMessage('Скрипт остановлен');
			}else{
				PDT.showMessage('Невозможно остановить скрипт');
			}
			PDT.loadScriptList();
			PDT.hideLoading();
		});
	},
	
	showPopup: function(type, title, msg, cb){
		// showPopup(0, 'Заголовок Alert', 'Сообщение');
		// showPopup(1, 'Заголовок Confirm', 'Сообщение', function(){...});
		// showPopup(2, 'Заголовок Prompt', 'Сообщение', function(){ $('#pdt_popup_value').text(); });
		var wrap = $('<div>', {class: 'pdt_popup'}).appendTo('#wrap');
		$('<div>', {class: 'pdt_popup_title'}).html(title).appendTo(wrap);
		var content = $('<div>', {class: 'pdt_popup_content'}).html(msg).appendTo(wrap);
		$('<div>', {class: 'clear'}).appendTo(content);
		if(type == 'alert' || type == 0){
			$('<div>', {class: 'pdt_button'}).html('OK').bind('click', function(){$(this).parent().parent().fadeOut(150);}).appendTo(content);
		}else if(type == 'confirm' || type == 1){
			$('<div>', {class: 'pdt_button', onclick: '$(this).parent().parent().fadeOut(150);'}).html('Подтвердить').bind('click', cb).appendTo(content);
			$('<div>', {class: 'pdt_button', onclick: '$(this).parent().parent().fadeOut(150);'}).html('Отмена').appendTo(content);
		}else if(type == 'prompt' || type == 2){
			pr_value = $('<div>', {class: 'pdt_input_text', contenteditable: 'true'}).appendTo(content);
			$('<div>', {class: 'pdt_button', onclick: '$(this).parent().parent().fadeOut(150);'}).bind('click', {'obj':pr_value}, cb).html('OK').appendTo(content);
		}
		$(wrap).fadeIn(150);
	},
	
	addNewScript: function(e){
		PDT.showLoading();
		PDT.loadHandler('addScript', {script:$(e.data.obj).html()}, function(arr){
			PDT.loadScriptList();
			if(typeof(arr.error) == "undefined"){
				PDT.loadScriptEditor(arr.script, function(){
					PDT.loadScriptEditor(arr.script, function(){
						PDT.showScriptEditor(arr.script);
					});
				});
			}else{
				PDT.showMessage(arr.error);
			}
			PDT.hideLoading();
		});
	},
	
	deleteScript: function(scr){
		PDT.showLoading();
		PDT.loadHandler('deleteScript', {script:scr}, function(data){
			PDT.loadScriptList();
			if(typeof data.error == 'undefined' && data.success == 'true'){
				PDT.showMessage('Скрипт удален');
			}else{
				PDT.showMessage(data.error);
			}
			PDT.hideLoading();
		});
	},
	
	saveScript: function(scr, editor){
		PDT.showLoading();
		PDT.loadHandler('saveScript', {script:scr, content:editor.getValue()}, function(data){
			if(typeof data.error == 'undefined' && data.success == 'true'){
				PDT.showMessage('Скрипт сохранен');
			}else{
				PDT.showMessage(data.error);
			}
			PDT.hideLoading();
		});
	},
	
	loadScriptList: function(){		// Наполняет страницу скриптов (обновляет)
		PDT.showLoading();
		PDT.loadHandler('scriptList', {}, function(arr){
			$('#page_scripts').html('');
			var block_title = $('<div>', {class: 'block_title'}).html('Список скриптов').appendTo('#page_scripts');
			$('<div>', {class: 'title_button'}).bind('click', function(){PDT.showPopup(2, 'Новый скрипт', 'Укажите название нового скрипта', function(e){PDT.addNewScript(e);});}).html('Создать новый').appendTo(block_title);
			$('<div>', {class: 'title_button'}).bind('click', function(){PDT.loadScriptList();}).html('Обновить').appendTo(block_title);
			$(arr).each(function(k,v){
				v.status = parseInt(v.status);
				if(v.status == 0 || isNaN(v.status)){
					var statclass = 'list_unit_status_red';
					var stattitle = 'Остановлен';
				}else if(v.status == 1){
					var statclass = 'list_unit_status_green';
					var stattitle = 'Работает';
				}else if(v.status == 2){
					var statclass = 'list_unit_status_yellow';
					var stattitle = 'Ожидает / спит';
				}
				
				var li = $('<div>', {class: 'list_unit', script: v.script}).appendTo('#page_scripts');
				$('<div>', {class: 'list_unit_status '+statclass, title: stattitle}).appendTo(li);
				$('<div>', {class: 'list_unit_name', title: 'Использовано памяти: '+v.ramsize+'b'}).html(v.script).appendTo(li);
				if(v.status == 0 || isNaN(v.status)){
					$('<div>', {class: 'list_unit_button list_unit_button_delete', title: 'Удалить', onclick: 'PDT.showPopup(1, "Удаление скрипта", "Вы действительно хотите удалить скрипт '+v.script+'?", function(){PDT.deleteScript("'+v.script+'");});'}).appendTo(li);
					$('<div>', {class: 'list_unit_button list_unit_button_edit', title: 'Редактировать', onclick: 'PDT.loadScriptEditor("'+v.script+'", function(){PDT.showScriptEditor("'+v.script+'");})'}).appendTo(li);
					$('<div>', {class: 'list_unit_button list_unit_button_run', title: 'Запустить', onclick: 'PDT.runScript("'+v.script+'")'}).appendTo(li);
					$('<div>', {class: 'list_unit_button list_unit_button_console', title: 'Консоль', onclick: 'PDT.loadScriptConsole("'+v.script+'", function(){PDT.showScriptConsole("'+v.script+'");})'}).appendTo(li);
				}else if(v.status == 1 || v.status == 2){
					$('<div>', {class: 'list_unit_button list_unit_button_delete', title: 'Удалить', onclick: 'PDT.showPopup(1, "Удаление скрипта", "Вы действительно хотите удалить скрипт '+v.script+'?", function(){PDT.deleteScript("'+v.script+'");});'}).appendTo(li);
					$('<div>', {class: 'list_unit_button list_unit_button_edit', title: 'Редактировать', onclick: 'PDT.loadScriptEditor("'+v.script+'", function(){PDT.showScriptEditor("'+v.script+'");})'}).appendTo(li);
					$('<div>', {class: 'list_unit_button list_unit_button_stop', title: 'Остановить', onclick: 'PDT.stopScript("'+v.script+'")'}).appendTo(li);
					$('<div>', {class: 'list_unit_button list_unit_button_console', title: 'Консоль', onclick: 'PDT.loadScriptConsole("'+v.script+'", function(){PDT.showScriptConsole("'+v.script+'");})'}).appendTo(li);
				}
			});
			PDT.hideLoading();
		});
	},
	
	loadScriptEditor: function(scr, cb){	// Загружает (создает) вкладку редактора скрипта (обновляет)
		PDT.showLoading();
		PDT.loadHandler('scriptEditor', {script:scr}, function(arr){
			if(!$('#script_'+scr+'_editor')[0]){
				$('<div>', {id: 'script_'+scr+'_editor_tab', class: 'editor_tab tab', onclick: 'PDT.showScriptEditor("'+scr+'")'}).html(scr).appendTo('#editor_tabs');
				$('<div>', {id: 'script_'+scr+'_editor', class: 'editor'}).appendTo('#editor');
			}

			editor = ace.edit("script_"+scr+"_editor");
			editor.getSession().setMode("ace/mode/php");
			editor.setTheme("ace/theme/eclipse_notepad");
			editor.setValue(arr.content);
			
			$("#script_"+scr+"_editor").editorhdl = editor;
			
			editor.gotoLine(1);
			
			$("#script_"+scr+"_editor").keypress(function(e) {
				if (!(e.which == 115 && e.ctrlKey) && !(e.which == 19)) return true;
					if($("#script_"+scr+"_editor").attr('paused') == 'true'){
						e.preventDefault();
						return false;
					}else{
						PDT.saveScript(scr, editor);
						$("#script_"+scr+"_editor").attr('paused', 'true');
						setTimeout(function(){
							$("#script_"+scr+"_editor").attr('paused', 'false');
						}, 750);
						e.preventDefault();
						return false;
					}
			});
			if(typeof cb == 'function'){
				cb();
			}
			PDT.hideLoading();
		});
	},
	
	loadScriptConsole: function(scr, cb){	// Загружает (создает) вкладку с консолью скрипта (обновляет)
		PDT.loadHandler('console', {script:scr}, function(arr){
			if($('#script_'+scr+'_console').length){
				var console_div = $('#script_'+scr+'_console').html('');
			}else{
				$('<div>', {id: 'script_'+scr+'_console_tab', class: 'console_tab tab', onclick: 'PDT.showScriptConsole("'+scr+'")'}).html(scr).appendTo('#console_tabs');
				var console_div = $('<div>', {id: 'script_'+scr+'_console', class: 'console'}).appendTo('#console');
				$('<div>', {id: "console_line_"+scr, class: "console_line", contenteditable: "true"}).appendTo('#console_line');
				$('<div>', {id: "console_line_"+scr+"_submit", class: "console_line_submit", onclick: "PDT.inputScript('"+scr+"', $('#console_line_"+scr+"').html());"}).html('Отправить').appendTo('#console_line');
				$("#console_line_"+scr).keypress(function(e) {
					if (e.which == 13){
						PDT.inputScript(scr, $('#console_line_'+scr).html());
						e.preventDefault();
						return false;
					}
				});
			}
			$(arr).each(function(k,v){
				var li = $('<div>', {class: 'console_unit '+v.type}).appendTo('#script_'+scr+'_console');
				$('<div>', {class: 'time', title: v.date}).html(v.time).appendTo(li);
				$('<div>', {class: 'message'}).html(v.message).appendTo(li);
				if(v.type == 'shutdown'){
					PDT.showMessage('Скрипт завершил работу');
					PDT.loadScriptList();
				}
			});
			
						
			if(typeof cb == 'function'){
				cb(scr);
			}
		});
	},
	
	showScriptConsole: function(scr){	// Открывает вкладку консоли скрипта
		PDT.focusOnPage('console');
		$('.console_line').each(function(){
			$(this).hide();
		});
		$('#console_line_'+scr).show();
		
		$('.console_line_submit').each(function(){
			$(this).hide();
		});
		$('#console_line_'+scr+'_submit').show();
		
		$('.console').each(function(){
			$(this).hide();
		});
		$('#script_'+scr+'_console').show();
		
		$('.console_tab').each(function(){
			$(this).removeClass('active');
		});
		$('#script_'+scr+'_console_tab').addClass('active');
	},
	
	showScriptEditor: function(scr){	// Открывает вкладку редактора скрипта
		PDT.focusOnPage('editor');
		$('.editor').hide();
		$('#script_'+scr+'_editor').show();
		
		$('.editor_tab').removeClass('active');
		$('#script_'+scr+'_editor_tab').addClass('active');
	},
	
	inputScript: function(scr, data){
		$('#console_line_'+scr).html('');
		PDT.loadHandler('inputScript', {script: scr, data: data}, 'silent');
	},
	
	listenChanges: function(){
		PDT.loadHandler('listener', {}, function(arr){
			if(typeof(arr.timeout) == "undefined"){	// Changes ;)
				$(arr).each(function(k,v){
					if(!$('#script_'+v+'_console').length){
						PDT.loadScriptConsole(v, function(scr){
							PDT.showScriptConsole(scr);
						});
					}else{
						PDT.loadScriptConsole(v);
					}
				});
			}
			PDT.listenChanges();
		});
	}
}

	