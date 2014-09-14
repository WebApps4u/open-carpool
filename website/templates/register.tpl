{include file='design/header.tpl' title="{t t='Register'}"}

{if ($display==1)}

    {$t_intro}
    
    {if ($err)}
        {$t_error}
    {/if}
    {include file="parts/panel_start.tpl" title="{t t='Register'}"}
        <form action="register.php" method="post" role="form" class="form-horizontal register">
            <input type="hidden" name="step1" value="1" />
        
            {* Name *}
          	{include file='parts/input.tpl' name="name" description=$t_name placeholder=$t_name value=$smarty.post.name error=$err_name_text info="{t t='Use the name your colleagues should be given to contact you to arrange a pickup. The full name is recommended.'}"}
            
            {* Email *}
          	{include file='parts/input.tpl' name="email" description=$t_email placeholder=$t_email value=$smarty.post.email error=$err_email_text info="{t t='Please only use your corporate email address. All other addresses will not be allowed.'}"}
        
            {* Phone *}
          	{include file='parts/input.tpl' name="handynummer" description=$t_phone placeholder=$t_phone value=$smarty.post.handynummer error=$err_phone_text info="{t t='The number consists of 3 parts:<ul><li><b>+1</b> ... country code depending on your country, see link at +1</li><li><b>234</b> ...area or provider code</li><li><b>567890</b> ... phone number</li></ul>Your phone must support SMS text messages.'}"}
        
            {* Submit *}
          	{include file='parts/panel_submit.tpl' text=$t_submit symbol='ok'}
      </form>
  {include file="parts/panel_end.tpl"}
{/if} {* Display 1 *} 

{if ($display==2)}
    {$t_thankyou}
    <hr/>
    <p>{t t='Here is how you can register:'}</p>
    <img class="img-responsive" src="images/ocp-register_400.jpg" alt="{t t='How to register at Open CarPool'}" />
{/if}

{if ($display==3)}

  {include file="parts/panel_start.tpl" title="{t t='Enter confirmation code'}"}  
  {$t_entercode}
  <form action="register.php" method="post" role="form" class="form-horizontal register">
    <input type="hidden" name="step1" value="3" />
    <input type="hidden" name="code" value="{$code}" />

    {* SMS Code *}
  	{include file='parts/input.tpl' name="smscode" description=$t_confirmcode placeholder=$t_confirmcode value=$smarty.post.smscode error=$err_code_text}

    {* Submit *}
  	{include file='parts/panel_submit.tpl' text=$t_submit symbol='ok'}
  </form>
  {include file="parts/panel_end.tpl"}
{/if}

{if ($display==4)}
    {$t_finish}
    <hr/>
    <p>{t t='Here is how you can register:'}</p>
    <img class="img-responsive" src="images/ocp-register_400.jpg" alt="{t t='How to register at Open CarPool'}" />
{/if}

{if ($display==5)}
    {$t_error_code}
    <hr/>
    <p>{t t='Here is how you can register:'}</p>
    <img class="img-responsive" src="images/ocp-register_400.jpg" alt="{t t='How to register at Open CarPool'}" />
{/if}
{include file='design/footer.tpl'}