Présentation
============

Ce plugin fait parti d'un ensemble de plugins pour Jeedom permettant l'aide au maintien à domicile des personnes âgées : SeniorCare.

La demande initiale vient de ce sujet sur le forum : [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111).

Ce plugin permet la gestion de boutons d’alertes, ses principales fonctionnalités sont les suivantes :
* Gestion d'une quantité illimitée de boutons d'alertes
* Gestion d'actions d'alertes séquentielles
* Gestion Accusé de Réception de l'alerte et liste d'actions associées
* Gestion annulation de l'alerte et liste d'actions associées

Les boutons d'alerte peuvent être de n'importe quel type : bouton à porter, interrupteur mural, détecteur de mouvement, ...

Les actions peuvent être n'importe quelle action Jeedom : gestion lampe, avertisseur sonore, notification sur smartphone, sms, email, message vocal, ...

Il est aussi possible d'utiliser ce plugin comme simple bouton d'appel.

Lien vers le code source : [https://github.com/AgP42/sequencing/](https://github.com/AgP42/sequencing/)

Si vous souhaitez participer au projet, n’hésitez pas à le faire savoir ici [Développer un logiciel d’Analyse comportementale](https://community.jeedom.com/t/developper-un-logiciel-danalyse-comportementale/19111)

Avertissement
==========

Ce plugin a été conçu pour apporter une aide aux personnes souhaitant rester chez elles et à leurs aidants.
Nous ne pouvons toutefois pas garantir son bon fonctionnement ni qu'un dysfonctionnement de l’équipement domotique n'arrive au mauvais moment.
Merci de l'utiliser en tant que tel et de ne pas prendre de risque pour la santé de ceux que nous cherchons à aider !
Ce plugin est gratuit et open source, il est fourni sans garanti de bon fonctionnement.

Changelog
==========

Voir le [Changelog](https://agp42.github.io/sequencing/fr_FR/changelog)

Seules les modifications ayant un impact fonctionnel sur le plugin sont listées dans le changelog.

Principe de fonctionnement
========================

Le principe est le suivant :
* La personne âgée dispose d'un ou plusieurs boutons d'alerte, au déclenchement de l'un d'eux, le mécanisme d'alerte est lancé. Vous pouvez alors définir des actions à réaliser dans le logement (changement de couleur d'une lampe, alerte sonore, ...) ainsi que des actions vers un ou plusieurs aidants extérieurs. Ces actions peuvent être des notifications sur leur téléphone, un sms, un email, ... Ces différentes actions peuvent être réalisées immédiatement ou être retardées. Ceci permet de définir plusieurs personnes à avertir successivement tant que l'alerte n'a pas été prise en compte par l'un d'eux
* Les aidants peuvent accuser réception de l'alerte, ce qui aura pour effet de déclencher des actions spécifiques (changer la couleur de la lampe dans le logement de la personne pour la prévenir que son alerte a été prise en compte par exemple). A la réception d'un AR, les actions d'alerte programmés qui n'ont pas encore été exécutées sont annulées. Ceci permet de couper la chaîne d'alerte. Il est possible de définir des actions à ne réaliser que si une action d'alerte précédente a été exécutée. Ceci permet de prévenir les autres personnes ayant reçu l'alerte que quelqu'un en a accusé réception par exemple.
* Une fois l’aidant sur place ou si la personne réalise avoir déclenché une alerte par erreur, des boutons d'annulation d'alerte et actions associées sont définis.

Configuration du plugin
========================

Ajouter les différentes personnes à suivre, puis pour chacune configurer les différents onglets.

Onglet **Général**
---

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletGeneral.png)

* **Informations Jeedom**
   * Indiquer le nom de la personne
   * Objet parent : il s'agit de l'objet Jeedom auquel rattacher la personne. Il doit être différent de "Aucun"
   * Activer le plugin pour cette personne
   * Visible sert a visualiser les infos sur le dashboard, il n'y a rien a visualiser pour ce plugin

* **Informations concernant la personne dépendante**

Vous pouvez saisir ici des informations sur la personne dépendante. Ces informations seront utilisées uniquement pour la saisie de tags dans les messages d'alertes, tous ces champs sont facultatifs.

Onglet **Boutons d'alerte**
---

Cet onglet permet de regrouper différents boutons d'alertes immédiates que la personne pourra activer pour demander de l'aide. Il peut s'agir d'un bouton à porter sur soi ou de boutons dans une zone particulière. Il n'y a pas de limite de nombre de boutons.

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletBoutons.png)

* Cliquer sur "ajouter un bouton" pour définir un ou plusieurs capteurs de type "bouton" ou "interrupteur"
* **Nom** : champs obligatoire
* **Capteur** : champs obligatoire


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


Onglet **Avancé - Commandes Jeedom**
---

Vous pouvez configurer ici les commandes utilisées par ce plugin. Vous pouvez notamment définir la visibilité du bouton d'accusé de réception sur le dashboard Jeedom (pour tests notamment)

Remarques sur le comportement du plugin
======

Dans les exemples ci-dessous, nous considérons que les actions à la réception d'un AR et les actions d'annulation sont correctement liées aux actions d'alerte (via un label).
D'une manière générale le plugin a été conçu pour aller dans le sens de la sécurité de la personne, en cas de mode dégradés le comportement sera celui de relancer une alerte (éventuellement à tort) plutôt que de ne rien faire.

Au démarrage et après redémarrage Jeedom
---
* Si des actions d'alerte avaient été programmées pendant la coupure de Jeedom, elles seront exécutées au démarrage. Les actions enregistrées ne sont pas perdues par un redémarrage de Jeedom.

En cas d'appuis multiple sur le bouton d'alerte
---
### Avant qu'un accusé de réception extérieur ait été reçu

* Toutes les actions à déclenchement immédiat seront relancées à chaque appui
* Les actions différées ne sont pas décalées par ces appui multiples (ce qui serai l'inverse de l'effet recherché par la personne), la programmation initiale reste. Les actions différées ne sont pas non plus multipliées par chaque appui.
* Dans le cas où le nouvel appui serait réalisé après que certaines actions différées ai eu lieu, ces actions seront reprogrammées (à partir de la date courante).

### Après qu'un accusé de réception extérieur ait été reçu

* Toutes les actions à déclenchement immédiat seront relancées à chaque appui
* Les actions différées (qui ont été annulées par la réception de l'AR) seront reprogrammées (1 fois) par rapport à l'heure du 1er nouvel appui après la réception de l'AR
* Le statut de l'état des alertes envoyées n'est pas impacté.
* Exemple :
   * Il y a 5 personnes dans la chaîne de transmission de l'alerte
   * Les 3 premières personnes ont reçu l'alerte avant qu'un AR soit émis (les 3 personnes reçoivent l'info qu'un AR a été reçu)
   * La personne âgée relance l'alerte par appui sur son bouton, la chaîne d'alerte complète est relancée : les actions immédiates ainsi que les actions différées.
   * La première personne accuse à nouveau réception, les 3 personnes ayant reçues l'alerte initiale seront a nouveau notifiées qu'un AR a été reçu. La chaîne des messages d’alerte est à nouveau coupée.
   * Lors de l'annulation finale de l'alerte, les 3 personnes avant reçu un message initialement seront notifiées
Ainsi le fait de relancer le bouton d'alerte en cours du processus n’exclut pas les personnes ayant reçu l'alerte initiale des infos suivantes sur la prise en compte de l'alerte. Seule l’annulation de l’alerte remet à 0 la liste des actions déjà exécutées.

En cas de multiples Accusé de réceptions reçus
---
* Toutes les actions de la liste des actions AR seront relancées à chaque réception de la commande d'AR. Si elles dépendent d'une action d'alerte de référence (via un label), elles seront réalisées si l'action initiale a été réalisée uniquement.
* Les actions différées sont annulées à la réception du premier AR.

Exemple :
* 3 personnes ont reçu des messages d'alerte
* Lorsqu'une de ces personnes accuse réception de l'alerte, chacun reçoit la notification d'AR
* Si une seconde personne accuse aussi réception, chacun reçoit a nouveau une notification d'AR
* Si d'autres personnes dans la chaîne d'alerte n'avaient pas encore été alertées, elles ne recevront rien, ni l'alerte initiale, ni les messages d'AR, ni les messages d'annulation de l'alerte

Il est à noter que la personne à l’origine de l’AR recevra elle aussi la notification que qu’AR à été reçu.

Si l'annulation d'alerte n'est jamais appelée
---

Par exemple si l'aidant ne désactive pas l'alerte en arrivant dans le logement.

* Le comportement sera identique au cas de l'appui multiple. L'alerte sera donc relancée et les actions différées (si déjà exécutées lors de l'alerte précédente) seront a nouveau programmées.

Si un AR ou une annulation d'alerte est appelée sans qu'une alerte ait été précédemment initiées
---

Le comportement dépendra de la configuration du plugin :
* si toutes les actions sont liées à des label, alors il ne se passera rien.
* si certaines actions ne sont pas conditionnées : elles seront exécutées.

Infos capteurs
---

* Pour les capteurs "bouton d'alerte" et "bouton d'annulation d'alerte", c'est le changement de valeur du capteur qui est détecté et déclenche les actions, la valeur en elle-même n'est pas prise en compte !
* Si vous voulez utiliser un capteur complexe, comme un accéléromètre, vous pouvez utiliser un équipement "Virtuel" dans Jeedom pour définir des seuils et déclencher l'alerte du présent plugin en conséquence.
* L'ensemble des capteurs définis dans le plugin doivent avoir un nom unique. Le changement de nom d'un capteur revient à le supprimer et à en créer un nouveau. L'historique associé à ce capteur sera donc perdue.

Support
===

* Pour toute demande de support ou d'information : [Forum Jeedom](https://community.jeedom.com/c/plugins/security/86)
* Pour un bug ou une demande d'évolution, merci de passer de préférence par [Github](https://github.com/AgP42/sequencing/issues)
