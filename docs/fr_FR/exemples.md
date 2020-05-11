Exemples d'utilisation
===

Réveil
---

* **Déclenchement** :

Séquence programmée tous les matins à 6h, les jours de semaines, hors jours fériés (pour un reveil à 7h):

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ExOngletTriggerReveil.png)

* **Actions** :

  * Immédiatement : changer le thermostat pour baisser le chauffage dans les chambres et l'augmenter dans les pièces de vie (label : thermostat)
  * Délai 60 min : allumer progressivement la lumière (label : lumière)
  * Délai 60 min : ouvrir les volets (label : volets)
  * Délai 65 min : activer la machine à café (label : café)

* **Déclenchement d'annulation** :

Pour les matins difficiles : un bouton sur la table de nuit pour annuler la séquence :

![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ExTriggerAnnulationReveil.png)

* **Actions d'annulation** :

  * Si "thermostat" : remettre le thermostat en "nuit"
  * Si "lumière" : couper la lumière
  * Si "volets" : fermer les volets
  * Si "café" : couper la machine à café

Gestion arrosage automatique
---

Vous avez 5 capteurs d'humidité dans votre jardin et le plugin Weather

* **Déclenchement** :

  * Définir vos 5 capteurs avec pour chacun, un seuil minimum et éventuellement une durée minimum de 20 min sous ce seuil pour être valide (**Déclencheur selon valeur et répétition**) :
![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ExArrosage5Humid.png)
  * Puis choisir comme évaluation "x conditions valides suffisent" et choisir par exemple 3 capteurs sur les 5

Un peu plus complexe : avec 5 capteurs et des conditions sur la météo, les heures de levé/couché du soleil et la durée d'arrosage déjà effectuée dans la journée :
  * Définir vos 5 capteurs avec pour chacun, un seuil minimum et éventuellement une durée minimum de 20 min sous ce seuil pour être valide (**Déclencheur selon valeur et répétition**) :
![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ExArrosage5Humid.png)
  * Ajouter la condition "la météo prévoit de la pluie" (**Déclencheur selon valeur et répétition** avec les infos du plugin Weather)
![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ExArrosagePluie.png)
  * Ajouter les conditions "le soleil va se coucher dans moins de 15 min" (**Condition type scénario** avec time_op()) et "le soleil n'est pas encore levé"
  * Ajouter la condition sur "l'arrosage déjà déclenché plus de 4h aujourd'hui" (**Condition type scénario** avec duration() ou durationBetween())
![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ExArrosageConditions.png)

  * Puis choisir comme évaluation :
    * "Condition personnalisée" et donner comme condition "((%humid1%+%Humid2%+%Humid3%+%Humid4%+%Humid5%)>=3)&&%couché soleil%&&%Levé soleil%&&%Moins4hArrosageAujourdhui%&&!%Pluie aujourdhui%"
![](https://raw.githubusercontent.com/AgP42/sequencing/dev/docs/assets/images/ExArrosageEvaluation.png)
    * Explication de la condition :
      * "((%humid1%+%Humid2%+%Humid3%+%Humid4%+%Humid5%)>=3)" : sur ces 5 conditions là, vous en voulez au moins 3 valides (=1) (peut importe lesquelles)
      * "&&%couché soleil%&&%Levé soleil%&&%Moins4hArrosageAujourdhui%" : ces 3 conditions doivent être valide (=1)
      * "&&!%Pluie aujourdhui%" : et cette condition doit être fausse (vous ne voulez **pas** de pluie)

Et si vous voulez ajouter un bouton de déclenchement manuel en parallèle? Définissez votre bouton en déclencheur, puis dans la condition personnalisée "||%Bouton%". Il suffira alors que le bouton soit valide pour bypasser les autres conditions.

* **Actions** :

  * Immédiatement : lancer l'arrosage automatique

* **Déclenchement d'annulation** :

* Evaluation en OU des conditions :
  * Si la météo prévoit de la pluie (**Déclencheur selon valeur et répétition** avec les infos du plugin Weather)
  * Si le soleil va se coucher dans moins de 15 min (**Condition type scénario** avec time_op())
  * Si l'arrosage a déjà été déclenché plus de 4h aujourd'hui (**Condition type scénario** avec duration() ou durationBetween())
  * Et ajouter un programmateur de type cron toutes les 15 minutes pour évaluer ces conditions (les "Condition type scénario" et "Condition selon plage temporelle" pas des déclencheurs mais juste des conditions) (**Déclencheur selon programmation**)

* **Actions d'annulation** :

  * couper l'arrosage
