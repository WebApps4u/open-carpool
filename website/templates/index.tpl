{include file='design/header.tpl' title="Open CarPool"}

<div class="row">
{if !isset($user) || !$user->group_id}
  <div class="col-lg-6">
    <p>
	{t t='Open CarPool is a simple but effective ride sharing system. For more information, please visit'} <a href="http://www.opencarpool.org">www.opencarpool.org</a>.
    </p>
    <ul>
	<li>{t t='To offer or request a lift, please <a href="login.php">log in</a>.'}    </li>
      	<li>{t t='If you have not got an account yet, please <a href="register.php">register</a>.'}    </li>
	<li>{t t='For support, feedback or requests to use it for your own organization, please contact us via our contact tab on the right side of this page.'}    </li>
    </ul>
 </div>

{/if}

{if isset($user)}
  <div class="col-lg-6">
    <p>{t t='Open CarPool helps you to find colleagues driving your route at the same time. In case of a match, the system will simply show you the names and phone numbers of matching colleagues. Call each other and arrange a pick up!'}</p>

    <div class="row">
      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">{t t='Passenger?'}</h3>
          </div>
          <div class="panel-body">
		      <a href="request.php" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span> {t t='Request a lift'}</a>
          </div>
        </div>
      </div>
  
      <div class="col-lg-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">{t t='Driver?'}</h3>
          </div>
          <div class="panel-body">
		      <a href="offer.php" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> {t t='Offer a lift'}</a>
          </div>
        </div>
      </div>
    </div>
  </div>  
{/if}

  <div class="col-lg-6">
    <p>
      <strong>{t t='Here is how Open Carpool works:'}</strong><br />
	    <img alt="{t t='Here is how Open Carpool works:'}" src="images/{t t='open-carpool_how-it-works_400x447.jpg'}" class="img-responsive">
    </p>
  </div>
</div>

{include file='design/footer.tpl'}
