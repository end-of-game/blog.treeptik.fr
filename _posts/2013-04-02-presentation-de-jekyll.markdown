---
layout: post
title: Présentation de Jekyll
author: Hervé Fontbonne
tags:
- android
- onClick
- mobile
- jekyll
excerpt: 
comments: true
---

###Préambule

Cet article présente un aperçu de l'outil de génération de pages utilisé pour créer ce blog : **jekyll**.
Il introduira les différents composants utilisés pour pouvoir rapidement comprendre le fonctionnement de jekyll.
Quelques outils externes et le langage de balisage **markdown** seront évoqués.
L'installation des outils nécessaires et le déploiement en production ne sont pas abordés ici, beaucoup de tutoriels expliquent déjà bien ses deux parties.

## Pourquoi utiliser Jekyll?


+ Idéal pour un site statique, un blog, un site sans base de données
+ Pour sa simplicité
+ Une plus grande flexibilité que les gestionnaires de contenu classiques (CMS)
+ une mise en page simple
+ Une mise en forme simple: markdow.

## Les chefs d'orchestre :

#### - config.yml :

+ définit les paramètres généraux du site généré.
+ Exemple : extrait de fichier de **config.yml**:
{% highlight html %}
title: "..."
url: "http://..."
author: "..."
email: "emaildecontact"
description: "..."

auto: false 
//configure la regénération des pages si les sources sont modifiées
paginate: 5 
//indique le nombre d'articles par page
paginate_files:
 - index.html

permalink: /:year/:month/:title 
//définit le format du permalink des articles

markdown: rdiscount
pygments: true

exclude:
  - README.markdown
  - _drafts

production_url : ...
{% endhighlight %}

#### - Rakefile :

+ définit les commandes
+ automatise les tâches

## Mise en page - le dossier_layouts

Ce dossier contient les gabarit (*templates*) de base de votre site. Grâce à un en-tête que vous allez rajouter à chacun de vos fichiers (*YAML front matter*). Vous allez définir quel gabarit utiliser pour ce fichier. Cela va donc vous permettre d’utiliser des structures déjà prédéfinies, pré-remplies pour différentes pages. 

+ default.html : gabarit d'index.html et super-gabarit des autres gabarits.  
c'est lui qui va structurer l'ensemble des pages de votre site

+ page.html : page “isolée”
+ post.html : structure les articles (posts)

##Outils

#### - En-tête (YAML front matter)
+ Décrit les paramètres du fichier comme le layout utilisé, le titre, l'auteur...
+ Exemple d'en-tête:

{% highlight html %}
---
layout: post
title: Titre de votre article
published: true
excerpt: 
author: auteur
comments: true
---
{% endhighlight %}

### - DISQUS

un compte *DISQUS* a été créé pour gérer les commentaires sur les articles du blog.

+ code inclu dans la template des posts, post.html:

{% highlight html %}
<div id="disqus_thread"></div>
<script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = 'lepetitnomdevotreblog'; // required: replace example with your forum shortname

    // The following are highly recommended additional parameters. Remove the slashes in front to use.
    // var disqus_identifier = 'unique_dynamic_id_1234';
    var disqus_url = '{{ site.url }}{{ page.url }}';

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
</script>
{% endhighlight %}

+ dans la template "default.html" on précise \{ % include disqus.html % \}
qui renvoie à un fichier du dossier (qui porte bien son nom) **_includes**.
+ Voici le code de ce fichier :
{% highlight html %}
<script type="text/javascript">
  //<![CDATA[
  (function() {
    var links = document.getElementsByTagName('a');
    var query = '?';
    for(var i = 0; i < links.length; i++) {
      if(links[i].href.indexOf('#disqus_thread') >= 0) {
        query += 'url' + i + '=' + encodeURIComponent(links[i].href) + '&';
      }
    }
    document.write('<script charset="utf-8" type="text/javascript" src="http://disqus.com/forums/tjstein/get_num_replies.js' + query + '"></' + 'script>');
  })();
  //]]>
</script>
{% endhighlight %}

### - Index.html
+ Page d'accueil de votre site
+ Présente les contenus les plus récents.

###Le dossier **_post**

+ dossier où sont localisées les articles.
+ Pour générer un fichier dans ce dossier, voici la commande :
`rake post title="Mon super article"`


### Commandes principales
+ Créer un fichier dans le dossier _post :  
	`rake post title="titre de votre article"`

+ Génère le site : `jekyll`  
L'analyse du dossier racine comportant tous les sous dossiers et fichiers est effectuée, le site est généré dans le dossier **_site**


+ Déploie en local le site généré : `jekyll --server`  
Le site est accessible à l'adresse `localhost:4000`.

+ Pour demander un peu d'aide : `jekyll --help`

## Markdown :
Pour écrire les articles de ce blog, nous utilisons le langage **markdown** pour la simplicité de sa syntaxe.
Voici quelques exemples de syntaxe de mise en forme : 

### - Niveaux de titre :
\## Titre niveau 2 =
## Titre niveau 2
\### Titre niveau 3 =
### Titre niveau 3


### - Une liste :
\+ pommes  
\+ poires  
\+ bananes  
donne :  

+ pommes
+ poires
+ bananes


### - Insérer un bloc de code :

**{ % highlight java % }** (sans espace entre { et %).  
Mon bout de code en java   
**{ % endhighlight % }** (sans espace entre % et })

{% highlight java %}
@Stateless
public abstract class GenericService <T extends GenericDAO<E, K>, E extends GenericModel<K>, K> {
	
	protected abstract T getDao() ;
	
	public E create(E entite) throws ServiceException{
		try{
			getDao().create(entite);
		}catch(DAOException e){
			throw new ServiceException(e.getMessage(), e);
		}
		return entite;
	}
{% endhighlight %}


