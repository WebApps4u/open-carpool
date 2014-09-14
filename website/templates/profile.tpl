{assign 'title' "{t t='Profile'} {$ui->name}"}
{include file='design/header.tpl' title=$title}


<script>
  var phones = new Array();
  {foreach $locations as $k => $v}
    phones[{$k}] = '{$v->phone}';
  {/foreach}
  function changeLocation() {
    $('#dlid').next().html("{t t='Phone number:'} "+phones[$("#dlid").val()]);
  }
</script>

{* Profile Data *}
{include file="parts/panel_start.tpl" title="{t t='Profile Data'}"}
    <form role="form" action="profile.php" method="post" accept-charset="utf-8" class="form-horizontal">
              
      {if ($admin_view) }
        <input type="hidden" name="uid" value="{$uid}" id="uid"/>
        <input type="hidden" name="action" value="update_userinfo" id="action"/>
        
        {* User ID *}
        {include file='parts/input.tpl' name="id" description="{t t='User ID'}" value=$ui->id static=true}
        
        {* Group *}
        {assign "help_value" $ui->group}
        {if $admin_view}{assign "help_value" $ui->group_id}{/if}
        {include file='parts/input.tpl' name="gid" description="{t t='Group'}" value=$help_value values=c2r_groupnames_select($user->group_id) select=true static=!$admin_view}
        
        {* Active *}
        {assign "help_value" $ui->is_active_text}
        {if $admin_view}{assign "help_value" $ui->is_active}{/if}
        {include file='parts/input.tpl' name="is_active" description="{t t='Active User?'}" value=$help_value values=c2r_yesno() select=true static=!$admin_view}
      {/if}
      
      {* Gravatar *}
      {include file='parts/input.tpl' name="gravatar" description="{t t='Gravatar'}" value=$user->gravatar_big image=true}
      
      {* Name *}
      {include file='parts/input.tpl' name="name" description="{t t='Name'}" placeholder="{t t='Enter name'}" value=$ui->name static=!$admin_view}
    
      {* Email *}
      {include file='parts/input.tpl' name="email" description="{t t='E-Mail'}" placeholder="{t t='Enter email'}" value=$ui->email static=!$admin_view}
      
      {* Company *}
      {assign "help_value" $ui->company->name}
      {if $admin_view && $user->group_id > 2}{assign "help_value" $ui->company_id}{/if}
      {include file='parts/input.tpl' name="cid" description="{t t='Company'}" placeholder="{t t='Enter company'}" value=$help_value values=$companies select=true static=!($admin_view && $user->group_id > 2)}
      
      {* Location *}
      {include file='parts/input.tpl' name="dlid" description="My Location" placeholder="{t t='Choose location'}" value=$ui->default_location_id values=$locations select=true helpblock="{t t='Phone number:'} {$ui->default_location->phone}" extra="onchange='changeLocation()'" info="{t t='This defines the time zone and which set of routes will be available to you.'}"}
        
      {* Language *}  
      {include file='parts/input.tpl' name="language" description='My Language' placeholder="{t t='Choose language'}" value=$ui->language values=c2r_getLanguages() select=true}  
        
      {* Submit *}
      {include file='parts/panel_submit.tpl' text="{t t='Save Profile'}" symbol='ok'}
              
    </form>
{include file="parts/panel_end.tpl"}



{include file="parts/panel_start.tpl" title="{t t='Change password'}"}
    <form role="form" action="profile.php" method="post" accept-charset="utf-8" class="form-horizontal">
        {if ($admin_view) }
        <input type="hidden" name="uid" value="{$uid}" id="uid"/>
        {/if}
        
        {* New Password *}
        {include file='parts/input.tpl' name="new_password" description="{t t='Password'}" placeholder="{t t='Enter new password'}" value='' type="password"}
        
        {* Retype *}
        {include file='parts/input.tpl' name="retype" description="{t t='Retype'}" placeholder="{t t='Retype new password'}" value='' type="password"}
        
        {* Submit *}
        {include file='parts/panel_submit.tpl' text="{t t='Change password'}" symbol='ok'}
    </form>
{include file="parts/panel_end.tpl"}

{include file="parts/panel_start.tpl" title="{t t='Manage default mobile number'}"}
    <form action="profile.php" method="post" accept-charset="utf-8" class="form-horizontal">
    	{if ($admin_view) }
    		<input type="hidden" name="uid" value="{$uid}" id="uid"/>
    	{/if}
    	
    	{* Default Number / Delete Numbers*}
    	{foreach $uns as $key => $un}
    	  {if $un->is_active}
    	    {include file='parts/input.tpl' name="mobile number" description="{t t='Mobile number'}" placeholder="{t t='Enter new mobile number'}" value=$ui->language values=$un->id radio=true}
    	  {/if}
    	{/foreach}
      
        {* Submit *}
        {include file='parts/panel_submit.tpl' text="{t t='Change default number'}" symbol='ok'}
    </form>
{include file="parts/panel_end.tpl"}
		
{include file="parts/panel_start.tpl" title="{t t='Add a new mobile phone number'}"}
    <p>{t t='You will receive a text message with a code to verify your number. Please use the international format starting with the country code (+1234567890).'}</p>

    <form role="form" action="profile.php" method="post" accept-charset="utf-8" class="form-horizontal">
        {if ($admin_view)}
    		<input type="hidden" name="uid" value="{$uid}" id="uid"/>
    		<input type="hidden" name="action" value="new_number" id="action"/>
    	{/if}
    	
    	{* Mobile Number *}
    	{include file='parts/input.tpl' name="number" description="{t t='Mobile phone number'}" placeholder="{t t='Enter mobile number'}" value='' info="{t t='The number consists of 3 parts:<ul><li><b>+1</b> ... country code depending on your country, see link at +1</li><li><b>234</b> ...area or provider code</li><li><b>567890</b> ... phone number</li></ul>Your phone must support SMS text messages.'}"}
    	
        {* Submit *}
        {include file='parts/panel_submit.tpl' text="{t t='Add new mobile phone number'}" symbol='ok'}
    </form>
{include file="parts/panel_end.tpl"}

{* Activate Number *}
{if (!$admin_view && $bool_to_activate)}	
  {include file="parts/panel_start.tpl" title="{t t='Mobile phone number to validate'}"}
	  {foreach $uns as $key => $un}
		  {if (!$un->is_active)}
			  <form action="profile.php" method="post" accept-charset="utf-8" role="form" class="form-horizontal">
			    <input type="hidden" name="validate_unid" value="{$un->id}" />
			    {* Validateion Code *}

                {include file='parts/input.tpl' name="number" description="{t t='Number'}" value=$un->number static=true text="<a href=\"profile.php?number_delete={$un->id}\"><span class=\"glyphicon glyphicon-trash\"></span></a>"}      
			        		      
                {* Validation Code *}
                {include file='parts/input.tpl' name="code" description="Validation Code" placeholder="{t t='Enter validation code'}"}
            	  
			    {* Submit *}
                {include file='parts/panel_submit.tpl' text='validate' symbol='ok'}
			  </form>
	    {/if}
	  {/foreach}
  </table>		
  {include file="parts/panel_end.tpl"}
{/if}

{* Delete Profile *}
{include file="parts/panel_start.tpl" title="{t t='Delete Profile'}"}
	<a href="profile.php?user_delete={$uid}" class="btn btn-danger" onclick='return confirm("{t t='Are you sure you want to delete the profile? It cannot be restored.'} {$ui->name}")'>
	    <span class="glyphicon glyphicon-trash"></span> {t t='Delete Profile of'} {$ui->name}
	</a>	
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}