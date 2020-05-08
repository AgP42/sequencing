Exemples d'utilisation
===

Cloche 2.0
---

Quand j'étais enfant ma grand-mère nous appelait pour les repas en sonnant la cloche. Aujourd'hui elle aurait un bouton dans sa cuisine avec déclenchement de la séquence suivante :
* Immédiatement : mémoriser les états des lampes et faire clignoter toutes les lampes de la maison (label : lampes)
* Immédiatement : envoyer une notification sur les smartphones des grands
* Délai 1 min : couper le clignotement des lampes et retour état précédent
* Délai 5 min : couper le courant de la télévision, des consoles de jeux et des radios (label : tv)

Annulation (un autre bouton ou 2 appuis sur le même bouton) :
* Si "lampe" : couper le clignotement des lampes et retour état précédent
* Si "tv" : rallumer le courant de la télévision, des consoles de jeux et des radios

Réveil
---

Séquence programmée tous les matins, les jours de semaines, hors jours fériés à 6h :
* Immédiatement : changer le thermostat pour baisser le chauffage dans les chambres et l'augmenter dans les pièces de vie (label : thermostat)
* Délai 60 min : allumer progressivement la lumière (label : lumière)
* Délai 60 min : ouvrir les volets (label : volets)
* Délai 65 min : activer la machine à café (label : café)

Annulation :
* Si "thermostat" : remettre le thermostat en "nuit"
* Si "lumière" : couper la lumière
* Si "volets" : fermer les volets
* Si "café" : couper la machine à café

Et pour les matins difficiles : un bouton sur la table de nuit pour annuler la séquence !

Départ/retour maison
---

Séquence déclenchée par le plugin "Mode" ou "Présence" :
* Immédiatement : fermer les volets
* Immédiatement : couper les lumières
* Immédiatement : baisser le chauffage
* Délai 5 min : activer l'alarme

Annulation, déclenchée par le plugin "Mode" ou "Présence" :
* Ouvrir les volets
* Relancer le chauffage
* Désactiver l'alarme

Gestion arrosage automatique (déclencheurs complexes)
---

Si vous avez 5 capteurs d'humidité dans votre jardin et le plugin Weather
* Déclenchement conditionné :
  * Définir vos 5 capteurs avec pour chacun, un seuil minimum et éventuellement la condition de 3 répétitions dans l'heure pour être valide (**Déclencheur selon valeur et répétition**)
  * Choisir comme évaluation "x conditions valides suffisent" et choisir par exemple 3 capteurs sur les 5
* Actions : lancer l'arrosage automatique avec un délai de 2 minutes (pour laisser au plugin le temps d'évaluer les conditions d'annulation éventuelles)
* Déclenchement d'annulation conditionné (évaluation en OU) :
  * Si la météo prévoit de la pluie dans l'heure (**Déclencheur selon valeur et répétition** avec les infos du plugin Weather)
  * Si le soleil va se coucher dans moins de 15 min (**Condition type scénario** avec time_op())
  * Si la plage horaire est entre 21h et 6h du matin (répété tous les jours) (**Condition selon plage temporelle**)
  * Si l'arrosage a déjà été déclenché plus de 4h aujourd'hui (**Condition type scénario** avec duration() ou durationBetween())
  * Et ajouter un programmateur de type cron toutes les minutes pour évaluer ces conditions (les "Condition type scénario" et "Condition selon plage temporelle" pas des déclencheurs mais juste des conditions) (**Déclencheur selon programmation**)
* Actions d'annulation : couper l'arrosage
