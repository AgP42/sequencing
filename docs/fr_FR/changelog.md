# Stables

1.0.0 - 24 avril 2020 => Stable n°1
---

2.0.0 - 11 mai 2020 => Stable n°2 (scope de la beta 1.0.7 du 7 mai)
---

# Betas

1.0.7 - 7 mai => Stable n°2
---

* Update regex pour les évaluation "Sequencement" et "Perso" pour ajouter les accents et caractéres chelous. Merci @naboleo
* Ajout évaluation sur la durée de validité pour les triggers sur "valeur"
* Refactorisation code notamment sur les crons
* Update logs
* Relecture et debugs
* Tests sur Jeedom v4.0.54
* Mise à jour documentation

1.0.6 - 6 mai
---

* Update des évaluation "Sequencement" et "Perso" pour utiliser les tags Jeedom # et mixer les conditions de temps et de résultat
* Debug (Jeedom v4.0.54)
* Update documentation

1.0.5 - 4 mai
---

* Ajout condition de type "scenario"
* Debug (Jeedom v4.0.54)
* Update documentation

1.0.4 - 3 mai
---

* Ajout condition de déclenchement par test du séquencement des conditions
* Debug
* Update documentation

1.0.3 - 1er mai & 2 mai (multiples versions !)
---

* Changement logique pour répétition de valeur : un déclencheur non valide est ignoré sans remettre le compteur de "valide" à 0
* Changement logs pour avoir le humanName au lieu du numero de la cmd => annulé, générait des php fatal error dans les logs...
* Multi debug (erreurs dans logs notamments)
* Update documentation

1.0.2 - 1er mai
---

* Correction des tags "déclencheurs"
* Ajout liste déroulante choix label de référence pour les actions d'annulation
* Update documentation

1.0.1 - 29 avril 2020
---

* Majeur :
  * Ajout condition sur répétition d'une valeur
  * Ajout programmation (CRON) pour déclenchement annulation
  * Ajout déclencheurs par crons pour évaluer les conditions pour lancer ou annuler la séquence
  * Ajout des condition d'évaluation "plage temporelle" pour lancer ou annuler la séquence
  * Ajout évaluation des conditions par "x parmi N"
  * Ajout évaluation des conditions par condition personnalisée (encore expérimental)
  * Suppression gestion "filtrer répétition", mais ajout dans la doc de l'explication comment gérer la même chose via le core jeedom
* Mineur :
  * Limitation d'action et action annulation en secondes (et non plus en minutes)
  * Ajout de trim() (suppression des espaces et autres caractères invisibles) partout pour les labels pour limiter les erreurs utilisateur
  * Tentative correction "PHP Warning: A non-numeric value encountered in /var/www/html/plugins/sequencing/core/class/sequencing.class.php on line 235"
* Mise à jour majeur documentation
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
