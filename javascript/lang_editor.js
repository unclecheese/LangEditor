(function($) {
$(function() {
	
	$('body').ajaxStart(function() {$('#ajax-loader').show();});
	$('body').ajaxStop(function() {$('#ajax-loader').hide();});
	
	$('#available_languages ul li a').live("click",function() {
		$t = $(this);
		$('#translations').load($t.attr('href'));
		$('#available_modules').load($t.attr('href').replace("show", "updatemodules"));
		$('#available_languages').load($t.attr('href').replace("show", "updatelanguages"));
		$('#create_translation_form').load($t.attr('href').replace("show", "createtranslationform"));
		return false;
	});
	
	$('#available_modules ul li a').live("click",function() {
		$t = $(this);
		$('#translations').load($t.attr('href'));
		$('#available_modules').load($t.attr('href').replace("show", "updatemodules"));
		$('#available_languages').load($t.attr('href').replace("show", "updatelanguages"));
		$('#create_translation_form').load($t.attr('href').replace("show", "updatecreateform"));
		return false;
	});

	$('#translations h3 a').livequery("click",function() {
		$t = $(this);
		$t.parents('h3').next('.namespace').slideDown();		
	});
	
	$('#Form_CreateTranslationForm').live("submit",function() {
		var $t = $(this);
		var new_lang = $('#Form_CreateTranslationForm_LanguageTo').val();
		$.post(
			$t.attr('action'),
			$t.serialize(),
			function(data) {
				$('#available_languages').html(data);
				$('#'+new_lang).click();
			}
		);
		return false;
	});
	
	$('#Form_TranslationForm').live("submit",function() {
		$t = $(this);
		$.post(
			$t.attr('action'),
			$t.serialize(),
			function(data) {
				$('#message').show().html(data);
				setTimeout(function() {
					$('#message').fadeOut();
				},3000);
			}
		);
		return false;
	});
	
	$('#Namespace').live("change", function() {
		if($(this).val().length) {
			$('.namespace').hide().filter('#namespace-'+$(this).val()).show();
		}
		else {
			$('.namespace').show();
		}
	});

	var timeout = false;

	$('#search input').live("keyup",function() {
		if(timeout) window.clearTimeout(timeout);
		s = $(this).val();		
		timeout = window.setTimeout(function() {
			$('.entity').hide();
			$('.entity').each(function() {
				reg = new RegExp(s,"i");
				if($(this).find('.entity_label').text().match(reg) || $(this).find('.entity_field input').val().match(reg)) {
					$(this).show();
				}
			});		
		},150);
				
	});
	
});
})(jQuery);