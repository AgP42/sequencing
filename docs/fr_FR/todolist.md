# Debug :
  - cron qui se lance tout seul lors du set => demandé sur le forum dev

# Amélioration :

Divers :
  * tester si la date des crons est pas dans le passé ?
  * pouvoir appliquer un délai d’exécution sur une action annulation => NON
  * Ajout des déclencheurs de type « variables » => comment on met un listener sur une variable ???
  * Ajout des conditions d’évaluation Regex (matches)
  * datetimepicker : jours en anglais à passer en fr

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
  * DONE séquencement (le bon ordre...)

# A documenter :

* comment passer par un virtuel pour avoir un trigger cmd (à tester avant... éventuellement créér des cmd à la demande ?)
* Utiliser domogeek et ou Heliotrope pour les conditions

# Notes
les plugins avec des crons :
- datetime : thermostat, weather, calendar
- périodique : camera, conso, network, Monitoring, speedtest, ecodevice, IPX800, philipsHue
