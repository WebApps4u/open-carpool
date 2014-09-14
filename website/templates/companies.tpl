{include file='design/header.tpl' title='Companies'}

{if ($edit_cid)}
  {include file="parts/panel_start.tpl" title="Edit company"}
  <form action="companies.php" role="form" method="post" accept-charset="utf-8" class="form-horizontal">
	  <input type="hidden" name="do" value="update" id="do"/>
	  <input type="hidden" name="cid" value="{$edit_cid}" id="cid"/>
	
	  {* Name *}
    {include file='parts/input.tpl' name="name" description="Name" placeholder="Enter name" value=$companies[$edit_cid]->name}
    
    {* Email *}
    {include file='parts/input.tpl' name="email" description="Email" placeholder="Enter Email" value=$companies[$edit_cid]->email}
    
    {* SMS Key *}
    {include file='parts/input.tpl' name="smskey" description="SMS Key" placeholder="Enter SMS Key" value=$companies[$edit_cid]->smskey}
    
    {* Zen Desk Id *}
    {include file='parts/input.tpl' name="zendeskid" description="Zen Desk Id" placeholder="Enter Zen Desk Id" value=$companies[$edit_cid]->zendeskid}
    
    {* Logo Url *}
    {include file='parts/input.tpl' name="logourl" description="Logo Url" placeholder="Logo Url" value=$companies[$edit_cid]->logourl}
    
	  {* Submit *}
    {include file='parts/panel_submit.tpl' text='Save Company' symbol='ok'}
    
  </form>
  {include file="parts/panel_end.tpl"}
{/if}

{if !$bycompany}
<div class="row">
  <div class="col-lg-6">
    <table class="table table-condensed table-striped table-hover table-datatable">
      <caption>Companies</caption>  
	    <thead>
		    <tr>
			    <th>#</th>
			    <th>Name</th>
			    <th>SMS Key</th>
			    <th>Zendesk ID</th>
			    <th>Action</th>
		    </tr>
	    </thead>
	    <tbody>
		    {foreach $companies as $cid => $company}
		    <tr>
			    <td>{$company->id}</td>
			    <td>{$company->name}</td>
			    <td>{$company->smskey}</td>
			    <td>{$company->zendeskid}</td>
			    <td>
			      <a href="companies.php?do=edit&amp;cid={$company->id}"><span class="glyphicon glyphicon-edit"></span></a> 
			      <a onclick="return confirm('Really delete {$company->name}')" href="companies.php?do=delete&amp;cid={$company->id}"><span class="glyphicon glyphicon-trash"></span></a>
			    </td>
		    </tr>
		    {/foreach}
	    </tbody>
    </table>
  </div>
</div>

{include file="parts/toggle.tpl" id="new_company" text="New Company"}

{include file="parts/panel_start.tpl" title="New company" id="new_company" hidden=true}
<form action="companies.php" method="post" accept-charset="utf-8" class="form-horizontal" role="form">
	<input type="hidden" name="do" value="insert" id="do"/>

  {* Name *}
  {include file='parts/input.tpl' name="name" description="Name" placeholder="Enter name"}
  
   {* Email *}
  {include file='parts/input.tpl' name="email" description="Email" placeholder="Enter Email"}

  {* SMS Key *}
  {include file='parts/input.tpl' name="smskey" description="SMS Key" placeholder="Enter SMS Key"}
  
  {* Zen Desk Id *}
  {include file='parts/input.tpl' name="zendeskid" description="Zen Desk Id" placeholder="Enter Zen Desk Id"}
  
  {* Logo Url *}
  {include file='parts/input.tpl' name="logourl" description="Logo Url" placeholder="Logo Url"}
  
  {* Submit *}
  {include file='parts/panel_submit.tpl' text='Insert Company' symbol='ok'}
  
</form>
{include file="parts/panel_end.tpl"}
{/if}

{include file="design/footer.tpl"}
