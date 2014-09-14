{include file='design/header.tpl' title="{t t='My Location'}"}

{* Location *}
{include file="parts/panel_start.tpl" title="{t t='Location'}"}
<form role="form" action="defaultlocation.php" method="post" accept-charset="utf-8" class="form-horizontal">

  {* Location *}
  {include file='parts/input.tpl' 
    helpblock="{t t='Local phone number:'} {$user->ui->default_location->phone}" 
    name="dlid" 
    description="{t t='Select Location'}" 
    value=$user->default_location_id 
    values=$locations 
    select=true 
    info="{t t='This defines the time zone and which set of routes will be available to you.'}"
    extra="onchange='changeLocation()'"}
  
  {* Submit *}
  {include file='parts/panel_submit.tpl' text="{t t='Save my location'}" symbol='ok'}

</form>
{include file="parts/panel_end.tpl"}

{include file='design/footer.tpl'}

<script>
  var phones = new Array();
  {foreach $locations as $k => $v}
    phones[{$k}] = '{$v->phone}';
  {/foreach}
  function changeLocation() {
    $('#dlid').next().html('phonenumber: '+phones[$("#dlid").val()]);
  }
</script>