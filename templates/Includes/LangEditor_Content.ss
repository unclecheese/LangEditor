<div id="lang-editor-cms-content" class="cms-content center $BaseCSSClasses" data-layout-type="border" data-pjax-fragment="Content" data-ignore-tab-state="true">

	<div class="cms-content-header north">
		<div class="cms-content-header-info">
			<h2 id="page-title-heading">
				<% include CMSBreadcrumbs %>
			</h2>
		</div>
		<div class="cms-content-header-filter">
			<% include LangEditor_TranslationFilter %>
		</div>
	</div>
	
	$Tools
	$TranslationForm

</div>