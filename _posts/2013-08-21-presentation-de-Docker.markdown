---
layout: post
title: Presentation de Docker
author: Herve FONTBONNE
date: 2013-08-21 12:12:39 +0200
description: Depuis quelque temps, nous utilisons <strong>Docker</strong> pour gérer des containers Linux LXC. Un container peut être vu comme une machine virtuelle basique. Il va permettre de créer un systeme de fichiers isolé qui va partager le kernel de l'OS qui l'héberge.
tags:
- cloud
- virtualisation
---

Depuis quelque temps, nous utilisons [Docker](http://www.docker.io) pour gérer des containers [Linux LXC](http://lxc.sourceforge.net/). Un container peut être vu comme une machine virtuelle basique. Il va permettre de créer un systeme de fichiers isolé qui va partager le kernel de l'OS qui l'héberge.  
Docker est un outil récent, sortie en open source en Mars 2013, il a été développé par [dotCloud](https://www.dotcloud.com).   
DotCloud est un PaaS (Platform As A Service) multi-langage, mais le succès de Docker semble dépassé celui de dotCloud. En effet il connait une reconnaissance grandissante depuis sa sortie et est déjà utilisé dans plus de 150 projets. Voici les principaux:

### Projets utilisant Docker

####Dokku 

[Dokku](https://github.com/progrium/dokku) est un mini-[Heroku](https://www.heroku.com), il se démarque par sa simplicité, il est considéré comme la plus petite implémentation de PaaS, en moins de 100 lignes de bash!

####Flynn

Le créateur de dokku est également le cofondateur de [flynn](https://flynn.io). C'est un peu le "super dokku", sa version commerciale. Docker est utilisé pour la gestion des containers, les "briques" contenant les services disponibles pour la plate-forme.

####CoreOS

[CoreOS](http://coreos.com) est une version la plus allégé possible de linux pour améliorer les performances pour des déploiement massifs de serveurs. Il est compatible notamment avec [vagrant](http://www.vagrantup.com) et Docker.

####Deis

[Deis](http://deis.io) est un PaaS open source qui permet de déployer et scaler des containers LXC, des noeuds Chef ou des buildpacks Heroku.


####CloudUnit

Notre projet! Docker est un des outils utilisé pour développer notre solution de [cloud public](http://www.treeptik.fr/cloudcomputing-devops.html). Il nous permet de gérer la création des containers hébergeant chacun un service (Tomcat, Mysql ...) pour nos clients. Chaque container est une réplique d'un même modèle , d'une même image.



Des sociétés importantes comme eBay, [CloudFlare](https://fr.cloudflare.com) et [Mailgun](http://www.mailgun.com) planifient de l'utiliser en production prochainement.

### Fonctionnement basique de Docker
</br>
Une fois [Docker installé](http://docs.docker.io/en/latest) vous pourrez l'utiliser en ligne de commande. 
La première étape est de lancer un container sur un modèle existant, une image pour le jargon Docker.

{% highlight bash %}
~$ sudo docker run -i -t ubuntu /bin/bash
{% endhighlight %}
</br>
<u>Explication :</u>  
Cette commande lance un container qui est une réplique de l'image ubuntu. L'option -t, alloue un pseudo-tty et -i laisse le stdin ouvert. le container est lancé ave la commande bin/bash.

Un système indépendant de fichier sous ubuntu est créé avec un shell pour executer n'importe quelle commande. Ce container peut ainsi être modifier en installant ce que l'on veut.

Pour sortir et aussi arrêter le container il suffit de taper **`exit`**.

On peut voir l’état de nos containers via la commande :

{% highlight bash %}
~$ docker ps -a
     ID         IMAGE       COMMAND    CREATED     STATUS
675482c7b623 ubuntu:latest /bin/bash 3 minutes ago Exit 0
{% endhighlight %}
</br>

On voit ainsi que notre container ayant pour ID 675482c7b623 à été crée il y a 3 minutes et qu’il est actuellement arrêté.

L'état du container a été sauvegardé à son arrêt .

Pour le relancer et cette fois si en mode "daemon" il suffit d’utiliser la commande run :

{% highlight bash %}
~$ docker start 675482c7b623
675482c7b623
~$ docker ps -a
    ID          IMAGE       COMMAND     CREATED      STATUS
675482c7b623 ubuntu:latest /bin/bash 10 minutes ago Up 2 seconds
{% endhighlight %}
</br>
Si on veut sauvegarder l’état de ce container, pour le cloner ultérieurement la commande à exécuter sera alors :

**docker commit ID_DOCKER NOM_DE_L_IMAGE_CREEE**

Pour l'exemple:
{% highlight bash %}
~$ docker commit 675482c7b623 cloudunit/tomcat
{% endhighlight %}
</br>
Imaginons que nous ayons installé tomcat avant la sauvegarde du container en image. On va à présent lancer une "instance" de cette image:

{% highlight bash %}
docker run -d -p 8080 cloudunit/tomcat /opt/tomcat/bin/catalina.sh
{% endhighlight %}
</br>

<u>A noter :</u>  
L’option -p 8080 qui demande à Docker de "mapper" le port 8080 du container pour qu’il soit accessible depuis l’hôte (et par la même occasion de l’extérieur).  
La commande que l’on va exécuter au lancement,  `/opt/tomcat/bin/catalina.sh`, qui va lancer le serveur tomcat.

On va utiliser la commande suivante pour connaître le "mappage" » du port 8080 :

{% highlight bash %}
~$ docker ps -a
   ID             IMAGE                  COMMAND                   STATUS         CREATED      PORTS
 37f541a064d4 cloudunit/tomcat:latest /opt/tomcat/bin/catalina.sh Up 8 seconds 8 seconds ago 49256->8080
{% endhighlight %}
</br>
ou encore
{% highlight bash %}
~$ docker port 37f541a064d4 8080
49256
{% endhighlight %}
</br>
####Dockerfile Builder
</br>
Docker peut agir comme un constructur et lire les instructions d'un fichier texte nommé **dockerfile** pour automatiser les étapes que l'on doit faire manuellement pour créer une image "évoluée" à partir d'une image de base.

Reprenons l'exemple de la création d'une image comprenant un tomcat, voici les différentes instructions du dockerfile correspondant:

{% highlight bash %}
FROM ubuntu:12.04
RUN wget http://apache.crihan.fr/dist/tomcat/tomcat-7/v7.0.42/bin/apache-tomcat-7.0.42.tar.gz
RUN tar xzf apache-tomcat-7.0.42.tar.gz 
RUN mv apache-tomcat-7.0.42 /opt/
RUN mv /opt/apache-tomcat-7.0.42 /opt/tomcat
EXPOSE 8080
CMD ["/opt/tomcat/bin/catalina.sh","run"] 
{% endhighlight %}
</br>

L'instruction FROM définit l'image sur laquelle les instructions qui suivent doivent se baser. Elle doit être la première instruction du Dockerfile.

L'instruction RUN exécute une commande sur l'image courante et commit le résultat

L'instruction CMD définit la commande exécutée au lancement d'un container clone de cette image.

L'instruction EXPOSE définit les ports qui devront être exposés publiquement.


Pour effectuer un build à partir d'un Dockerfile, la commande à exécuter sera :

{% highlight bash %}
sudo docker build -t cloudunit/tomcat .
{% endhighlight %}
</br>
Le . indique qu'on lit le dockerfile présent dans le répertoire courant.
-t cloudunit/tomcat est le tag assigné à l'image à la fin du build.

Pratiquement toutes les commandes de docker sont executables via une **API REST** dont voici la [page de référence](http://docs.docker.io/en/latest/api/docker_remote_api_v1.4)
Des API pour communiquer en python, ruby, javascript et java ont également été développées.


### En résumé
</br>
 - <u>Isolé et répétable</u>  
Docker permet de créer simplement un environnement isolé et répétable. Un environnement est créé une fois puis sauvegarder et peut ensuite être lancer sur une autre machine. Tout ce qui tourne dans le container est isolé de la machine sous-jacente.

 - <u>Performance</u>  
En terme de performance, tout les containers LXC partagent l'OS de la machine hôte alors qu'une virtualisation traditionnelle entraine un OS par machine.
La taille est donc bien inférieure mais le démarrage ou le reboot est également bien plus rapide au redémarrage de l'OS d'une VM.


![alt text](http://www.docker.io/static/img/about/docker_vm.jpg "Title")
*source: http://www.docker.io/the-whole-story (Figure 9)*

 - <u>Sauvegarde</u>  
La sauvegarde automatique de chaque container créé, avec l'assignement d'un ID pour le redémarrer simplement, est également un atout majeur par rapport aux VM qui si un noeud tombe n'auront pas forcément leur état sauvegardé.





















