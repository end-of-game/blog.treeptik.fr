---
layout: post
title: Une Google Map avec Primefaces
author: Loïc Carnot
tags:
- Google Map
- p:gmap
- primefaces
- JSF
published: true
excerpt: 
comments: true
description: Primefaces est un DTD (Document Type Definition) qui fournit une suite de composants JSF. Cet surcouche permet d'avoir accès à de nombreux composants supplémentaires, enrichissant ainsi le panel de possiblité déjà fournit par JSF seul. Un de ces composants est le gmap et permet d'incruster une carte Google Map. Cet article propose une implémentation simple de ce composant ainsi que son implémentation lors de l'utilisation d'un template puis au sein d'une pop-up.
---

**Primefaces** est un DTD (Document Type Definition) qui fournit une suite de composants JSF. Cet surcouche permet d'avoir accès à de nombreux composants supplémentaires, enrichissant ainsi le panel de possiblité déjà fournit par JSF seul. Un de ces composants est le **gmap** et permet d'incruster une carte Google Map. Cet article propose une implémentation simple de ce composant ainsi que son implémentation lors de l'utilisation d'un **template** puis au sein d'une **pop-up**.  

### Première utilisation de Gmap 

Le composant Primefaces `<p :gmap>` repose sur l’utilisation de l’API Google Maps version 3. Il est donc nécessaire, avant toute chose, de faire appel à cette API. Idéalement, cette référence à l’API de Google Maps se fait au niveau du head de votre page en y ajoutant le script suivant :

{% highlight html %}

<script src="http://maps.google.com/maps/api/js?sensor=true|false" type="text/javascript"></script

{% endhighlight %}


Vous aurez remarqué qu’un paramètre est passé au sein de l’URL servant de source au script. Ce paramètre est le sensor et prend soit la valeur true ou false (notez qu’ici les 2 valeurs y sont mises mais il faut préciser la valeur true or false dans le cadre de votre utilisation). Ce paramètre spécifie si votre application utilise un sensor comme GPSLocator par exemple.  

Pour ceux qui auraient utilisé des versions plus anciennes de l’API de Google Maps, avec la V3 il n’est plus nécessaire de générer une API key (clé API) afin d’incorporer une gmap à notre application.

Ce script permet de faire appel à l’API Google Map, il est donc maintenant possible d’utiliser le composant de Primefaces `<p:gmap/>`.

4 composants sont obligatoires pour placer une gmap :  
- `center` : coordonnées GPS du centre de la map que l’on désire afficher  
- `zoom` : niveau du zoom de la carte  
- `type` : type de la map (HYBRID,SATELLITE,TERRAIN)  
- `style` : Dimensions de la carte  

exemple de page html:
{% highlight html %}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
	xmlns:h="http://java.sun.com/jsf/html"
	xmlns:f="http://java.sun.com/jsf/core"
	xmlns:ui="http://java.sun.com/jsf/facelets"
	xmlns:p="http://primefaces.org/ui">

<h:head>
	<meta charset="utf-8" />
	<title>Exemple Gmap</title>
	<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
</h:head>
<body>

	<p:gmap center="41.381542, 2.122893" zoom="15" type="HYBRID" style="width:600px;height:400px"/>
</body>
</htlm>
{% endhighlight %}

### Gmap au sein d'un template

Il n’est pas rare d’utiliser un template pour uniformiser l’affichage de notre application et pour éviter le chargement intempestif de composants de la page. On a alors un template général au sein duquel seront chargés des composants spécifiques à la page que l’on désire afficher.  

Dans le cas où notre gmap est un composant spécifique de la page, la référence à l’API de Google Maps se fait toujours au sein du head de notre template général (comme le recommande l’API Google Maps), et notre composant gmap sera chargé de la même manière que dans notre cas simple ci-dessus.  

template.html:
{% highlight html %}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
	xmlns:h="http://java.sun.com/jsf/html"
	xmlns:f="http://java.sun.com/jsf/core"
	xmlns:ui="http://java.sun.com/jsf/facelets">

<h:head>
	<meta charset="utf-8" />
	<title>Exemple Gmap au sein d'un template</title>
	<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
</h:head>
<body>
	<ui:insert name="body" />
</body>
</htlm>
{% endhighlight %}

page.html:
{% highlight html %}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
	xmlns:h="http://java.sun.com/jsf/html"
	xmlns:f="http://java.sun.com/jsf/core"
	xmlns:ui="http://java.sun.com/jsf/facelets"
	xmlns:p="http://primefaces.org/ui">

<ui:composition template="/template.xhtml">
	<ui:define name="body">
		<p:gmap center="41.381542, 2.122893" zoom="15" type="HYBRID" style="width:600px;height:400px"/>
	</ui:define>
<ui:composition>

</htlm>
{% endhighlight %}


### Gmap dans un onglet ou une pop-up
En premier lieu il faut ajouter une balise `script`:
{% highlight html %}
<h:outputScript library="primefaces" name="jquery/jquery.js target="head" />
{% endhighlight %}
En effet, certains composants primefaces ont un comportement (comme notre pop-up) qui repose sur du JQuery.

Lorsque l’on désire afficher une gmap au sein d’un composant préalablement caché sur la page (type onglet ou pop-up), on observe souvent des erreurs d’affichage de la gmap. Afin d’éviter ce désagrément, plusieurs paramètres doivent être renseignés. On va les passer en revue au sein d’un exemple simple d’affichage d’une gmap dans pop-up à l’appui d’un bouton poussoir.  
Tout d’abord, il est nécessaire d’implémenter le bouton et l’affichage d’une pop-up sous l’action d’appui sur ce même bouton. L’implémentation suivante est un exemple de cette implémentation en utilisant deux composants Primefaces : `commandButton` et `dialog`.

{% highlight html %}
<p: commandButton id= "showButton", styleClass="btn btn-large btn-info" value="Afficher Gmap" onclick="dlg.show()"/>

<p:dialog widgetVar="dlg" width="625" height="450" modal="true"/>
{% endhighlight %}
On a alors normalement une pop-up fonctionnelle. 

#### Utilisateur de Bootstrap - conflit en approche...

Cependant, si cette pop-up n'est pas fonctionnelle à cette étape et que vous utilisez le **framework Bootstrap**, il faut désactiver la balise suivante:
{% highlight html %}
<script src="..../bootstrap/js/jquery.js"/>
{% endhighlight %}
Certains composants Primefaces ont un comportement (comme notre pop-up) qui repose sur du JQuery. Or, lors de la demande d'affichage de la pop up, votre application ne sait pas s'il doit utiliser le JQuery de Bootstrap ou de Primefaces. Ce conflit empêche l'affichage de la pop up.
 
Le code html obtenu est alors le suivant:  

{% highlight html %}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
	xmlns:h="http://java.sun.com/jsf/html"
	xmlns:f="http://java.sun.com/jsf/core"
	xmlns:ui="http://java.sun.com/jsf/facelets"
	xmlns:p="http://primefaces.org/ui">

<ui:composition template="/template.xhtml">
	<ui:define name="body">
		<h:outputScript library="primefaces" name="jquery/jquery.js" target="head" />
		<p:dialog widgetVar="dlg" width="625" height="450" modal="true"/>
	</ui:define>
<ui:composition>

</htlm>
{% endhighlight %}

Il suffit maintenant d’ajouter notre balise gmap au sein de la pop-up en précisant les 4 champs obligatoires de la façon suivante :  

{% highlight html %}

<p:dialog widgetVar="dlg" width="625" height="450" modal="true">
	
	<p:gmap center= "41.381542, 2.122893" zoom="15" type="HYBRID" style="width :600px ;height :400px"/>

</p:dialog>

{% endhighlight %}

Les premières erreurs que j’ai pu observer chez certains utilisateurs sont de ne pas préciser le width et le height de la pop-up (ce qui n’est pas vraiment une erreur liée à la Gmap mais à la pop-up). 
Ensuite, si vous tenter d’exécuter le code que je viens de vous fournir, vous remarquerez que la pop-up s’affiche durant un court espace de temps puis disparaît (ou ne s’affiche pas du tout). Il est alors nécessaire d’ajouter l’appel d’une méthode checkResize() lors de l’affichage de la pop-up. Pour se faire, on effectue l’implémentation ci-dessous :  

{% highlight html %}

<p:dialog widgetVar="dlg" width="625" height="450" modal="true" onShow="myMap.checkResize()">
	
	<p:gmap center= "41.381542, 2.122893" zoom="15" type="HYBRID" style="width :600px ;height :400px" widgetVar="myMap"/>

</p:dialog>

{% endhighlight %}

Ainsi, votre pop-up contenant la gmap devrait rester à l’affichage (ou s’afficher si aucun affichage ne s’effectuait précédemment). 
Si vous avez suivi l’implémentation ci dessus et bien ajouté la référence à l’API Google Maps dans le header de votre page, il n’y a pas de raison que votre gmap ne s’affiche pas correctement.

### Avantages :

* Utilisation de l'API Google Maps et de ses fonctionnalités


