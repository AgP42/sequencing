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
Les valeurs en cache utilisés :
* Les valeurs des triggers, pour la gestion de repetition :
  $this->setCache('trigger_' . $_type . $trigger['name'], $_option['value']); // $_type peut etre trigger ou trigger_cancel

* Les exécutions d'actions, pour gere les actions d'annulation associées
  $this->setCache('execAction_'.$action['action_label'], 1);

* le nom, la valeur et l'heure du dernier trigger OU trigger_cancel
  $this->setCache('trigger_name', $trigger['name']);
  $this->setCache('trigger_value', $_option['value']);
  $this->setCache('trigger_datetime', date('Y-m-d H:i:s'));
  $this->setCache('trigger_time', date('H:i:s'));

*/

//TODO : ajouter trim() pour les labels (supprime les espaces et caracteres invisibles en debut et fin de chaine)

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class sequencing extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    public static function actionDelayed($_options) { // fonction appelée par les cron qui servent a reporter l'execution des actions
    // Dans les options on trouve le eqLogic_id et 'action' qui lui meme contient tout ce qu'il faut pour executer l'action reportée, incluant le titre et message pour les messages

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      if (is_object($sequencing)) {
        log::add('sequencing', 'debug', $sequencing->getHumanName() . ' - Fct actionDelayed appellée par le CRON - eqLogic_id : ' . $_options['eqLogic_id'] . ' - cmd : ' . $_options['action']['cmd'] . ' - action_label : ' . $_options['action']['action_label']);

        $sequencing->execAction($_options['action']);
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'exécution d\'une action différée - EqLogic inconnu. Vérifiez l\'ID');
      }


    }

    public static function startProgrammed($_options) { // fonction appelée par le cron de programmation start

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      if (is_object($sequencing)) {
        log::add('sequencing', 'debug', $sequencing->getHumanName() . ' - Fct startProgrammed appellée par le CRON');

        $sequencing->setCache('trigger_name', 'programmé');
        $sequencing->setCache('trigger_value', '');
        $sequencing->setCache('trigger_datetime', date('Y-m-d H:i:s'));
        $sequencing->setCache('trigger_time', date('H:i:s'));

        $sequencing->actionsLaunch();
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'exécution de la programmation - EqLogic inconnu. Vérifiez l\'ID');
      }


    }

    public static function triggerLaunch($_option) { // fct appelée par le listener des triggers (mais pas par la cmd start qui elle, va bypasser l'évaluation des conditions !)
    // dans _option on a toutes les infos du trigger (from les champs du JS)

    //  log::add('sequencing', 'debug', '################ Trigger déclenché, on va évaluer les conditions ############');

      $sequencing = sequencing::byId($_option['sequencing_id']); // on cherche l'équipement correspondant au trigger

      if (is_object($sequencing)) {
        $sequencing->evaluateTrigger($_option, 'trigger');
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'appel d\'un trigger - EqLogic inconnu. Vérifiez l\'ID');
      }

    }

    public static function triggerCancel($_option) { // fct appelée par le listener des triggers d'annulation (mais pas par la cmd stop !)

    //  log::add('sequencing', 'debug', '################ Trigger d\'annulation déclenché, on va évaluer les conditions ############');

      $sequencing = sequencing::byId($_option['sequencing_id']); // on cherche l'équipement correspondant au trigger

      if (is_object($sequencing)) {
        $sequencing->evaluateTrigger($_option, 'trigger_cancel');
      } else {
        log::add('sequencing', 'erreur', $sequencing->getHumanName() . ' - Erreur lors de l\'appel d\'un trigger d\'annulation - EqLogic inconnu. Vérifiez l\'ID');
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

    public function evaluateTrigger($_option, $_type) { // $_option nous donne l'event_id et la valeur du trigger, $_type nous dit si c'est un trigger ou trigger_cancel

    //  log::add('sequencing', 'debug', $this->getHumanName() . ' => Detection d\'un trigger encore inconnu');

      foreach ($this->getConfiguration($_type) as $trigger) { // on boucle dans tous les trigger ou trigger_cancel de la conf
        if ('#' . $_option['event_id'] . '#' == $trigger['cmd']) {// on cherche quel est l'event qui nous a déclenché pour pouvoir chopper ses infos et évaluer les conditions
        //TODO : gérer les variables ?
        // dans tous les cas on cherche qui nous a declenché pour évaluer la repetition et chopper les infos pour les tags, meme si on doit évaluer tous les triggers de ce _type !

          $value = jeedom::evaluateExpression($trigger['cmd']); // on pourrait utiliser directement $_option['value'], mais il vire les accents et caractéres speciaux...

          log::add('sequencing', 'debug', $this->getHumanName() . ' => Detection d\'un ' . $_type . ' <= nom : ' . $trigger['name'] . ' - cmd : ' . $trigger['cmd']  . ' - Filtrer répétitions : ' . $trigger['new_value_only'] . ' - valeur : ' . $value);

          if (!$trigger['new_value_only'] || $trigger['new_value_only'] && $this->getCache('trigger_' . $_type . $trigger['name']) != $value){ // si on veut tous les triggers ou uniquement new_value et que notre valeur a changé => on évalue le reste des conditions

            if(($_type == 'trigger' && $this->getConfiguration('trigger_and')) || ($_type == 'trigger_cancel' && $this->getConfiguration('trigger_cancel_and'))) { // si on veut évaluer tous les triggers ("ET")
              log::add('sequencing', 'debug', $this->getHumanName() . ' - ' . $_type . ' et case ET cochée');

              $check = 1;
              foreach ($this->getConfiguration($_type) as $trigger2) { // c'est pas tres joli...

                $value2 = jeedom::evaluateExpression($trigger2['cmd']); // la valeur courante de cette commande
                $check *= $this->evaluateConditions($trigger2, $value2); // on évalue sa condition et si 1 seul retour 0, $check passera a 0
                log::add('sequencing', 'debug', $this->getHumanName() . ' - Résultat total après évaluation de : ' . $trigger2['name'] . ' : ' . $check);
              }

            } else {
              $check = $this->evaluateConditions($trigger, $value);
            }

            if ($check == 1 || $check || $check == '1') {

              log::add('sequencing', 'info', $this->getHumanName() . ' => Detection ' . $_type . ' valide <= nom : ' . $trigger['name'] . ' - cmd : ' . $trigger['cmd'] . ' - valeur : ' . $value);

              $this->setCache('trigger_name', $trigger['name']);
              $this->setCache('trigger_value', $value);
              $this->setCache('trigger_datetime', date('Y-m-d H:i:s'));
              $this->setCache('trigger_time', date('H:i:s'));

              if($_type == 'trigger') {
                $this->actionsLaunch();
              } else if ($_type == 'trigger_cancel'){
                $this->actionsCancel();
              }

            } else {
              log::add('sequencing', 'debug', $this->getHumanName() . ' - Ce ou ces trigger(s) ne valide(nt) pas les conditions voulues => on fait rien');
            }

          } else {
            log::add('sequencing', 'debug', $this->getHumanName() . ' - Ce trigger est une répétition et on a configuré qu\'on en voulait pas => on fait rien');
          }

          $this->setCache('trigger_' . $_type . $trigger['name'], $value); // on garde la valeur en cache pour gestion de la repetition

        }
      }

    }

    public function evaluateConditions($trigger, $value) {

      if(!is_numeric($value)){
        log::add('sequencing', 'debug', $this->getHumanName() . ' Notre valeur à évaluer n\'est pas numerique : ' . $value);
        $value = '"'.$value.'"'; // parfois ca marche sans, parfois ca marche pas... mais ca marche a tous les coup avec !
      }

      if($trigger['condition_operator'] != ''){ // on a 2 conditions
        log::add('sequencing', 'debug', $this->getHumanName() . ' Expression à évaluer (valeur et conditions) : ' . $value . $trigger['condition_operator1'] . $trigger['condition_test1'] . $trigger['condition_operator'] . $value . $trigger['condition_operator2'] . $trigger['condition_test2']);
        $check = jeedom::evaluateExpression($value . $trigger['condition_operator1'] . $trigger['condition_test1'] . $trigger['condition_operator'] . $value . $trigger['condition_operator2'] . $trigger['condition_test2']);
      } else if($trigger['condition_operator1'] != ''){ // une seule condition

        log::add('sequencing', 'debug', $this->getHumanName() . ' Expression à évaluer (valeur et conditions) : ' . $value . $trigger['condition_operator1'] . $trigger['condition_test1']);

        $check = jeedom::evaluateExpression($value . $trigger['condition_operator1'] . $trigger['condition_test1']);

    //    $check2 = evaluate($value . $trigger['condition_operator1'] . $trigger['condition_test1']);
    //    log::add('sequencing', 'debug', $this->getHumanName() . ' resultat 1 et 2 : ' . $check . ' - ' . $check2);

      } else { // sinon on a pas de condition : tout est valide
        $check = 1;
      }

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Résultat evaluateConditions pour : ' . $trigger['name'] . ' : ' . $check);

      return $check;

    }

    public function execAction($action) { // execution d'une seule action

      log::add('sequencing', 'debug', $this->getHumanName() . '################ Execution de l\' action ' . $action['action_label'] . ' ############');

      try {
        $options = array(); // va permettre d'appeler les options de configuration des actions, par exemple un scenario ou les textes pour un message
        if (isset($action['options'])) {
          $options = $action['options'];
          foreach ($options as $key => $value) { // ici on peut définir les "tag" de configuration qui seront à remplacer par des variables
            // str_replace ($search, $replace, $subject) retourne une chaîne ou un tableau, dont toutes les occurrences de search dans subject ont été remplacées par replace.
            $value = str_replace('#tag1#', $this->getConfiguration('tag1'), $value);
            $value = str_replace('#tag2#', $this->getConfiguration('tag2'), $value);
            $value = str_replace('#tag3#', $this->getConfiguration('tag3'), $value);

            $value = str_replace('#action_label#', $action['action_label'], $value);
            $value = str_replace('#action_timer#', $action['action_timer'], $value);
            $value = str_replace('#action_label_liee#', $action['action_label_liee'], $value);

            $value = str_replace('#trigger_name#', $this->getCache('trigger_name'), $value);
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
          $this->setCache('execAction_'.$action['action_label'], 1);
      //    log::add('sequencing', 'debug', 'setCache TRUE pour label : ' . $action['action_label']);
        }

      } catch (Exception $e) {
        log::add('sequencing', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());

      }

    }


    public function actionsLaunch() { // fct appelée par la cmd 'start' appelée par l'extérieur ou par un trigger valide (via fonction triggerLaunch) ou via le cron de programmation

      log::add('sequencing', 'debug', $this->getHumanName() . '################ Evaluation timers et lancement des actions ############');

      foreach ($this->getConfiguration('action') as $action) { // pour toutes les actions définies

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Config Action - action_label : ' . $action['action_label'] . ' - action_timer : ' . $action['action_timer'] . ' - reporter : ' . $action['reporter']);

        if(is_numeric($action['action_timer']) && $action['action_timer'] > 0){ // si on a un timer bien defini et > 0 min, on va lancer un cron pour l'execution retardée de l'action

          $this->setCronDelay($action);

        }else{ // pas de timer valide defini, on execute l'action immédiatement

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Pas de timer lié, on execute ' . $action['cmd']);

          $this->execAction($action);

        }

      } // fin foreach toutes les actions

    }


    public function actionsCancel() { // fct appelée par la cmd 'stop' appelée par l'extérieur ou par un trigger_cancel valide (via fonction triggerCancel)

      log::add('sequencing', 'debug', $this->getHumanName() . '################ Exécution des actions d\'annulation ############');

      foreach ($this->getConfiguration('action_cancel') as $action) { // pour toutes les actions d'annulation définies

        $execActionLiee = $this->getCache('execAction_'.$action['action_label_liee']); // on va lire le cache d'execution de l'action liée, savoir si deja lancé ou non...

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Config Action Annulation, action : '. $action['cmd'] .', label action liée : ' . $action['action_label_liee'] . ' - action liée deja executée : ' . $execActionLiee);

        if($action['action_label_liee'] == ''){ // si pas d'action liée, on execute direct

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Pas d\'action liée, on execute ' . $action['cmd']);

          $this->execAction($action);

        }else if(isset($action['action_label_liee']) && $action['action_label_liee'] != '' && $execActionLiee == 1){ // si on a une action liée définie et qu'elle a été executée => on execute notre action et on remet le cache de l'action liée à 0

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Action liée ('.$action['action_label_liee'].') executée précédemment, donc on execute ' . $action['cmd'] . ' et remise à 0 du cache d\'exec de l\'action origine');

          $this->execAction($action);

          $this->setCache('execAction_'.$action['action_label_liee'], 0);

        }else{ // sinon, on log qu'on n'execute pas l'action et la raison
          log::add('sequencing', 'debug', $this->getHumanName() . ' - Action liée ('.$action['action_label_liee'].') non executée précédemment, donc on execute pas ' . $action['cmd']);
        }

      } // fin foreach toutes les actions

      //coupe les CRON des actions d'alertes non encore appelés
      $this->cleanAllDelayedActionsCron();

    }

    public function setCronDelay($action) {

      $cron = cron::byClassAndFunction('sequencing', 'actionDelayed', array('eqLogic_id' => intval($this->getId()), 'action' => $action)); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (eqLogic, action (qui contient cmd, option (les titres et messages notamment) et label))" Si on change le label ou le message, c'est plus le meme "action" et donc cette fonction ne le trouve pas et un nouveau cron sera crée !
      // lors d'une sauvegarde ou suppression de l'eqLogic, si des crons sont existants, ils seront supprimés avec un message d'alerte

      if (!is_object($cron)) { // pas de cron trouvé, on le cree

          $cron = new cron();
          $cron->setClass('sequencing');
          $cron->setFunction('actionDelayed');

          $options['eqLogic_id'] = intval($this->getId());
          $options['action'] = $action; //inclu tout le detail de l'action : sa cmd, ses options pour les messages, son label, ...

          $cron->setOption($options);

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Set CRON : ' . $options['eqLogic_id'] . ' - ' . $options['action']['cmd'] . ' - ' . $options['action']['action_label']);

          $cron->setEnable(1);
          $cron->setTimeout(5); //minutes

          $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$action['action_timer'].' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'action_timer' min
          $cron->setSchedule(cron::convertDateToCron($delai));

          $cron->setOnce(1); //permet qu'il s'auto supprime une fois executé
          $cron->save();

      } else if($action['reporter']) { // si on a bien trouvé notre cron mais on a un new trigger et on a choisi dans ce cas de reporter l'actions

        $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$action['action_timer'].' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'action_timer' min
        $cron->setSchedule(cron::convertDateToCron($delai));

        $cron->save();

      } else { // sinon : le cron existe mais on veut pas le reporter : on fait rien !

        log::add('sequencing', 'debug', $this->getHumanName() . ' - CRON existe déjà et on veut pas le reporter pour : ' . $cron->getOption()['eqLogic_id'] . ' - ' . $cron->getOption()['action']['cmd'] . ' - ' . $cron->getOption()['action']['action_label'] . ' => on ne fait rien !');
      }

    }

    public function cleanAllDelayedActionsCron($displayWarningMessage = false) {

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Fct cleanAllDelayedActionsCron');

      $crons = cron::searchClassAndFunction('sequencing','actionDelayed'); // on prend tous nos crons de ce plugin, cette fonction, pour tous les equipements
      if (is_array($crons) && count($crons) > 0) {
        foreach ($crons as $cron) {
          if (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // si l'id correspond et qu'il est pas en cours, on le vire

            log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron trouvé à supprimer pour eqLogic_id : ' . $cron->getOption()['eqLogic_id'] . ' - cmd : ' . ' - ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);

            if($displayWarningMessage){

              log::add('sequencing', 'error', $this->getHumanName() . ' - Attention, des actions avec un délai avant exécution étaient en cours et vont être supprimées, action supprimée : ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);
            }

            $cron->remove();

          }
        }
      }


/*      $cron = cron::byClassAndFunction('sequencing', 'actionDelayed'); //on cherche le 1er cron pour ce plugin et cette action (il n'existe pas de fonction core renvoyant un array avec tous les cron de la class, comme pour les listeners... dommage...) => en fait si : searchClassAndFunction, voir new fct ci-dessus

      while (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // s'il existe et que l'id correspond et qu'il est pas en cours, on le vire puis on cherche le suivant et tant qu'il y a un suivant on boucle

        log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron trouvé à supprimer pour eqLogic_id : ' . $cron->getOption()['eqLogic_id'] . ' - cmd : ' . ' - ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);

        if($displayWarningMessage){

          log::add('sequencing', 'error', $this->getHumanName() . ' - Attention, des actions avec un délai avant exécution étaient en cours et vont être supprimées, action supprimée : ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);
        }

        $cron->remove();
        $cron = cron::byClassAndFunction('sequencing', 'actionDelayed'); // on cherche le suivant
      }*/

    }

    public function cleanProgCron() {

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Fct cleanProgCron');

      $crons = cron::searchClassAndFunction('sequencing','startProgrammed'); // on prend tous nos crons de ce plugin, cette fonction, pour tous les equipements
      if (is_array($crons) && count($crons) > 0) {
        foreach ($crons as $cron) {
          if (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId() && $cron->getState() != 'run') { // si l'id correspond et qu'il est pas en cours, on le vire
            log::add('sequencing', 'debug', $this->getHumanName() . ' - Cron de programmation trouvé à supprimer');
            $cron->remove();
          }
        }
      }
    }

    public function cleanAllListener() {

      log::add('sequencing', 'debug', $this->getHumanName() . ' - Fct cleanAllListener');

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

    // Méthode appellée après la création de votre objet --> on va créer les cmd de déclenchement et annulation
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

    public function preSave() {
      //supprime les CRON des actions d'alertes non encore appelés, affiche une alerte s'il y en avait
      //sert à ne pas laisser trainer des CRONs en cours si on change le message ou le label puis en enregistre. Cas exceptionnel, mais au cas où...

      $this->cleanAllDelayedActionsCron(true); // clean tous les cron des actions

    }

    // fct appellée par Jeedom aprés l'enregistrement de la configuration
    public function postSave() {

      //########## 1 - On va lire la configuration des capteurs dans le JS et on la stocke dans un tableau #########//

      $jsSensors = array(
        'trigger' => array(), // sous-tableau pour stocker les triggers
        'trigger_cancel' => array(), // idem trigger_cancel
      );

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de capteurs pour recuperer les infos
        log::add('sequencing', 'debug', $this->getHumanName() . ' - Boucle de $jsSensors : key : ' . $key);

        if (is_array($this->getConfiguration($key))) {
          foreach ($this->getConfiguration($key) as $sensor) {
            if ($sensor['name'] != '' && $sensor['cmd'] != '') { // si le nom et la cmd sont remplis

              $jsSensors[$key][$sensor['name']] = $sensor; // on stocke toute la conf, c'est à dire tout ce qui dans notre js avait la class "expressionAttr". Pour retrouver notre champs exact : $jsSensors[$key][$sensor['name']][data-l1key]. // attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut écrire, pas dans la variable qui le represente dans cette boucle
              log::add('sequencing', 'debug', $this->getHumanName() . ' - Capteurs sensor config lue : ' . $sensor['name'] . ' - ' . $sensor['cmd']);

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

          log::add('sequencing', 'debug', $this->getHumanName() . ' - New Capteurs config : type : ' . $key . ', sensor name : ' . $sensor['name'] . ', sensor cmd : ' . $sensor['cmd']);

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

        } //*/ // fin foreach restant. A partir de maintenant on a des triggers qui refletent notre config lue en JS
      }

      //########## 4 - Mise en place des listeners de capteurs pour réagir aux events et du cron de start si besoin #########//

      if ($this->getIsEnable() == 1) { // si notre eq est actif, on va lui definir nos listeners de capteurs

        //########## D'abord les listeners
        // un peu de menage dans nos events avant de remettre tout ca en ligne avec la conf actuelle
        $this->cleanAllListener();

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
            $listener->setFunction($listenerFunction); // la fct qui sera appellée a chaque evenement sur une des sources écoutée
            $listener->setOption(array('sequencing_id' => intval($this->getId())));
          }
          $listener->addEvent($cmd->getValue()); // on ajoute les event à écouter de chacun des capteurs definis. On cherchera le trigger a l'appel de la fonction si besoin

          log::add('sequencing', 'debug', $this->getHumanName() . ' - sensor listener set - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

          $listener->save();

        } // fin foreach cmd du plugin

        //########## Puis le cron de programmation start
        $cron = cron::byClassAndFunction('sequencing', 'startProgrammed', array('eqLogic_id' => intval($this->getId()))); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (eqLogic)

        $prog = $this->getConfiguration('programmation');

        if (!is_object($cron) && $prog != '') { // pas de cron trouvé et on en veut 1, on le cree

            $cron = new cron();
            $cron->setClass('sequencing');
            $cron->setFunction('startProgrammed');

            $options['eqLogic_id'] = intval($this->getId());
            $cron->setOption($options);

            $cron->setEnable(1);
            $cron->setTimeout(5); //minutes
            $cron->setSchedule(checkAndFixCron($prog));

            $cron->setLastRun(date('Y-m-d H:i:s'));

            $cron->save();

            log::add('sequencing', 'debug', $this->getHumanName() . ' - Set CRON start programmation : ' . $prog . ' lastrun : ' . $cron->getLastRun());

        } else if(is_object($cron) && $prog != '') { // si cron existant et programmation non vide, on le met à jour

          $cron->setSchedule(checkAndFixCron($prog));

          $cron->save();

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Update CRON start programmation : ' . $prog . ' lastrun : ' . $cron->getLastRun());

        } else if (is_object($cron) && $prog == '') { // le cron existe mais on veut plus de programmation : on va le virer

          $cron->remove();

          log::add('sequencing', 'debug', $this->getHumanName() . ' - Suppression du CRON start programmation');
        } else {
          log::add('sequencing', 'debug', $this->getHumanName() . ' - CRON start programmation existe pas, et on a pas de programmation => on fait rien');
        }

      } // fin if eq actif
      else { // notre eq n'est pas actif ou il a ete desactivé, on supprime les listeners et le cron de programmation s'ils existaient

        $this->cleanAllListener();

        $this->cleanProgCron();

      }

      log::add('sequencing', 'info', $this->getHumanName() . ' - Fin sauvegarde');

    } // fin fct postSave

    // preUpdate ⇒ Méthode appellée avant la mise à jour de votre objet
    // ici on vérifie la présence de nos champs de config obligatoire
    public function preUpdate() {

      $triggersType = array( // liste des types avec des champs a vérifier
        'trigger',
        'trigger_cancel',
      );

      foreach ($triggersType as $type) {
        if (is_array($this->getConfiguration($type))) {
          foreach ($this->getConfiguration($type) as $trigger) { // pour tous les capteurs de tous les types, on veut un nom et une cmd
            if ($trigger['name'] == '') {
              throw new Exception(__('Le champs Nom pour les capteurs ('.$type.') ne peut être vide',__FILE__));
            }

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

          }
        }
      }
    }

    public function postUpdate() {

    }

    public function preRemove() { //quand on supprime notre eqLogic

      // on vire nos listeners associés
      $this->cleanAllListener();

      //supprime les CRON des actions d'alertes non encore appelés, affiche une alerte s'il y en avait
      //sert à ne pas laisser trainer des CRONs en cours si on change le message ou le label puis en enregistre. Mais ne devrait arriver qu'exceptionnellement
      $this->cleanAllDelayedActionsCron(true);

      // on vire notre programmation
      $this->cleanProgCron();

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
        $eqLogic->setCache('trigger_value', '');
        $eqLogic->setCache('trigger_datetime', date('Y-m-d H:i:s'));
        $eqLogic->setCache('trigger_time', date('H:i:s'));

        $eqLogic->actionsLaunch();

      } else if ($this->getLogicalId() == 'stop') {
        $eqLogic = $this->getEqLogic();

        log::add('sequencing', 'debug', $this->getHumanName() . 'Appel stop');

        $eqLogic->setCache('trigger_name', 'user/api');
        $eqLogic->setCache('trigger_value', '');
        $eqLogic->setCache('trigger_datetime', date('Y-m-d H:i:s'));
        $eqLogic->setCache('trigger_time', date('H:i:s'));

        $eqLogic->actionsCancel();

      } else { // sinon c'est un sensor et on veut juste sa valeur

        log::add('sequencing', 'debug', $this->getHumanName() . '-> ' . jeedom::evaluateExpression($this->getValue()));
        return jeedom::evaluateExpression($this->getValue());

        //pour la gestion des variables
        //log::add('sequencing', 'debug', $this->getHumanName() . '-> ' . str_replace('#', '', jeedom::evaluateExpression($this->getValue())));
        //return str_replace('#', '', jeedom::evaluateExpression($this->getValue())); // s'il y a encore des '#' apres évaluation(cas d'une variable), on les vire et on prend que le resultat
      }

    }

    /*     * **********************Getteur Setteur*************************** */
}


