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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class sequencing extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    public static function actionDelayed($_options) { // fonction appelée par les cron qui servent a reporter l'execution des actions d'alerte.
    // Dans les options on trouve le eqLogic_id et 'action' qui lui meme contient tout ce qu'il faut pour executer l'action reportée, incluant le titre et message pour les messages

      log::add('sequencing', 'debug', 'Fct actionDelayed Appellée par le CRON - eqLogic_id : ' . $_options['eqLogic_id'] . ' - cmd : ' . $_options['action']['cmd'] . ' - action_label : ' . $_options['action']['action_label']);

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      $sequencing->execAction($_options['action']);

    }

/*    public static function actionRepeat($_options) { // fonction appelée par les cron qui servent a reporter l'execution des actions d'alerte.
    // Dans les options on trouve le eqLogic_id et 'action' qui lui meme contient tout ce qu'il faut pour executer l'action reportée, incluant le titre et message pour les messages

      log::add('sequencing', 'debug', 'Fct actionRepeat Appellée par le CRON - eqLogic_id : ' . $_options['eqLogic_id'] . ' - cmd : ' . $_options['action']['cmd'] . ' - action_label : ' . $_options['action']['action_label']);

      $sequencing = sequencing::byId($_options['eqLogic_id']);

      $sequencing->execAction($_options['action']);
  //    $sequencing->setRepeatCron($_options['action']);

    }*/

    public static function triggerLaunch($_option) { // fct appelée par le listener des triggers (mais pas par la cmd start qui elle, va bypasser l'évaluation des conditions !)
    // dans _option on a toutes les infos du trigger (from les champs du JS)

    //  log::add('sequencing', 'debug', '################ Trigger déclenché, on va évaluer les conditions ############');

      $sequencing = sequencing::byId($_option['sequencing_id']); // on cherche l'équipement correspondant au trigger

      $sequencing->evaluateTrigger($_option, 'trigger');

    }

    public static function triggerCancel($_option) { // fct appelée par le listener des triggers d'annulation (mais pas par la cmd stop !)

      log::add('sequencing', 'debug', '################ Trigger d\'annulation déclenché, on va évaluer les conditions ############');

      $sequencing = sequencing::byId($_option['sequencing_id']); // on cherche la personne correspondant au bouton

      $sequencing->evaluateTrigger($_option, 'trigger_cancel');

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

    public function evaluateTrigger($_option, $_type) {

      // on cherche quel est l'event qui nous a déclenché pour pouvoir chopper toutes ses infos et évaluer les conditions
      foreach ($this->getConfiguration($_type) as $trigger) { // on boucle direct dans tous les trigger ou trigger_cancel de la conf
        if ('#' . $_option['event_id'] . '#' == $trigger['cmd']) { //TODO : gérer les variables ?

          log::add('sequencing', 'debug', $this->getHumanName() . ' => Detection d\'un trigger <= nom : ' . $trigger['name'] . ' - cmd : ' . $trigger['cmd']  . ' - Filtrer répétitions : ' . $trigger['new_value_only']);

          if (!$trigger['new_value_only'] || $trigger['new_value_only'] && $this->getCache('trigger_' . $_type . $_option['event_id']) != $_option['value']){ // si on veut tous les triggers ou uniquement new_value et que notre valeur a changé => on évalue le reste des conditions

            if($trigger['condition_operator'] != ''){ // on a 2 conditions
              log::add('sequencing', 'debug', $this->getHumanName() . ' Expression à évaluer (valeur et conditions) : ' . $_option['value'] . $trigger['condition_operator1'] . $trigger['condition_test1'] . $trigger['condition_operator'] . $_option['value'] . $trigger['condition_operator2'] . $trigger['condition_test2']);
              $check = jeedom::evaluateExpression($_option['value'] . $trigger['condition_operator1'] . $trigger['condition_test1'] . $trigger['condition_operator'] . $_option['value'] . $trigger['condition_operator2'] . $trigger['condition_test2']);
            } else if($trigger['condition_operator1'] != ''){
              log::add('sequencing', 'debug', $this->getHumanName() . ' Expression à évaluer (valeur et conditions) : ' . $_option['value'] . $trigger['condition_operator1'] . $trigger['condition_test1']);
              $check = jeedom::evaluateExpression($_option['value'] . $trigger['condition_operator1'] . $trigger['condition_test1']);
            } else {
              $check = 1; // sinon on a pas de condition : tout est valide
            }

            log::add('sequencing', 'debug', 'Résultat évaluation : ' . $check);

            if ($check == 1 || $check || $check == '1') {

              if($_type == 'trigger') {
                $this->actionsLaunch();
              } else if ($_type == 'trigger_cancel'){
                $this->actionsCancel();
              }

            } else {
              log::add('sequencing', 'debug', 'Ce trigger ne valide pas les conditions d\'évaluation => on fait rien');
            }

          } else {
            log::add('sequencing', 'debug', 'Ce trigger est une répétition et on a configuré qu\'on en voulait pas => on fait rien');
          }

          $this->setCache('trigger_' . $_type . $_option['event_id'], $_option['value']); // on garde la valeur en cache pour gestion de la repetition

        }
      }

    }


    public function execAction($action) { // execution d'une seule action avec ses infos de triggers pour les tags

      log::add('sequencing', 'debug', '################ Execution de l\' actions ' . $_config . ' pour ' . $this->getName() .  ' ############');

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

/*            $value = str_replace('#trigger_name#', $trigger_name, $value);
            $value = str_replace('#trigger_value#', $trigger_value, $value); // attention, c'est un tag scenario existant*/

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


    public function actionsLaunch() { // fct appelée par la cmd 'start' appelée par l'extérieur ou par un trigger valide (via fonction triggerLaunch)

      log::add('sequencing', 'debug', '################ Exécution des actions ############');

      foreach ($this->getConfiguration('action') as $action) { // pour toutes les actions définies

        log::add('sequencing', 'debug', 'Config Action - action_label : ' . $action['action_label'] . ' - action_timer : ' . $action['action_timer'] . ' - action_repetition : ' . $action['action_repetition']);

        if(is_numeric($action['action_timer']) && $action['action_timer'] > 0){ // si on a un timer bien defini et > 0 min, on va lancer un cron pour l'execution retardée de l'action
          // si le CRON existe deja, on ne l'update pas pour ne pas retarder l'alerte en cas de multi appui d'alerte. Et on veut pas non plus setter plusieurs CRON pour la meme action..
          //TODO : laisser le choix de retarder ou non dans la conf en cas de multi declenchement ?

          $cron = cron::byClassAndFunction('sequencing', 'actionDelayed', array('eqLogic_id' => intval($this->getId()), 'action' => $action)); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (personne, action (qui contient cmd, option (les titres et messages notamment) et label))" Si on change le label ou le message, c'est plus le meme "action" et donc cette fonction ne le trouve pas et un nouveau cron sera crée !
          // lors d'une sauvegarde ou suppression de l'eqLogic, si des crons sont existants, ils seront supprimés avec un message d'alerte

          if (!is_object($cron)) { // pas de cron trouvé, on le cree

              $cron = new cron();
              $cron->setClass('sequencing');
              $cron->setFunction('actionDelayed');

              $options['eqLogic_id'] = intval($this->getId());
              $options['action'] = $action; //inclu tout le detail de l'action : sa cmd, ses options pour les messages, son label, ...
          //    $options['trigger_name'] = $_trigger_name;
          //    $options['trigger_value'] = $_trigger_value;
              $cron->setOption($options);

              log::add('sequencing', 'debug', 'Set CRON : ' . $options['eqLogic_id'] . ' - ' . $options['action']['cmd'] . ' - ' . $options['action']['action_label']);

              $cron->setEnable(1);
              $cron->setTimeout(5); //minutes

              $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$action['action_timer'].' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'action_timer' min
              $cron->setSchedule(cron::convertDateToCron($delai));

              $cron->setOnce(1); //permet qu'il s'auto supprime une fois executé
              $cron->save();

          } else {

            log::add('sequencing', 'debug', 'CRON existe déjà pour : ' . $cron->getOption()['eqLogic_id'] . ' - ' . $cron->getOption()['action']['cmd'] . ' - ' . $cron->getOption()['action']['action_label'] . ' => on ne fait rien !');
          }

        }else{ // pas de timer valide defini, on execute l'action immédiatement

          log::add('sequencing', 'debug', 'Pas de timer liée, on execute ' . $action['cmd']);

          $this->execAction($action);
    //      $this->setRepeatCron($action);

        }

      } // fin foreach toutes les actions

    }

/*    public function setRepeatCron($action) {

      if($action['action_repetition'] != ''){

        log::add('sequencing', 'debug', 'On veut une repetition de ' . $action['action_repetition'] . ' min');

        $cron = cron::byClassAndFunction('sequencing', 'actionRepeat', array('eqLogic_id' => intval($this->getId()), 'action' => $action)); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (personne, action (qui contient cmd, option (les titres et messages notamment) et label))" Si on change le label ou le message, c'est plus le meme "action" et donc cette fonction ne le trouve pas et un nouveau cron sera crée !
        // lors d'une sauvegarde ou suppression de l'eqLogic, si des crons sont existants, ils seront supprimés avec un message d'alerte

        if (is_object($cron)) { // pas de cron trouvé, on le cree
          log::add('sequencing', 'debug', 'CRON repetition existe déjà pour : ' . $cron->getFunction() . ' - ' . $cron->getOption()['action']['cmd'] . ' - ' . $cron->getOption()['action']['action_label'] . ' => on le vire !');
          $cron->remove();
        }

        $cron = new cron();
        $cron->setClass('sequencing');
        $cron->setFunction('actionRepeat');

        $options['eqLogic_id'] = intval($this->getId());
        $options['action'] = $action; //inclu tout le detail de l'action : sa cmd, ses options pour les messages, son label, ...

        $cron->setOption($options);

        log::add('sequencing', 'debug', 'Set CRON repetition : ' . $options['eqLogic_id'] . ' - ' . $options['action']['cmd'] . ' - ' . $options['action']['action_label']);

        $cron->setEnable(1);
        $cron->setTimeout(5); //minutes

        $delai = strtotime(date('Y-m-d H:i:s', strtotime('+'.$action['action_repetition'].' min ' . date('Y-m-d H:i:s')))); // on lui dit de se déclencher dans 'action_timer' min
        $cron->setSchedule(cron::convertDateToCron($delai));

        $cron->setOnce(1); //permet qu'il s'auto supprime une fois executé
        $cron->save();


      }

      if($action['repeat_action_cron'] != ''){

        log::add('sequencing', 'debug', 'On veut une repetition de ' . $action['repeat_action_cron']);

        $cron = cron::byClassAndFunction('sequencing', 'actionRepeat', array('eqLogic_id' => intval($this->getId()), 'action' => $action)); // cherche le cron qui correspond exactement à "ce plugin, cette fonction et ces options (personne, action (qui contient cmd, option (les titres et messages notamment) et label))" Si on change le label ou le message, c'est plus le meme "action" et donc cette fonction ne le trouve pas et un nouveau cron sera crée !
        // lors d'une sauvegarde ou suppression de l'eqLogic, si des crons sont existants, ils seront supprimés avec un message d'alerte

        if (!is_object($cron)) { // pas de cron trouvé, on le cree

            $cron = new cron();
            $cron->setClass('sequencing');
            $cron->setFunction('actionRepeat');

            $options['eqLogic_id'] = intval($this->getId());
            $options['action'] = $action; //inclu tout le detail de l'action : sa cmd, ses options pour les messages, son label, ...

            $cron->setOption($options);

            log::add('sequencing', 'debug', 'Set CRON repetition : ' . $options['eqLogic_id'] . ' - ' . $options['action']['cmd'] . ' - ' . $options['action']['action_label']);

            $cron->setEnable(1);
            $cron->setTimeout(5); //minutes
        //    $cron->setLastRun(date('Y-m-d H:i:s', strtotime('now')));
        //    $cron->setCache('lastRun', date('Y-m-d H:i:s'));
            $cron->setSchedule($action['repeat_action_cron']);

        //    $cron->setLastRun(date('Y-m-d H:i:s'));
            $cron->setLastRun('2020-04-15 20:09:50');
            $cron->save();

        } else {

          log::add('sequencing', 'debug', 'CRON repetition existe déjà pour : ' . $cron->getFunction() . ' - ' . $cron->getOption()['action']['cmd'] . ' - ' . $cron->getOption()['action']['action_label'] . ' => on ne fait rien !');
        }

      }
    }*/

    public function actionsCancel() { // fct appelée par la cmd 'stop' appelée par l'extérieur ou par un trigger_cancel valide (via fonction triggerCancel)

      log::add('sequencing', 'debug', '################ Exécution des actions d\'annulation ############');

      foreach ($this->getConfiguration('action_cancel') as $action) { // pour toutes les actions d'annulation définies

        $execActionLiee = $this->getCache('execAction_'.$action['action_label_liee']); // on va lire le cache d'execution de l'action liée, savoir si deja lancé ou non...

        log::add('sequencing', 'debug', 'Config Action Annulation, action : '. $action['cmd'] .', label action liée : ' . $action['action_label_liee'] . ' - action liée deja executée : ' . $execActionLiee);

        if($action['action_label_liee'] == ''){ // si pas d'action liée, on execute direct

          log::add('sequencing', 'debug', 'Pas d\'action liée, on execute ' . $action['cmd']);

          $this->execAction($action);

        }else if(isset($action['action_label_liee']) && $action['action_label_liee'] != '' && $execActionLiee == 1){ // si on a une action liée définie et qu'elle a été executée => on execute notre action et on remet le cache de l'action liée à 0

          log::add('sequencing', 'debug', 'Action liée ('.$action['action_label_liee'].') executée précédemment, donc on execute ' . $action['cmd'] . ' et remise à 0 du cache d\'exec de l\'action origine');

          $this->execAction($action);

          $this->setCache('execAction_'.$action['action_label_liee'], 0);

        }else{ // sinon, on log qu'on n'execute pas l'action et la raison
          log::add('sequencing', 'debug', 'Action liée ('.$action['action_label_liee'].') non executée précédemment, donc on execute pas ' . $action['cmd']);
        }

      } // fin foreach toutes les actions

      //coupe les CRON des actions d'alertes non encore appelés
      $this->cleanAllCron();

    }

    public function cleanAllCron($displayWarningMessage = false) {

      log::add('sequencing', 'debug', 'Fct cleanAllCron pour : ' . $this->getName());

      $cron = cron::byClassAndFunction('sequencing', 'actionDelayed'); //on cherche le 1er cron pour ce plugin et cette action (il n'existe pas de fonction core renvoyant un array avec tous les cron de la class, comme pour les listeners... dommage...)

      while (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId()) { // s'il existe et que l'id correspond, on le vire puis on cherche le suivant et tant qu'il y a un suivant on boucle

        log::add('sequencing', 'debug', 'Cron trouvé à supprimer pour eqLogic_id : ' . $cron->getOption()['eqLogic_id'] . ' - cmd : ' . ' - ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);

        if($displayWarningMessage){

          log::add('sequencing', 'error', 'Attention, des actions d\'alerte avec un délai avant exécution sont en cours et vont être supprimées, merci de vous assurer que la personne associée n\'a pas besoin d\'assistance ! Il s\'agit de ' . $this->getConfiguration('senior_name') . ' - pour l\'eqLogic ' . $this->getName() . ', action supprimée : ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);
        }

        $cron->remove();
        $cron = cron::byClassAndFunction('sequencing', 'actionDelayed');
      }


/*      $cron = cron::byClassAndFunction('sequencing', 'actionRepeat'); //on cherche le 1er cron pour ce plugin et cette action (il n'existe pas de fonction core renvoyant un array avec tous les cron de la class, comme pour les listeners... dommage...)

      while (is_object($cron) && $cron->getOption()['eqLogic_id'] == $this->getId()) { // s'il existe et que l'id correspond, on le vire puis on cherche le suivant et tant qu'il y a un suivant on boucle

        log::add('sequencing', 'debug', 'Cron trouvé à supprimer pour eqLogic_id : ' . $cron->getOption()['eqLogic_id'] . ' - cmd : ' . ' - ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);

        if($displayWarningMessage){

          log::add('sequencing', 'error', 'Attention, des actions d\'alerte avec un délai avant exécution sont en cours et vont être supprimées, merci de vous assurer que la personne associée n\'a pas besoin d\'assistance ! Il s\'agit de ' . $this->getConfiguration('senior_name') . ' - pour l\'eqLogic ' . $this->getName() . ', action supprimée : ' . $cron->getOption()['action']['cmd'] . ' - action_label : ' . $cron->getOption()['action']['action_label']);
        }

        $cron->remove();
        $cron = cron::byClassAndFunction('sequencing', 'actionRepeat');
      }*/


    }

    public function cleanAllListener() {

      log::add('sequencing', 'debug', 'Fct cleanAllListener pour : ' . $this->getName());

      $listeners = listener::byClass('sequencing'); // on prend tous nos listeners de ce plugin, pour tous les equipements
      foreach ($listeners as $listener) {
        $sequencing_id_listener = $listener->getOption()['sequencing_id'];

    //    log::add('sequencing', 'debug', 'cleanAllListener id lue : ' . $sequencing_id_listener . ' et nous on est l id : ' . $this->getId());

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

  //    $calculstarttime = date('H:i:s');

      $this->cleanAllCron(true);

  //    log::add('sequencing', 'debug', 'cleanAllCron start à ' . $calculstarttime . ' fin à : ' . date('H:i:s'));


    }

    // fct appellée par Jeedom aprés l'enregistrement de la configuration
    public function postSave() {

      //########## 1 - On va lire la configuration des capteurs dans le JS et on la stocke dans un tableau #########//

      $jsSensors = array(
        'trigger' => array(), // sous-tableau pour stocker toutes les triggers
        'trigger_cancel' => array(), // idem trigger_cancel
      );

      foreach ($jsSensors as $key => $jsSensor) { // on boucle dans tous nos types de capteurs pour recuperer les infos
        log::add('sequencing', 'debug', 'Boucle de $jsSensors : key : ' . $key);

        if (is_array($this->getConfiguration($key))) {
          foreach ($this->getConfiguration($key) as $sensor) {
            if ($sensor['name'] != '' && $sensor['cmd'] != '') { // si le nom et la cmd sont remplis

              $jsSensors[$key][$sensor['name']] = $sensor; // on stocke toute la conf, c'est à dire tout ce qui dans notre js avait la class "expressionAttr". Pour retrouver notre champs exact : $jsSensors[$key][$sensor['name']][data-l1key]. // attention ici a ne pas remplacer $jsSensors[$key] par $jsSensor. C'est bien dans le tableau d'origine qu'on veut écrire, pas dans la variable qui le represente dans cette boucle
              log::add('sequencing', 'debug', 'Capteurs sensor config lue : ' . $sensor['name'] . ' - ' . $sensor['cmd']);

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
              if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
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

          // ce qui identifie d'un point de vu unique notre capteur c'est son type et sa value(cmd)

          log::add('sequencing', 'debug', 'New Capteurs config : type : ' . $key . ', sensor name : ' . $sensor['name'] . ', sensor cmd : ' . $sensor['cmd']);

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
          if (is_nan($cmd->execCmd()) || $cmd->execCmd() == '') {
            $cmd->setCollectDate('');
            $cmd->event($cmd->execute());
          }

        } //*/ // fin foreach restant. A partir de maintenant on a des triggers qui refletent notre config lue en JS
      }

      //########## 4 - Mise en place des listeners de capteurs pour réagir aux events #########//

      if ($this->getIsEnable() == 1) { // si notre eq est actif, on va lui definir nos listeners de capteurs

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

          log::add('sequencing', 'debug', 'sensor listener set - cmd :' . $cmd->getHumanName() . ' - event : ' . $cmd->getValue());

          $listener->save();

        } // fin foreach cmd du plugin
      } // fin if eq actif
      else { // notre eq n'est pas actif ou il a ete desactivé, on supprime les listeners s'ils existaient

        $this->cleanAllListener();

      }

      //########## 5 - Divers #########//

      // on pouvait pas le faire à la creation de la cmd donc on le fait dans le postSave : on prend l'url et on l'enregistre en configuration
      /* //sauf que le tag marche pas, donc sert à rien, je vire pour l'instant, à voir TODO
      $cmd = $this->getCmd(null, 'alerte_bt_ar');
      if (is_object($cmd)) {
        $cmd->setConfiguration('url_ar', $cmd->getDirectUrlAccess());
        $cmd->save();
      } //*/


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


    //    $trigger['condition_operator1'] . $trigger['condition_test1'] . $trigger['condition_operator'] . $trigger['condition_operator2'] . $trigger['condition_test2']);




    public function postUpdate() {

    }

    public function preRemove() { //quand on supprime notre eqLogic

      // on vire nos listeners associés
      $this->cleanAllListener();

      //supprime les CRON des actions d'alertes non encore appelés, affiche une alerte s'il y en avait
      //sert à ne pas laisser trainer des CRONs en cours si on change le message ou le label puis en enregistre. Mais ne devrait arriver qu'exceptionnellement
      $this->cleanAllCron(true);

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
       // log::add('sequencing', 'debug', 'Appel start');
        $eqLogic = $this->getEqLogic();
        $eqLogic->actionsLaunch();

      } else if ($this->getLogicalId() == 'stop') {
       // log::add('sequencing', 'debug', 'Appel stop');
        $eqLogic = $this->getEqLogic();
        $eqLogic->actionsCancel();

      } else { // sinon c'est un sensor et on veut juste sa valeur

        log::add('sequencing', 'debug', $this->getHumanName() .' fct execute pour : ' . $this->getLogicalId() . ' - valeur renvoyée : ' . jeedom::evaluateExpression($this->getValue()));

        return jeedom::evaluateExpression($this->getValue());
      }

    }

    /*     * **********************Getteur Setteur*************************** */
}


