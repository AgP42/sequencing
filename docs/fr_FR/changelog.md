# Stables

1.0.0 - 24 avril 2020 => Stable n°1
---

# Betas

1.0.1 - 27 avril 2020
---

* Limitation d'action et action annulation en secondes (et plus en minutes)
* Ajout condition sur répétition d'une valeur
* Ajout programmation pour déclenchement annulation
* Tests sur jeedom v4.0.52

0.0.6 - 24 avril 2020 => Stable 1.0.0
---

* Correction coquilles dans les logs
* Changement log info pour ajouter le humanName
* Ajout du tag #trigger_full_name#
* Ajout limitation d'exécution pour les actions et actions d'annulation
* Mise à jour page de configuration des actions et actions d'annulation
* Mise à jour documentation en conséquence
* Tests sur jeedom v4.0.52

0.0.5 - 23 avril 2020
---

* Changement ordre évaluation d'un || pour éviter un warning
* Ajout lancement de la saugevarde lors de l'activation du plugin pour relancer les listeners et cron (à voir à l'usage si c'est une vrai bonne idée...)
* Debug cas où l'annulation est appelée en différée par la liste des actions - et réécriture de la fonction cleanAllCron() qui devient cleanAllDelayedActionsCron()
* Debug suppression du cron de programmation dans le cas de plusieurs séquences programmées
* Tests sur jeedom v4.0.51

0.0.4 - 21 avril 2020
---

* Correction coquille log debug et erreur si enregistrement d'un capteur qui n'est pas une cmd jeedom
* Debug évaluation de string ayant des accents

0.0.3 - 20 avril 2020
---

* Débug affichage champ label et label action de reference
* Ajout possibilité d'évaluer des "string" dans les conditions de declenchements
* Ajout exception si enregistrement avec des capteurs qui ne sont pas des commandes Jeedom
* Mise à jour doc en conséquence

0.0.2 - 18 avril 2020
---

* Ajout vérification existence des commandes "start" et "stop" au cas où l'utilisateur les auraient supprimées manuellement
* Ajout vérifications lors des appels byId (robustification du code)
* Update liens Jeedom dans le template docs
* Ajout possibilité de vérifier que tous les triggers ou trigger_cancel soient valides pour déclencher (évaluation en ET)
* Mise à jour documentation

0.0.1 - 17 avril 2020
---

* 1ere version, les principales fonctionnalités sont les suivantes :
    * Gestion illimitée d'actions séquentielles (immédiates ou retardées)
    * Déclenchement :
       * Quantité illimitée de déclencheurs, avec chacun jusqu'à 2 conditions selon leur valeur, et la possibilité de filtrer les répétitions de valeur
       * Programmation du déclenchement (cron) à une date/horaire ou périodiquement
       * Gestion d'appel externe pour déclencher la séquence d'actions (via un autre plugin, scenario, appel API, le dashboard, ...)
       * Historisation des capteurs de déclenchements
    * En cas de multidéclenchement, choix de garder la programmation initiale de chaque action ou de les reporter
    * Gestion annulation de la séquence et liste d'actions associées
    * Les actions d'annulation peuvent être conditionnées par l'exécution ou non d'une action de la séquence initiale
    * Tags dans les messages pour les personnaliser selon le contexte
* Création documentation
* Création changelog
