{include file='design/header.tpl' title='Translations'}

<form role="form" action="translations.php" method="post" accept-charset="utf-8" class="form-horizontal">
  
  <div class="row row-space-after">
    <div class="col-lg-12">
        <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Save translations</button>
    </div>
  </div>
  
  {foreach $translations as $language=>$values}
      <div class="row">
          <div class="col-lg-12">
          <table class="table table-condensed table-striped table-hover">
             <caption>Translation {$language}</caption>
             <thead>
                <tr>
                   <th class="col-lg-1">#</th>
                   <th class="col-lg-3">Key</th>
                   <th class="col-lg-8">Translation</th>
                </tr>
             </thead>
             <tbody>
               {foreach $values as $t}
                  <tr>
                     <th>{$t->id}</th>
                     <td>{$t->key|htmlentities}</td>
                     <td><textarea name="translation_{$t->id}" class="form-control" rows="3">{$t->translation}</textarea></td>
                  </tr>
               {/foreach}
               </tbody>
            </table>
          </div>
      </div>
{/foreach}

  <div class="row row-space-after">
    <div class="col-lg-12">
        <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Save translations</button>
    </div>
 </div>
</form>

{include file='design/footer.tpl'}