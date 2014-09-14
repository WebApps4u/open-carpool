{include file='design/header.tpl' title="{t t='Login'}"}

{include file="parts/panel_start.tpl" title="{t t='Login'}"}
<form role="form" action="login.php" method="post" accept-charset="utf-8" class="form-horizontal">
  {* Email *}

  {include file='parts/input.tpl' name="email" description="{t t='Email adress'}" placeholder="{t t='Enter email adress'}"}
    
  {* New Password *}
  {include file='parts/input.tpl' name="password" description="{t t='Password'}" placeholder="{t t='Enter password'}" type="password"}
    
  {* Submit *}
  {include file='parts/panel_submit.tpl' text="{t t='sign in'}" symbol='ok'}
    
</form>
{include file="parts/panel_end.tpl"}



<div class="row row-space-after">
    <div class="col-lg-12">
        <a href="#" onclick="$('#lostpw').toggle()" class="btn btn-primary">{t t='Lost Password'}</a>
    </div>
</div>
	
{include file="parts/panel_start.tpl" title="{t t='Lost Password'}" hidden=true id='lostpw'}
    <form role="form" action="login.php" method="post" accept-charset="utf-8" class="form-horizontal">
      {* Email *}
      {include file='parts/input.tpl' name="lost_email" description="{t t='Email adress'}" placeholder="{t t='Enter email adress'}"}
                
      {* Submit *}
      {include file='parts/panel_submit.tpl' text="{t t='continue'}" symbol='ok'}
    </form>
{include file="parts/panel_end.tpl"}
  
<script>		
  $(function() {
    $('#lostpw').hide();
  });	
</script>

{include file='design/footer.tpl'}