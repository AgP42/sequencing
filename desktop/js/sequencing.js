
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
$("#div_alert_bt").sortable({axis: "y", cursor: "move", items: ".alert_bt", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action_alert_bt").sortable({axis: "y", cursor: "move", items: ".action_alert_bt", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action_ar_alert_bt").sortable({axis: "y", cursor: "move", items: ".action_ar_alert_bt", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_cancel_alert_bt").sortable({axis: "y", cursor: "move", items: ".cancel_alert_bt", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#div_action_cancel_alert_bt").sortable({axis: "y", cursor: "move", items: ".action_cancel_alert_bt", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

// le bouton "ajouter un bt d'alerte" de l'onglet bouton d'alerte
$('.addSensorBtAlert').off('click').on('click', function () {
  addSensorBtAlert({});
});
// le bouton "ajouter un bt d'annulation d'alerte" de l'onglet bouton d'alerte
$('.addSensorCancelBtAlert').off('click').on('click', function () {
  addSensorCancelBtAlert({});
});

// tous les boutons d'action regroupés !
$('.addAction').off('click').on('click', function () {
  addAction({}, $(this).attr('data-type'));
});

// tous les - qui permettent de supprimer la ligne
$("body").off('click','.bt_removeAction').on('click','.bt_removeAction',function () {
  var type = $(this).attr('data-type');
  $(this).closest('.' + type).remove();
});

// permet d'afficher la liste des cmd Jeedom pour choisir sa commande de type "info" (pas les actions donc)
// TODO ce morceau de code est un copier/coller du plugin thermostat, a voir s'il n'y a pas des trucs inutiles là-dedans
$("body").off('click', '.listCmdInfoWindow').on('click', '.listCmdInfoWindow',function () {
  var el = $(this).closest('.form-group').find('.expressionAttr[data-l1key=cmd]');
  jeedom.cmd.getSelectModal({cmd: {type: 'info', subtype: 'binary'}}, function (result) {
    el.value(result.human);
  });
});

// affiche les cmd jeedom de type action
// TODO ce morceau de code est un copier/coller du plugin thermostat, a voir s'il n'y a pas des trucs inutiles là-dedans
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

// TODO ce morceau de code est un copier/coller du plugin thermostat, a voir s'il n'y a pas des trucs inutiles là-edans
$('body').off('focusout','.cmdAction.expressionAttr[data-l1key=cmd]').on('focusout','.cmdAction.expressionAttr[data-l1key=cmd]',function (event) {
  var type = $(this).attr('data-type');
  var expression = $(this).closest('.' + type).getValues('.expressionAttr');
  var el = $(this);
  jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
    el.closest('.' + type).find('.actionOptions').html(html);
  });

});

//////////////// Les fonctions CAPTEURS /////////////////////////////////

// ajoute chaque ligne de bt alerte immédiate
function addSensorBtAlert(_info) {
  var div = '<div class="alert_bt">';
    div += '<div class="form-group ">';

      div += '<label class="col-sm-1 control-label">{{Nom}}</label>';
      div += '<div class="col-sm-2">';
        div += '<div class="input-group">';
          div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="alert_bt" title="{{Supprimer le bouton}}"><i class="fas fa-minus-circle"></i></a>';
          div += '</span>';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="name" title="{{Le nom doit être unique}}"/>'; // dans la class ['name']
        div += '</div>';
      div += '</div>';

      div += '<label class="col-sm-1 control-label">Capteur</label>';
      div += '<div class="col-sm-3">';
        div += '<div class="input-group">';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="cmd" />';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default listCmdInfoWindow roundedRight"><i class="fas fa-list-alt"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

  // TODO : ajouter gestion des boutons inversés ?
  //    div += '<div class="col-sm-1">';
  //      div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr cmdInfo" data-l1key="invert" title="{{Cocher si ce capteur renvoie un 0 lors d\'une activation}}"/>{{Inverser}}</label>';
  //    div += '</div>';

    div += '</div>';
  div += '</div>';
  $('#div_alert_bt').append(div);
  $('#div_alert_bt .alert_bt').last().setValues(_info, '.expressionAttr');
}

// ajoute chaque ligne de cancel bt alerte immédiate
function addSensorCancelBtAlert(_info) {
  var div = '<div class="cancel_alert_bt">';
    div += '<div class="form-group ">';

      div += '<label class="col-sm-1 control-label">{{Nom}}</label>';
      div += '<div class="col-sm-2">';
        div += '<div class="input-group">';
          div += '<span class="input-group-btn">';
          div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="cancel_alert_bt" title="{{Supprimer le bouton}}""><i class="fas fa-minus-circle"></i></a>';
          div += '</span>';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="name" title="{{Le nom doit être unique}}"/>'; // dans la class ['name']
        div += '</div>';
      div += '</div>';

      div += '<label class="col-sm-1 control-label">Capteur</label>';
      div += '<div class="col-sm-3">';
        div += '<div class="input-group">';
          div += '<input class="expressionAttr form-control cmdInfo" data-l1key="cmd" />';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default listCmdInfoWindow roundedRight"><i class="fas fa-list-alt"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

  // TODO : ajouter gestion des boutons inversés ?
  //    div += '<div class="col-sm-1">';
  //      div += '<label class="checkbox-inline"><input type="checkbox" class="expressionAttr cmdInfo" data-l1key="invert" title="{{Cocher si ce capteur renvoie un 0 lors d\'une activation}}"/>{{Inverser}}</label>';
  //    div += '</div>';

    div += '</div>';
  div += '</div>';
  $('#div_cancel_alert_bt').append(div);
  $('#div_cancel_alert_bt .cancel_alert_bt').last().setValues(_info, '.expressionAttr');
}

//////////////// Les fonctions ACTIONS /////////////////////////////////

// fonction générique pour ajouter chaque ligne d'action.
function addAction(_action, _type) {
  var div = '<div class="' + _type + '">';
    div += '<div class="form-group ">';

      if(_type == 'action_alert_bt'){ // pour les actions d'alertes,, on ajoute un label et un timer
        div += '<label class="col-sm-1 control-label">{{Label}} <sup><i class="fas fa-question-circle tooltips" title="{{Renseigner un label si vous voulez lier des actions de désactivations à cette action}}"></i></sup></label>';
        div += '<div class="col-sm-1">';
          div += '<input type="text" class="expressionAttr form-control cmdInfo" data-l1key="action_label"/>';
        div += '</div>';

        div += '<label class="col-sm-1 control-label">{{Délai avant exécution (min)}} <sup><i class="fas fa-question-circle tooltips" title="{{Le délai doit être donné par rapport au déclenchement de l\'alerte initiale et non par rapport à l\'action précédente. Ne pas remplir ou 0 pour déclenchement immédiat}}"></i></sup></label>';
        div += '<div class="col-sm-1">';
          div += '<input type="number" class="expressionAttr form-control cmdInfo" data-l1key="action_timer"/>';
        div += '</div>';
      } else { // pour les actions à la reception d'1 AR ou d'annulation d'alerte, on ajoute le label de l'action d'alerte à lier
        div += '<label class="col-sm-2 control-label">{{Label action de référence}} <sup><i class="fas fa-question-circle tooltips" title="{{Renseigner le label de l\'action de référence. Cette action ne sera exécutée que si l\'action de référence a été précédemment exécutée. }}"></i></sup></label>';
        div += '<div class="col-sm-1">';
          div += '<input type="text" class="expressionAttr form-control cmdInfo" data-l1key="action_label_liee"/>';
        div += '</div>';
      }

      div += '<label class="col-sm-1 control-label">Action</label>';
      div += '<div class="col-sm-2">';
        div += '<div class="input-group">';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>';
          div += '</span>';
          div += '<input class="expressionAttr form-control cmdAction" data-l1key="cmd" data-type="' + _type + '" />';
          div += '<span class="input-group-btn">';
            div += '<a class="btn btn-default listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fa fa-tasks"></i></a>';
            div += '<a class="btn btn-default listCmdAction roundedRight" data-type="' + _type + '" title="{{Sélectionner une commande}}"><i class="fas fa-list-alt"></i></a>';
          div += '</span>';
        div += '</div>';
      div += '</div>';

      div += '<div class="col-sm-5 actionOptions">'; // on laisse la place pour afficher les champs "message" ou autre selon les options associées à l'action choisie par l'utilisateur si besoin
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

  _eqLogic.configuration.alert_bt = $('#div_alert_bt .alert_bt').getValues('.expressionAttr');
  _eqLogic.configuration.action_alert_bt = $('#div_action_alert_bt .action_alert_bt').getValues('.expressionAttr');
  _eqLogic.configuration.action_ar_alert_bt = $('#div_action_ar_alert_bt .action_ar_alert_bt').getValues('.expressionAttr');
  _eqLogic.configuration.cancel_alert_bt = $('#div_cancel_alert_bt .cancel_alert_bt').getValues('.expressionAttr');
  _eqLogic.configuration.action_cancel_alert_bt = $('#div_action_cancel_alert_bt .action_cancel_alert_bt').getValues('.expressionAttr');

  return _eqLogic;
}

// fct core permettant de restituer les cmd declarées
function printEqLogic(_eqLogic) {

  $('#div_alert_bt').empty();
  $('#div_action_alert_bt').empty();
  $('#div_action_ar_alert_bt').empty();
  $('#div_cancel_alert_bt').empty();
  $('#div_action_cancel_alert_bt').empty();

  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.alert_bt)) {
      for (var i in _eqLogic.configuration.alert_bt) {
        addSensorBtAlert(_eqLogic.configuration.alert_bt[i]);
      }
    }
    if (isset(_eqLogic.configuration.action_alert_bt)) {
      for (var i in _eqLogic.configuration.action_alert_bt) {
        addAction(_eqLogic.configuration.action_alert_bt[i], 'action_alert_bt');
      }
    }
    if (isset(_eqLogic.configuration.action_ar_alert_bt)) {
      for (var i in _eqLogic.configuration.action_ar_alert_bt) {
        addAction(_eqLogic.configuration.action_ar_alert_bt[i], 'action_ar_alert_bt');
      }
    }
    if (isset(_eqLogic.configuration.cancel_alert_bt)) {
      for (var i in _eqLogic.configuration.cancel_alert_bt) {
        addSensorCancelBtAlert(_eqLogic.configuration.cancel_alert_bt[i]);
      }
    }
    if (isset(_eqLogic.configuration.action_cancel_alert_bt)) {
      for (var i in _eqLogic.configuration.action_cancel_alert_bt) {
        addAction(_eqLogic.configuration.action_cancel_alert_bt[i], 'action_cancel_alert_bt');
      }
    }
  }
}


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
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
