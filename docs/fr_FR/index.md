Présentation
============

Ce plugin permet de déclencher des actions séquencées (actions immédiates ou actions retardées) suite à l'activation d'un ou plusieurs déclencheurs, par programmation, ou via appel externe (par un autre plugin, un scenario, un appel API, ...).

Des déclencheurs d'annulation permettent de stopper la séquence et d'exécuter des actions d'annulation spécifiques selon les actions déjà réalisées ou non.

Les principales fonctionnalités sont les suivantes :
* Gestion illimitée d'actions séquentielles (immédiates ou retardées)
* Déclenchement :
   * Quantité illimitée de déclencheurs, avec chacun jusqu'à 2 conditions selon leur valeur, et la possibiliter de filtrer les répétitions de valeur
   * Programmation du déclenchement (cron) à une date/horaire ou périodiquement
   * Gestion d'appel externe pour déclencher la séquence d'actions (via un autre plugin, scenario, appel API, le dashboard, ...)
   * Historisation des capteurs de déclenchements
* En cas de multidéclenchement, choix de garder la programmation initiale de chaque action ou de les reporter
* Gestion annulation de la séquence et liste d'actions associées
* Les actions d'annulation peuvent être conditionnées par l'exécution ou non d'une action de la séquence initiale
* Tags dans les messages pour les personnaliser selon le contexte

Les déclencheurs internes peuvent être n'importe quelle commande Jeedom de type "Info" (capteur, bouton, info virtuelle, ...)

Les actions peuvent être n'importe quelle commande Jeedom de type "action" (lampe, avertisseur sonore, notification sur smartphone, messages, ...) ou "mot clé" (alerte, scenario, variable, évènement, redemarrer Jeedom, ...)

Ce plugin est payant (2€) pour soutenir Jeedom (1,2€) ainsi que mes developpements (0,8€) mais je laisse les sources ouvertes à tous, vous pouvez ainsi le tester gratuitement ou l'utiliser gratuitement si vous ne souhaitez pas le payer.
Lien vers le code source : [https://github.com/AgP42/sequencing/](https://github.com/AgP42/sequencing/)

Changelog
==========

Voir le [Changelog](https://agp42.github.io/sequencing/fr_FR/changelog)

Seules les modifications ayant un impact fonctionnel sur le plugin sont listées dans le changelog.

Configuration du plugin
========================

Ajouter un équipement, puis pour configurer les différents onglets.

Onglet **Général**
---

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletGeneral.png)

* **Informations Jeedom**
   * Indiquer le nom de l'équipement
   * Objet parent : il s'agit de l'objet Jeedom auquel rattacher l'équipement
   * Catégorie : catégorie Jeedom à laquelle rattacher l'équipement
   * Activer le plugin
   * Visible sert a visualiser les infos sur le dashboard, par défaut vous aurez uniquement les commandes pour Déclencher et Arrêter la séquence. Vous pouvez choisir (onglet **Avancé - Commandes**) de visualiser les capteurs de déclenchement et d'annulation. Le plugin n'a pas besoin d'être visible sur le dashboard pour fonctionner.

* **Tags messages**

Vous pouvez saisir ici des tags personnalisés pour cet équipement. Ces informations peuvent être utilisées pour les messages, ces champs sont facultatifs.
Vous pouvez notamment utiliser des tags dans ces tags, par exemple vous pouvez définir en #tag1# un texte personnalisé contenant plusieurs autres tags ('#action_label# exécutée à #time# suite déclenchement de #trigger_name# à #trigger_time# et après un délais de #action_timer# min') et réutiliser ce #tag1# dans plusieurs autres actions du plugin.

Détail des tags (utilisables dans toutes les actions, sauf indication contraire):

* #tag1# : tag personnalisé 1
* #tag2# : tag personnalisé 2 (#tag2# ne peut pas reprendre #tag1#)
* #tag3# : tag personnalisé 3 (#tag3# ne peut reprendre ni #tag1# ni #tag2#)

* #eq_full_name# : le nom Jeedom complet (Objet parent et nom) de votre équipement ("[Maison][SequenceTest]")
* #eq_name# : le nom Jeedom de votre équipement ("SequenceTest"), tel que défini dans l'onglet **Général**

* #action_label# : le label de votre action courante. Vide si non défini. Uniquement pour les **Actions**
* #action_timer# : le délai avant exécution de votre action courante. Vide si non défini. Uniquement pour les **Actions**
* #action_label_liee# : le label de votre action de référence. Vide si non défini. Uniquement pour les **Actions d'annulation**

* Tags selon les déclencheurs :
   * Infos :
      * les informations correspondront toujours au dernier déclencheur valide
      * il est donc possible qu'il ne corresponde pas au déclencheur d'origine de votre action. Par exemple : votre déclencheur 1 lance une action message contenant #trigger_name# et décallée de 10 min. Si le déclencheur 2 est déclenchée avant l'exécution effective du message, le tag #trigger_name# contiendra le nom du déclencheur 2 (bien qu'elle est été initialement lancée par le déclencheur 1).
   * Tags disponibles :
      * #trigger_name# : plusieurs possibilitées :
         * le **Nom** du déclencheur s'il s'agit d'un déclencheur interne du plugin.
         * "user/api" si déclenché par l'API ou par la commande du dashboard ou via un autre plugin.
         * "programmé" si déclenché par la programmation du plugin. Uniquement pour les **Actions**.
      * #trigger_value# : la valeur du déclencheur, uniquement pour les déclenchements par un déclencheur interne du plugin. Sera vide dans les autres cas.
      * #trigger_datetime# : La date et l'heure du déclenchement au format "2020-04-16 18:50:18". Il ne s'agit donc pas de la date et heure de l'action s'il s'agit d'une action retardée.
      * #trigger_time# : idem uniquement l'heure "18:50:18"

* Tags jeedom (idem scenarios) - les infos de date et heure correspondent à l'instant de l'exécution effective de l'action :
  * #seconde# : Seconde courante (sans les zéros initiaux, ex : 6 pour 08:07:06),
  * #minute# : Minute courante (sans les zéros initiaux, ex : 7 pour 08:07:06),
  * #heure# : Heure courante au format 24h (sans les zéros initiaux, ex : 8 pour 08:07:06 ou 17 pour 17:15),
  * #heure12# : Heure courante au format 12h (sans les zéros initiaux, ex : 8 pour 08:07:06),
  * #jour# : Jour courant (sans les zéros initiaux, ex : 6 pour 06/07/2017),
  * #semaine# : Numéro de la semaine (ex : 51),
  * #mois# : Mois courant (sans les zéros initiaux, ex : 7 pour 06/07/2017),
  * #annee# : Année courante,
  * #date# : Jour et mois. Attention, le premier nombre est le mois. (ex : 1215 pour le 15 décembre),
  * #time# : Heure et minute courante (ex : 1715 pour 17h15),
  * #timestamp# : Nombre de secondes depuis le 1er janvier 1970,
  * #sjour# : Nom du jour de la semaine (ex : Samedi),
  * #smois# : Nom du mois (ex : Janvier),
  * #njour# : Numéro du jour de 0 (dimanche) à 6 (samedi),
  * #jeedom_name# : Nom que vous avez donné à votre Jeedom,
  * #hostname# : Nom de la machine Jeedom (ex : "raspberrypi"),
  * #IP# : IP interne de Jeedom,

Onglet **Déclencheurs**
---

A jour
-----------------------------------------------------------------
Pas à jour


Onglet **Actions**
---

Cet onglet permet de regrouper différents boutons d'alertes immédiates que la personne pourra activer pour demander de l'aide. Il peut s'agir d'un bouton à porter sur soi ou de boutons dans une zone particulière. Il n'y a pas de limite de nombre de boutons.

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletActions.png)

* Cliquer sur "ajouter un bouton" pour définir un ou plusieurs capteurs de type "bouton" ou "interrupteur"
* **Nom** : champs obligatoire
* **Capteur** : champs obligatoire

Onglet **Déclencheurs d'annulation**
---

Onglet **Actions d'annulation**
---



Onglet **Actions d'alerte**
---

Cet onglet permet de définir les actions à déclencher lorsqu'un bouton d'alerte est activé.

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletActions.png)

* Cliquer sur "ajouter une action" pour définir une ou plusieurs actions
* **Label** : Champs facultatif permettant de lier cette action aux actions lors de la réception d'un accusé de réception ou d'annulation.
* **Délai avant exécution (min)** :
   * ne pas remplir ou 0 : cette action sera exécutée immédiatement. En cas de multiples appels sur le bouton d'alerte, ces actions seront déclenchées à chaque appel.
   * valeur supérieure à 0 : cette action sera enregistrée dans le moteur de tâches Jeedom (cron) pour une exécution différée selon le délai saisi.
   * le délai doit être saisi par rapport au déclenchement du bouton d'alerte. Si vous souhaitez 3 actions, l'une immédiate puis 10 min après puis 10 min après, il faudra saisir 0, 10 et 20.
* **Action** : la commande jeedom correspondant à l'action voulue. L'action peut etre de n'importe quel type : une lampe du logement, un message vers les aidants, l'appel d'un scenario jeedom, ...

Remarques :
* Dans le cas d'un redémarrage de Jeedom alors que des actions sont enregistrées, les actions seront réalisées dès le lancement de Jeedom (si l'heure de l'action est dépassée).
* Lors de l'enregistrement ou de la suppression, si des actions étaient enregistrées, elles seront supprimées avec un message d'erreur donnant le nom de la personne :

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/msgcron.png)

* Si l'une de vos action est de type "message", vous pouvez utiliser les tags définis dans l'onglet **Général**


Onglet **Accusé de réception**
---
Cet onglet fourni l'URL à appeler pour déclencher l'Accusé de Réception ainsi que définir les actions à réaliser lors de la réception de l'AR

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletAR.png)

* **Commande à appeler depuis l'extérieur pour accuser réception de l'alerte**
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée soit fonctionnelle.
   * Vous pouvez cliquer sur le lien pour tester son bon fonctionnement
   * Cet URL peut être appelé par n'importe quel équipement extérieur, notamment un smartphone
* **Actions à la réception d'un accusé de réception (pour prévenir la personne qu'un aidant arrive, je dois ?)**
   * **Label action de référence** :
      * Vous pouvez ici saisir le label de l'action de référence de l'onglet "Actions d'alerte".
      * Le label saisi doit être strictement identique, attention aux espaces.
      * Lorsque le label est renseigné et correspond à une action d'alerte, il faut que l'action d'alerte de référence ait été précédemment lancée pour que la présente action s'exécute.
      * Attention, si vous renseignez un label qui n'existe pas (et donc ne sera jamais exécuté), l'action liée ne s'exécutera jamais.
      * Exemple 1 : l'action d'alerte est d'envoyer un message à Mr x, 30 min après le déclenchement du bouton d'alerte (une alerte immédiate vers un autre aidant étant définie par ailleurs). L'action lors de l'AR est d'envoyer un message à Mr x pour le prévenir que quelqu'un a accusé réception de l'alerte. L'action d'AR ne sera exécutée que si l'action d'alerte initiale avait été exécutée à la fin de son délai de 30min. Ceci permet de ne pas envoyer des messages lors d'un AR alors que la personne n'avait pas reçu le message d'alerte initial.
      * Exemple 2 : l'action d'alerte est d'allumer immédiatement une lampe en orange (signaler à la personne que son bouton fonctionne et que l'alerte est envoyée). L'action d'AR est de passer cette lampe en vert lorsqu'un aidant a accusé réception de l'alerte. Il n'est ici pas nécessaire de définir un label pour les lier, car l'action initiale étant immédiate, il n'y a pas de risque d'annuler une action n'ayant jamais eu lieu.
   * **Action** : la commande jeedom correspondant à l'action voulue. L'action peut être de n'importe quel type : une lampe du logement, un message vers les aidants, l'appel d'un scenario jeedom, ... Si l'une de vos action est de type "message", vous pouvez utiliser les tags définis dans l'onglet **Général**

Lors de la réception d'un accusé de réception, toutes les actions d'alertes "futures" sont annulées.

Onglet **Annulation d'alerte**
---
Cet onglet permet de configurer des boutons et actions d'annulation d'alerte. Il s'agit ici de désactiver le mécanisme d'alerte lorsqu'un aidant arrive dans le logement ou si la personne se rend compte qu'elle a appuyé par erreur sur son bouton d'alerte.

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletAnnulation.png)

* Définir un ou plusieurs capteurs de type "bouton" ou "interrupteur" qui serviront à annuler l'alerte. Il est aussi possible de définir le capteur de porte du logement par exemple, mais alors il faut bien définir des labels pour toutes les actions, sinon les actions seront réalisées à chaque ouverture de porte du logement même si l'alerte n'a pas été déclenchée précédemment.
* Définir les actions qui seront réalisées à l'activation des capteurs d'annulation. Le fonctionnement des labels est identique aux actions de l'onglet **Accusé de réception**.

Lors d'une annulation, toutes les actions d'alertes "futures" sont annulées.

Si l'une de vos action est de type "message", vous pouvez utiliser les tags configurés dans l'onglet "Général".

Pas à jour
-----------------------------------------------------------------
A jour

Onglet **Avancé - Commandes Jeedom**
---

Vous pouvez configurer ici les commandes utilisées par ce plugin. Vous pouvez notamment définir la visibilité des boutons de déclenchement et d'arrêt sur le dashboard Jeedom (visibles par défaut), et la visibilité des valeurs des déclencheurs (non-visibles par défaut, mais historisés).

Remarques sur le comportement du plugin
======

Au démarrage et après redémarrage Jeedom
---
* Si des actions avaient été programmées pendant la coupure de Jeedom, elles seront exécutées au démarrage (immédiatement si l'heure prévu est dépassé ou à leur heure initialement prévue). Les actions enregistrées ne sont pas perdues par un redémarrage de Jeedom.

En cas de déclenchement multiples
---

* Toutes les actions à déclenchement immédiat seront relancées à chaque déclenchement
* Les actions différées dont la case "reporter" n'est pas cochée ne sont pas décalées, la programmation initiale reste. Ces actions différées ne sont pas non plus multipliées par chaque nouveau déclenchement, seul le délai initialement prévu reste.
* Les actions différées dont la case "reporter" est cochée : la programmation initiale est reportée pour correspondre au nouveau délai.
* Dans le cas d'un nouveau déclenchement après que certaines actions différées ai eu lieu, ces actions seront reprogrammées (à partir de la date courante). Ainsi si vos actions ne sont pas toutes en mode "Reporter", il est possible d'avoir des comportements où l'ordre de déclenchement n'est plus respecté.

Si une annulation est déclenchée sans qu'un déclenchement ait été précédemment initiée
---

Le comportement dépendra de la configuration du plugin :
* si toutes les actions d'annulation sont liées à des label, alors il ne se passera rien.
* si certaines actions d'annulation ne sont pas conditionnées : elles seront exécutées.

Infos capteurs
---

* L'ensemble des capteurs définis dans le plugin doivent avoir un nom unique. Le changement de nom d'un capteur revient à le supprimer et à en créer un nouveau. L'historique associé à ce capteur sera donc perdu.

Support
===

* Pour toute demande de support ou d'information : [Forum Jeedom](https://community.jeedom.com/c/plugins/automatisation/48)
* Pour un bug ou une demande d'évolution, merci de passer de préférence par [Github](https://github.com/AgP42/sequencing/issues)
