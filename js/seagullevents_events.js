
var ajaxurl = '/assets/modules/seagullevents/ajax.php';

$(document).ready(function() {
	$('ul.b-tabs__buttons').delegate('li:not(.b-tabs__button_current)', 'click', function() {
		$(this).addClass('b-tabs__button_current').siblings().removeClass('b-tabs__button_current').parents('div.b-tabs').find('div.b-tabs__page').hide().eq($(this).index()).show();
	})

	$('.ckeditor').ckeditor();

	$('.b-datepicker').datepicker({dateFormat: 'dd.mm.yy', firstDay:1});
	$('.b-timepicker').timepicker({timeFormat: 'hh:mm'});

//	Save event
	$('#f-event').ajaxForm({url:ajaxurl, dataType:'json',
		beforeSubmit: function(arr, $form, options) {
//			arr['emails'] = window.emails;
			msg.show('Операция выполняется...', 'loading');
		},
		success: function(data) {
			if (data.msgType === 'ok' && typeof(data.itemID)!=undefined) {
				msg.show(data.ok, 'ok');
				$('#itemID').val(data.itemID);
				if (data.reload)
					postForm('editEvent', data.itemID);
			}
			else
				msg.showAjax(data);
		}
	});

//	Постраничная навигация
	$(document).on('click', 'a.b-paginator-link', function() {
		if (!$(this).hasClass('b-paginator-link_disabled')) {
			var page = $(this).attr('href').replace('#page', '');

			$.ajax({ type:'POST', url:ajaxurl, dataType:'json',
				data: {cmd:'getPaginatorPage', pageID:page},
				success: function(data) {
					if (data.error)
						msg.showAjax(data);

					$('#js-table-paginator').children('tbody').html(data.tbody);
					$('ul.b-paginator-links').html(data.links);
				},
				error: function(data){
					msg.show('Ошибка при отправке запроса', 'error');
				}
			});
		}
		return false;
	})

	function split(val) {
		return val.split( /,\s*/ );
	}
	function extractLast( term ) {
		return split(term).pop();
	}

	$("#ff-tags")
		// don't navigate away from the field on tab when selecting an item
		.bind("keydown", function(event) {
			if (event.keyCode === $.ui.keyCode.TAB && $(this).data("autocomplete").menu.active) {
				event.preventDefault();
			}
		})
		.autocomplete({
			source: function(request, response) {
				$.getJSON("/assets/modules/seagulltags/ac_tags.php", {
					term: extractLast(request.term)
				}, response);
			},
			search: function() {
				// custom minLength
				var term = extractLast(this.value);
				if (term.length < 1) {
					return false;
				}
			},
			focus: function() {
				// prevent value inserted on focus
				return false;
			},
			select: function(event, ui) {
				var terms = split(this.value);
				// remove the current input
				terms.pop();
				// add the selected item
				terms.push(ui.item.value);
				// add placeholder to get the comma-and-space at the end
				terms.push("");
				this.value = terms.join(", ");
				return false;
			}
		});

});
