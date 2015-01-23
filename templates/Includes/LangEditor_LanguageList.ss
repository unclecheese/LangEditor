<ul>
	<% loop $Languages %>
		<li><a class="<% if $Current %> current<% end_if %>" id="{$Locale}" href="{$Link}">$Name</a></li>
	<% end_loop %>
</ul>
