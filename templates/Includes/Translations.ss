<% if Namespaces %>
	<div id="namespaces">
		 <% loop Namespaces %>
			 <div class="namespace" id="namespace-$Namespace">
				<% loop Entities %>
					<div class="entity">
						<div class="entity_label">{$Namespace}.{$Entity}</div>
						<div class="entity_field">$EntityField.Field</div>
					</div>
				<% end_loop %>			 	
			 </div>
		 <% end_loop %>
	 </div>
<% end_if %>
