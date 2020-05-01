
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

// permet de reorganiser les elements de la div en les cliquant/deplacant
$("#div_trigger").sortable({axis: "y", cursor: "move", items: ".trigger", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_trigger_prog").sortable({axis: "y", cursor: "move", items: ".trigger_prog", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_trigger_timerange").sortable({axis: "y", cursor: "move", items: ".trigger_timerange", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action").sortable({axis: "y", cursor: "move", items: ".action", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_trigger_cancel").sortable({axis: "y", cursor: "move", items: ".trigger_cancel", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_trigger_prog_cancel").sortable({axis: "y", cursor: "move", items: ".trigger_prog_cancel", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_trigger_timerange_cancel").sortable({axis: "y", cursor: "move", items: ".trigger_timerange_cancel", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action_cancel").sortable({axis: "y", cursor: "move", items: ".action_cancel", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

// gestion des champs additionnels selon le menu déroulant condition entre triggers et triggers_cancel
$('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_type]').change(function () {
  if($('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_type]').value() == "OR" || $('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_type]').value() == "AND"){
    $('.x_sur_N_value').hide();
    $('.condition_perso').hide();
  } else if($('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_type]').value() == "x_sur_N"){
    $('.x_sur_N_value').show();
    $('.condition_perso').hide();
  } else if($('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_type]').value() == "perso"){
    $('.x_sur_N_value').hide();
    $('.condition_perso').show();
  } else {
    $('.x_sur_N_value').hide();
    $('.condition_perso').hide();
  }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_cancel_type]').change(function () {
  if($('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_cancel_type]').value() == "OR" || $('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_cancel_type]').value() == "AND"){
    $('.x_sur_N_value_cancel').hide();
    $('.condition_perso_cancel').hide();
  } else if($('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_cancel_type]').value() == "x_sur_N"){
    $('.x_sur_N_value_cancel').show();
    $('.condition_perso_cancel').hide();
  } else if($('.eqLogicAttr[data-l1key=configuration][data-l2key=check_triggers_cancel_type]').value() == "perso"){
    $('.x_sur_N_value_cancel').hide();
    $('.condition_perso_cancel').show();
  } else {
    $('.x_sur_N_value_cancel').hide();
    $('.condition_perso_cancel').hide();
  }
});

// ajoute chaque ligne de trigger ou trigger_cancel pour condition sur valeur
$('.addTriggerValue').off('click').on('click', function () {
  addTriggerValue({}, $(this).attr('data-type'));
});

// ajoute chaque ligne de trigger_prog ou trigger_prog_cancel pour programmation
$('.addTriggerProg').off('click').on('click', function () {
  addTriggerProg({}, $(this).attr('data-type'));
});

// ajoute chaque ligne de trigger_timerange ou trigger_timerange_cancel pour condition sur plage horaire
$('.addTriggerTimeRange').off('click').on('click', function () {
  addTriggerTimeRange({}, $(this).attr('data-type'));
});


var _labels; // variable pour memoriser les labels "action", la variable est remplie à la sauvegarde dans printEqLogic
// ajoute chaque ligne d'action ou action_cancel
$('.addAction').off('click').on('click', function () {
  addAction({}, $(this).attr('data-type'), _labels);
});

// tous les - qui permettent de supprimer la ligne
$("body").off('click','.bt_removeAction').on('click','.bt_removeAction',function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

// permet d'afficher la liste des cmd Jeedom pour choisir sa commande de type "info" (pas les actions donc)
$("body").off('click','.bt_selectTrigger').on('click','.bt_selectTrigger',  function (event) {
  var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    el.value(result.human);
  });
});

/*// permet la selection des variables comme trigger
$("body").off('click','.bt_selectDataStoreTrigger').on( 'click','.bt_selectDataStoreTrigger', function (event) {
  var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
  jeedom.dataStore.getSelectModal({cmd: {type: 'info'}}, function (result) {
    el.value(result.human);
  });
});*/

// affiche les cmd jeedom de type action
$("body").off('click','.listCmdAction').on('click','.listCmdAction', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'action'}}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
    });

  });
});

// copier/coller du core (cmd.configure.php), permet de choisir la liste des actions (scenario, attendre, ...)
$("body").undelegate(".listAction", 'click').delegate(".listAction", 'click', function () {
  var type = $(this).attr('data-type');
  var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]');
  jeedom.getSelectActionModal({}, function (result) {
    el.value(result.human);
    jeedom.cmd.displayActionOption(el.value(), '', function (html) {
      el.closest('.' + type).find('.actionOptions').html(html);
      taAutosize();
    });
  });
});

//sert à charger les champs quand on clique dehors
$('body').off('focusout','.cmdAction.expressionAttr[data-l1key=cmd]').on('focusout','.cmdAction.expressionAttr[data-l1key=cmd]',function (event) {
  var type = $(this).attr('data-type');
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
  });

});

// chaque ligne de trigger ou trigger_cancel pour les declencheurs de type "conditions"
function addTriggerValue(_action, _type) {
  var div = '<div class="' + _type + '">';
    div += '<div class="form-group">';

  //    div += '<label class="col-sm-1 control-label">{{Valeur}}</label>';
      div += '<div class="col-sm-5 col-md-2">';
        div += '<div class="input-group">';
          div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '" title="{{Supprimer}}""><i class="fas fa-minus-circle"></i></a>';
          div += '</span>';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="name" title="{{Le nom doit être unique}}" placeholder="{{Nom}}"/>'; // dans la class ['name']
        div += '</div>';
      div += '</div>';

  //    div += '<label class="col-sm-1 control-label">Capteur</label>';
      div += '<div class="col-sm-7 col-md-2">';
        div += '<div class="input-group">';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="cmd" placeholder="{{Commande}}"/>';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default cursor bt_selectTrigger" title="{{Choisir une commande}}"><i class="fas fa-list-alt"></i></a>';
        //    div += '<a class="btn btn-default cursor bt_selectDataStoreTrigger" title="{{Choisir une variable}}"><i class="fas fa-calculator"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

/*      div += '<div class="col-sm-2 col-md-1">';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr cmdInfo" data-l1key="new_value_only"/>{{Filtrer répétitions}} <sup><i class="fas fa-question-circle tooltips" title="{{Cocher pour ne prendre en compte que les nouvelles valeurs}}"></i></sup></label></span>';
      div += '</div>';*/

      div += '<label class="col-sm-2 col-md-1 control-label">{{Conditions}}</label>';
      div += '<div class="col-sm-2 col-md-1">';
        div += '<select class="expressionAttr eqLogicAttr form-control" data-l1key="condition_operator1" placeholder="{{Opérateur 1}}">'; // dans la class : ['condition_operator1']
        div += '<option value="" select></option>';
        div += '<option value="==">{{égal}}</option>';
        div += '<option value="!=">{{différent}}</option>';
        div += '<option value=">=">{{supérieur ou égal}}</option>';
        div += '<option value=">">{{strictement supérieur}}</option>';
        div += '<option value="<=">{{inférieur ou égal}}</option>';
        div += '<option value="<">{{strictement inférieur}}</option>';
    //    div += '<option value="matches">{{contient (matches)}}</option>';
        div += '</select>';
      div += '</div>';
// TODO : ajouter matches et not() ? (donc ce cas c'est plus des types number dessous)

      div += '<div class="col-sm-2 col-md-1">';
        div += '<input type="" class="expressionAttr form-control" data-l1key="condition_test1" placeholder="{{Condition 1}}"/>';
      div += '</div>';

      div += '<div class="col-sm-2 col-md-1">';
        div += '<select class="expressionAttr eqLogicAttr form-control" data-l1key="condition_operator">';
        div += '<option value="" select></option>';
        div += '<option value="&&">{{ET}}</option>';
        div += '<option value="||">{{OU}}</option>';
        div += '<option value="|^">{{OU Exclusif}}</option>';
        div += '</select>';
      div += '</div>';

      div += '<div class="col-sm-2 col-md-1">';
        div += '<select class="expressionAttr eqLogicAttr form-control" data-l1key="condition_operator2" placeholder="{{Opérateur 1}}">';
        div += '<option value="" select></option>';
        div += '<option value="==">{{égal}}</option>';
        div += '<option value=">=">{{supérieur ou égal}}</option>';
        div += '<option value=">">{{strictement supérieur}}</option>';
        div += '<option value="<=">{{inférieur ou égal}}</option>';
        div += '<option value="<">{{strictement inférieur}}</option>';
        div += '<option value="!=">{{différent}}</option>';
        div += '</select>';
      div += '</div>';

      div += '<div class="col-sm-2 col-md-1">';
        div += '<input type="" class="expressionAttr form-control" data-l1key="condition_test2" placeholder="{{Condition 2}}"/>';
      div += '</div>';

  //    div += '<label class="col-sm-2 col-md-1 control-label">{{Nombre de fois}}</label>';
      div += '<div class="col-sm-3 col-md-1">';
        div += '<input type="number" min="2" class="expressionAttr form-control" data-l1key="condition_rep_nb_fois" placeholder="{{Nombre de fois}}"/>';
      div += '</div>';

  //    div += '<label class="col-sm-2 col-md-1 control-label">{{Pendant}}</label>';
      div += '<div class="col-sm-3 col-md-1">';
        div += '<input type="number" min="0" class="expressionAttr form-control" data-l1key="condition_rep_periode" placeholder="{{Pendant (secondes)}}"/>';
      div += '</div>';

    div += '</div>';
  div += '</div>';
  $('#div_' + _type).append(div);
  $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
}

function addTriggerProg(_action, _type) {
  var div = '<div class="' + _type + '">';
    div += '<div class="form-group">';

      div += '<label class="col-sm-3 control-label">{{Déclencheur programmé ou périodique}} <sup><i class="fas fa-question-circle tooltips" title="{{Cette programmation déclenchera l\'évaluation des conditions ci-dessous.}}"></i></sup></label>';
      div += '<div class="input-group col-sm-6 col-md-3">';
        div += '<input type="text" class="expressionAttr form-control" data-l1key="trigger_prog" placeholder="{{format cron}}"/>';
        div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default cursor jeeHelper" data-helper="cron">';
            div += '<i class="fas fa-question-circle"></i>';
          div += '</a>';
        div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '" title="{{Supprimer}}""><i class="fas fa-minus-circle"></i></a>';
        div += '</span>';
      div += '</div>';

    div += '</div>';
  div += '</div>';
  $('#div_' + _type).append(div);
  $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
}

function addTriggerTimeRange(_action, _type) {
  var div = '<div class="' + _type + '">';
    div += '<div class="form-group">';

      div += '<div class="col-sm-4 col-md-2">';
        div += '<div class="input-group">';
          div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '" title="{{Supprimer}}""><i class="fas fa-minus-circle"></i></a>';
          div += '</span>';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="name" title="{{Le nom doit être unique}}" placeholder="{{Nom}}"/>'; // dans la class ['name']
        div += '</div>';
      div += '</div>';

      div += '<label class="col-sm-2 col-md-1 control-label">{{Plage temporelle}} <sup><i class="fas fa-question-circle tooltips" title="{{Cette période est une condition uniquement (pas un déclencheur). Ajouter une programmation si besoin. La condition sera valide si l\'heure courante est comprise dans la plage.}}"></i></sup></label>';

      div += '<div class="col-sm-6 col-md-3">';
        div += '<span>';
           div += '<div> {{Du}} <input class="expressionAttr form-control in_datepicker" data-l1key="timerange_start" style="display : inline-block; width: 170px;" value=""/> {{au }}';
             div += '<input class="expressionAttr form-control in_datepicker" data-l1key="timerange_end" style="display : inline-block; width: 170px;" value=""/>';
          div += '</div>';
        div += '</span>';
      div += '</div>';

      div += '<label class="col-sm-1 col-md-1 control-label">{{Répéter}}</label>';

      div += '<div class="col-sm-11 col-md-5">';
     //   div += '<span>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_1"/>{{lundis}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_2"/>{{mardis}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_3"/>{{mercredis}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_4"/>{{jeudis}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_5"/>{{vendredis}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_6"/>{{samedis}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_7"/>{{dimanches}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_week"/>{{semaines}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_month"/>{{mois}} </label>';
        div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr" data-l1key="rep_year"/>{{années}} </label>';
       // div += '</span>';
      div += '</div>';

    div += '</div>';
  div += '</div>';
  $('#div_' + _type).append(div);
  $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
  $(".in_datepicker").datetimepicker({
          lang: 'fr',
          dayOfWeekStart : 1,
          i18n: {
            fr: {
              months: [
                'Janvier', 'Février', 'Mars', 'Avril',
                'Mai', 'Juin', 'Juillet', 'Aout',
                'Septembre', 'Octobre', 'Novembre', 'Décembre',
              ],
              dayOfWeek: [
                "Di", "Lu", "Ma", "Me",
                "Je", "Ve", "Sa",
              ]
            }
          },
          format: 'Y-m-d H:i:00',
          step: 15
        });
}

// chaque ligne d'action ou action_cancel
function addAction(_action, _type, _labels) {

  var div = '<div class="' + _type + '">';
    div += '<div class="form-group ">';

      if(_type == 'action'){ // pour les actions, on ajoute un label et un timer
      //  div += '<label class="col-sm-1 control-label">{{Label}} <sup><i class="fas fa-question-circle tooltips" title="{{Renseigner un label si vous voulez lier des actions de désactivations à cette action}}"></i></sup></label>';
        div += '<div class="col-sm-4 col-md-2">';
          div += '<div class="input-group">';
            div += '<span class="input-group-btn">';
              div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
            div += '</span>';
            div += '<input type="" class="expressionAttr form-control cmdInfo" data-l1key="action_label" placeholder="{{Label}}"/>'; // type = "text" fait un bug d'affichage sur le theme noir...
          div += '</div>';
        div += '</div>';

        div += '<label class="col-sm-2 col-md-1 control-label">{{Délai avant exécution}} <sup><i class="fas fa-question-circle tooltips" title="{{Le délai avant exécution doit être donné (en minutes) par rapport au déclenchement initial et non par rapport à l\'action précédente. Ne pas remplir ou 0 pour déclenchement immédiat.}}"></i></sup></label>';
        div += '<div class="col-sm-2 col-md-1">';
            div += '<input type="number" min="0" class="expressionAttr form-control cmdInfo" data-l1key="action_timer" placeholder="{{en minutes}}"/>';
            div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr cmdInfo" data-l1key="reporter"/>{{Reporter}} <sup><i class="fas fa-question-circle tooltips" title="{{Cocher pour reporter l\'exécution de l\'action en cas de nouveau déclenchement. }}"></i></sup></label>';
        div += '</div>';

      } else { // pour les action_cancel on ajoute le label de l'action à lier

        div += '<label class="col-sm-2 col-md-2 control-label">{{Label action de référence}} <sup><i class="fas fa-question-circle tooltips" title="{{Renseigner le label de l\'action de référence. Cette action ne sera exécutée que si l\'action de référence a été précédemment exécutée. }}"></i></sup></label>';

        div += '<div class="col-sm-6 col-md-2">';
          div += '<div class="input-group">';
            div += '<span class="input-group-btn">';
              div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
            div += '</span>';
            div += '<select class="expressionAttr eqLogicAttr form-control" data-l1key="action_label_liee">';
              div += _labels;
            div += '</select>';
          div += '</div>';
        div += '</div>';

      }

      div += '<label class="col-sm-2 col-md-1 control-label">{{Limiter exécution}} <sup><i class="fas fa-question-circle tooltips" title="{{Si vous souhaitez limiter le nombre d\'exécution sur une période donnée (en secondes). Ne pas remplir ou 0 pour exécuter systèmatiquement.}}"></i></sup></label>';
      div += '<div class="col-sm-2 col-md-1">';
          div += '<input type="number" min="0" class="expressionAttr form-control" data-l1key="action_time_limit" placeholder="{{en secondes}}"/>';
      div += '</div>';

    //  div += '<label class="col-sm-1 control-label">Action</label>';
      div += '<div class="col-sm-6 col-md-3">';
        div += '<div class="input-group">';
          div += '<input class="expressionAttr form-control cmdAction" data-l1key="cmd" data-type="' + _type + '" placeholder="{{Action}}"/>';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
            div += '<a class="btn btn-default listCmdAction roundedRight" data-type="' + _type + '" title="{{Sélectionner une commande}}"><i class="fas fa-list-alt"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

      div += '<div class="col-sm-6 col-md-3 actionOptions">'; // on laisse la place pour afficher les champs "message" ou autre selon les options associées à l'action choisie par l'utilisateur si besoin
        div += jeedom.cmd.displayActionOption(init(_action.cmd, ''), _action.options);
      div += '</div>';

    div += '</div>';
  div += '</div>';

  $('#div_' + _type).append(div);
  $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr');
}

// Fct core permettant de sauvegarder
function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {};
  }

  _eqLogic.configuration.trigger = $('#div_trigger .trigger').getValues('.expressionAttr');
  _eqLogic.configuration.trigger_prog = $('#div_trigger_prog .trigger_prog').getValues('.expressionAttr');
  _eqLogic.configuration.trigger_timerange = $('#div_trigger_timerange .trigger_timerange').getValues('.expressionAttr');
  _eqLogic.configuration.action = $('#div_action .action').getValues('.expressionAttr');

  _eqLogic.configuration.trigger_cancel = $('#div_trigger_cancel .trigger_cancel').getValues('.expressionAttr');
  _eqLogic.configuration.trigger_prog_cancel = $('#div_trigger_prog_cancel .trigger_prog_cancel').getValues('.expressionAttr');
  _eqLogic.configuration.trigger_timerange_cancel = $('#div_trigger_timerange_cancel .trigger_timerange_cancel').getValues('.expressionAttr');
  _eqLogic.configuration.action_cancel = $('#div_action_cancel .action_cancel').getValues('.expressionAttr');

  return _eqLogic;
}

// fct core permettant de restituer les infos declarées
function printEqLogic(_eqLogic) {

  $('#div_trigger').empty();
  $('#div_trigger_prog').empty();
  $('#div_trigger_timerange').empty();
  $('#div_action').empty();
  $('#div_trigger_cancel').empty();
  $('#div_trigger_prog_cancel').empty();
  $('#div_trigger_timerange_cancel').empty();
  $('#div_action_cancel').empty();

  _labels = '<option value="" select></option>'; // initialise notre liste deroulante de labels avec le choix "vide"

  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.trigger)) {
      for (var i in _eqLogic.configuration.trigger) {
        addTriggerValue(_eqLogic.configuration.trigger[i], 'trigger');
      }
    }
    if (isset(_eqLogic.configuration.trigger_prog)) {
      for (var i in _eqLogic.configuration.trigger_prog) {
        addTriggerProg(_eqLogic.configuration.trigger_prog[i], 'trigger_prog');
      }
    }
    if (isset(_eqLogic.configuration.trigger_timerange)) {
      for (var i in _eqLogic.configuration.trigger_timerange) {
        addTriggerTimeRange(_eqLogic.configuration.trigger_timerange[i], 'trigger_timerange');
      }
    }
    if (isset(_eqLogic.configuration.action)) {
      for (var i in _eqLogic.configuration.action) {
      //  console.log(_eqLogic.configuration.action[i].action_label);
        _labels += '<option value="'+_eqLogic.configuration.action[i].action_label+'">'+_eqLogic.configuration.action[i].action_label+'</option>'; // a chaque action, on prend son label pour le mettre dans la liste déroulante
        addAction(_eqLogic.configuration.action[i], 'action', '');
      }
    }
    if (isset(_eqLogic.configuration.trigger_cancel)) {
      for (var i in _eqLogic.configuration.trigger_cancel) {
        addTriggerValue(_eqLogic.configuration.trigger_cancel[i], 'trigger_cancel');
      }
    }
    if (isset(_eqLogic.configuration.trigger_prog_cancel)) {
      for (var i in _eqLogic.configuration.trigger_prog_cancel) {
        addTriggerProg(_eqLogic.configuration.trigger_prog_cancel[i], 'trigger_prog_cancel');
      }
    }
    if (isset(_eqLogic.configuration.trigger_timerange_cancel)) {
      for (var i in _eqLogic.configuration.trigger_timerange_cancel) {
        addTriggerTimeRange(_eqLogic.configuration.trigger_timerange_cancel[i], 'trigger_timerange_cancel');
      }
    }
    if (isset(_eqLogic.configuration.action_cancel)) {
      for (var i in _eqLogic.configuration.action_cancel) {
        addAction(_eqLogic.configuration.action_cancel[i], 'action_cancel', _labels); // on passe en argument notre liste de labels
      }
    }
  }
}


/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
