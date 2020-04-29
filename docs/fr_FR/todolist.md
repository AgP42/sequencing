# Debug :
  - cron qui se lance tout seul lors du set => demandé sur le forum dev

# Amélioration :

    • Priorité 1 (à faire) :
        ◦ Déclenchement si x conditions valides sur N (actuellement soit 1 seule (OU), soit toutes (ET))
        ◦ DONE Déclenchement si répétition de la même valeur (à spécifier par l’utilisateur) au moins x fois en N minutes : permet de filtrer un déclenchement intempestif d’un capteur. Exemple : fermer les volets si plusieurs rafale de vents et pas dès la 1ere
        ◦ Conditionner selon plage horaire (datetime picker de début et fin de période pour commencer, en verra après si début/fin peuvent être des commandes (heure de levé du soleil, …) -> sera utilisé en condition uniquement, en complément d’autres déclencheurs. Ne sera pas un déclencheur en soit.
        ◦ Séquencement des conditions : il faut condition 1 puis condition 2 puis condition 3 pour déclencher
        ◦ DONE Ajouter un déclencheur programmé ou périodique (idem celui existant) pour utilisation comme condition supplémentaire. (Aujourd’hui il bypass toutes les conditions, comme le déclenchement manuel, là il pourrait être utilisé en OU, en ET, …)

    • Priorité 3 (en gros : je le ferais pas sauf si plébiscité sur le forum…) :
        ◦ pouvoir appliquer un délai d’exécution sur une action annulation => NON
        ◦ Ajout des déclencheurs de type « variables » => comment on met un listener sur une variable ???
        ◦ Ajout des conditions d’évaluation Regex (matches)
        ◦ Conditions personnalisée entre les déclencheurs (« cond1 ET (cond1 OU cond2) OU (cond3 XOR cond1) »)
        ◦ Déclenchement sur condition valide plus de xx minutes

Divers :
  * ajouter menu deroulant pour choisir les labels ou un bouton de check
  * revoir tous les tags et notamment les tags triggers à refaire ! (passer les triggers dans les crons en changeant comment chercher les cron par search et non byClassAndFunction...)
  * revoir toute la mise en page bootstrap md et sm

# Notes
les plugins avec des crons :
- datetime : thermostat, weather, calendar
- périodique : camera, conso, network, Monitoring, speedtest, ecodevice, IPX800, philipsHue


# Overview code :
* triggers :
  * DONE - conditions sur valeurs et répétition de la même valeur au moins x fois en N minutes
  * DONE - programmé (CRON)
  * plage horaire (datetime picker de début et fin de période pour commencer, en verra après si début/fin peuvent être des commandes (heure de levé du soleil, …)
  * (condition valide plus de x minutes)
* conditions entre triggers :
  * DONE - ET (all conditions valides)
  * DONE - OU (1 condition valide)
  * x conditions valides sur N
  * séquencement (le bon ordre...)
  * (condition perso)

# A documenter :
* comment gerer la repetition des valeurs via la fonction core de l'onglet avancé vu que supprimé du plugin
* si condition string, il faut mettre " " ou rien, mais pas de '' !
* repetition de valeur : nb de fois c'est un "au moins" x fois en N secondes. Pas un "exactement" ni un "maximum"
* pour les repetitions de valeurs, lorsque le seuil est atteint, la condition est valide a chaque nouvel évenement valide dans le délai consideré. Si 3 fois en 5 min, à partir de la 3eme fois, chaque fois supplémentaire déclenche (limiter la repetition des actions si besoin...)
* il n'y a plus de log info pour les triggers valides
* comportement des crons à la sauvegarde (ok pour date précise, mais chelou pour périodique)
* pour les crons, jeedom propose toutes les min, 5min, 10 min, ... si vous voulez 3 min il faut le changer à la main dans le resultat
* s'il n'y a aucune condition à évaluer, les cron triggers ne lanceront rien, il faut utiliser le cron bypass
* comment passer par un virtuel pour avoir un trigger cmd (à tester avant... éventuellement créér des cmd à la demande ?)
