{include file='design/header.tpl' title="{t t='Lost Password'}"}
	
{include file="parts/panel_start.tpl" title="{t t='Lost Password'}" hidden=false id='lostpw'}
<form role="form" action="login.php" method="post" accept-charset="utf-8" class="form-horizontal">
  {* Email *}
  {include file='parts/input.tpl' name="lost_email" description="{t t='Email adress'}" placeholder="{t t='Enter email adress'}"}
            
  {* Submit *}
  {include file='parts/panel_submit.tpl' text="{t t='continue'}" symbol='ok'}
</form>
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}