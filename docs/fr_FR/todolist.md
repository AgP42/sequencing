# Debug :
  - cron qui se lance tout seul lors du set => demandé sur le forum dev

# Amélioration :

    • Priorité 1 (à faire) :
        ◦ DONE Déclenchement si x conditions valides sur N (actuellement soit 1 seule (OU), soit toutes (ET))
        ◦ DONE Déclenchement si répétition de la même valeur (à spécifier par l’utilisateur) au moins x fois en N minutes : permet de filtrer un déclenchement intempestif d’un capteur. Exemple : fermer les volets si plusieurs rafale de vents et pas dès la 1ere
        ◦ DONE Conditionner selon plage horaire (datetime picker de début et fin de période pour commencer, en verra après si début/fin peuvent être des commandes (heure de levé du soleil, …) -> sera utilisé en condition uniquement, en complément d’autres déclencheurs. Ne sera pas un déclencheur en soit.
        ◦ Séquencement des conditions : il faut condition 1 puis condition 2 puis condition 3 pour déclencher
        ◦ DONE Ajouter un déclencheur programmé ou périodique (idem celui existant) pour utilisation comme condition supplémentaire. (Aujourd’hui il bypass toutes les conditions, comme le déclenchement manuel, là il pourrait être utilisé en OU, en ET, …)

    • Priorité 3 (en gros : je le ferais pas sauf si plébiscité sur le forum…) :
        ◦ pouvoir appliquer un délai d’exécution sur une action annulation => NON
        ◦ Ajout des déclencheurs de type « variables » => comment on met un listener sur une variable ???
        ◦ Ajout des conditions d’évaluation Regex (matches)
        ◦ DONE Conditions personnalisée entre les déclencheurs (« cond1 ET (cond1 OU cond2) OU (cond3 XOR cond1) »)
        ◦ Déclenchement sur condition valide plus de xx minutes

Divers :
  * ajouter menu deroulant pour choisir les labels ou un bouton de check
  * revoir tous les tags et notamment les tags triggers à refaire ! (passer les triggers dans les crons en changeant comment chercher les cron par search et non byClassAndFunction...)
  * revoir toute la mise en page bootstrap md et sm
  * tester si la date des crons est pas dans le passé ?
  * si dans la condition personnalisée, on veut une condition fausse (==0) et que c'est elle qui declenche, on va pas plus loin (puisque non validée...)

# Notes
les plugins avec des crons :
- datetime : thermostat, weather, calendar
- périodique : camera, conso, network, Monitoring, speedtest, ecodevice, IPX800, philipsHue

# Overview code :
* triggers :
  * DONE - conditions sur valeurs et répétition de la même valeur au moins x fois en N minutes
  * DONE - programmé (CRON)
  * DONE plage horaire (datetime picker de début et fin de période)
  * (plage temporelle via des commandes (heure de levé du soleil, …) ?)
  * (condition valide plus de x minutes)
* conditions entre triggers :
  * DONE - ET (all conditions valides)
  * DONE - OU (1 condition valide)
  * DONE x conditions valides sur N
  * DONE condition perso
  * séquencement (le bon ordre...)

# A documenter :

* comment passer par un virtuel pour avoir un trigger cmd (à tester avant... éventuellement créér des cmd à la demande ?)
