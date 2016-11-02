<form $FormAttributes data-layout-type="border">

    <div class="cms-content-fields center">
		<% if $Message %>
            <p id="{$FormName}_error" class="message $MessageType">$Message</p>
		<% else %>
            <p id="{$FormName}_error" class="message $MessageType" style="display: none"></p>
		<% end_if %>

        <fieldset>
			<% if $Namespaces %>
                <div id="namespaces">
					<% loop $Namespaces %>
                        <div class="namespace" id="namespace-$Namespace">
							<% loop $Entities %>
                                <div class="field noborder entity">
                                    <label class="left">{$Namespace}.{$Entity}</label>
                                    <div class="entity_field">$EntityField.Field</div>
                                </div>
							<% end_loop %>
                        </div>
					<% end_loop %>
                </div>
			<% end_if %>
            <input type="hidden" name="SecurityID" value="$SecurityID" />
            <input type="hidden" name="Module" value="$SelectedModule" />
            <div class="clear"><!-- --></div>
        </fieldset>
    </div>

    <div class="cms-content-actions south">
		<% if $Actions %>
            <div class="Actions">
				<% loop $Actions %>
					$Field
				<% end_loop %>
            </div>
		<% end_if %>
    </div>
</form>
