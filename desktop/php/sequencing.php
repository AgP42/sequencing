<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('sequencing');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">

  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
        <div class="cursor eqLogicAction logoPrimary" data-action="add">
          <i class="fas fa-plus-circle"></i>
          <br>
          <span>{{Ajouter}}</span>
      </div>
        <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
          <i class="fas fa-wrench"></i>
          <br>
          <span>{{Configuration}}</span>
        </div>
    </div>
    <legend><i class="fas fa-list"></i> {{Équipement}}</legend>
  	   <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
      <div class="eqLogicThumbnailContainer">
          <?php
          foreach ($eqLogics as $eqLogic) {
          	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
          	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
          	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
          	echo '<br>';
          	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
          	echo '</div>';
          }
          ?>
      </div>
  </div>

<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>

  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Général}}</a></li>

    <li role="presentation"><a href="#triggerstab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-toggle-on"></i> {{Déclencheurs}}</a></li>

    <li role="presentation"><a href="#actionstab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-hand-point-right"></i> {{Actions}}</a></li>

    <li role="presentation"><a href="#triggerscanceltab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-toggle-off"></i> {{Déclencheurs d'annulation}}</a></li>

    <li role="presentation"><a href="#actionscanceltab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-hand-paper"></i> {{Actions d'annulation}}</a></li>

    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Avancé - Commandes}}</a></li>

  </ul>

  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">

    <!-- TAB GENERAL -->
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-tachometer-alt"></i> {{Informations Jeedom}} </legend>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom Jeedom}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom }}"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
            <div class="col-sm-3">
              <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                <option value="">{{Aucun}}</option>
                <?php
                  foreach (jeeObject::all() as $object) {
  	                echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                ?>
              </select>
            </div>
          </div>
  	   <div class="form-group">
                  <label class="col-sm-3 control-label">{{Catégorie}}</label>
                  <div class="col-sm-9">
                   <?php
                      foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                      echo '<label class="checkbox-inline">';
                      echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                      echo '</label>';
                      }
                    ?>
                 </div>
             </div>
        	<div class="form-group">
        		<label class="col-sm-3 control-label"></label>
        		<div class="col-sm-9">
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
        		</div>
        	</div>
        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-hashtag"></i> {{Tags messages}} <sup><i class="fas fa-question-circle tooltips" title="{{Ces informations peuvent-être utilisées pour personnaliser les messages avec des tags, tous ces champs sont facultatifs.}}"></i></sup></legend>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Tag 1}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tag1"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#tag1#</strong>}}</div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Tag 2}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tag2"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#tag2#</strong>}}</div>
          </div>

          <div class="form-group">
            <label class="col-sm-3 control-label">{{Tag 3}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tag3"/>
            </div>
            <div class="col-sm-3">{{tag <strong>#tag3#</strong>}}</div>
          </div>

        </fieldset>
      </form>

    </div>

    <!-- TAB Triggers -->
    <div class="tab-pane" id="triggerstab">

      <br/>
<!--       <div class="alert alert-info">
        {{}}
      </div> -->

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-external-link-alt"></i> {{Commande à appeler pour déclencher les actions}} <sup><i class="fas fa-question-circle tooltips" title="{{Réglages/Système/Configuration/Réseaux doit être correctement renseigné. Cette commande déclenchera la séquence sans évaluer les éventuelles conditions ci-dessous.}}"></i></sup>
          </legend>
          <div class="form-group">
            <!-- <label class="col-sm-1 control-label">{{URL }}</label> -->

            <div class="col-sm-12">
              <?php
              if(init('id') != ''){
                $eqLogic = eqLogic::byId(init('id'));
                if(is_object($eqLogic)){
                  $cmd = $eqLogic->getCmd(null, 'start');
                  if(is_object($cmd)){
                    echo '<p>N\'importe où dans Jeedom, appelez cette commande : <i class="fas fa-code-branch"></i><b>  '. $cmd->getHumanName() . '</b><br>Où via l\'extérieur : <a href="' . $cmd->getDirectUrlAccess() . '" target="_blank"><i class="fas fa-external-link-alt"></i>  '. $cmd->getDirectUrlAccess() . '</a></p>';
                  } else {
                    echo 'Hum... vous n\'auriez pas supprimé manuellement la commande "start" par hasard ? Il ne vous reste plus qu\'à supprimer cet équipement et recommencer !';
                  }
                } else {
                  echo 'Erreur : cet eqLogic n\'existe pas';
                }
              } else {
                echo 'Sauvegarder ou rafraichir la page pour afficher les infos';
              }
              ?>
            </div>

          </div>
        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-clock"></i> {{Programmation}} <sup><i class="fas fa-question-circle tooltips" title="{{La programmation déclenchera la séquence sans évaluer les éventuelles conditions ci-dessous.}}"></i></sup></legend>
          <label class="col-sm-3 control-label">{{Déclenchement programmé ou périodique}}</label>
          <div class="input-group col-sm-2">
            <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="programmation" placeholder="{{format cron}}"/>
            <span class="input-group-btn">
              <a class="btn btn-default cursor jeeHelper" data-helper="cron">
                <i class="fas fa-question-circle"></i>
              </a>
            </span>
          </div>
        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-toggle-on"></i> {{Déclencheurs et conditions}} <sup><i class="fas fa-question-circle tooltips" title="{{Vous pouvez ici ajouter des déclencheurs selon des conditions sur leur valeur ou autres. Vous pouvez aussi choisir les conditions entre ces déclencheurs. Voir la doc pour plus d'infos.}}"></i></sup>
            <a class="btn btn-success btn-sm addTriggerValue" data-type="trigger" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Déclencheur selon valeur}}</a>
            <a class="btn btn-success btn-sm addTriggerRep" data-type="trigger" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Déclencheur selon répétition}}</a>
            <a class="btn btn-success btn-sm addTriggerProg" data-type="trigger" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Déclencheur selon programmation}}</a>
            <a class="btn btn-success btn-sm addTriggerTimeRange" data-type="trigger" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Condition selon plage horaire}}</a>
            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="trigger_and"/>{{Tous les déclencheurs doivent être valides}} <sup><i class="fas fa-question-circle tooltips" title="{{Cocher pour déclencher la séquence uniquement si l'ensemble des déclencheurs répondent à leur condition. Non coché : chaque déclencheur sera évalué individuellement}}"></i></sup></label>
          </legend>
          <div id="div_trigger"></div>
        </fieldset>
      </form>

    </div>

    <!-- TAB actions -->
    <div class="tab-pane" id="actionstab">

      <br/>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-hand-point-right"></i> {{Actions}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions à réaliser. Renseigner un label si vous voulez lier des actions de désactivations à une action. Tags utilisable : voir doc}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action"></div>

        </fieldset>
      </form>

      <br>

    </div>

    <!-- TAB triggers d'annulation -->
    <div class="tab-pane" id="triggerscanceltab">
      <br/>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-external-link-alt"></i> {{Commande à appeler pour annuler les actions}} <sup><i class="fas fa-question-circle tooltips" title="{{Réglages/Système/Configuration/Réseaux doit être correctement renseigné !}}"></i></sup>
          </legend>
          <div class="form-group">
            <!-- <label class="col-sm-1 control-label">{{URL }}</label> -->

            <div class="col-sm-12">
              <?php
              if(init('id') != ''){
                $eqLogic = eqLogic::byId(init('id'));
                if(is_object($eqLogic)){
                  $cmd = $eqLogic->getCmd(null, 'stop');
                  if(is_object($cmd)){
                    echo '<p>N\'importe où dans Jeedom, appelez cette commande : <i class="fas fa-code-branch"></i><b>  '. $cmd->getHumanName() . '</b><br>Où via l\'extérieur : <a href="' . $cmd->getDirectUrlAccess() . '" target="_blank"><i class="fas fa-external-link-alt"></i>  '. $cmd->getDirectUrlAccess() . '</a></p>';
                  } else {
                    echo 'Hum... vous n\'auriez pas supprimé manuellement la commande "stop" par hasard ? Il vous reste plus qu\'à supprimer cet équipement et recommencer !';
                  }
                } else {
                  echo 'Erreur : cet eqLogic n\'existe pas';
                }
              } else {
                echo 'Sauvegarder ou rafraichir la page pour afficher les infos';
              }
              ?>
            </div>

          </div>
        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-toggle-off"></i> {{Déclencheurs d'annulation}} <!-- <sup><i class="fas fa-question-circle tooltips" title="{{Bouton de désactivation d'alerte}}"></i></sup> -->
            <a class="btn btn-success btn-sm addTrigger" data-type="trigger_cancel" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter un bouton}}</a>
            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="trigger_cancel_and"/>{{Tous les déclencheurs doivent être valides}} <sup><i class="fas fa-question-circle tooltips" title="{{Cocher pour annuler la séquence uniquement si l'ensemble des déclencheurs répondent à leur condition. Non coché : chaque déclencheur sera évalué individuellement}}"></i></sup></label>
          </legend>
          <div id="div_trigger_cancel"></div>
        </fieldset>
      </form>

    </div>

    <!-- TAB actions annulation -->
    <div class="tab-pane" id="actionscanceltab">
      <br/>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-hand-paper"></i> {{Actions d'annulation}} <sup><i class="fas fa-question-circle tooltips" title="{{Actions réalisées sur activation d'un déclencheur d'annulation. Tags utilisable : voir doc}}"></i></sup>
            <a class="btn btn-success btn-sm addAction" data-type="action_cancel" style="margin:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
          </legend>
          <div id="div_action_cancel"></div>

        </fieldset>
      </form>

      <br>

    </div>

    <!-- TAB COMMANDES -->
    <div role="tabpanel" class="tab-pane" id="commandtab">
      <br>
      <!-- <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/> -->
      <table id="table_cmd" class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>

  </div> <!-- fin DIV contenant toutes les tab -->

</div>
</div>

<?php include_file('desktop', 'sequencing', 'js', 'sequencing');?>
<?php include_file('core', 'plugin.template', 'js');?>
