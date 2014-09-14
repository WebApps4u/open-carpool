<!DOCTYPE html>
<!-- Open CarPool is free software published under GPLv3 license, see license.txt for details. (C) 2009 Oliver Pintat, Clemens Rath -->
<html {if $user}lang="{$user->ui->language}"{elseif $smarty.cookies.language}lang="{$smarty.cookies.language}"{/if}>
	<head>
        <meta charset="utf-8">
        <xmeta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <title>{if $title}{$title}{else}{$titletag}{/if} | eBay Inc. Green Team</title>
        
        <link rel="icon" href="favicon.ico" type="image/ico">
        
        <link rel="stylesheet" type="text/css" href="lib/bootstrap/dist/css/bootstrap.min.css" media="screen">
        <!-- DataTables CSS -->
        <link rel="stylesheet" type="text/css" href="http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/css/jquery.dataTables.css">
        <link rel="stylesheet" type="text/css" href="css/style.css" media="screen" title="CSS" charset="utf-8"/>
    	<link rel="stylesheet" type="text/css" href="lib/datepicker/css/datepicker.css">
        
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
          <script src="lib/bootstrap/assets/js/html5shiv.js"></script>
          <script src="lib/bootstrap/assets/js/respond.min.js"></script>
        <![endif]-->

    	<script src="lib/bootstrap/assets/js/jquery.js"></script>
    	
    	<!-- DataTables -->
        <script type="text/javascript" charset="utf8" src="https://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js"></script>
        <script src="lib/bootstrap/dist/js/bootstrap.min.js"></script>
        
        {if isset($maps) && $maps}
            {* google Maps API *}
            <script type="text/javascript" src="https://maps.google.com/maps/api/js?libraries=places&sensor=true"></script>
        {/if}
        
        <script src="lib/datepicker/js/bootstrap-datepicker.js"></script>
        <script src="js/ocp.js"></script>

	</head>
	
	<body>
        <div class="navbar navbar-inverse navbar-fixed-top" id="main-navbar">
              <div class="container">
                <div class="navbar-header">
                  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                  </button>
				  <a class="navbar-brand" href="index.php">{if $ocp_logourl}<img id="ocp_logo" src="{$ocp_logourl}">{else}<img id="ocp_logo" src="images/opencarpool.png" alt="Open CarPool" width="70" height="32">{/if}<div id="logo_spacer"></div></a>
                </div>
                <div class="navbar-collapse collapse">
                    
                  <ul class="nav navbar-nav">
                  {if isset($user) && $user}
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> {t t='Passenger'} <b class="caret"></b></a>
                      <ul class="dropdown-menu">                    
                       	<li><a href="request.php"><span class="glyphicon glyphicon-search"></span> {t t='Request a Lift'}</a></li>
                       	<li><a href="matchingoffers.php"><span class="glyphicon glyphicon-ok"></span> {t t='Matching Offers'}</a></li>
                       	<li><a href="requests.php?status=open"><span class="glyphicon glyphicon-user"></span> {t t='My Requests'}</a></li>
                      </ul>
                    </li>
                    
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-road"></span> {t t='Driver'} <b class="caret"></b></a>
                      <ul class="dropdown-menu">                    
                       	<li><a href="offer.php"><span class="glyphicon glyphicon-plus"></span> {t t='Offer a Lift'}</a></li>
                       	<li><a href="matchingrequests.php"><span class="glyphicon glyphicon-ok"></span> {t t='Matching Requests'}</a></li>
                       	<li><a href="offers.php?status=open"><span class="glyphicon glyphicon-user"></span> {t t='My Offers'}</a></li>
                      </ul>
                    </li>
                    
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-transfer"></span> {t t='Routes'} <b class="caret"></b></a>
                      <ul class="dropdown-menu">      
                         {* show wall monitor menu item only on large screens *}
                         {*
                       	 <li class="hidden-xs hidden-sm"><a href="wallmonitor.php"><span class="glyphicon glyphicon-hd-video"></span> Wall Monitor Departures</a></li>
                         *}
                       	 <li><a href="allroutes.php"><span class="glyphicon glyphicon-transfer"></span> {t t='All Routes'}</a></li>
                       	 <li><a href="routes.php?my=1"><span class="glyphicon glyphicon-user"></span> {t t='My Routes'}</a></li>
                      </ul>
                    </li>
    
                    <!-- Admin functions won't be translated -->
    				{if (isset($user) && $user->group_id > 1)}
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin<b class="caret"></b></a>
                      <ul class="dropdown-menu">                    
                       	<li><a href="pickuppoints.php">Pickup Points</a></li>
                       	<li><a href="routes.php">Routes</a></li>
                       	<li><a href="locations.php">Locations</a></li>
    			              <li><a href="offers.php?all=1&status=open">All Offers</a></li>
    			              <li><a href="requests.php?all=1&status=open">All Requests</a></li>
                        <li><a href="users.php">User</a></li>
                        <li><a href="emailsuffix.php">Email Suffix</a></li>
                        <li><a href="companies.php?do=edit&amp;cid={$user->company_id}">Company</a></li>
                        {if ($user->group_id == 2 && in_array($user->id, $ocp_allowed_user_for_translation))}
                          <li><a href="translations.php">Translations</a></li>
                        {/if}
                        {if ($user->group_id > 2)}
                        <li class="divider"></li>
                        <li class="dropdown-header">Super Admin</li>
                      	<li><a href="companies.php">Companies</a></li>
                      	<li><a href="translations.php">Translations</a></li>
                        {/if}
                      </ul>
                    </li>
                    {/if}
    
                    {else}
                      <li><a href="register.php"><span class="glyphicon glyphicon-user"></span> {t t='Register'}</a></li>
                    {/if}
    
                    {if $user->group_id == 1 && in_array($user->id, $ocp_allowed_user_for_translation)}
                        <li><a href="translations.php"><span class="glyphicon glyphicon-globe"></span> {t t='Translations'}</a></li>
                    {/if}
    
                    <li class="dropdown">
                      <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-info-sign"></span> {t t='Help &amp; Contact'} <b class="caret"></b></a>
                      <ul class="dropdown-menu">                    
                       	<li><a href="https://opencarpool.zendesk.com/" target="_blank"><span class="glyphicon glyphicon-question-sign"></span> {t t='Help (new window)'}</a></li>
                       	<li><a href="feedback.php"><span class="glyphicon glyphicon-envelope"></span> {t t='Contact'}</a></li>
    			        {if !$user->group_id}
                      		<li><a href="lostpassword.php"><span class="glyphicon glyphicon-warning-sign"></span> {t t='Lost Password'}</a></li>
    			        {/if}
                      </ul>
                    </li>
                  </ul>
                  
                  {if !$user || !$user->group_id}
                      <form action="login.php" class="navbar-form navbar-right" method="post">
                       <div class="form-group">
                          <input type="text" placeholder="{t t='Email'}" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                          <input type="password" placeholder="{t t='Password'}" name="password" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-globe"></span> {t t='Sign in'}</button>
                      </form>
                      <ul class="nav navbar-nav navbar-right">                  
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-globe"></span> {$ocp_languages.$ocp_language}<b class="caret"></b></a>
                          <ul class="dropdown-menu">
                            {foreach $ocp_languages as $short=>$long}
                                <li><a href="changelanguage.php?lang={$short}"><span class="glyphicon glyphicon-globe"></span> {$long}</a></li>
                            {/foreach}
                          </ul>
                        </li>               
                      </ul>
                  {else}
                    <ul class="nav navbar-nav navbar-right">                  
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img class="gravatar" src="{$user->gravatar_small}" alt="{$user->name}" style="margin: 0 10px;"/> {$user->name} <b class="caret"></b></a>
                          <ul class="dropdown-menu">
                            <li><a href="profile.php"><span class="glyphicon glyphicon-user"></span> {t t='My Profile'}</a></li>
                            <li><a href="offers.php?status=open"><span class="glyphicon glyphicon-road"></span> {t t='My Offers'}</a></li>
                            <li><a href="requests.php?status=open"><span class="glyphicon glyphicon-search"></span> {t t='My Requests'}</a></li>
                            <li><a href="defaultlocation.php"><span class="glyphicon glyphicon-map-marker"></span> {t t='My Location'}</a></li>
                       	    <li><a href="routes.php?my=1"><span class="glyphicon glyphicon-transfer"></span> {t t='My Routes'}</a></li>
                       	    <li class="divider"></li>
                            <li><a href="login.php?do=logout"><span class="glyphicon glyphicon-off"></span> {t t='Logout'}</a></li>
                          </ul>
                        </li>               
                    </ul>
                {/if}
                  
                </div><!--/.navbar-collapse -->
              </div>
            </div>
    
    
            <div class="container">
              {if isset($breadcrumb) && $breadcrumb}
              <ol class="breadcrumb">
                <li><a href="index.php"><span class="glyphicon glyphicon-globe"></span> {t t='Home'}</a></li>
                {foreach $breadcrumb as $name => $url}
                  {if $url}
                    <li><a href="{$url}"><span class="glyphicon glyphicon-globe"></span> {$name}</a></li>
                  {else}
                    <li class="active"><span class="glyphicon glyphicon-globe"></span> {$name}</li>
                  {/if}
                {/foreach}
              </ol>
              {/if}
              
              <h1>{$title}</h1>
              {include file='parts/messages.tpl'}
