
var ajaxurl = '/assets/modules/seagullevents/ajax.php';
var active_lang = undefined;

$(document).ready(function() {
	$('#js-lang-add').click(function(){
		if ($('#js-lang-alias').val() && $('#js-lang-name').val()) {
			$('#js-lang-select').append('<option value="'+$('#js-lang-alias').val()+'">'+$('#js-lang-name').val()+'</option>');
		}
		else
			alert('Заполните все поля');
		return false;
	})

	$('#js-lang-del').click(function(){
		if (active_lang !== undefined) {
			$('#js-lang-select option[value="'+active_lang+'"]').remove();
		}
		else
			alert('Выделите язык');
		return false;
	})

	$('#js-lang-select').change(function(){
        active_lang = $('option:selected', this).val();
        $('#js-lang-alias').val($('option:selected', this).val());
		$('#js-lang-name').val($('option:selected', this).html());
		return false;
	})

	$('#js-lang-up').click(function(){
		if (active_lang !== undefined) {
			sss = $('#js-lang-select > option:selected');
			if (sss.index() > 0) {
				ddd = sss.prev();
				var i = sss.clone().insertBefore(ddd).index()+1;
				sss.remove();
				$('#js-lang-select > option:eq('+i+')').attr('selected', 'selected');
			}
		}
		else
			alert('Выделите язык');
		return false;
	})
	$('#js-lang-down').click(function(){
		if (active_lang !== undefined) {
			sss = $('#js-lang-select > option:selected');
			var count = $('#js-lang-select > option:last').index();
			if (sss.index() < count) {
				ddd = sss.next();
				var i = sss.clone().insertAfter(ddd).index()-1;
				sss.remove();
//				$('#js-lang-select > option').attr('selected', '');
				$('#js-lang-select > option:eq('+i+')').attr('selected', 'selected');
			}
/*
			sss = $('#js-lang-select > option[value="'+active_lang+'"]');
			var count = $('#js-lang-select > option:last').index();
			if (sss.index() < count) {
				ddd = sss.next();
				var i = sss.clone().insertAfter(ddd).index()-1;
				sss.remove();
				console.log(i);
				$('#js-lang-select > option').attr('selected', '');
				$('#js-lang-select > option:eq('+i+')').attr('selected', 'selected');
			}
*/
		}
		else
			alert('Выделите язык');
		return false;
	})
});
