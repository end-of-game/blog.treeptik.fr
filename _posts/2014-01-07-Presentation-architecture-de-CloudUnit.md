---
layout: post
title: Presentation de l'architecture de CloudUnit
author: Fabien Amico
tags:
- Cloud
- CloudUnit
published: true
excerpt: 
comments: true
description: CloudUnit est une solution PAAS qui vous permet de déployer et d'administrer des applications Java et Java EE dans le cloud. C’est une solution créée par des développeurs pour simplifier la vie des développeurs. Avec CloudUnit vous pouvez créer vos serveurs d’application, vos bases de données et déployer vos applications d’entreprise en quelques minutes. La plate-forme se compose de trois unités qui collaborent ensemble. Le Manager, les serveurs et les modules. Chaque unité a une responsabilité bien précise.
---

CloudUnit est une solution PAAS qui vous permet de déployer et d'administrer des applications Java et Java EE dans le cloud. C’est une solution créée par des développeurs pour simplifier la vie des développeurs. Avec CloudUnit vous pouvez créer vos serveurs d’application, vos bases de données et déployer vos applications d’entreprise en quelques minutes. La plate-forme se compose de trois unités qui collaborent ensemble. Le Manager, les serveurs et les modules. Chaque unité a une responsabilité bien précise.

### Présentation 
La plate-forme se compose de trois unités qui collaborent ensemble. Le Manager, les serveurs et les modules. Chaque unité a une responsabilité bien précise : 

* **Le Manager** : C’est l’unité qui est responsable de toute les actions d’administration. C’est lui qui crée les autres unités, qui gère les proxy et les DNS. Le manager s’utilise soit au travers de l’application web, soit au travers de l’application en ligne de commande. Les deux outils utilisent l’API REST exposée par le manager. 

* **Le Serveur** : C’est le serveur d’application qui fait fonctionner l’application Java JEE. CloudUnit en supporte plusieurs versions (Tomvat 5, 6, 7, Jboss 6, 7 et d’autre à venir ) mais un seul type de serveur n’est possible pour une application donnée. Le type du serveur est défini au moment de la création de l’application.

* **Les Modules** : C’est tout ce qui sera utilisé en backend par les applications déployées dans CloudUnit. Les bases de données (MySql, MariaDB, PostgreSQL), des systèmes de cache (MemCached), des systèmes clé / valeur comme Redis et bien d’autre à venir. Une application peut avoir plusieurs modules en même temps comme deux bases de données et un système de cache par exemple.



### Architecture simplifiée 

Le schéma ci-dessous présente un aperçu de l’architecture de CloudUnit. 
Le manager reçoit toutes les commandes au travers de l’API REST, il pilote ensuite les serveurs, les modules et le proxy. Les utilisateurs web se connectent ensuite aux applications déployées sur CloudUnit au travers du HA proxy. 

![architecture-cloudunit](https://docs.google.com/drawings/d/1MeJisVAuf0m-iCnSoXSLSxtPXONWO5G-SHBHsvPQK-o/pub?w=946&h=428)


### Cas d’utilisation détaillé 

#### Création et déploiement d’une application 

Ce diagramme décrit comment créer et déployer simplement une application Java EE dans CloudUnit. 

![creation-application](https://docs.google.com/drawings/d/1EWTTgi2WdHeDaZ0GiNPxUxXMx9kCCMoyZi7iEbwU0gI/pub?w=867&h=497)




#### Création de l’application 

1. 1- Le développeur demande la création d’une application en précisant le nom de l’application et le type de serveur d’application. Il peut utiliser soit l’application Web, soit l’application en ligne de commande pour passer ses ordres au manager.
2. 2- Le manager réserve l’espace nécessaire et installe le serveur d’application accompagné d’un repository Git. 
3. 3- La manager déclare la nouvelle application dans le Proxy
4. 4- Le développeur demande ensuite l’ajout d’un module (Ex : Mysql)
5. 5- Le manager réserve l’espace nécessaire et installe le module  

#### Déploiement de l’application 

6. 6- Le développeur demande le déploiement de l’application au format WAR ou EAR en utilisant l’interface web de CloudUnit. 
7. 7- Le manager déploie l’archive sur le serveur d’application
8. 6'- Le développeur pousse le code source de son application avec une commande `git push` sur la branche distante Git mit à sa disposition.
9. 7'- CloudUnit compile le code source, package l’application et la déploie sur le serveur  


#### Déploiement d’une ancienne version de l’application 
A chaque déploiement de l’application avec un `git push` CloudUnit crée un **tag** de la version déployée. Il est ensuite possible de demander au manager de déployer automatiquement un ancien tag. 

![deploiement-tag](https://docs.google.com/drawings/d/1MhQTSiLWzo2XhNk1LMEKzXvO9Rh-t5VFXLcsRUlRTa0/pub?w=946&h=428)


1. 1- Le développeur demande la liste des versions précédemment déployées.
2. 2- Le manager liste les tags depuis git
3. 3- La liste des anciennes versions déployées est retournée à l’utilisateur
4. 4- Le développeur demande le déploiement d’une ancienne version
5. 5- L’ancien tag est remonté, compilé, assemblé puis déployé sur le serveur

