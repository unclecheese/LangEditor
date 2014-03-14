<div class="cms-content-tools west cms-panel cms-panel-layout" id="cms-content-tools-LangEditor" data-expandOnClick="false" data-layout-type="border">
	<div class="cms-panel-content center">
		<h3 class="cms-panel-header"><% _t('AssetAdmin_Tools.FILTER', 'Filter') %></h3>
		<h4><% _t('LangEditor.AVAILABLEMODULES','Available modules') %></h4>
		<div id="available_modules" data-pjax-fragment="ModuleList">
			<% include LangEditor_ModuleList %>
		</div>
		
		<h4><% _t('LangEditor.AVAILABLELANGUAGES','Available languages') %></h4>
		<div id="available_languages" data-pjax-fragment="LanguageList">
			<% include LangEditor_LanguageList %>
		</div>
		
		<h4><% _t('LangEditor.CREATENEW','Create new translation file') %></h4>
		<div id="create_translation_form" data-pjax-fragment="CreateTranslationForm">
			<% include LangEditor_CreateTranslationForm %>
		</div>
	</div>
	<div class="cms-panel-content-collapsed">
		<h3 class="cms-panel-header"><% _t('AssetAdmin_Tools.FILTER', 'Filter') %></h3>
	</div>
	
</div>