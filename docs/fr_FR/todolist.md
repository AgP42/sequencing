# Debug :
  - cron qui se lance tout seul lors du set => demandé sur le forum dev

# Amélioration :

    • Priorité 1 (à faire et je vois comment faire) :
        ◦ Déclenchement si x conditions valides sur N (actuellement soit 1 seule (OU), soit toutes (ET))
    • Priorité 2 (à faire, mais je dois analyser le comment, donc peut-être trop compliqué…) :
        ◦ Déclenchement si répétition de la même valeur (à spécifier par l’utilisateur) au moins x fois en N minutes : permet de filtrer un déclenchement intempestif d’un capteur. Exemple : fermer les volets si plusieurs rafale de vents et pas dès la 1ere.
        ◦ Conditionner selon plage horaire (datetime picker de début et fin de période pour commencer, en verra après si début/fin peuvent être des commandes (heure de levé du soleil, …) -> sera utilisé en condition uniquement, en complément d’autres déclencheurs. Ne sera pas un déclencheur en soit.
        ◦ Séquencement des conditions : il faut condition 1 puis condition 2 puis condition 3 pour déclencher
        ◦ pouvoir appliquer un délai d’exécution sur une action annulation => NON
        ◦ Ajouter un déclencheur programmé ou périodique (idem celui existant) pour utilisation comme condition supplémentaire. (Aujourd’hui il bypass toutes les conditions, comme le déclenchement manuel, là il pourrait être utilisé en OU, en ET, …)
    • Priorité 3 (en gros : je le ferais pas sauf si plébiscité sur le forum…) :
        ◦ Ajout des déclencheurs de type « variables » => comment on met un listener sur une variable ???
        ◦ Ajout des conditions d’évaluation Regex (matches)
        ◦ Conditions personnalisée entre les déclencheurs (« cond1 ET (cond1 OU cond2) OU (cond3 XOR cond1) »)
        ◦ Déclenchement sur condition valide plus de xx minutes

Divers :
  - ajouter menu deroulant pour choisir les labels ou un bouton de check
  - passer les triggers dans les crons en changeant comment chercher les cron par search et non byClassAndFunction...

# Notes
les plugins avec des crons :
- datetime : thermostat, weather, calendar
- périodique : camera, conso, network, Monitoring, speedtest, ecodevice, IPX800, philipsHue


# Overview code :
* triggers :
  * conditions sur valeurs
  * répétition de la même valeur au moins x fois en N minutes
  * plage horaire (datetime picker de début et fin de période pour commencer, en verra après si début/fin peuvent être des commandes (heure de levé du soleil, …)
  * programmé (CRON)
  * (condition valide plus de x minutes)
* conditions entre triggers :
  * ET (all conditions valides)
  * OU (1 condition valide)
  * x conditions valides sur N
  * séquencement (le bon ordre...)
  * (condition perso)
