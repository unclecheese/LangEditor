(function($) {
	$.entwine.warningLevel = $.entwine.WARN_LEVEL_BESTPRACTISE;
	$.entwine('ss', function($) {

		$('#Form_CreateTranslationForm').entwine({
			onsubmit: function(){
				var $t = $(this);
				var old_lang = $('#Form_CreateTranslationForm_LanguageFrom').val();
				var new_lang = $('#Form_CreateTranslationForm_LanguageTo').val();
				$.post(
					$t.attr('action'),
					$t.serialize(),
					function(data) {
						// display message
						statusMessage(decodeURIComponent(data), 'good');
						// reload language list
						var url = $('#'+old_lang).attr('href').replace('/'+old_lang+'/', '/'+new_lang+'/');
						window.location = $.path.makeUrlAbsolute(url, $('base').attr('href'));
					}
				);
				return false;
			}
		});

		$('#Form_TranslationForm').entwine({
			onsubmit: function(e) {
				$('.cms-content').addClass('loading');
				$t = $(this);
				$t.removeClass('changed');
				$.post(
					$t.attr('action'),
					$t.serialize(),
					function(data) {
						$('.cms-content').removeClass('loading');
						statusMessage(data, 'good');
						var url = $('#available_languages a.current').first().attr('href');
						window.location = $.path.makeUrlAbsolute(url, $('base').attr('href'));
					}
				);
				return false;
			}
		});

		$('#Form_LangEditor_Search .select-search select').entwine({
			onchange: function(){
				if($(this).val().length > 0) {
					$('.namespace').hide();
					$('#namespace-'+$(this).val()).show();
				} else {
					$('.namespace').show();
				}
			}
		});

		var timeout = false;

		$('#Form_LangEditor_Search .inline-search input').entwine({
			onkeyup: function(){
				if(timeout) window.clearTimeout(timeout);
				s = $(this).val();
				timeout = window.setTimeout(function() {
					$('.entity').hide();
					$('.entity').each(function() {
						reg = new RegExp(s,"i");
						if($(this).find('label').text().match(reg) || $(this).find('.entity_field input').val().match(reg)) {
							$(this).show();
						}
					});
				},150);
			}
		});

	});
})(jQuery);