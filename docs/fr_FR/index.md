Présentation
============

Ce plugin permet de déclencher des actions séquencées (actions immédiates ou actions retardées) suite à l'activation d'un ou plusieurs déclencheurs : par programmation, via appel externe (par un autre plugin, un scénario, un appel API, ...) ou via des conditions élaborées (conditions sur valeur d'un déclencheur, répétition de valeur dans un délai donné, condition valable plus de x min, plage temporelle, logique floue, activations de capteurs dans un ordre donné, etc.)

Un mécanisme d'annulation permet de stopper la séquence et d'exécuter des actions d'annulation spécifiques selon les actions déjà réalisées ou non.

Les principales fonctionnalités sont les suivantes :
* Gestion illimitée d'actions séquentielles (immédiates ou retardées)
* Déclenchement immédiat (bypass des conditions si elles existent) :
   * Programmation du déclenchement (cron) à une date/horaire ou périodiquement
   * Gestion d'appel externe pour déclencher la séquence d'actions (via un autre plugin, scénario, appel API, le Dashboard Jeedom, ...)
* Déclenchement conditionné :
   * Quantité illimité de déclencheurs selon programmation (date-heure précise ou périodique)
   * Quantité illimité de déclencheurs selon commande Jeedom avec conditions sur valeur (binaires, numériques ou chaîne de caractères) et/ou selon répétition dans une durée donnée et/ou selon une durée minimum de validité
   * Quantité illimité de conditions selon plage temporelle (avec répétition possible chaque jour de la semaine, chaque semaine, mois, année)
   * Quantité illimité de conditions type "scénario" permettant d'utiliser toutes les fonctions de calculs des champs **Si** des scénarios Jeedom (conditions sur les variables, calculs de dates sur des commandes, etc.)
   * Historisation des déclencheurs (ceux liés à une commande Jeedom uniquement)
   * Toutes ces différentes conditions peuvent être évaluées en OU, en ET, en logique floue (x conditions sur N), selon l'ordre d’occurrence ou selon une condition totalement personnalisée mixant ces différentes conditions.
* En cas de multi-déclenchement de la séquence d'action, choix de garder la programmation initiale de chaque action ou de les reporter
* Gestion d'annulation de la séquence (immédiat ou conditionné, identique gestion du déclenchement) et liste d'actions associées
* Les actions d'annulation peuvent être conditionnées par l'exécution ou non d'une action de la séquence initiale
* Les actions et les actions d'annulation peuvent être limitées dans leur fréquence d'exécution
* Les actions et actions d'annulation peuvent être n'importe quelle commande Jeedom de type "action" (lampe, avertisseur sonore, notification sur smartphone, messages, ...) ou "mot clé" (alerte, scénario, variable, évènement, redémarrer Jeedom, ...)
* Tags dans les messages pour les personnaliser selon le contexte


Ce plugin est payant (2€) pour soutenir Jeedom (1,2€) ainsi que mes développements (0,8€) mais je laisse les sources ouvertes à tous, vous pouvez ainsi le tester gratuitement ou l'utiliser gratuitement si vous ne souhaitez pas nous soutenir.
Lien vers le code source : [https://github.com/AgP42/sequencing/](https://github.com/AgP42/sequencing/)

Changelog
==========

Voir le [Changelog](https://agp42.github.io/sequencing/fr_FR/changelog)

Seules les modifications ayant un impact fonctionnel sur le plugin sont listées dans le changelog.

Exemples détaillés
==========

Voir la page [Exemples Séquencement](https://agp42.github.io/sequencing/fr_FR/exemples)

(N'hésitez pas à me proposer des exemples de vos utilisations !)

Configuration du plugin
========================

Ajouter un équipement, puis configurer les différents onglets.

Onglet **Général**
---

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/OngletGeneral.png)

### **Informations Jeedom**
   * Indiquer le nom de l'équipement
   * Objet parent : il s'agit de l'objet Jeedom auquel rattacher l'équipement
   * Catégorie : catégorie Jeedom à laquelle rattacher l'équipement
   * Activer le plugin
   * Visible sert à visualiser les informations sur le Dashboard, par défaut vous aurez uniquement les commandes pour déclencher et arrêter la séquence. Vous pouvez renommer ces boutons via l'onglet **Avancé - Commandes**. Vous pouvez choisir (onglet **Avancé - Commandes**) de visualiser les capteurs de déclenchement et d'annulation sur le dashboard. Le plugin n'a pas besoin d'être visible sur le Dashboard pour fonctionner.

### **Tags messages**

Vous pouvez saisir ici des tags personnalisés pour cet équipement. Ces informations peuvent être utilisées pour les messages, ces champs sont facultatifs.
Vous pouvez notamment utiliser des tags dans ces tags, par exemple vous pouvez définir en #tag1# un texte personnalisé contenant plusieurs autres tags ('#action_label# exécutée à #time# suite déclenchement de #trigger_name# à #trigger_time# et après un délai de #action_timer# min') et réutiliser ce #tag1# dans plusieurs autres actions du plugin.

#### **Détail des tags** (utilisables dans toutes les actions, sauf indication contraire) :

##### Tags personnalisés

* #tag1# : tag personnalisé 1
* #tag2# : tag personnalisé 2 (#tag2# ne peut pas reprendre #tag1#)
* #tag3# : tag personnalisé 3 (#tag3# ne peut reprendre ni #tag1# ni #tag2#)

##### Tags généraux
* #eq_full_name# : le nom Jeedom complet (Objet parent et nom) de votre équipement ("[Maison][SequenceTest]")
* #eq_name# : le nom Jeedom de votre équipement ("SequenceTest"), tel que défini dans l'onglet **Général**

##### Tags actions
* #action_label# : le label de votre action courante. Vide si non défini. Uniquement pour les **Actions**
* #action_timer# : le délai avant exécution de votre action courante. Vide si non défini. Uniquement pour les **Actions**
* #action_label_liee# : le label de votre action de référence. Vide si non défini. Uniquement pour les **Actions d'annulation**

##### Tags selon les déclencheurs
* les informations correspondent au dernier déclencheur valide
* il est donc possible qu'il ne corresponde pas au déclencheur d'origine de votre action. Par exemple : votre déclencheur 1 lance une action message contenant #trigger_name# et décalée de 10 min. Si le déclencheur 2 est déclenchée avant l'exécution effective du message, le tag #trigger_name# contiendra le nom du déclencheur 2 (bien qu'elle ait été initialement lancée par le déclencheur 1).
* Tags disponibles :
   * #trigger_name# : plusieurs possibilités :
      * le **Nom** du déclencheur (celui que vous avez saisi dans l'onglet Déclencheur ou Déclencheur d'annulation) s'il s'agit d'un déclencheur interne du plugin.
      * "user/api" si déclenché par l'API ou par la commande du Dashboard ou via un autre plugin.
      * "programmé" si déclenché par la programmation du plugin (celle qui ne vérifie pas les autres conditions ou celle permettant de déclencher l'évaluation des conditions).
   * #trigger_full_name# : plusieurs possibilités :
      * le **HumanName** Jeedom du déclencheur s'il s'agit d'une commande de déclencheur interne du plugin ([Objet][Equipement][cmd])
      * "user/api" si déclenché par l'API ou par la commande du Dashboard ou via un autre plugin.
      * "programmé" si déclenché par la programmation du plugin (celle qui ne vérifie pas les autres conditions ou celle permettant de déclencher l'évaluation des conditions).
   * #trigger_value# : la valeur du déclencheur, uniquement pour les déclenchements par un déclencheur interne du plugin. Sera vide dans les autres cas.
   * #trigger_datetime# : La date et l'heure du déclenchement au format "2020-04-16 18:50:18". Il ne s'agit pas de la date et heure de l'action s'il s'agit d'une action retardée.
   * #trigger_time# : idem uniquement l'heure "18:50:18"

##### Tags généraux (idem scénarios)
* les informations de date et heure correspondent à l'instant de l'exécution effective de l'action :
* Rappel des tags scenarios utilisables (tous les tags scenarios qui seront ajoutés dans le core jeedom par la suite seront aussi fonctionnels)
  * #seconde# : Seconde courante (sans les zéros initiaux, ex : 6 pour 08:07:06),
  * #minute# : Minute courante (sans les zéros initiaux, ex : 7 pour 08:07:06),
  * #heure# : Heure courante au format 24h (sans les zéros initiaux, ex : 8 pour 8:07:06 ou 17 pour 17:15),
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

Onglet **Déclenchement immédiat**
---

Le déclenchement immédiat ne tient compte d'aucune autre condition par ailleurs, il permet de déclencher directement la séquence d'action.

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/OngletDeclenchementImmediat.png)

### Via l'API, un autre plugin ou un scénario, ou via le Dashboard

* Pour l'API, utilisez le lien donné (actualiser ou sauvegarder si l'URL ne s'affiche pas directement)
   * "Réglages/Système/Configuration/Réseaux" doit être correctement renseigné pour que l'adresse affichée soit fonctionnelle.
   * Vous pouvez cliquer sur le lien pour tester son bon fonctionnement
   * Cette URL peut être appelée par n'importe quel équipement extérieur, notamment un smartphone
* Pour un appel via un scenario ou un autre plugin (Mode, Agenda, Présence, ...), utilisez la commande Jeedom donnée ([Déclencher]).
* La commande de déclenchement est aussi disponible via un bouton sur le Dashboard

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/widget.png)

### Par programmation

Vous pouvez programmer un cron directement via le plugin pour une exécution simple ou une exécution périodique.

> Information sur le comportement des crons périodiques après sauvegarde : voir "Comportement des crons périodiques" ci-dessous

Onglet **Déclenchement conditionné**
---

Cet onglet permet de configurer des déclencheurs complexes pour lancer la séquence d'action. Ils sont indépendant du déclenchement immédiat défini à l'onglet précédent.

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/OngletDeclencheursConditionnesVide.png)

### Principe de fonctionnement
Cet onglet contient 2 parties : **Déclencheurs et conditions** et **Évaluation**.

**Déclencheurs et conditions** permet de définir une multitude de déclencheurs et de conditions, puis **Évaluation** permettra de déterminer les relations entre ces déclencheurs et conditions.

**Déclencheurs et conditions** contient 4 catégories d'éléments :
* **Déclencheurs programmés** : il s'agit d'un ou plusieurs cron(s) (programmation) que vous pouvez choisir d'exécuter à une heure donnée ou périodiquement. Ici cette programmation permettra de déclencher l'évaluation des conditions suivante uniquement. S'il n'y a aucune autre condition, il ne se passera rien (utilisez pour cela la programmation en déclenchement immédiat).
* **Déclencheurs avec conditions** : il s'agit ici de déclencheurs **et** de conditions. Chaque changement de valeur ou d'état d'une de ces commandes déclenchera l'évaluation des conditions liées à cette commande. Si ce déclencheur est valide, les autres conditions seront alors évaluées.
* **Conditions selon plage temporelle** : il s'agit d'une ou plusieurs plage(s) de date-heure, éventuellement répétées, pendant lesquels la séquence d'action peut-être activée. Il s'agit uniquement de **conditions** et non de **déclencheurs** (la séquence ne se déclenchera pas spontanément en début de plage)
* **Conditions type scénario** : il s'agit d'un champ de condition quasi identique aux conditions **Si** des scénarios du core de Jeedom. Il permet d'écrire des conditions complexes et d'utiliser les différentes fonctions des scénarios Jeedom [Doc scenario v4.0](https://doc.jeedom.com/fr_FR/core/4.0/scenario). Il s'agit uniquement de **conditions** et non de **déclencheurs** (la séquence ne se déclenchera pas spontanément lorsque la condition devient valide)

**Évaluation** permet de choisir les conditions que vous voulez appliquer entre ces différents éléments :
* **ET** : toutes les conditions (valeur, plage temporelle et conditions "scenarios") doivent être valides pour déclencher la séquence d'action
* **OU** : une seule condition suffit
* **x conditions valides** : seules x parmi vos N conditions doivent être valides pour déclencher la séquence
* **Séquencement** : vous pouvez ici choisir l'ordre d'arrivée des conditions pendant une durée donnée et choisir si toutes les conditions doivent toujours être valides ou non.
* **Condition personnalisée** : vous pouvez ici choisir condition par condition l'évaluation à faire.

### Configuration

Cliquez sur le bouton correspondant à l'élément que vous souhaitez ajouter.

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/OngletDeclencheursConditionnes.png)

Pour chaque nouvelle ligne, cliquez sur le bouton **-** pour la supprimer.

* **Déclencheurs programmés** :

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/TriggerCron.png)

  * cliquez sur le bouton **?** pour choisir la programmation voulue. Vous pouvez ensuite adapter la programmation manuellement si besoin (toutes les 2 mins par exemple). Le code vérifiera la validité de votre programmation avant l'enregistrement. (Techniquement il n'est pas possible de descendre sous la minute et la programmation sera toujours réalisée en début de minute)
  * le champ ne peut pas être laissé vide

> Information sur le comportement des crons périodique après sauvegarde : voir "Comportement des crons périodiques" ci-dessous

* **Déclencheur selon valeur et répétition** :

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/TriggersValeur.png)

  * **Nom** : chaque déclencheur doit avoir un nom unique. Champ obligatoire. Le changement de nom d'un déclencheur revient à le supprimer et à en créer un nouveau. L'historique associé sera donc perdu. Chaque déclencheur de cette catégorie est automatiquement historisé, vous pouvez changer ceci via l'onglet **Avancé - commandes**
  * **Commande** : la commande Jeedom du déclencheur. Champ obligatoire. Il ne peut s'agir que d'une commande (pas d'une variable ou autre).
  * **Conditions** :
    * 1 ou 2 condition(s) possible(s) sur la valeur du capteur. Il peut s'agit d'une valeur binaire, numérique ou d'un texte. Pour un texte, vous pouvez ajouter des "guillemets doubles" ou aucun guillemet, mais il ne faut pas utiliser de 'guillemets simples'.
    * **Durée** :
      * permet de ne considérer cette condition valide que si les conditions de valeurs restent valable un certain temps (en minutes).
      * Attention, l'évaluation de la durée est réalisée par les crons Jeedom qui peuvent avoir jusqu'à 1 min de moins que voulu (voir **Comportement des évaluations sur la durée** ci-dessous)
      * Exemple si vous souhaitez une alerte lorsque la température est inférieur à 18° ou supérieur à 25° pendant plus d'1h : condition 1 : <18 "ET" condition 2 : >25, pendant "60".
    * **Nombre de fois** et **en x secondes** :
      * permet d'ajouter comme condition une répétition de la valeur (seules les conditions valides sont comptées) sur une période donnée.
      * Par exemple si vous souhaitez ne déclencher la fermeture d'un store qu'après 3 rafales de vents > 50km/h en 10min : condition 1 "strictement supérieur" "50". Pas de condition 2. Répétition : "3" en "600" secondes.
      * Vous pouvez utiliser les champs de répétitions sans définir de conditions, dans ce cas toutes les valeurs de la commande seront considérées dans la répétition.
      * La condition sera valide pour toutes les occurrences à partir de la limite choisie. Dans l'exemple précédent, les 3ème, 4ème ou 5ème occurrences >50km/h dans les 10min seront considérées comme des déclencheurs valides, et ce même s'il y a aussi eu des occurrences non valide pendant la période.
      * la durée doit être donné en secondes
    * **Durée** et **Nombre de fois en x secondes** sont exclusifs : vous pouvez utiliser l'un ou l'autre mais pas les 2 ensemble

> si votre capteur est susceptible de répéter régulièrement sa valeur, vous pouvez choisir d'ignorer les répétitions (strictement identiques) via l'onglet **Avancé - commandes**. Pour la commande voulue (le nom dans cet onglet correspond au nom que vous avez donné dans l'onglet **Déclenchement conditionné**), cliquez sur le bouton de configuration (engrenage) puis "Configuration" et "Gestion de la répétition des valeurs" puis choisir "Jamais répéter".

* **Condition selon plage temporelle** :

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/PlageTemporelle.png)

  * **Nom** : chaque élément doit avoir un nom unique. Champ obligatoire.
  * Début et fin de **Plage temporelle** : utilisez le sélectionneur de date-heure pour la plage souhaitée. Vous pouvez corriger manuellement si vous souhaitez un horaire plus précis (le plugin est capable d'évaluer à la seconde sur ces champs)
  * **Répéter** : choisir ici la répétition voulue pour votre plage temporelle
    * un ou plusieurs jours de la semaine : le plugin n'utilisera que les heures données dans les champs de début et fin de plage et répétera ces heures aux jours choisis
    * **semaines** : le plugin regardera les jours de la semaine (lundi, mardi,...) des dates début/fin choisies et les répéteras toutes les semaines (aux heures choisies). Par exemple si vous choisissez du lundi 8h au vendredi 18h pour une semaine donnée, ceci sera répété toutes les semaines du lundi au vendredi.
    * **mois** : la répétition sera réalisée tous les mois à la même date de jour que sélectionné. Par exemple si vous choisissez du 1/01/2020 8h au 15/01/2020 18h, la plage sera active tous les mois du 1er à 8h au 15 à 18h du mois courant.
    * **année** : même principe que pour **mois** mais seule l'année sélectionnée est ignorée.
    * (Par définition, vous pouvez donc choisir soit un ou plusieurs jours de la semaine, soit "semaines", soit "mois", soit "année")

* **Conditions type scénario** :

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ConditionScenario.png)

  * **Nom** : chaque élément doit avoir un nom unique. Champ obligatoire.
  * **Condition** :
    * Utilisez les boutons en fin de ligne pour choisir :
      * Une commande
      * Une variable
      * Un scénario
      * Un équipement
    * Écrivez votre condition suivant la syntaxe définie dans la documentation des scénarios
    * Vous pouvez tester votre syntaxe via la fonction core "Outils/Testeur expression" (tout en restant sur la page du plugin)

* **Évaluation**

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/evaluation.png)

Choisir ici les conditions que vous voulez appliquer entre ces différents éléments :
* **ET** : toutes les conditions (valeur, plage temporelle et scenario) doivent être valides pour déclencher la séquence d'action

* **OU** : une seule condition suffit

* **x conditions valides** : seules x parmi vos N conditions doivent être valides pour déclencher la séquence. Par exemple si vous avez plusieurs capteurs de températures et seuls 3 sur 4 doivent être sous un seuil donné pour déclencher une alerte ou le chauffage. Ou pour déclencher un arrosage automatique sans attendre que tous les capteurs soient hors seuils.

* **Séquencement** : vous pouvez ici choisir l'ordre d'arrivée des conditions dans une durée donnée et choisir si toutes les conditions doivent toujours être valides ou non pour le déclenchement effectif. Attention cette fonctionnalité est encore expérimentale, merci de me faire part des problèmes éventuels (avec le log en mode "debug" associé svp) pour que je puisse l'améliorer ;-).
Fonctionnement :
  * Seuls les "déclencheurs" peuvent être utilisés ici (les plages temporelles ou condition "scenarios" peuvent être utilisées en condition de validité mais elles n'ont pas de "date" d'exécution utilisables).
  * Vous devez écrire la condition logique à respecter au format suivant : (@Cond1@<@Cond2@)&&(@Cond2@<=@Cond3@) (Condition 1 puis Condition 2 puis Condition 3, qui peut-être simultanée à la Condition2)
  * Le nom de chaque condition doit être encadré par des @ (n'utilisez pas de @ par ailleurs dans la condition...)
  * Vous pouvez utiliser des () pour déterminer les priorités
  * Vous pouvez utiliser les symboles usuels pour les comparaisons et les conditions (==, >=, <=, <, >, ||(ou), &&(et), !(inversion),...)
  * Vous pouvez utiliser les tags Jeedom des scenarios dans l'expression, notamment #timestamp# pour avoir le timestamp actuel.
  * Vous pouvez définir un délai spécifique entre chaque conditions, par exemple "@btblanc@+30<@btrouge@" pour demander minimum 30 secondes entre l'activation de "@btblanc@" et "@btrouge@"
  * Le champ **Durée maximum** permet de limiter la prise en compte des déclencheurs trop anciens. Toutes les conditions comprises dans le champ **Condition** doivent avoir été validées (= déclenchées et correct) dans la période correspondante. En secondes.
  * Si la case **Toutes conditions toujours valides** est cochée, le plugin évaluera en plus la validité de la totalité des conditions avant de déclencher (toutes les conditions définies, pas uniquement celles utilisées dans la condition)

> Pour vous aider à écrire la condition, voilà le détail du traitement qui sera réalisé par le plugin :
>    * chaque @Condx@ sera remplacée par le timestamp de son dernier déclenchement **valide**.
>    * Puis l'expression complète sera évaluée logiquement, le résultat doit être == 1 pour être valide
>    * Le plugin vérifiera si les timestamps de toutes les conditions **utilisées dans l’expression** respectent le champ timeout.
>    * Si la case "Toutes conditions toujours valides" est cochée, le plugin évaluera en plus la validité de la totalité des conditions (toutes celles définies dans l'onglet, pas uniquement celles utilisées dans la condition)
>    * Si ces 3 conditions sont réunies => la séquence d'action sera déclenchée


* **Condition personnalisée** : vous pouvez ici choisir condition par condition l'évaluation à réaliser. Attention cette fonctionnalité est encore expérimentale, merci de me faire part des problèmes éventuels (avec le log en mode "debug" associé svp) pour que je puisse l'améliorer ;-).
Fonctionnement :
  * Vous devez écrire la condition logique à respecter au format suivant : (@Cond1@<@Cond2@)&&(%Cond2%||%Cond3%) (Condition 1 puis Condition 2 et Condition 2 ou Condition 3 valide)
  * Pour utiliser l'état de validité d'une condition (valide sera égale à 1 et non valide à 0) : encadrez son nom par des %
  * Pour utiliser le timestamp du dernier déclenchement valide d'un déclencheur : encadrez son nom par des @. (Attention, seuls les **Déclencheurs** ont un timestamp associés, les **Conditions** (plage temporelle ou type "scenario" n'en n'ont pas et ne peuvent donc pas être utilisées avec un @)
  * Vous pouvez utiliser des () pour déterminer les priorités
  * Vous pouvez utiliser les symboles usuels pour les comparaisons et les conditions (==, >=, <=, <, >, ||(ou), &&(et), !(inversion),...)
  * Vous pouvez utiliser les tags Jeedom des scenarios dans l'expression, notamment #timestamp# pour avoir le timestamp actuel.
  * Vous pouvez utiliser directement une commande Jeedom dans l'expression, elle sera remplacée par sa valeur, par exemple " #[Developpement][Capteur porte][Etat]#"
  * Vous pouvez utiliser la valeur d'une variable au format "variable(nom_variable)" (sans #)
  * Vous pouvez utiliser directement toutes les opérations des scenarios Jeedom (time_op(), ...)
  * Vous pouvez définir un délai spécifique entre chaque conditions, par exemple "@btblanc@+30<@btrouge@" pour demander minimum 30 secondes entre l'activation de "@btblanc@"" et "@btrouge@"
  * Vous pouvez évaluer "x sur N conditions valides" en utilisant la syntaxe suivante : "%Cond1%+%Cond2%+%Cond3%+%Cond4%+%Cond5%>=3" (sur les 5 conditions données, au moins 3 doivent être valides)
  * Il n'est pas nécessaire d'ajouter le ==1 dans la condition : "#lundis#==1" ou "#lundis#" auront un comportement identiques
  * Si vous voulez tester une condition non valide (==0), cette condition ne pourra pas être le déclencheur

> Pour vous aider à écrire la condition, voilà le détail du traitement qui sera réalisé par le plugin :
>    * chaque @Condx@ sera remplacée par le timestamp de son dernier déclenchement **valide**.
>    * chaque %Condx% sera remplacée par sa validité (0 ou 1)
>    * chaque #xxx# sera remplacé par sa valeur Jeedom
>    * Puis l'expression complète sera évaluée logiquement, le résultat doit être == 1 pour déclencher la séquence d'action

Onglet **Actions**
---

Cet onglet permet de définir les actions de la séquence.

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/OngletActions.png)

Cliquer sur "ajouter une action" pour définir une ou plusieurs actions puis les configurer :
* **Label** : Champs facultatif permettant de lier cette action à une ou plusieurs actions d'annulation. Vous pouvez aussi utiliser ce champ pour personnaliser le tag lié à cette action (#action_label#)
* **Délai avant exécution (min)** :
   * ne pas remplir ou 0 : cette action sera exécutée immédiatement. En cas de multiples déclenchements, ces actions seront déclenchées à chaque appel.
   * délai supérieure à 0 : cette action sera enregistrée dans le moteur de tâches Jeedom (cron) pour une exécution différée selon le délai voulu.
   * le délai doit être saisi par rapport au déclenchement. Si vous souhaitez 3 actions, l'une immédiate puis 10 min après puis 10 min après, il faudra saisir 0, 10 et 20.
   * **Reporter** : permet de définir le comportement de l'action différée dans le cas d'un déclenchement multiple : laisser l'action à sa programmation initiale ou la reporter pour correspondre au dernier déclenchement.
* **Limiter exécution** : permet de limiter la fréquence de l'exécution de cette action. Laisser vide ou 0 pour une exécution systématique. Si vous avez plusieurs actions dont la commande est la même (cas des messages par exemples) et dont vous souhaitez limiter l'exécution, il est souhaitable de saisir un label pour cette action pour éviter les interférences dans le calcul de la dernière date d'exécution.
* **Action** : la commande Jeedom correspondant à l'action voulue. Pour les actions de type "message", vous pouvez utiliser les tags définis ci-dessus. Les actions peuvent être des "mots-clés" Jeedom, pour lancer un scénario ou définir la valeur d'une variable par exemple.

Remarques :
* Dans le cas d'un redémarrage de Jeedom alors que des actions sont enregistrées, les actions seront réalisées dès le lancement de Jeedom (si l'heure de l'action est dépassée) ou à leur programmation prévue.
* Lors de l'enregistrement ou de la suppression de l'équipement, si des actions étaient enregistrées, elles seront supprimées avec un message d'erreur donnant le nom de l'action supprimée
* Les mots-clés spécifiques des scénarios Jeedom comme "pause" ou "attendre" n'auront pas d'effet ici
* Vous pouvez choisir plusieurs actions ayant le même délai, elles seront alors exécutées simultanément après le délai voulu

Onglets **Déclenchement immédiat d'annulation** et **Déclenchement conditionné d'annulation**
---

Ces onglets regroupent les différentes façons d'annuler la séquence d'action.

L'annulation consiste à :
* Annuler la programmation des actions programmées et non exécutées
* Déclencher des actions d'annulation, qui peuvent être conditionnées selon l'exécution précédente d'une **Action** (voir onglet **Actions d'annulation**)

Le fonctionnement et la configuration sont identiques aux onglets **Déclenchement immédiat** et **Déclenchement conditionné** décrits ci-dessus.

Onglet **Actions d'annulation**
---

Cet onglet permet de définir des actions d'annulation de la séquence. Les actions d'annulation sont facultatives selon votre usage.

Par exemple :
* si vous aviez déclenché l'activation d'un appareil avec un délai de 5 min, vous pouvez choisir de couper l'appareil, uniquement s'il a été effectivement déclenché.
* si vous aviez une chaîne de message, vous pouvez choisir d'envoyer un message d'annulation uniquement aux personnes ayant reçu le message initial.

Vous pouvez aussi avoir des actions d'annulation systématiques (non conditionnées).

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/OngletActionsAnnulation.png)

Cliquer sur "ajouter une action" pour définir une ou plusieurs actions d'annulation puis les configurer :
* **Label action de référence** :
   * Choisir dans le menu déroulant le label de l'action de référence de l'onglet **Actions**.
   * Lorsque le label est renseigné et correspond à une action d'alerte, il faut que l'action d'alerte de référence ait été précédemment exécutée pour que la présente action s'exécute.
   * Laissez le champs vide pour exécuter l'action d'annulation sans condition (à chaque déclenchement d'annulation)
* **Limiter exécution** : permet de limiter la fréquence de l'exécution de cette action. Laisser vide ou 0 pour une exécution systématique. Si vous avez plusieurs actions dont la commande est la même (cas des messages par exemples) et dont vous souhaitez limiter l'exécution, il est souhaitable de saisir un "label action de référence" (valide, sinon l'action ne sera jamais exécutée) pour cette action pour éviter les interférences dans le calcul de la dernière date d'exécution.
* **Action** : la commande Jeedom correspondant à l'action voulue. Pour les actions de type "message", vous pouvez utiliser les tags définis ci-dessus. Les actions peuvent être des "mots-clés" Jeedom, pour lancer un scénario ou définir la valeur d'une variable par exemple.

Onglet **Avancé - Commandes Jeedom**
---

Vous pouvez configurer ici les commandes utilisées par ce plugin. Vous pouvez notamment définir la visibilité des boutons de déclenchement et d'arrêt sur le Dashboard Jeedom (visibles par défaut), et la visibilité des valeurs des déclencheurs (non-visibles par défaut, mais historisés). Ainsi que le filtrage de la répétition des valeurs identiques pour vos déclencheurs sur condition de valeur.

Remarques sur le comportement du plugin
======

Au démarrage et après redémarrage Jeedom
---
* Si des actions avaient été programmées pendant la coupure de Jeedom, elles seront exécutées au démarrage (immédiatement si l'heure prévue est dépassée ou à leur heure initialement prévue). Les actions enregistrées ne sont pas perdues par un redémarrage de Jeedom.

Lors d'une sauvegarde (nouvelle configuration ou non)
---
* Toutes les **actions** programmées sont supprimées, avec un message d'erreur pour chacune
* La programmation du déclenchement n'est pas impactée (elle est mise à jour si elle a été modifiée)

En cas de déclenchement multiples
---

* Toutes les actions à déclenchement immédiat seront relancées à chaque déclenchement
* Les actions différées dont la case "reporter" n'est pas cochée ne sont pas décalées, la programmation initiale reste. Ces actions différées ne sont pas non plus multipliées par chaque nouveau déclenchement, seul le délai initialement prévu reste.
* Les actions différées dont la case "reporter" est cochée : la programmation initiale est reportée pour correspondre au nouveau délai.
* Dans le cas d'un nouveau déclenchement après que certaines actions différées aient eu lieu, ces actions seront reprogrammées (à partir de la date courante). Ainsi si vos actions ne sont pas toutes en mode "Reporter", il est possible d'avoir des comportements où l'ordre de déclenchement n'est plus respecté.

Si une annulation est déclenchée sans qu'un déclenchement ait été précédemment initiée
---

Le comportement dépendra de la configuration du plugin :
* si toutes les actions d'annulation sont liées à des labels, alors il ne se passera rien (puisqu'ils n'ont pas été déclenchés précédemment).
* si certaines actions d'annulation ne sont pas conditionnées : elles seront exécutées.

Comportement des crons périodiques
---

Les crons périodiques peuvent être configurées pour déclencher la séquence ou en déclencheur pour l'évaluation des conditions. (Idem pour l'annulation de la séquence). Ils ne sont utilisés nul part d'autre dans le plugin.

Le comportement du core Jeedom concernant ces crons périodiques est le suivant :
* Lors de la 1ere minute après leur création (=sauvegarde de l'équipement), ils seront exécutés (quelque soit la période choisie)
* Il peut arriver qu'ils soient aussi exécutés lors de la prochaine "5min pleine" (12h00 ou 12h05 ou 12h10, ... par exemple)
* Puis le comportement se normalise et ils s'exécutent correctement selon la période voulue

Ceci est un comportement Jeedom que je n'ai pas encore réussi à contourner, toute personne ayant un indice : merci de m'en faire part ;-)

Comportement des évaluations sur la durée
---

Lorsque vous définissez une évaluation sur une durée en minutes, le plugin peut mettre jusqu'à 1 min de moins que choisie car l'évaluation se fera toujours en début de minute. Par exemple si votre déclencheurs se déclenche à 11h50min58s et que vous demandez 3 minutes de validité, la validité sera déclarée à 11h53min02s (et non à 11h53min58s)

Support
===

* Pour toute demande de support ou d'information : [Forum Jeedom](https://community.jeedom.com/c/plugins/automatisation/48) en utilisant le tag "plugin-sequencing" et/ou en taggant mon pseudo (@agp.com) pour que j'en soit informée.
* Pour un bug ou une demande d'évolution, merci de passer de préférence par [Github](https://github.com/AgP42/sequencing/issues)
