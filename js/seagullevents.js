
var ajaxurl = '/assets/modules/seagullevents/ajax.php';

$(document).ready(function() {
//	В td:not(td:nth-child(2)) перечислить номера тех столбцов на которых при клике не осуществлять переход
	$(document).on('click', '.b-table > tbody > tr.row-edit > td:not(td:nth-child(2))', function() {
		postForm('editEvent', $(this).parent('tr').attr('id').replace(/row/, ''));
	});

	$('.datepicker').datepicker({
		dateFormat: 'dd.mm.yy',
		yearRange: "1950:2015",
		changeMonth: true,
		changeYear: true,
		showOn: "both",
		buttonImage: "/assets/modules/seagulllibrary/css/images/calendar.png",
		buttonImageOnly: true
	});

	$('#filter-send').click(function() {
		$.ajax({ type:'POST', url:ajaxurl, dataType:'json',
			data: {cmd:'filter', date:$('#filter-date').val()},
			success: function(data) {
				if (data.error)
					msg.showAjax(data);

				$('#t-events').children('tbody').html(data.tbody);
//				$('ul.paginator-links').html(data.links);
				$('#btn-show-all').show();
			},
			error: function(data){
				msg.show('Ошибка при отправке запроса', 'error');
			}
		});
	});

	$('#btn-show-all').click(function() {
		$.ajax({ type:'POST', url:ajaxurl, dataType:'json',
			data: {cmd:'getPaginatorPage', pageID:1},
			success: function(data) {
				if (data.error)
					msg.showAjax(data);

				$('#t-events').children('tbody').html(data.tbody);
				$('ul.paginator-links').html(data.links);
			},
			error: function(data){
				msg.show('Ошибка при отправке запроса', 'error');
			}
		});
	});

//	Постраничная навигация
	$(document).on('click', 'a.paginator-link', function() {
		if (!$(this).hasClass('paginator-link_disabled')) {
			var page = $(this).attr('href').replace('#page', '');

			$.ajax({ type:'POST', url:ajaxurl, dataType:'json',
				data: {cmd:'getPaginatorPage', pageID:page},
				success: function(data) {
					if (data.error)
						msg.showAjax(data);

					$('#t-events').children('tbody').html(data.tbody);
					$('ul.paginator-links').html(data.links);
				},
				error: function(data){
					msg.show('Ошибка при отправке запроса', 'error');
				}
			});
		}
		return false;
	})
});
