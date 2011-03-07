<div id="utility_bar">
	<h2><% _t('LangEditor.EDITING','Editing') %>: $SelectedLanguage [$SelectedLocale]</h2>
	<div id="namespace_dropdown">$NamespaceDropdown.FieldHolder</div>
	<div id="search"><label><% _t('LangEditor.SEARCH','Search') %></label><input type="text" value="" /></div>	
</div>

<% if Namespaces %>
	<form {$TranslationForm.FormAttributes}>
		<div id="namespaces">
			 <% control Namespaces %>
				 <div class="namespace" id="namespace-$NamespaceID">
					<% control Entities %>
						<div class="entity">
							<div class="entity_label">{$Namespace}.{$Entity}</div>
							<div class="entity_field">$EntityField.Field</div>
						</div>
					<% end_control %>			 	
				 </div>
			 <% end_control %>
		 </div>
		 <input type="hidden" name="SecurityID" value="$SecurityID" />
		 <div class="actions">
		 	<% control TranslationForm %>
		 	<% control Actions %>
		 		$Field
		 	<% end_control %>
		 	<% end_control %>
		 </div>
	</form>
<% else %>
	<% _t('LangEditor.PLEASESELECT','Please select a module and a language from the left.') %>
<% end_if %>
