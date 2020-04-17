# Beta

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
