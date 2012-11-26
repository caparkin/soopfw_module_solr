<div class="form_button" id="add_server"><%t key='Add server'%></div>
<%include file='form.tpl'%>
<br />
<%$pager|unescape%>
<table class="ui-widget ui-widget-content " style="margin-top: 10px;" cellspacing="0" cellpadding="0" border="0">
	<thead class="ui-widget-header">
		<tr>
			<td style="width: 25px;text-align: center;"><input type="checkbox" id="dmySelectAll" class="input_checkbox"/></td>
			<td style="text-align: left;"><%t key='Server'%></td>
			<td style="width: 50px;text-align: center;"></td>
		</tr>
	</thead>
	<tbody>
	<%foreach from=$servers item=server%>
		<tr id="server_row_<%$server.id%>">
			<td style="text-align: center;"><input type="checkbox" name="selected[]" value="<%$server.id%>" id="dmySelect_<%$server.id%>" class="dmySelect input_checkbox"/></td>
			<td style="text-align: left;"><a href="/solr/save_server/<%$server.id%>" class="edit_server" did="<%$server.id%>" onclick="return false;"><span><%$server.server%></span></a></td>
			<td style="text-align: center;">
				<img src="/modules/solr/templates/images/commit.png" class="ui-icon-soopfw linkedElement dmyCommit" style="background: none" did="<%$server.id%>" title="<%t key='commit server'%>" alt="<%t key='commit server'%>">
				<img src="/1x1_spacer.gif" class="ui-icon-soopfw ui-icon-soopfw-cancel linkedElement dmyDelete" did="<%$server.id%>" title="<%t key='delete?'%>" alt="<%t key='delete?'%>">
			</td>
		</tr>
	<%foreachelse%>
	<tr>
		<td colSpan="10" style="font-style: italic; text-align:center;">
			<%t key='Nothing found'%>
		</td>
	</tr>
	</tbody>
	<%/foreach%>
</table>
<div class="multi_action">
	&nbsp;&nbsp;&nbsp;<img src="/templates/images/multi_choose_arrow.png">
	<select id="multi_action">
		<option value=""><%t key='selected:'%></option>
		<option value="commit"><%t key='commit'%></option>
		<option value="delete"><%t key='delete?'%></option>
	</select>
</div>