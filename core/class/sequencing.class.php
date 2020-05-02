<?php

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

/*

/*  $value = jeedom::evaluateExpression($trigger['cmd']); // on pourrait utiliser directement $_option['value'], mais il vire les accents et caractéres speciaux dans le cas de conditions string
    $value_test = cmd::byId(str_replace('#', '', $trigger['cmd']))->execCmd(); // resultat identique, quel est la meilleur pratique ? TODO
*/

/*  $check = jeedom::evaluateExpression($value . $trigger['condition_operator1'] . $trigger['condition_test1']);
    $check2 = evaluate($value . $trigger['condition_operator1'] . $trigger['condition_test1']);
    log::add('sequencing', 'debug', $this->getHumanName() . ' resultat 1 et 2 : ' . $check . ' - ' . $check2);*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class sequencing extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */


    public static function startProgrammed($_options) { // fonction appelée par le cron de programmation start

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      if (is_object($sequencing)) {
        log::add('sequencing', 'debug', $sequencing->getHumanName() . ' - Fct startProgrammed appelée par le CRON principal (qui bypass les conditions)');

        $sequencing->setCache('trigger_name', 'lancement programmé');
        $sequencing->setCache('trigger_full_name', 'lancement programmé');
        $sequencing->setCache('trigger_value', '');
        $sequencing->setCache('trigger_datetime', date('Y-m-d H:i:s'));
        $sequencing->setCache('trigger_time', date('H:i:s'));

        $sequencing->actionsLaunch();
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'exécution de la programmation - EqLogic inconnu. Vérifiez l\'ID');
      }


    }

    public static function endProgrammed($_options) { // fonction appelée par le cron de programmation cancel

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      if (is_object($sequencing)) {
        log::add('sequencing', 'debug', $sequencing->getHumanName() . ' - Fct endProgrammed appelée par le CRON principal (qui bypass les conditions)');

        $sequencing->setCache('trigger_name', 'annulation programmée');
        $sequencing->setCache('trigger_full_name', 'annulation programmée');
        $sequencing->setCache('trigger_value', '');
        $sequencing->setCache('trigger_datetime', date('Y-m-d H:i:s'));
        $sequencing->setCache('trigger_time', date('H:i:s'));

        $sequencing->actionsCancel();
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'exécution de la programmation - EqLogic inconnu. Vérifiez l\'ID');
      }

    }

    public static function triggerCron($_options) { // fonction appelée par l'un des crons de trigger

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      if (is_object($sequencing)) {
        log::add('sequencing', 'debug', $sequencing->getHumanName() . ' - Fct trigger appelée par un CRON (on va aller évaluer les autres conditions)');

        // on garde "temporairement" nos infos en cache, si elles s'averent valides, on les mettra dans le cache des tags...
        $sequencing->setCache('trigger_name_temp', 'programmé');
        $sequencing->setCache('trigger_full_name_temp', 'programmé');
        $sequencing->setCache('trigger_value_temp', '');
        $sequencing->setCache('trigger_datetime_temp', date('Y-m-d H:i:s'));
        $sequencing->setCache('trigger_time_temp', date('H:i:s'));

        $sequencing->evaluateEachConditions('trigger');
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'exécution de la programmation - EqLogic inconnu. Vérifiez l\'ID');
      }
    }

    public static function triggerCancelCron($_options) { // fonction appelée par l'un des crons de trigger_cancel

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      if (is_object($sequencing)) {
        log::add('sequencing', 'debug', $sequencing->getHumanName() . ' - Fct triggerCancel appelée par un CRON (on va aller évaluer les autres conditions)');

        // on garde "temporairement" nos infos en cache, si elles s'averent valides, on les mettra dans le cache des tags...
        $sequencing->setCache('trigger_name_temp', 'programmé');
        $sequencing->setCache('trigger_full_name_temp', 'programmé');
        $sequencing->setCache('trigger_value_temp', '');
        $sequencing->setCache('trigger_datetime_temp', date('Y-m-d H:i:s'));
        $sequencing->setCache('trigger_time_temp', date('H:i:s'));

        $sequencing->evaluateEachConditions('trigger_cancel');
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'exécution de la programmation - EqLogic inconnu. Vérifiez l\'ID');
      }
    }

    public static function triggerLaunch($_option) { // fct appelée par le listener des triggers (mais pas par la cmd start qui elle, va bypasser l'évaluation des conditions !)
    // dans _option on a toutes les infos du trigger (from les champs du JS)
    // Attention, on peut avoir plusieurs triggers qui utilisent la meme cmd et donc arrivent 1 seule fois ici

      log::add('sequencing', 'debug', '############ Trigger déclenché ############');

      $sequencing = sequencing::byId($_option['sequencing_id']); // on cherche l'équipement correspondant au trigger

      if (is_object($sequencing)) {
        $sequencing->evaluateTrigger($_option, 'trigger');
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'appel d\'un trigger - EqLogic inconnu. Vérifiez l\'ID');
      }

    }

    public static function triggerCancel($_option) { // fct appelée par le listener des triggers d'annulation (mais pas par la cmd stop !)

      log::add('sequencing', 'debug', '############ Trigger d\'annulation déclenché ############');

      $sequencing = sequencing::byId($_option['sequencing_id']); // on cherche l'équipement correspondant au trigger

      if (is_object($sequencing)) {
        $sequencing->evaluateTrigger($_option, 'trigger_cancel');
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'appel d\'un trigger d\'annulation - EqLogic inconnu. Vérifiez l\'ID');
      }

    }

    public static function actionDelayed($_options) { // fonction appelée par les cron qui servent a reporter l'exécution des actions
    // Dans les options on trouve le eqLogic_id et 'action' qui lui meme contient tout ce qu'il faut pour exécuter l'action reportée, incluant le titre et message pour les messages

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      if (is_object($sequencing)) {
        log::add('sequencing', 'debug', $sequencing->getHumanName() . ' - Fct actionDelayed appelée par le CRON - eqLogic_id : ' . $_options['eqLogic_id'] . ' - cmd : ' . cmd::byId(str_replace('#', '', $_options['action']['cmd']))->getHumanName() . ' - action_label : ' . trim($_options['action']['action_label']));

        $sequencing->execAction($_options['action']);
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'exécution d\'une action différée - EqLogic inconnu. Vérifiez l\'ID');
      }


    }

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */


    /*     * *********************Méthodes d'instance************************* */

    // Cette fonction va évaluer toutes les conditions associées à 1 trigger donné qui vient de se déclencher //
    public function evaluateTrigger($_option, $_type) { // $_option nous donne l'event_id et la valeur du trigger, $_type nous dit si c'est un trigger ou trigger_cancel
    // on peut avoir plusieurs triggers qui utilisent la meme cmd et donc arrivent 1 seule fois ici. On va tous les évaluer et si 1 est correcte, on continu

    //  log::add('sequencing', 'debug', $this->getHumanName() . ' => Détection d\'un trigger encore inconnu');

      $results = array(); // va stocker le resultat de chaque condition associée à ce trigger

      foreach ($this->getConfiguration($_type) as $trigger) { // on boucle dans tous les trigger ou trigger_cancel de la conf
        if ('#' . $_option['event_id'] . '#' == $trigger['cmd']) {// on cherche quel est l'event qui nous a déclenché pour pouvoir chopper ses infos et évaluer les conditions. Si la meme commande est utilisée plusieurs fois, cette boucle sera évaluée pour chaque et le resultat stocké dans $results[]

          $value = jeedom::evaluateExpression($trigger['cmd']); // on pourrait utiliser directement $_option['value'], mais il vire les accents et caractéres speciaux dans le cas de conditions string

          log::add('sequencing', 'debug', $this->getHumanName() . ' Nom : ' . $trigger['name'] . ' - cmd : ' . cmd::byId(str_replace('#', '', $trigger['cmd']))->getHumanName() . ' - valeur : ' . $value);

          $results[$trigger['name']] = $this->checkTriggerValues($trigger, true); // true : c'est un trigger

          // on garde "temporairement" nos infos en cache, si elles s'averent valides, on les mettra dans le cache des tags...
          $this->setCache('trigger_name_temp', $trigger['name']);
          $this->setCache('trigger_full_name_temp', cmd::byId(str_replace('#', '', $trigger['cmd']))->getHumanName());
          $this->setCache('trigger_value_temp', jeedom::evaluateExpression($trigger['cmd']));
          $this->setCache('trigger_datetime_temp', date('Y-m-d H:i:s'));
          $this->setCache('trigger_time_temp', date('H:i:s'));

        } // fin if notre event correspond à un trigger
      } // fin foreach tous les triggers ou trigger_cancel

      // on boucle dans tous nos résultats pour CE trigger là
      foreach ($results as $key => $value) {
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Résultat évaluation pour ce trigger (' . $key . ') : ' . $value);
      }

      $triggerValide = in_array(1, $results); // si on a au moins 1 ligne de condition lié à ce trigger qui est valide

      if ($triggerValide && (($_type == 'trigger' && $this->getConfiguration('check_triggers_type') == 'OR') || ($_type == 'trigger_cancel' && $this->getConfiguration('check_triggers_cancel_type') == 'OR' ))){ // on est en condition "OU" et on en a deja 1, inutile d'evaluer le reste : on declenche !

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Au moins 1 correct et on est en OU, on cherche pas plus => on déclenche !');

        $this->setCache('trigger_name', $this->getCache('trigger_name_temp'));
        $this->setCache('trigger_full_name', $this->getCache('trigger_full_name_temp'));
        $this->setCache('trigger_value', $this->getCache('trigger_value_temp'));
        $this->setCache('trigger_datetime', $this->getCache('trigger_datetime_temp'));
        $this->setCache('trigger_time', $this->getCache('trigger_time_temp'));

        if($_type == 'trigger') {
          $this->actionsLaunch();
        } else if ($_type == 'trigger_cancel'){
          $this->actionsCancel();
        }

      }else if($triggerValide){ // on ne continue que si ce trigger là est valide, pour ne pas declencher sur une autre condition... Maintenant on va évaluer tout le reste
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Au moins 1 correct, on continu');
        $this->evaluateEachConditions($_type);
      } else {
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Aucune condition validée pour ce trigger => on s\'arrête là');
      }

    }

/*    if (is_array($this->getConfiguration('trigger_timerange'))) {
      foreach ($this->getConfiguration('trigger_timerange') as $timerange) {
        if ($timerange['name'] == '') {
          throw new Exception(__('Le champs Nom pour les plages temporelle (déclencheur) ne peut être vide',__FILE__));
        }
      }
    }
    if (is_array($this->getConfiguration('trigger_timerange_cancel'))) {
      foreach ($this->getConfiguration('trigger_timerange_cancel') as $timerange) {
        if ($timerange['name'] == '') {
          throw new Exception(__('Le champs Nom pour les plages temporelle (déclencheur d\'annulation) ne peut être vide',__FILE__));
        }
      }
    }*/

    public function evaluateEachConditions($_type){ // $_type ici peut etre trigger ou trigger_cancel
      // Cette fonction va renvoyer un tableau avec le resultat de toutes les conditions ($key : le nom de la condition, et $value : 0 ou 1(valide))
      // les infos du JS peuvent etre : trigger, trigger_prog, trigger_timerange, trigger_cancel, trigger_prog_cancel et trigger_timerange_cancel.

      $results = array(); // va stocker le resultat de toutes les conditions (oui on va recalculer notre trigger éventuel aussi...;-( ))
      foreach ($this->getConfiguration($_type) as $triggerOrCond) {
        $results[$triggerOrCond['name']] = $this->checkTriggerValues($triggerOrCond, false); // false : c'est pas un trigger qui nous appelle (default)
      }

      if($_type == 'trigger'){ //Pas trés beau... il aurai fallu que trigger_timerange_cancel s'appelle trigger_cancel_timerange dans le JS pour faire un simple $_type.'_timerange' dans le foreach ci-dessous... TODO ?
        $_type_timerange = 'trigger_timerange';
      } else if ($_type == 'trigger_cancel'){
        $_type_timerange = 'trigger_timerange_cancel';
      }

      foreach ($this->getConfiguration($_type_timerange) as $triggerOrCond) {
        $results[$triggerOrCond['name']] = $this->checkCondTimeRange($triggerOrCond);
      }

      foreach ($results as $key => $value) {
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Tous les résultats pour toutes les conditions : ' . $key . ' : ' . $value);
      }

      $this->evaluateGlobalConditions($_type, $results);

    }

    public function evaluateGlobalConditions($_type, $results){ //$_type est trigger ou trigger_cancel. Cette fonction prend le tableau avec le resultat de toutes les conditions ($key : le nom de la condition, et $value : 0 ou 1(valide)) et regarde s'il valide les conditions

      // maintenant on va voir le type de condition entre nos différents triggers et décider de lancer nos actions ou non
      $evaluationTotaleTrigger = 0;

      if(($_type == 'trigger' && $this->getConfiguration('check_triggers_type') == 'AND') || ($_type == 'trigger_cancel' && $this->getConfiguration('check_triggers_cancel_type') == 'AND')) { // si on veut évaluer tous les triggers en "ET" (on veut que des 1 dans notre tableau)

        if(!empty($results)){ //il faut absolument tester que le tableau n'est pas vide car cette fonction renvoie un 1 si vide...
          $evaluationTotaleTrigger = array_product($results); // renverra 1 uniquement si tous les resultats sont 1 (tous corrects)
        } else {
          $evaluationTotaleTrigger = 0;
        }

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Evaluation "ET" et evaluationTotaleTrigger : ' . $evaluationTotaleTrigger);

      } else if(($_type == 'trigger' && $this->getConfiguration('check_triggers_type') == 'OR') || ($_type == 'trigger_cancel' && $this->getConfiguration('check_triggers_cancel_type') == 'OR' )){ // OU : on cherche au moins 1 "1" dans notre tableau de resultat

        $evaluationTotaleTrigger = in_array(1, $results);
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Evaluation "OU" et evaluationTotaleTrigger : ' . $evaluationTotaleTrigger);

      } else if (($_type == 'trigger' && $this->getConfiguration('check_triggers_type') == 'x_sur_N') || ($_type == 'trigger_cancel' && $this->getConfiguration('check_triggers_cancel_type') == 'x_sur_N' )) { // x sur N : on cherche au moins "x_sur_N_value" 1 dans le tableau

        $count = 0; // il y a une fonction php qui devrait faire ca tout seule (array_count_values($results)[1]), mais il ne compte jamais le 1er item...
        foreach ($results as $value) {
          if($value == 1){
            $count++;
          }
        }

        if ($_type == 'trigger'){
          $xVoulu = $this->getConfiguration('x_sur_N_value');
        } else if ($_type == 'trigger_cancel'){
          $xVoulu = $this->getConfiguration('x_sur_N_value_cancel');
        }

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Evaluation "x sur N", on en veut : ' . $xVoulu . ' et on en a : ' . $count);

        if($count >= $xVoulu){
          $evaluationTotaleTrigger = 1;
        } else {
          $evaluationTotaleTrigger = 0;
        }

      } else if (($_type == 'trigger' && $this->getConfiguration('check_triggers_type') == 'perso') || ($_type == 'trigger_cancel' && $this->getConfiguration('check_triggers_cancel_type') == 'perso' )) {

        if ($_type == 'trigger'){
          $condition = $this->getConfiguration('condition_perso');
        } else if ($_type == 'trigger_cancel'){
          $condition = $this->getConfiguration('condition_perso_cancel');
        }

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Evaluation "perso", condition : ' . $condition);

        preg_match_all('/#(\w*)#/', $condition, $matches, PREG_SET_ORDER);
        foreach ($matches as $key => $matche) {
          log::add('sequencing', 'debug', $this->getHumanName() . ' - matche : ' . $matche[1]);
          $condition = str_replace('#'.$matche[1].'#', $results[$matche[1]], $condition); // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
        }
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Condition après moulinette regex : ' . $condition);


        $evaluationTotaleTrigger = jeedom::evaluateExpression($condition);

        log::add('sequencing', 'debug', $this->getHumanName() . ' - evaluationTotaleTrigger : ' . $evaluationTotaleTrigger);

      }

      if($evaluationTotaleTrigger){ // le resultat final qui dit qu'on a bien évalué TOUTES nos conditions selon les criteres voulus

        // on met dans les caches des tags les dernieres infos enregistrées de notre trigger initial
        $this->setCache('trigger_name', $this->getCache('trigger_name_temp'));
        $this->setCache('trigger_full_name', $this->getCache('trigger_full_name_temp'));
        $this->setCache('trigger_value', $this->getCache('trigger_value_temp'));
        $this->setCache('trigger_datetime', $this->getCache('trigger_datetime_temp'));
        $this->setCache('trigger_time', $this->getCache('trigger_time_temp'));

        if($_type == 'trigger') {
          $this->actionsLaunch();
        } else if ($_type == 'trigger_cancel'){
          $this->actionsCancel();
        }
      }

    }

    public function checkTriggerValues($trigger, $_fromTrigger = false){ // cette fonction évalue si le trigger de type "declencheur sur valeur et repetition" valide tout ce qu'on lui demande (condition sur valeur et repetition)

        $conditions = $this->checkTriggerValuesConditions($trigger); // on évalue nos conditions sur la valeur

        if($trigger['condition_rep_nb_fois'] > 1){ // si on doit évaluer en plus la repetition de cette valeur

          // on met à jour les caches concernant la repetition de valeur
          if($conditions){ // conditions de valeur valide, il faut évaluer nos conditions de repetitions

            // On va couper le compteur si délai dépassé, ce qui permet de ne pas avoir à tester le timestamp par la suite mais uniquement les valeurs du compteur.
            $tempsDepuisTrigger = time() - $this->getCache('timestamp_counter_trigger_' . $trigger['name']);
            if($tempsDepuisTrigger > $trigger['condition_rep_periode']){ // la durée est échue, on reinitialise notre compteur.
              $this->setCache('counter_trigger_' . $trigger['name'], 0);
            }

            $compteur_cache = $this->getCache('counter_trigger_' . $trigger['name']);

            if($_fromTrigger){ // si on a été appelé par un trigger et non par la fonction d'évaluation de toutes les conditions, on va setter nos caches
              if($compteur_cache == '' || $compteur_cache == 0){ // si c'est notre 1er trigger valide => on commence le compte
              // cache inexistant ou 0 c'est pareil pour Jeedom, le 0 ne semble pas stocké, mais on le test quand meme au cas où...
                $this->setCache('counter_trigger_' . $trigger['name'], 1);
                $this->setCache('timestamp_counter_trigger_' . $trigger['name'], time()); // on garde le timestamp en mémoire pour pouvoir évaluer la période
              } else { // on est dans les délais (sinon on serait à 0 par le if d'avant), on incremente
                $this->setCache('counter_trigger_' . $trigger['name'], $compteur_cache + 1);
              }
            }

            log::add('sequencing', 'debug', $this->getHumanName() . ' - Compteur répétition pour : ' . $trigger['name'] . ' : ' . $this->getCache('counter_trigger_' . $trigger['name']));

            // on va lire les caches de repetition pour savoir si valide ou non ($check)
            if($this->getCache('counter_trigger_' . $trigger['name']) >= $trigger['condition_rep_nb_fois']){ // on a atteint la quantité de repetition voulue

              log::add('sequencing', 'debug', $this->getHumanName() . ' - On a atteint le nombre de repetition dans le temps imparti => cette condition est VALIDÉE !');
              $check = 1;

            }else{
              log::add('sequencing', 'debug', $this->getHumanName() . ' - On a pas encore atteint le nombre de repetition => cette condition n\'est pas validée ! (on ne fait rien)');
              $check = 0;
            }

          }
/*else{ // conditions de valeur NON valide, il faut reinitialiser notre compteur
            $this->setCache('counter_trigger_' . $trigger['name'], 0);
          }*/

        }else{ // on regarde pas les repetitions pour validité, on prend le resultat precedent directement
          $check = $conditions;
        }

        if($check == ''){
          $check = 0;
        }

        return $check;

    }

    public function checkTriggerValuesConditions($trigger) { // cette fonction évalue si le trigger de type "declencheur sur valeur et repetition" valide uniquement les conditions sur valeur !

      $value = jeedom::evaluateExpression($trigger['cmd']);

      if(!is_numeric($value)){
    //    log::add('sequencing', 'debug', $this->getHumanName() . ' Notre valeur à évaluer n\'est pas numerique : ' . $value);
        $value = '"'.$value.'"'; // parfois ca marche sans, parfois ca marche pas... mais ca marche a tous les coup avec !
        $trigger['condition_test1'] = '"'.$trigger['condition_test1'].'"';
        $trigger['condition_test2'] = '"'.$trigger['condition_test2'].'"';
      }

      if($trigger['condition_operator'] != ''){ // on a 2 conditions
        log::add('sequencing', 'debug', $this->getHumanName() . ' Expression à évaluer (valeur et conditions) : ' . $value . $trigger['condition_operator1'] . $trigger['condition_test1'] . $trigger['condition_operator'] . $value . $trigger['condition_operator2'] . $trigger['condition_test2']);
        $check = jeedom::evaluateExpression($value . $trigger['condition_operator1'] . $trigger['condition_test1'] . $trigger['condition_operator'] . $value . $trigger['condition_operator2'] . $trigger['condition_test2']);
      } else if($trigger['condition_operator1'] != ''){ // une seule condition

        log::add('sequencing', 'debug', $this->getHumanName() . ' Expression à évaluer (valeur et conditions) : ' . $value . $trigger['condition_operator1'] . $trigger['condition_test1']);

        $check = jeedom::evaluateExpression($value . $trigger['condition_operator1'] . $trigger['condition_test1']);

      } else { // sinon on a pas de condition : tout est valide
        $check = 1;
      }

    //  log::add('sequencing', 'debug', $this->getHumanName() . ' - Résultat checkTriggerValuesConditions pour : ' . $trigger['name'] . ' : ' . $check);

      return $check;

    }

    public function checkCondTimeRange($timerange){

      $start_datetime = $timerange['timerange_start'];
      $end_datetime = $timerange['timerange_end'];

      if($timerange['rep_'.date('N')] == 1){ // si aujourd'hui est coché date('N') renvoie le jour courant de 1 (lundi) à 7(dimanche)
          log::add('sequencing', 'debug', $this->getHumanName() . ' - Aujourd\'hui (' . date('N') . ') est cochée ');

          $now = date('H:i:s');
          $start = date('H:i:s', strtotime($start_datetime));
          $end = date('H:i:s', strtotime($end_datetime));

      } else if($timerange['rep_week'] == 1){
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Semaine est cochée ');

        $now = date('N H:i:s');
        $start = date('N H:i:s', strtotime($start_datetime));
        $end = date('N H:i:s', strtotime($end_datetime));

      } else if($timerange['rep_month'] == 1){
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Mois est coché ');

        $now = date('d H:i:s');
        $start = date('d H:i:s', strtotime($start_datetime));
        $end = date('d H:i:s', strtotime($end_datetime));

      } else if($timerange['rep_year'] == 1){
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Année est cochée ');

        $now = date('m-d H:i:s');
        $start = date('m-d H:i:s', strtotime($start_datetime));
        $end = date('m-d H:i:s', strtotime($end_datetime));

      } else if($timerange['rep_1'] != 1 && $timerange['rep_2'] != 1 && $timerange['rep_3'] != 1 && $timerange['rep_4'] != 1 && $timerange['rep_5'] != 1 && $timerange['rep_6'] != 1 && $timerange['rep_7'] != 1){ // aucun jour coché (ni semaine ni mois ni année testés avant)
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Aucune répétition cochée ');

        $now = date('Y-m-d H:i:s');
        $start = $start_datetime;
        $end = $end_datetime;

      } else { // je vois pas comment on peut arriver là, mais c'est au cas où...
        log::add('sequencing', 'debug', $this->getHumanName() . ' - ****************** On est passé dans le else final, comment ??? ');
        return 0;
      }

      log::add('sequencing', 'debug', $this->getHumanName() . ' On va évaluer : ' . $timerange['name'] . ' : ' . $start . ' - ' . $end . ' - avec now : ' . $now);

      if($now >= $start && $now <= $end){
        log::add('sequencing', 'debug', $this->getHumanName() . ' - On est dans la plage => OK');
        return 1;
      } else {
        log::add('sequencing', 'debug', $this->getHumanName() . ' - On n\'est pas dans la plage => nok');
        return 0;
      }

    }

    public function execAction($action) { // exécution d'une seule action

      log::add('sequencing', 'debug', $this->getHumanName() . '################ Exécution de l\'action ' . trim($action['action_label']) . ' ############');

      if(isset($action['action_time_limit']) && $action['action_time_limit'] != '' && is_numeric($action['action_time_limit'])){ // si on veut limiter la fréquence d'exécution

        $now = time();
        $tempsDepuisAction = $now - $this->getCache('execAction_'.$action['cmd'].'_'.trim($action['action_label']).'_'.trim($action['action_label_liee']).'_lastExec');

        log::add('sequencing', 'debug', 'tempsDepuisAction (s) : ' . $tempsDepuisAction . ' - période voulue sans répétition (s) : ' . $action['action_time_limit']);

        if ($tempsDepuisAction < $action['action_time_limit']){ //TODO : PHP Warning: A non-numeric value encountered in /var/www/html/plugins/sequencing/core/class/sequencing.class.php on line 235 (il y avait un *60 à l'époque du warning) => devrait etre resolu avec l'ajout du test is_numeric ci-dessus

          log::add('sequencing', 'debug', 'Action déjà exécutée dans la période => on ne l\'exécute pas');
          return;
        }
      }

      try {
        $options = array(); // va permettre d'appeler les options de configuration des actions, par exemple un scenario ou les textes pour un message
        if (isset($action['options'])) {
          $options = $action['options'];
          foreach ($options as $key => $value) { // ici on peut définir les "tag" de configuration qui seront à remplacer par des variables
            // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
            $value = str_replace('#tag1#', $this->getConfiguration('tag1'), $value);
            $value = str_replace('#tag2#', $this->getConfiguration('tag2'), $value);
            $value = str_replace('#tag3#', $this->getConfiguration('tag3'), $value);

            $value = str_replace('#action_label#', trim($action['action_label']), $value);
            $value = str_replace('#action_timer#', $action['action_timer'], $value);
            $value = str_replace('#action_label_liee#', trim($action['action_label_liee']), $value);

            $value = str_replace('#trigger_name#', $this->getCache('trigger_name'), $value);
            $value = str_replace('#trigger_full_name#', $this->getCache('trigger_full_name'), $value);
            $value = str_replace('#trigger_value#', $this->getCache('trigger_value'), $value);
            $value = str_replace('#trigger_datetime#', $this->getCache('trigger_datetime'), $value);
            $value = str_replace('#trigger_time#', $this->getCache('trigger_time'), $value);

            // reprise des tags jeedom des scenatios
            $value = str_replace('#seconde#', (int) date('s'), $value);
            $value = str_replace('#minute#', (int) date('i'), $value);
            $value = str_replace('#heure#', (int) date('G'), $value);
            $value = str_replace('#heure12#', (int) date('g'), $value);
            $value = str_replace('#jour#', (int) date('d'), $value);
            $value = str_replace('#semaine#', date('W'), $value);
            $value = str_replace('#mois#', (int) date('m'), $value);
            $value = str_replace('#annee#', (int) date('Y'), $value);
            $value = str_replace('#date#', (int) date('md'), $value);
            $value = str_replace('#time#', date('Gi'), $value);
            $value = str_replace('#timestamp#', time(), $value);
            $value = str_replace('#sjour#', '"' . date_fr(date('l')) . '"', $value);
            $value = str_replace('#smois#', '"' . date_fr(date('F')) . '"', $value);
            $value = str_replace('#njour#', (int) date('w'), $value);
            $value = str_replace('#jeedom_name#', '"' . config::byKey('name') . '"', $value);
            $value = str_replace('#hostname#', '"' . gethostname() . '"', $value);
            $value = str_replace('#IP#', '"' . network::getNetworkAccess('internal', 'ip', '', false) . '"', $value);

            $value = str_replace('#eq_full_name#', $this->getHumanName(), $value);
            $options[$key] = str_replace('#eq_name#', $this->getName(), $value);
          }
        }
        scenarioExpression::createAndExec('action', $action['cmd'], $options);

        if(isset($action['action_label'])){ // si on avait un label (donc c'est une action), on memorise qu'on l'a lancé
          $this->setCache('execAction_'.trim($action['action_label']), 1);
      //    log::add('sequencing', 'debug', 'setCache TRUE pour label : ' . $action['action_label']);
        }

        if(isset($action['action_time_limit'])){ // si on veut limiter la fréquence d'exécution
          // garde en cache le timestamp de la derniere exéc
          $this->setCache('execAction_'.$action['cmd'].'_'.trim($action['action_label']).'_'.trim($action['action_label_liee']).'_lastExec', $now); // on met un max de truc pour eviter les interferences entre actions, vu qu'on demande pas de nom unique et que la cmd peut etre utilisée plusieurs fois...
          log::add('sequencing', 'debug', 'setCache : execAction_'.$action['cmd'].'_'.trim($action['action_label']).'_'.trim($action['action_label_liee']).'_lastExec - timestamp : ' . $now);
        }

      } catch (Exception $e) {
        log::add('sequencing', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());

      }

    }


    public function actionsLaunch() { // fct appelée par la cmd 'start' appelée par l'extérieur ou par un trigger valide (via fonction triggerLaunch) ou via le cron de programmation

      log::add('sequencing', 'debug', $this->getHumanName() . '################ Évaluation timers et lancement des actions ############');

      foreach ($this->getConfiguration('action') as $action) { // pour toutes les actions définies

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Config Action - action_label : ' . trim($action['action_label']) . ' - action_timer : ' . $action['action_timer'] . ' - reporter : ' . $action['reporter']);

        if(is_numeric($action['action_timer']) && $action['action_timer'] > 0){ // si on a un timer bien defini et > 0 min, on va lancer un cron pour l'exécution retardée de l'action

          $this->setActionDelayedCron($action);

        }else{ // pas de timer valide defini, on execute l'action immédiatement

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Pas de timer lié, on execute cmd ' . $action['cmd']);
        //  log::add('sequencing', 'debug', $this->getHumanName() . ' - Pas de timer lié, on execute ' . cmd::byId(str_replace('#', '', $action['cmd']))->getHumanName());

          $this->execAction($action);

        }

      } // fin foreach toutes les actions

    }


    public function actionsCancel() { // fct appelée par la cmd 'stop' appelée par l'extérieur ou par un trigger_cancel valide (via fonction triggerCancel)

      log::add('sequencing', 'debug', $this->getHumanName() . '################ Exécution des actions d\'annulation ############');

      foreach ($this->getConfiguration('action_cancel') as $action) { // pour toutes les actions d'annulation définies

        $execActionLiee = $this->getCache('execAction_'.trim($action['action_label_liee'])); // on va lire le cache d'exécution de l'action liée, savoir si déjà lancé ou non...

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Config Action Annulation, action : '. cmd::byId(str_replace('#', '', $action['cmd']))->getHumanName() .', label action liée : ' . trim($action['action_label_liee']) . ' - action liée déjà exécutée : ' . $execActionLiee);

        if(trim($action['action_label_liee']) == ''){ // si pas d'action liée, on execute direct

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Pas d\'action liée, on execute ' . cmd::byId(str_replace('#', '', $action['cmd']))->getHumanName());

          $this->execAction($action);

        }else if(isset($action['action_label_liee']) && trim($action['action_label_liee']) != '' && $execActionLiee == 1){ // si on a une action liée définie et qu'elle a été exécutée => on execute notre action et on remet le cache de l'action liée à 0

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Action liée ('.trim($action['action_label_liee']).') exécutée précédemment, donc on execute ' . cmd::byId(str_replace('#', '', $action['cmd']))->getHumanName() . ' et remise à 0 du cache d\'exéc de l\'action origine');

          $this->execAction($action);

          $this->setCache('execAction_'.trim($action['action_label_liee']), 0);

        }else{ // sinon, on log qu'on n'execute pas l'action et la raison
          log::add('sequencing', 'debug', $this->getHumanName() . ' - Action liée ('.trim($action['action_label_liee']).') non exécutée précédemment, donc on execute pas ' . cmd::byId(str_replace('#', '', $action['cmd']))->getHumanName());
        }

      } // fin foreach toutes les actions

      //coupe les CRON des actions d'alertes non encore appelés
      $this->cleanAllDelayedActionsCron();

    }

    public function setActionDelayedCron($action) { // appelé pour chaque action à reporter

      $cron = cron::byClassAndFunction('sequencing', 'actionDelayed', array('eqLogic_id' => intval($this->getId()), 'action' => $action)); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (eqLogic, action (qui contient cmd, option (les titres et messages notamment) et label))" Si on change le label ou le message, c'est plus le meme "action" et donc cette fonction ne le trouve pas et un nouveau cron sera crée !
      // lors d'une sauvegarde ou suppression de l'eqLogic, si des crons sont existants, ils seront supprimés avec un message d'alerte

      if (!is_object($cron)) { // pas de cron trouvé, on le cree

          $cron = new cron();
          $cron->setClass('sequencing');
          $cron->setFunction('actionDelayed');

          $options['eqLogic_id'] = intval($this->getId());
          $options['action'] = $action; //inclu tout le detail de l'action : sa cmd, ses options pour les messages, son label, ...

          $cron->setOption($options);

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Set CRON pour la cmd : ' . cmd::byId(str_replace('#', '', $options['action']['cmd']))->getHumanName() . ' - label :' . trim($options['action']['action_label']));

          $cron->setEnable(1);
          $cron->setTimeout(5); //minutes

          $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$action['action_timer'].' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'action_timer' min
          $cron->setSchedule(cron::convertDateToCron($delai));

          $cron->setOnce(1); //permet qu'il s'auto supprime une fois exécuté
          $cron->save();

      } else if($action['reporter']) { // si on a bien trouvé notre cron mais on a un new trigger et on a choisi dans ce cas de reporter l'actions

        $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$action['action_timer'].' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'action_timer' min
        $cron->setSchedule(cron::convertDateToCron($delai));

        $cron->save();

      } else { // sinon : le cron existe mais on veut pas le reporter : on fait rien !

        log::add('sequencing', 'debug', $this->getHumanName() . ' - CRON existe déjà et on veut pas le reporter pour la cmd: ' . cmd::byId(str_replace('#', '', $cron->getOption()['action']['cmd']))->getHumanName() . ' - label : ' . trim($cron->getOption()['action']['action_label']) . ' => on ne fait rien !');
      }

    }

    public function setLaunchAndCancelSequenceCron($type) { // appelé lors de l'enregistrement de l'eqLogic, 1 fois pour 'programmation' et 1 fois pour 'programmation_cancel'

      if($type == 'programmation'){
        $fonction = 'startProgrammed';
      } else if($type == 'programmation_cancel'){
        $fonction = 'endProgrammed';
      }

      $prog = $this->getConfiguration($type);
      $cron = cron::byClassAndFunction('sequencing', $fonction, array('eqLogic_id' => intval($this->getId()))); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (eqLogic)"

      if (!is_object($cron) && $prog != '') { // pas de cron trouvé et on en veut 1, on le cree

          $cron = new cron();
          $cron->setClass('sequencing');
          $cron->setFunction($fonction);

          $options['eqLogic_id'] = intval($this->getId());
          $cron->setOption($options);

          $cron->setEnable(1);
          $cron->setTimeout(5); //minutes
          $cron->setSchedule(checkAndFixCron($prog));

          $cron->setLastRun(date('Y-m-d H:i:s')); // marche pas...

          $cron->save();

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Set CRON ' . $type . ' : ' . $prog . ' lastrun : ' . $cron->getLastRun());

      } else if(is_object($cron) && $prog != '') { // si cron existant et programmation non vide, on le met à jour

        $cron->setSchedule(checkAndFixCron($prog));

        $cron->save();

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Update CRON ' . $type . ' : ' . $prog . ' lastrun : ' . $cron->getLastRun());

      } else if (is_object($cron) && $prog == '') { // le cron existe mais on veut plus de programmation : on va le virer

        $cron->remove();

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Suppression du CRON ' . $type);
      } else {
        log::add('sequencing', 'debug', $this->getHumanName() . ' - CRON ' . $type . ' existe pas, et on a pas de programmation => on fait rien');
      }

    }


    public function setTriggersCron($type) { // appelé lors de l'enregistrement de l'eqLogic, 1 fois pour 'trigger_prog' et 1 fois pour 'trigger_prog_cancel'

      if($type == 'trigger_prog'){
        $fonction = 'triggerCron';
      } else if($type == 'trigger_prog_cancel'){
        $fonction = 'triggerCancelCron';
      }

      if (is_array($this->getConfiguration($type))) {
        foreach ($this->getConfiguration($type) as $prog) {

          $cron = cron::byClassAndFunction('sequencing', $fonction ,  array('eqLogic_id' => intval($this->getId()), 'prog' => $prog['trigger_prog'])); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options"

          if (!is_object($cron)) { // pas de cron trouvé, on le cree. Mais vu qu'on viens de tous les virer avant, c'est peu probable qu'on en trouve !

              $cron = new cron();
              $cron->setClass('sequencing');
              $cron->setFunction($fonction ) ;

              $options['eqLogic_id'] = intval($this->getId());
              $options['prog'] = $prog['trigger_prog'];

              $cron->setOption($options);

              log::add('sequencing', 'debug', $this->getHumanName() . ' - Set CRON ' . $type . ' - programmation : ' . $options['prog']);

              $cron->setEnable(1);
              $cron->setTimeout(5); //minutes

              $cron->setSchedule(checkAndFixCron($prog['trigger_prog']));

          //    $cron->setOnce(1); //permet qu'il s'auto supprime une fois exécuté. cronDaily de Jeedom execute la fonction clean qui va supprimer ceux dans le passé (déjà exécutés ou non), donc on peut laisser "trainer" des crons programmés à date fixe. Si on met un setOnce sur un cron périodique, il ne s'execute qu'une seule fois...
              $cron->save();

          } // sinon : rien... ;-)

        }
      }

    }

    public function cleanAllDelayedActionsCron($displayWarningMessage = false) {

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Fct cleanAllDelayedActionsCron');

      $crons = cron::searchClassAndFunction('sequencing','actionDelayed'); // on prend tous nos crons de ce plugin, cette fonction, pour tous les equipements
      if (is_array($crons) && count($crons) > 0) {
        foreach ($crons as $cron) {
          if (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // si l'id correspond et qu'il est pas en cours, on le vire

            log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron trouvé à supprimer - cmd : ' . cmd::byId(str_replace('#', '', $cron->getOption()['action']['cmd']))->getHumanName() . ' - action_label : ' . trim($cron->getOption()['action']['action_label']));

            if($displayWarningMessage){

              log::add('sequencing', 'error', $this->getHumanName() . ' - Attention, des actions avec un délai avant exécution étaient en cours et vont être supprimées, action supprimée : ' . cmd::byId(str_replace('#', '', $cron->getOption()['action']['cmd']))->getHumanName() . ' - action_label : ' . trim($cron->getOption()['action']['action_label']));
            }

            $cron->remove();

          }
        }
      }
    }

    public function cleanLaunchAndCancelSequenceCron() {

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Fct cleanLaunchAndCancelSequenceCron');

      $crons = cron::searchClassAndFunction('sequencing','startProgrammed'); // on prend tous nos crons de ce plugin, cette fonction, pour tous les equipements
      if (is_array($crons) && count($crons) > 0) {
        foreach ($crons as $cron) {
          if (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // si l'id correspond et qu'il est pas en cours, on le vire
            log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron de programmation trouvé à supprimer');
            $cron->remove();
          }
        }
      }

      $crons = cron::searchClassAndFunction('sequencing','endProgrammed'); // on prend tous nos crons de ce plugin, cette fonction, pour tous les equipements
      if (is_array($crons) && count($crons) > 0) {
        foreach ($crons as $cron) {
          if (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // si l'id correspond et qu'il est pas en cours, on le vire
            log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron de programmation_cancel trouvé à supprimer');
            $cron->remove();
          }
        }
      }

    }

    public function cleanAllTriggersAndTriggersCancelCron() {

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Fct cleanAllTriggersAndTriggersCancelCron');

      $crons = cron::searchClassAndFunction('sequencing','triggerCron'); // on prend tous nos crons de ce plugin, cette fonction, pour tous les equipements
      if (is_array($crons) && count($crons) > 0) {
        foreach ($crons as $cron) {
          if (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // si l'id correspond et qu'il est pas en cours, on le vire
            log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron triggerCron trouvé à supprimer');
            $cron->remove();
          }
        }
      }

      $crons = cron::searchClassAndFunction('sequencing','triggerCancelCron'); // on prend tous nos crons de ce plugin, cette fonction, pour tous les equipements
      if (is_array($crons) && count($crons) > 0) {
        foreach ($crons as $cron) {
          if (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // si l'id correspond et qu'il est pas en cours, on le vire
            log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron triggerCancelCron trouvé à supprimer');
            $cron->remove();
          }
        }
      }

    }

    public function setTriggersListeners() {

      // on boucle dans toutes les cmd existantes
      foreach ($this->getCmd() as $cmd) {

        // on assigne la fonction selon le type de capteur
        if ($cmd->getLogicalId() == 'trigger'){
          $listenerFunction = 'triggerLaunch';
        } else if ($cmd->getLogicalId() == 'trigger_cancel'){
          $listenerFunction = 'triggerCancel';
        } else {
          continue; // sinon c'est que c'est pas un truc auquel on veut assigner un listener, on passe notre tour
        }

        // on set le listener associée
        $listener = listener::byClassAndFunction('sequencing', $listenerFunction, array('sequencing_id' => intval($this->getId())));
        if (!is_object($listener)) { // s'il existe pas, on le cree, sinon on le reprend
          $listener = new listener();
          $listener->setClass('sequencing');
          $listener->setFunction($listenerFunction); // la fct qui sera appelée a chaque evenement sur une des sources écoutée
          $listener->setOption(array('sequencing_id' => intval($this->getId())));
        }
        $listener->addEvent($cmd->getValue()); // on ajoute les event à écouter de chacun des capteurs definis. On cherchera le trigger a l'appel de la fonction si besoin

        log::add('sequencing', 'debug', $this->getHumanName() . ' - sensor listener set - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

        $listener->save();

      } // fin foreach cmd du plugin
    }

    public function cleanTriggersListener() {

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Fct cleanTriggersListener');

      $listeners = listener::byClass('sequencing'); // on prend tous nos listeners de ce plugin, pour tous les equipements
      foreach ($listeners as $listener) {
        $sequencing_id_listener = $listener->getOption()['sequencing_id'];

        if($sequencing_id_listener == $this->getId()){ // si on correspond, on le vire
          $listener->remove();
        }

      }

    }

    public function preInsert() {

    }

    // Méthode appelée après la création de votre objet --> on va créer les cmd de déclenchement et annulation
    public function postInsert() {

      $cmd = $this->getCmd(null, 'start');
      if (!is_object($cmd)) {
        $cmd = new sequencingCmd();
        $cmd->setName(__('Déclencher', __FILE__));
      }
      $cmd->setLogicalId('start');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setOrder(0);
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized(0);
      $cmd->setConfiguration('historizeMode', 'none');
      $cmd->save();

      $cmd = $this->getCmd(null, 'stop');
      if (!is_object($cmd)) {
        $cmd = new sequencingCmd();
        $cmd->setName(__('Arrêter', __FILE__));
      }
      $cmd->setLogicalId('stop');
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('action');
      $cmd->setSubType('other');
      $cmd->setOrder(1);
      $cmd->setIsVisible(1);
      $cmd->setIsHistorized(0);
      $cmd->setConfiguration('historizeMode', 'none');
      $cmd->save();

    }

    public function createTriggersCmd() {

      //########## 1 - On va lire la configuration des capteurs dans le JS et on la stocke dans un tableau #########//

      $jsSensors = array(
        'trigger' => array(), // sous-tableau pour stocker les triggers
        'trigger_cancel' => array(), // idem trigger_cancel
      );
      // on a maintenant trigger, trigger_prog, trigger_timerange, trigger_cancel, trigger_prog_cancel et trigger_timerange_cancel

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de triggers pour recuperer les infos
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Boucle de $jsSensors : key : ' . $key);

        if (is_array($this->getConfiguration($key))) {
          foreach ($this->getConfiguration($key) as $sensor) {
            if ($sensor['name'] != '' && $sensor['cmd'] != '') { // si le nom et la cmd sont remplis

              $jsSensors[$key][$sensor['name']] = $sensor; // on stocke toute la conf, c'est à dire tout ce qui dans notre js avait la class "expressionAttr". Pour retrouver notre champs exact : $jsSensors[$key][$sensor['name']][data-l1key]. // attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut écrire, pas dans la variable qui le represente dans cette boucle
              log::add('sequencing', 'debug', $this->getHumanName() . ' - Capteurs sensor config lue, nom : ' . $sensor['name'] . ' - cmd : ' . cmd::byId(str_replace('#', '', $sensor['cmd']))->getHumanName());

            }
          }
        }
      }

      //########## 2 - On boucle dans toutes les cmd existantes, pour les modifier si besoin #########//

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos différents types de capteurs. $key va prendre les valeurs suivantes : trigger puis trigger_cancel

        foreach ($this->getCmd() as $cmd) {
          if ($cmd->getLogicalId() == $key) {
            if (isset($jsSensor[$cmd->getName()])) { // on regarde si le nom correspond à un nom dans le tableau qu'on vient de recuperer du JS, si oui, on actualise les infos qui pourraient avoir bougé

              $sensor = $jsSensor[$cmd->getName()];
              $cmd->setValue($sensor['cmd']);

              $cmd->save();

              // va chopper la valeur de la commande puis la suivre a chaque changement
              if ($cmd->execCmd() == '' || is_nan($cmd->execCmd())) {
                $cmd->setCollectDate('');
                $cmd->event($cmd->execute());
              }

              unset($jsSensors[$key][$cmd->getName()]); // on a traité notre ligne, on la vire. Attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut virer notre ligne

            } else { // on a un sensor qui était dans la DB mais dont le nom n'est plus dans notre JS : on la supprime ! Attention, si on a juste changé le nom, on va le supprimer et le recreer, donc perdre l'historique éventuel. //TODO : voir si ça pose problème
              $cmd->remove();
            }
          }
        } // fin foreach toutes les cmd du plugin
      } // fin foreach nos differents types de capteurs//*/

      //########## 3 - Maintenant on va creer les cmd nouvelles de notre conf (= celles qui restent dans notre tableau) #########//

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de capteurs. $key va prendre les valeurs suivantes : trigger, puis trigger_cancel

        foreach ($jsSensor as $sensor) { // pour chacun des capteurs de ce type

          log::add('sequencing', 'debug', $this->getHumanName() . ' - New Capteurs config : type : ' . $key . ', name : ' . $sensor['name'] . ', cmd : ' . cmd::byId(str_replace('#', '', $sensor['cmd']))->getHumanName());

          $cmd = new sequencingCmd();
          $cmd->setEqLogic_id($this->getId());
          $cmd->setLogicalId($key);
          $cmd->setName($sensor['name']);
          $cmd->setValue($sensor['cmd']);
          $cmd->setType('info');
          $cmd->setSubType('numeric');
          $cmd->setIsVisible(0);
          $cmd->setIsHistorized(1);
          $cmd->setConfiguration('historizeMode', 'none');

          $cmd->save();

          // va chopper la valeur de la commande puis la suivre a chaque changement
          if ($cmd->execCmd() == '' || is_nan($cmd->execCmd())) {
            $cmd->setCollectDate('');
            $cmd->event($cmd->execute());
          }

        } //*/ // fin foreach restant. A partir de maintenant on a des triggers en DB qui refletent notre config lue en JS
      }

    }

    public function preSave() {
      //supprime les CRON des actions d'alertes non encore appelés, affiche une alerte s'il y en avait
      //sert à ne pas laisser trainer des CRONs en cours si on change le message ou le label puis en enregistre. Cas exceptionnel, mais au cas où...

      $this->cleanAllDelayedActionsCron(true); // clean tous les cron des actions

    }

    // fct appelée par Jeedom aprés l'enregistrement de la configuration
    public function postSave() {

      // Va aller lire la conf JS et créer toutes les commandes pour les "trigger" et "trigger_cancel"
      $this->createTriggersCmd();

      if ($this->getIsEnable() == 1) { // si notre eq est actif, on va lui definir nos listeners et crons

        //########## Les listeners
        // un peu de menage dans nos events puis on remet tout en ligne avec la conf actuelle
        $this->cleanTriggersListener();
        $this->setTriggersListeners(); // se base sur les commandes créées précédemments (ou pourrait aller lire la conf, mais ca n'est pas le cas ici)

        //########## Les crons de programmation start et cancel, si définis
        // pas besoin de clean ici, il ne peux y avoir qu'un cron et la gestion de l'update est faite dans la fonction
        $this->setLaunchAndCancelSequenceCron('programmation'); // le parametre correspond au data-type défini dans la conf (desktop)
        $this->setLaunchAndCancelSequenceCron('programmation_cancel');

        //########## Les crons de triggers start et cancel, si définis
        $this->cleanAllTriggersAndTriggersCancelCron();
        $this->setTriggersCron('trigger_prog'); // le parametre correspond au data-type défini dans la conf (desktop)
        $this->setTriggersCron('trigger_prog_cancel');

      } // fin if eq actif
      else { // notre eq n'est pas actif ou il a été désactivé, on supprime les listeners et les crons de programmation et triggers s'ils existaient

        $this->cleanTriggersListener();

        $this->cleanLaunchAndCancelSequenceCron();
        $this->cleanAllTriggersAndTriggersCancelCron();
        $this->cleanAllDelayedActionsCron(true); // clean tous les cron des actions, avec alerte s'il y en avait en cours

      }

      log::add('sequencing', 'info', $this->getHumanName() . ' - Sauvegardé');

    } // fin fct postSave

    // preUpdate ⇒ Méthode appelée avant la mise à jour de votre objet
    // ici on vérifie la présence de nos champs de config obligatoire
    public function preUpdate() {

      $triggersType = array( // liste des types avec des champs a vérifier
        'trigger',
        'trigger_cancel',
      );

      $allNames = array(); // on va stocker tous les noms pour verifier ensuite leur unicité

      foreach ($triggersType as $type) {
        if (is_array($this->getConfiguration($type))) {
          foreach ($this->getConfiguration($type) as $trigger) { // pour tous les capteurs de tous les types, on veut un nom et une cmd
            if (trim($trigger['name']) == '') {
              throw new Exception(__('Le champs Nom pour les capteurs ('.$type.') ne peut être vide',__FILE__));
            }

            array_push($allNames, trim($trigger['name']));

            if ($trigger['cmd'] == '') {
              throw new Exception(__('Le champs Capteur ('.$type.') ne peut être vide',__FILE__));
            }

            if (substr_count($trigger['cmd'], '#') < 2) {
              throw new Exception(__('Attention : '.$trigger['cmd'].' pour : ' . $trigger['name'] . ' n\'est pas être une commande jeedom valide',__FILE__));
            }

            // vérification de la cohérance des conditions de tests

            // pas d'operateur entre les 2 conditions alors qu'on a des infos pour la condition 2
            if ($trigger['condition_operator'] == '' && ($trigger['condition_operator2'] != '' || $trigger['condition_test2'] != '')) {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : vous devez choisir un opérateur entre les conditions 1 et 2, ou supprimer les champs de la seconde condition',__FILE__));
            }

            // operateur entre les 2 conditions alors qu'il manque des infos pour la condition 2
            if ($trigger['condition_operator'] != '' && ($trigger['condition_operator2'] == '' || $trigger['condition_test2'] == '')) {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : condition 2 incomplète',__FILE__));
            }

            // condition 1 incomplete
            if (($trigger['condition_operator1'] != '' && $trigger['condition_test1'] == '') || ($trigger['condition_operator1'] == '' && $trigger['condition_test1'] != ''))  {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : condition 1 incomplète',__FILE__));
            }

            // condition 2 incomplete
            if (($trigger['condition_operator2'] != '' && $trigger['condition_test2'] == '') || ($trigger['condition_operator2'] == '' && $trigger['condition_test2'] != ''))  {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : condition 2 incomplète',__FILE__));
            }

            // condition 2 sans condition 1
            if ($trigger['condition_operator1'] == '' && $trigger['condition_test1'] == '' && $trigger['condition_operator2'] != '' && $trigger['condition_test2'] != '') {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : Si vous n\'avez qu\'une condition, utilisez la 1ère',__FILE__));
            }

            // pas de période associé à une repetition > 1
            if ($trigger['condition_rep_nb_fois'] > 1 && $trigger['condition_rep_periode'] == '') {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : Vous avez déclaré une répétition, vous devez préciser la période correspondante',__FILE__));
            }

            // pas de repetition associé à une période
            if ($trigger['condition_rep_nb_fois'] == '' && $trigger['condition_rep_periode'] != '') {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : Vous avez déclaré une période de répétition, vous devez préciser le nombre correspondant',__FILE__));
            }

            // un de nos champs de durée ou répétition est négatif
            if ($trigger['condition_rep_nb_fois'] < 0 || $trigger['condition_rep_periode'] < 0) {
              throw new Exception(__('Capteur ' . $trigger['name'] . ' ('.$type.') : Les conditions de répétition doivent être des nombres positifs',__FILE__));
            }

          }
        }
      } // fin tests pour trigger et triggel_cancel

      // tests pour les crons
      if($this->getConfiguration('programmation')){
        $prog = $this->getConfiguration('programmation');
        preg_match('/(((\d+,)+\d+|(\d+(\/|-)\d+)|\d+|\*) ?){5,7}/', $prog, $matches);
        if(empty($matches[0])){
      //    log::add('sequencing', 'debug', $this->getHumanName() . ' - Test fct checkAndFixCron - Expression NON valide :  ' . $prog);
          throw new Exception(__('Cron Déclenchement programmé ou périodique ('.$prog.') : format non valide',__FILE__));
        }
      }

      if($this->getConfiguration('programmation_cancel')){
        $prog = $this->getConfiguration('programmation_cancel');
        preg_match('/(((\d+,)+\d+|(\d+(\/|-)\d+)|\d+|\*) ?){5,7}/', $prog, $matches);
        if(empty($matches[0])){
          throw new Exception(__('Cron Déclenchement programmé ou périodique d\'annulation ('.$prog.') : format non valide',__FILE__));
        }
      }

      if (is_array($this->getConfiguration('trigger_prog'))) {
        foreach ($this->getConfiguration('trigger_prog') as $prog) {
          preg_match('/(((\d+,)+\d+|(\d+(\/|-)\d+)|\d+|\*) ?){5,7}/', $prog['trigger_prog'], $matches);
          if(empty($matches[0])){
            throw new Exception(__('Cron Trigger ('.$prog['trigger_prog'].') : format non valide',__FILE__));
          }
        }
      }

      if (is_array($this->getConfiguration('trigger_prog_cancel'))) {
        foreach ($this->getConfiguration('trigger_prog_cancel') as $prog) {
          preg_match('/(((\d+,)+\d+|(\d+(\/|-)\d+)|\d+|\*) ?){5,7}/', $prog['trigger_prog'], $matches);
          if(empty($matches[0])){
            throw new Exception(__('Cron Trigger d\'annulation ('.$prog['trigger_prog'].') : format non valide',__FILE__));
          }
        }
      }

      // tests pour timerange
      $timerangeType = array( // liste des types avec des champs a vérifier
        'trigger_timerange',
        'trigger_timerange_cancel',
      );

      foreach ($timerangeType as $timeranges) {
        if (is_array($this->getConfiguration($timeranges))) {
          foreach ($this->getConfiguration($timeranges) as $timerange) {

            if (trim($timerange['name']) == '') {
              throw new Exception(__('Le champs Nom pour les plages temporelle ('.$timeranges.') ne peut être vide',__FILE__));
            }

            array_push($allNames, trim($timerange['name']));

            if ($timerange['timerange_start'] == '' || $timerange['timerange_end'] == '') {
              throw new Exception(__('Vous devez donner un début et une fin pour la plage temporelle ('.$timeranges.') : '. $timerange['name'],__FILE__));
            }

            if ($timerange['timerange_start'] >= $timerange['timerange_end']) {
              throw new Exception(__('Le début doit être antérieur à la fin pour la plage temporelle ('.$timeranges.') : '. $timerange['name'],__FILE__));
            }

          }
        }

      }

/*      if (is_array($this->getConfiguration('trigger_timerange_cancel'))) {
        foreach ($this->getConfiguration('trigger_timerange_cancel') as $timerange) {
          if ($timerange['name'] == '') {
            throw new Exception(__('Le champs Nom pour les plages temporelle (déclencheur d\'annulation) ne peut être vide',__FILE__));
          }
        }
      }*/

      //tester l'unicité de tous les noms
      if(count($allNames) !== count(array_unique($allNames))){
        throw new Exception(__('Les noms des déclencheurs et déclencheurs d\'annulation doivent être uniques',__FILE__));
      }

    }

    public function postUpdate() {

    }

    public function preRemove() { //quand on supprime notre eqLogic

      // on vire nos listeners associés
      $this->cleanTriggersListener();

      //supprime les CRON des actions d'alertes non encore appelés, affiche une alerte s'il y en avait
      //sert à ne pas laisser trainer des CRONs en cours si on change le message ou le label puis en enregistre. Mais ne devrait arriver qu'exceptionnellement
      $this->cleanAllDelayedActionsCron(true);

      // on vire notre programmation
      $this->cleanLaunchAndCancelSequenceCron();
      $this->cleanAllTriggersAndTriggersCancelCron();

    }

    public function postRemove() {

    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class sequencingCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {


      if ($this->getLogicalId() == 'start') {
        $eqLogic = $this->getEqLogic();

        log::add('sequencing', 'debug', $this->getHumanName() . 'Appel start');

        $eqLogic->setCache('trigger_name', 'user/api');
        $eqLogic->setCache('trigger_full_name', 'user/api');
        $eqLogic->setCache('trigger_value', '');
        $eqLogic->setCache('trigger_datetime', date('Y-m-d H:i:s'));
        $eqLogic->setCache('trigger_time', date('H:i:s'));

        $eqLogic->actionsLaunch();

      } else if ($this->getLogicalId() == 'stop') {
        $eqLogic = $this->getEqLogic();

        log::add('sequencing', 'debug', $this->getHumanName() . 'Appel stop');

        $eqLogic->setCache('trigger_name', 'user/api');
        $eqLogic->setCache('trigger_full_name', 'user/api');
        $eqLogic->setCache('trigger_value', '');
        $eqLogic->setCache('trigger_datetime', date('Y-m-d H:i:s'));
        $eqLogic->setCache('trigger_time', date('H:i:s'));

        $eqLogic->actionsCancel();

      } else { // sinon c'est un sensor et on veut juste sa valeur

/*        log::add('sequencing', 'debug', $this->getHumanName() . '-> ' . jeedom::evaluateExpression($this->getValue()));
        return jeedom::evaluateExpression($this->getValue());
*/
        //pour la gestion des variables (qui ne marche pas du tout... le listener ne se lance pas, la valeur de la variable est aléatoirement bonne ou "", .... Mais systematiquement "" si appel via l'API...)
        log::add('sequencing', 'debug', $this->getHumanName() . '-> ' . str_replace('#', '', jeedom::evaluateExpression($this->getValue())));
        return str_replace('#', '', jeedom::evaluateExpression($this->getValue())); // s'il y a encore des '#' apres évaluation(cas d'une variable), on les vire et on prend que le resultat
      }

    }

    /*     * **********************Getteur Setteur*************************** */
}


