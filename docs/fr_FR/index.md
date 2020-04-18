Présentation
============

Ce plugin permet de déclencher des actions séquencées (actions immédiates ou actions retardées) suite à l'activation d'un ou plusieurs déclencheurs, par programmation, ou via appel externe (par un autre plugin, un scenario, un appel API, ...).

Des déclencheurs d'annulation permettent de stopper la séquence et d'exécuter des actions d'annulation spécifiques selon les actions déjà réalisées ou non.

Les principales fonctionnalités sont les suivantes :
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

Les déclencheurs internes peuvent être n'importe quelle commande Jeedom de type "Info" (capteur, bouton, info virtuelle, ...)

Les actions peuvent être n'importe quelle commande Jeedom de type "action" (lampe, avertisseur sonore, notification sur smartphone, messages, ...) ou "mot clé" (alerte, scenario, variable, évènement, redémarrer Jeedom, ...)

Ce plugin est payant (2€) pour soutenir Jeedom (1,2€) ainsi que mes développements (0,8€) mais je laisse les sources ouvertes à tous, vous pouvez ainsi le tester gratuitement ou l'utiliser gratuitement si vous ne souhaitez pas nous soutenir.
Lien vers le code source : [https://github.com/AgP42/sequencing/](https://github.com/AgP42/sequencing/)

Changelog
==========

Voir le [Changelog](https://agp42.github.io/sequencing/fr_FR/changelog)

Seules les modifications ayant un impact fonctionnel sur le plugin sont listées dans le changelog.

Configuration du plugin
========================

Ajouter un équipement, puis configurer les différents onglets.

Onglet **Général**
---

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletGeneral.png)

### **Informations Jeedom**
   * Indiquer le nom de l'équipement
   * Objet parent : il s'agit de l'objet Jeedom auquel rattacher l'équipement
   * Catégorie : catégorie Jeedom à laquelle rattacher l'équipement
   * Activer le plugin
   * Visible sert a visualiser les infos sur le dashboard, par défaut vous aurez uniquement les commandes pour Déclencher et Arrêter la séquence. Vous pouvez choisir (onglet **Avancé - Commandes**) de visualiser les capteurs de déclenchement et d'annulation. Le plugin n'a pas besoin d'être visible sur le dashboard pour fonctionner.

### **Tags messages**

Vous pouvez saisir ici des tags personnalisés pour cet équipement. Ces informations peuvent être utilisées pour les messages, ces champs sont facultatifs.
Vous pouvez notamment utiliser des tags dans ces tags, par exemple vous pouvez définir en #tag1# un texte personnalisé contenant plusieurs autres tags ('#action_label# exécutée à #time# suite déclenchement de #trigger_name# à #trigger_time# et après un délais de #action_timer# min') et réutiliser ce #tag1# dans plusieurs autres actions du plugin.

#### **Détail des tags** (utilisables dans toutes les actions, sauf indication contraire):

* #tag1# : tag personnalisé 1
* #tag2# : tag personnalisé 2 (#tag2# ne peut pas reprendre #tag1#)
* #tag3# : tag personnalisé 3 (#tag3# ne peut reprendre ni #tag1# ni #tag2#)
* #eq_full_name# : le nom Jeedom complet (Objet parent et nom) de votre équipement ("[Maison][SequenceTest]")
* #eq_name# : le nom Jeedom de votre équipement ("SequenceTest"), tel que défini dans l'onglet **Général**
* #action_label# : le label de votre action courante. Vide si non défini. Uniquement pour les **Actions**
* #action_timer# : le délai avant exécution de votre action courante. Vide si non défini. Uniquement pour les **Actions**
* #action_label_liee# : le label de votre action de référence. Vide si non défini. Uniquement pour les **Actions d'annulation**

* Tags selon les déclencheurs :
   * les informations correspondent au dernier déclencheur valide
   * il est donc possible qu'il ne corresponde pas au déclencheur d'origine de votre action. Par exemple : votre déclencheur 1 lance une action message contenant #trigger_name# et décalée de 10 min. Si le déclencheur 2 est déclenchée avant l'exécution effective du message, le tag #trigger_name# contiendra le nom du déclencheur 2 (bien qu'elle ait été initialement lancée par le déclencheur 1).
   * Tags disponibles :
      * #trigger_name# : plusieurs possibilités :
         * le **Nom** du déclencheur s'il s'agit d'un déclencheur interne du plugin.
         * "user/api" si déclenché par l'API ou par la commande du dashboard ou via un autre plugin.
         * "programmé" si déclenché par la programmation du plugin. Uniquement pour les **Actions**.
      * #trigger_value# : la valeur du déclencheur, uniquement pour les déclenchements par un déclencheur interne du plugin. Sera vide dans les autres cas.
      * #trigger_datetime# : La date et l'heure du déclenchement au format "2020-04-16 18:50:18". Il ne s'agit pas de la date et heure de l'action s'il s'agit d'une action retardée.
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
  * #IP# : IP interne de Jeedom

Onglet **Déclencheurs**
---

Cet onglet regroupe les différentes façon de déclencher la séquence d'action.

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletDeclencheurs.png)

### Via l'API, un autre plugin ou un scenario

* Pour l'API, utilisez le lien donné (actualiser ou sauvegarder si l'URL ne s'affiche pas directement)
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée soit fonctionnelle.
   * Vous pouvez cliquer sur le lien pour tester son bon fonctionnement
   * Cet URL peut être appelé par n'importe quel équipement extérieur, notamment un smartphone
* Pour un appel via un scenario ou un autre plugin (Mode, Agenda, Presence, ...), utilisez la commande Jeedom donnée.
* La commande de déclenchement manuelle est aussi disponible via un bouton sur le dashboard

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/widget.png)

### Par programmation

Vous pouvez programmer un cron directement via le plugin pour une exécution simple retardée ou une exécution périodique.

Info : lors de la première sauvegarde d'une programmation périodique, le déclenchement se lance dans la 1ère minute après la sauvegarde, puis il se lance comme programmé. Vous devez arrêter la séquence manuellement dans ce cas. Ceci est du au fait que le cron ne connaissant pas l'heure de son précédent déclenchement, il se croit "en retard" et s'exécute. Ceci peut aussi se produire lorsque vous réduisez la valeur de programmation périodique.

### Par déclencheur

Vous pouvez configurer une liste infinie de déclencheurs, pour chacun :

* **Nom** : chaque déclencheur doit avoir un nom unique. Champs obligatoire. Le changement de nom d'un déclencheur revient à le supprimer et à en créer un nouveau. L'historique associé sera donc perdu.
* **Capteur** : la commande Jeedom du déclencheur. Champs obligatoire.
* **Filtrer répétitions** : lorsque votre capteur est susceptible de répéter régulièrement sa valeur, vous pouvez choisir d'ignorer les répétitions en cochant cette case.
* **Conditions** : 1 ou 2 conditions possible sur la valeur du capteur

> Activez les logs en mode "Info" pour tester vos conditions de déclencheurs.

Onglet **Actions**
---

Cet onglet permet de définir les actions de la séquence.

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletActions.png)

Cliquer sur "ajouter une action" pour définir une ou plusieurs actions puis les configurer :
* **Label** : Champs facultatif permettant de lier cette action à une ou plusieurs actions d'annulation. Vous pouvez aussi utiliser ce champ pour personnaliser le tag liée à cette action (#action_label#)
* **Délai avant exécution (min)** :
   * ne pas remplir ou 0 : cette action sera exécutée immédiatement. En cas de multiples déclenchement, ces actions seront déclenchées à chaque appel.
   * délai supérieure à 0 : cette action sera enregistrée dans le moteur de tâches Jeedom (cron) pour une exécution différée selon le délai voulu.
   * le délai doit être saisi par rapport au déclenchement. Si vous souhaitez 3 actions, l'une immédiate puis 10 min après puis 10 min après, il faudra saisir 0, 10 et 20.
   * **Reporter** : permet de définir le comportement de l'action différée dans le cas d'un déclenchement multiple : laisser l'action à sa programmation initiale ou la reporter pour correspondre au dernier déclenchement.
* **Action** : la commande jeedom correspondant à l'action voulue. Pour les actions de type "message", vous pouvez utiliser les tags définis ci-dessus. Les actions peuvent être des "mot-clé" jeedom, pour lancer un scenario ou définir la valeur d'une variable par exemple.

Remarques :
* Dans le cas d'un redémarrage de Jeedom alors que des actions sont enregistrées, les actions seront réalisées dès le lancement de Jeedom (si l'heure de l'action est dépassée) ou à leur programmation prévue.
* Lors de l'enregistrement ou de la suppression de l'équipement, si des actions étaient enregistrées, elles seront supprimées avec un message d'erreur donnant le nom de l'action supprimée
* Les mots-clé spécifiques des scenarios jeedom comme "pause" ou "attendre" n'auront pas d'effet ici
* Vous pouvez choisir plusieurs actions ayant le même délai, elles seront alors exécutées simultanément après le délai voulu
* Attention, vous ne pouvez pas utiliser une action avec un délai pour couper votre propre séquence. Utilisez pour cela un scenario Jeedom.


Onglet **Déclencheurs d'annulation**
---

Cet onglet regroupe les différentes façon d'annuler la séquence d'action.

L'annulation consiste à :
* Annuler la programmation des actions programmées et non exécutées
* Déclencher des actions d'annulation, qui peuvent être conditionnées selon l'exécution précédente d'une **Action** (voir onglet **Actions d'annulation**)

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletAnnulationDeclencheurs.png)

### Via l'API, un autre plugin ou un scenario

* Pour l'API, utilisez le lien donné (actualiser ou sauvegarder si l'URL ne s'affiche pas directement)
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée soit fonctionnelle.
   * Vous pouvez cliquer sur le lien pour tester son bon fonctionnement
   * Cet URL peut être appelé par n'importe quel équipement extérieur, notamment un smartphone
* Pour un appel via un scenario ou un autre plugin (Mode, Agenda, Presence, ...), utilisez la commande Jeedom donnée.
* La commande de déclenchement manuelle est aussi disponible via un bouton sur le dashboard

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/widget.png)

### Par déclencheur

Vous pouvez configurer une liste infinie de déclencheurs, pour chacun :

* **Nom** : chaque déclencheur doit avoir un nom unique. Champs obligatoire. Le changement de nom d'un déclencheur revient à le supprimer et à en créer un nouveau. L'historique associé sera donc perdu.
* **Capteur** : la commande Jeedom du déclencheur. Champs obligatoire.
* **Filtrer répétitions** : lorsque votre capteur est susceptible de répéter régulièrement sa valeur, vous pouvez choisir d'ignorer les répétitions en cochant cette case.
* **Conditions** : 1 ou 2 conditions possible sur la valeur du capteur

> Activez les logs en mode "Info" pour tester vos conditions de déclencheurs.

Onglet **Actions d'annulation**
---

Cet onglet permet de définir des actions d'annulation de la séquence. Les actions d'annulation sont facultatives selon votre usage.

Par exemple :
* si vous aviez déclenché l'activation d'un appareil avec un délai de 5 min, vous pouvez choisir de couper l'appareil, uniquement s'il a été effectivement déclenché.
* si vous aviez une chaîne de message, vous pouvez choisir d'envoyer un message d'annulation uniquement aux personnes ayant reçu le message initial.

Vous pouvez aussi avoir des actions d'annulation systématiques (non conditionnées).

![](https://raw.githubusercontent.com/AgP42/sequencing/master/docs/assets/images/OngletActionsAnnulation.png)

Cliquer sur "ajouter une action" pour définir une ou plusieurs actions d'annulation puis les configurer :
* **Label action de référence** :
   * Vous pouvez ici saisir le label de l'action de référence de l'onglet **Actions**.
   * Le label saisi doit être strictement identique, attention aux espaces.
   * Lorsque le label est renseigné et correspond à une action d'alerte, il faut que l'action d'alerte de référence ait été précédemment exécutée pour que la présente action s'exécute.
   * Attention, si vous renseignez un label qui n'existe pas (et donc ne sera jamais exécuté), l'action liée ne s'exécutera jamais. Vous ne pouvez donc pas utiliser ce champs pour personnaliser un tag liée à cette action uniquement.
   * Laissez le champs vide pour exécuter l'action d'annulation sans condition (à chaque déclenchement d'annulation)
* **Action** : la commande jeedom correspondant à l'action voulue. Pour les actions de type "message", vous pouvez utiliser les tags définis ci-dessus. Les actions peuvent être des "mot-clé" jeedom, pour lancer un scenario ou définir la valeur d'une variable par exemple.

Onglet **Avancé - Commandes Jeedom**
---

Vous pouvez configurer ici les commandes utilisées par ce plugin. Vous pouvez notamment définir la visibilité des boutons de déclenchement et d'arrêt sur le dashboard Jeedom (visibles par défaut), et la visibilité des valeurs des déclencheurs (non-visibles par défaut, mais historisés).

Remarques sur le comportement du plugin
======

Au démarrage et après redémarrage Jeedom
---
* Si des actions avaient été programmées pendant la coupure de Jeedom, elles seront exécutées au démarrage (immédiatement si l'heure prévu est dépassé ou à leur heure initialement prévue). Les actions enregistrées ne sont pas perdues par un redémarrage de Jeedom.

Lors d'une sauvegarde (nouvelle configuration ou non)
---
* Toutes les **actions** programmées sont supprimées, avec un message d'erreur pour chacune
* La programmation du déclenchement n'est pas impactée (elle est mise à jour si elle a été modifiée)

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

Exemples d'utilisation
===

Cloche 2.0
---

Quand j'étais enfant ma grand-mère nous appelait pour les repas en sonnant la cloche. Aujourd'hui elle aurait un bouton dans sa cuisine avec la séquence suivante :
* Immédiatement : mémoriser les états des lampes et faire clignoter toutes les lampes de la maison (label : lampes)
* Immédiatement : envoyer une notification sur les smartphones des grands
* Délai 1 min : couper le clignotement des lampes et retour état précédent
* Délai 5 min : couper le courant de la télé, des consoles de jeux et des radios (label : tv)

Annulation (un autre bouton ou 2 appuis sur le même bouton) :
* Si "lampe" : couper le clignotement des lampes et retour état précédent
* Si "tv" : rallumer le courant de la télé, des consoles de jeux et des radios

Réveil
---

Séquence programmée tous les matins à 6h (pour un réveil effectif vers 7h en semaine):
* Délai 5 min : changer le thermostat pour baisser le chauffage dans les chambres et l'augmenter dans les pièces de vie (label : thermostat)
* Délai 60 min : allumer progressivement la lumière (label : lumiere)
* Délai 60 min : ouvrir les volets (label : volets)
* Délai 65 min : activer la machine a café (label : cafe)

Annulation :
* Si "thermostat" : remettre le thermostat en "nuit"
* Si "lumiere" : couper la lumière
* Si "volets" : fermer les volets
* Si "cafe" : couper la machine a café

Pour le week-end et les jours fériés : un scenario à 6h02 pour lancer l'annulation
Pour les matins difficiles : un bouton sur la table de nuit pour annuler la séquence !

Départ maison
---

Séquence déclenchée par le plugin "mode" ou "presence" :
* Immédiatement : fermer les volets
* Immédiatement : couper les lumières
* Immédiatement : baisser le chauffage
* Délai 5 min : activer l'alarme

Annulation, déclenchée par le plugin "mode" ou "presence" :
* Ouvrir les volets
* Relancer le chauffage
* Désactiver l'alarme

Support
===

* Pour toute demande de support ou d'information : [Forum Jeedom](https://community.jeedom.com/c/plugins/automatisation/48)
* Pour un bug ou une demande d'évolution, merci de passer de préférence par [Github](https://github.com/AgP42/sequencing/issues)
