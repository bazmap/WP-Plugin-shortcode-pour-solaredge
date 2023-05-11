# Shortcode pour SolarEdge

Auteur [Arthur Bazin](https://www.arthurbazin.com)

Plugin WordPress pour récupérer et afficher les données de l'API SolarEdge.  
:warning: Ce plugin est NON officiel.

Pour récupérer les données, une clé d'API est nécessaire et disponible depuis [le site de l'API](https://www.solaredge.com/fr/service/support/).


## Documentation

L'utilisation se fait simplement en plaçant le shortcode suivant : ```[sc_solaredge]```

Plusieurs paramètres sont disponibles afin de spécifier les données voulues :


- ```paramètre``` :
  - ```horodatage``` : Renvoi la date et l'heure de récupération des données.
  - ```lien``` : Renvoi un lien vers les données de la commune dont le code INSEE est spécifiée dans les paramètres. A utiliser avec le paramètre : ```texte```.
  - ```co2_eco``` : renvoi un texte contenant la valeur de CO2 économisé.
  - ```so2_eco``` : renvoi un texte contenant la valeur de SO2 économisé.
  - ```nox_eco``` : renvoi un texte contenant la valeur de NOX économisé.
  - ```arbre_plante``` : renvoi un texte contenant le nombre d'arbre économisé.
  - ```ampoule``` : renvoi un texte contenant l'équivalent du nombre d'ampoule éteinte.
  - ```production_totale``` : renvoi un texte contenant la production totale en Wh.
  - ```revenu_total``` : renvoi un texte contenant le revenu total généré.
  - ```production_année``` : renvoi un texte contenant la production de l'année en Wh.
  - ```production_mois``` : renvoi un texte contenant la production du mois en Wh.
  - ```production_jour``` : renvoi un texte contenant la production du jour en Wh.
  - ```production_instantannee``` : renvoi un texte contenant la production instantanée en W.
- ```site``` : permet de spécifier le code du site pour lequel récupérer les données. Par défaut il s'agt du premier site spécifié dans les paramètres.
- ```texte``` : uniquement avec le paramètre ```indicateur=lien```. Texte à afficher dans le lien généré. Par défaut, le texte suivant est utilisé : "Tableau de bord détaillé".
- ```debug``` : utilisé sans valeur, les données bruttes sont renvoyées. Ce paramètre prime sur tous les autres.

Voici quelques exemples d'utilisation du shortcode :

```[sc_solaredge]```
=> equivalent à ```[sc_solaredge site="XXXX" parametre="production_instantannee"]```  

```[sc_solaredge parametre="co2_eco"]```
=>equivalent à ```[sc_solaredge site="XXXX" parametre="co2_eco"]```  

```[sc_solaredge parametre="production_instantannee" debug]```
=>equivalent à ```[sc_solaredge debug]``` (debug prime sur tout autre paramètre).  

```[sc_solaredge indicateur="horodatage"]```  

```[sc_solaredge indicateur="lien" texte="Données pour ma commune"]```


## Quelques captures d'écran

![Ecran de paramétrage](/doc/screenshot-1.png)
![Exemple de rendu](/doc/screenshot-2.png)
![Exemple de mise en forme pour le rendu de la capture précédente](/doc/screenshot-3.png)

