{include file='design/header.tpl' title='User List'}

<table class="table table-condensed table-striped table-hover table-datatable">
  <thead>
    <tr>
      <th>#</th>
      <th>Name</th>
      <th>Company</th>
      <th>Email</th>
      <th>Group</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
{foreach $users as $uid=>$u}
  <tr>
    <td>{$u->id}</td>
    <td>{$u->name}</td>
    <td>{$u->ui->company->name}</td>
    <td>{$u->ui->email}</td>
    <td>{$u->ui->group}</td>
    <td><a class="btn btn-primary btn-xs" href="profile.php?uid={$u->id}"><span class="glyphicon glyphicon-edit"></span></a> </td>
  </tr>
{/foreach}
  </tbody>
</table>


<div class="row row-space-after"><div class="col-lg-12">
<a onclick="$('#new_user').toggle()" class="btn btn-primary">New user</a>
</div></div>

{include file="parts/panel_start.tpl" title="New User" hidden=!$has_errors id="new_user"}

<form action="users.php" role="form" class="form-horizontal" method="post" accept-charset="utf-8">
	<input type="hidden" name="do" value="insert" id="do"/>
	
	{* Name *}
	{include file='parts/input.tpl' name="name" description="Name" placeholder="Enter name" value=$smarty.post.name}

	{* Email *}
	{include file='parts/input.tpl' name="email" description="Email" placeholder="Enter email" value=$smarty.post.email}
	
	{* Number *}
	{include file='parts/input.tpl' name="number" description="Number" placeholder="Enter phone number" value=$smarty.post.number}
  
  {* Company *}
  {assign "help_value" $user->ui->company->name}
  {if $user->group_id > 2}
    {assign "help_value" $user->ui->company->id}
  {else}
    <input type="hidden" name="cid" value="{$user->ui->company->id}" />
  {/if}
  {include file='parts/input.tpl' name="cid" description="Company" value=$help_value values=$companies select=true static=!($user->group_id > 2)}
  
  {* Default Location *}
  {include file='parts/input.tpl' name="dlid" description="Default location" value=$smarty.post.dlid values=$locations select=true}
	
	{* Group *}
  {include file='parts/input.tpl' name="gid" description="Group" value=$smarty.post.gid values=c2r_groupnames_select($user->group_id) select=true}

  {* Active *}
  {include file='parts/input.tpl' name="is_active" description="Active" value=$smarty.post.is_active values=c2r_yesno() select=true}
          
  {* Submit *}
  {include file='parts/panel_submit.tpl' text='Insert User' symbol='ok'}
</form>
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}
