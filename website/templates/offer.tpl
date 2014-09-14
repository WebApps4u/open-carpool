{include file='design/header.tpl' title=t('Offer a Lift') maps=true}


{include file="parts/panel_start.tpl" title=t("Offer")}        
<form role="form" action="offer.php" method="get" accept-charset="utf-8" class="form-horizontal">
  <input type="hidden" name="do" value="offerride" />

  {* Location, use default *}

  <input type="hidden" name="location_id" value="{$user->default_location_id}" />
  {include file='parts/input.tpl' name="location_id" description=t('Location') value=$user->ui->default_location->name static=true} 

  {* Route *}
  <div class="form-group">
    <label class="col-lg-2 control-label" for="route_id">{t t='Route'}</label>
    <div class="col-lg-4" id="route">
      <script type="text/javascript" charset="utf-8">
        $('#route').load('ajax/ajax_routes.php?onlyforuser=1&add_map=1&lid={$user->default_location_id}');
      </script>
    </div>
    {include file="parts/info.tpl" info='Select one of the existing routes or create your own route via My Routes.'}
  </div>
  
  {* Reverse *}
  <div class="form-group">
    <div class="col-lg-offset-2 col-lg-4">
      <div class="checkbox">
        <label>
          <input name="reverse" value="1" id="reverse" type="checkbox"> {t t='I will drive the route in reverse direction.'}
        </label>
      </div>
    </div>
  </div>
          
  {* Map *} 
  {include file="parts/map.tpl"}       

  {* Start Time *}
  {include file='parts/input.tpl' name="time" description="{t t='Departure Time'}" placeholder='hh:mm' info="{t t='Use 24h format, e.g. 14:00 for 2 p.m.'}"}

  {* Start Date *}
  {include file='parts/input.tpl' name="start_date" description="{t t='Departure Date'}" value=date('Y-m-d') date=true}
  
  {* Phonenumber *}
  {assign 'phonenumbers' c2r_user_number_get($user->id)}
  {if count($phonenumbers) > 1}
    {include file='parts/input.tpl' name="user_number_id" description="{t t='Phone Number'}" value='' values=$phonenumbers select=true info="{t t='Select the phone number you would like to be called at to arrange the pickup'}"}
  {else}
    {assign 'phonenumber' array_shift($phonenumbers)}
    <input type="hidden" name="user_number_id" value="{$phonenumber->id}" />
    {include file='parts/input.tpl' name="user_number_id" description=t('Phone number') value=$phonenumber->number static=true}
  {/if}

  <div class="col-lg-offset-2 col-lg-4">
  <p>
	{t t='You offer a one time lift in one direction.'}
  </p>
  </div>
  
  {* Submit *}
  {include file='parts/panel_submit.tpl' text='Offer Lift' symbol='ok'}
          
  <div class="col-lg-offset-2 col-lg-4">
  <p>
	{t t='To offer a lift in the reverse direction as well, please add an additional offer and check the respective box. To offer recurring lifts, please use your <a href="https://opencarpool.zendesk.com/hc/en-us/articles/201007956" target="_blank">Outlook calendar.</a>'}
  </p>
  </div>
		  
</form>
{include file="parts/panel_end.tpl"}

<script type="text/javascript">
	$(function() {
		$("#time").val(getComputerTime(10));
	});
</script>

<p>
	{t t='For more information visit our <a href="https://opencarpool.zendesk.com/hc/" target="_blank">Knowledge Base</a>.'}
</p>
{include file='design/footer.tpl'}