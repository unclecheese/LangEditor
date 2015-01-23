<ul>
	<% loop $Modules %>
		<li><a class="<% if $Current %> current<% end_if %>" id="{$Name}" href="{$Link}">$Name</a></li>
	<% end_loop %>
</ul>
