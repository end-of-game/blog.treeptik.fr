---
layout: post
title: Comment faire fonctionner cAdvisor sous Debian?
published: true
excerpt: 
author: Yves Dubromelle & Claire Wahl
tags:
- docker
- Debian
- cAdvisor
comments: true
description: "cAdvisor est un outil de monitoring efficace et en temps réel des conteneurs Docker : les utilisateurs ont accès aux ressources (temps CPU, mémoire, etc) consommées par leurs conteneurs. Mais cet outil ne fonctionne pas sous Debian!!"
---
![Comment faire fonctionner cAdvisor sous Debian](/images/cadvisor.png)

Nous utilisons [cAdvisor](https://github.com/google/cadvisor)  pour notre solution de [PaaS dédiée aux applications Java/JEE](http://cloudunit.fr/). Entièrement basé sur [Docker](https://www.docker.com/), CloudUnit permet de surveiller chacun des conteneurs utilisateur grâce à cet outil de monitoring. 

Notre application est initialement bâtie sur une image **Debian**. Pourtant, par défaut, le noyau trop ancien ne supporte pas **Docker**. Pire, une fois ce problème contourné, la gestion de la mémoire par **cgroups** est désactivée. cAdvisor retourne alors une mémoire nulle alors que toutes les autres données sont bien renseignées.

## Qu'est ce que cAdvisor?

cAdvisor est une **solution de monitoring** particulièrement efficace pour surveiller les conteneurs: les utilisateurs ont alors accès aux détails des ressources consommées. Initialement développé par **Google** pour surveiller leurs propres **conteneurs** [Imctfy](http://en.wikipedia.org/wiki/Lmctfy), les données peuvent être visualisées en temps réel via l'UI, récupérées grâce à l'API ou encore stockées en utilisant [InfluxDB](http://influxdb.com/).

## Actualisation du noyau Linux

La dernière version stable de Debian (Wheezy) est fournie avec linux 3.2, qui n'autorise pas l'execution de Docker. Il faut donc aller chercher un noyau plus récent depuis les dépôts backports.
Tout d'abord, il faut inclure ceux-ci dans la liste des sources de paquets:

{% highlight bash %}
bash sudo sh -c "cat deb http://ftp.debian.org/debian/ \
wheezy-backports main non-free contrib >> /etc/apt/sources/list"
{% endhighlight %}

La liste des paquets doit ensuite être mise à jour: `sudo apt-get update`, puis le nouveau noyau installé: `sudo apt-get -t wheezy-backports install linux-image-amd64`. Enfin, il suffit de redémarrer pour que le nouveau noyau soit utilisé.

Mais une fois **Docker** et **CloudUnit** installés, cAdvsisor ne retourne aucunes données sur l'utilisation de la mémoire.


## Cgroups à l'origine du problème

Pour retourner toutes ces données, cAdvisor utilise cgroups, une fonctionnalité du noyau Linux pour mesurer, limiter et isoler l'utilisation des ressources matérielles par des groupes de processus.
Par défaut, le contrôle de la mémoire par cgroups est désactivé sous Debian!
Un bon moyen de s'en rendre compte est de lancer la commande suivante :

{% highlight bash %}
    $ sudo lxc-checkconfig
    Kernel configuration not found at /proc/config.gz; searching...
    Kernel configuration found at /boot/config-3.17.2-031702-generic
    --- Namespaces ---
    Namespaces: enabled
    Utsname namespace: enabled
    Ipc namespace: enabled
    Pid namespace: enabled
    User namespace: enabled
    Network namespace: enabled
    Multiple /dev/pts instances: enabled
    --- Control groups ---
    Cgroup: enabled
    Cgroup clone_children flag: enabled
    Cgroup device: enabled
    Cgroup sched: enabled
    Cgroup cpu account: enabled
    Cgroup memory controller: missing
    Cgroup cpuset: enabled
    --- Misc ---
    Veth pair device: enabled
    Macvlan: enabled
    Vlan: enabled
    File capabilities: enabled
    Note : Before booting a new kernel, you can check its configuration
    usage : CONFIG=/path/to/config /usr/bin/lxc-checkconfig
{% endhighlight %}

En effet, `Cgroup memory controller` est dans l'état `missing`. Il faut maintenant autoriser cgroups à contrôler la mémoire, en passant une option au kernel. En utilisant grub2, ceci est réalisé facilement en ajoutant la ligne:

{% highlight bash %}
GRUB_CMDLINE_LINUX="cgroup_enable=memory"
{% endhighlight %}

au fichier `/etc/default/grub`. Il ne reste plus qu'à exécuter: `sudo update-grub2` et redémarrer.


![Screenshot de CloudUnit](/images/screenshot_monitoring_1.png)

À noter: sur une machine virtuelle, bien que nous n'ayons pas accès au menu grub, cette démarche reste valide.
