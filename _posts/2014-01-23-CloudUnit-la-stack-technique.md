---
layout: post
title: CloudUnit, la stack technique
author: Herve Fontbonne
tags:
- Cloud
- CloudUnit
- Technique
published: true
excerpt: 
comments: true
description: Dans cet article c'est l'architecture technique et logicielle utilisée actuellement par <a href="http://www.cloudunit.fr">CloudUnit</a> qui va être décrite.Il a été développé par des développeurs Java pour des développeurs Java.  CloudUnit fournira tous les outils favoris de l'environnement Java, Java EE pour que les utilisateurs conservent leurs habitudes de travail. C'est la plateforme qui s'adapte à l'utilisateur et non l'inverse comme c'est souvent le cas.
---

CloudUnit est un nouveau service de **PaaS**, bientôt disponible, pour déployer et administrer des applications. L'une des forces de ce projet est sa spécialisation, il a été développé par des développeurs Java pour des développeurs Java.  CloudUnit fournira tous les outils favoris de l'environnement Java, Java EE pour que les utilisateurs conservent leurs habitudes de travail. C'est la plateforme qui s'adapte à l'utilisateur et non l'inverse comme c'est souvent le cas.

Mais dans cet article c'est l'architecture technique et logicielle utilisée actuellement par CloudUnit qui va être décrite.

###L'interface Web
Nous avons développé une interface graphique intuitive grâce à un design sobre et clair. D'un point de vue technique, l'application est de type Single Page codée en HTML5 et Javascript. Pour ce dernier, nous nous aidons de la librairie Jquery pour la manipulation du DOM et les requêtes AJAX vers le serveur. Par soucis de productivité, la partie fonctionnelle est organisée autour du framework [MVVM Kendo UI](http://docs.kendoui.com/getting-started/framework/mvvm/overview).  

###La partie serveur
Bien entendu, tout est codé en Java. Côté framework, l'architecture de l'application est basée sur l'une des référence dans ce domaine, [Spring](http://spring.io/), utilisée pour sa simplicité sa modularité avec tous les modules nécessaires pour gérer les différentes parties comme la sécurité, l'AOP, l'accès aux données. 
Pour la persistance des données applicative, [Hibernate](http://hibernate.org/), bien évidemment, quasiment incontournable pour communiquer avec le système de gestion de base de données.
Le projet CloudUnit n'a pas été développé sur le mode du développement piloté par les tests (TDD) ils sont, tout de même, indispensables. **JUnit**  s'occupe des tests unitaires, pour les tests d'intégration, le module de test du framework Spring permet de simuler le contexte et les différentes parties de l'application. 
Le choix du serveur s'est porté sur [Apache Tomcat](http://tomcat.apache.org/) qui répond pour l'instant à nos attentes. 
Pour la partie stockage des données applicatives, le système de gestion de base de données est [MySQL](http://www.mysql.fr/) simple et efficace.
Pour la partie Mailing, les emails transactionnels sont gérés par le service [Mailjet](https://fr.mailjet.com/).

###Les outils indispensables
L'environnement du projet a été automatisé et cadré autant que possible.
[Sonar](http://www.sonarqube.org/) contrôle la qualité logicielle pour détecter rapidement d'éventuels problèmes. 
Ensuite l'intégration continue est assurée par Jenkins permettant l'automatisation des tests, des builds, une rigueur non négligeable et simple d'utilisation via son interface web. Bien sûr, Jenkins est associé avec :
- [Maven](http://maven.apache.org/what-is-maven.html) pour les builds, la gestion des dépendances et l'exécution des tests.
- [Git](http://git-scm.com/) pour la gestion des versions du code source codé par les différents développeurs en local et « poussé » sur un dépôt distant sur Gitlab. 
Un worflow classique a été mis en place avec une branche dev active, une prod avec une version stable validée par le responsable projet et la création de branche pour les mises en place de fonctionnalité plus longues.
Pour le côté matériel, tout est hébergé sur des serveurs Linux chez [Agarik](http://www.agarik.com/), une filiale de [Bull](http://www.bull.fr/). Un choix français pour éviter tout problème législatif et rester avec des acteurs locaux.


###API mise en place : 
L'application expose au final une API REST qui permet de créer des serveurs, déployer des applications et piloter tout CloudUnit. L'application répond en [JSON](http://www.json.org/) le langage d'échange de données génériques. Le choix du couple REST et JSON permet de développer facilement des clients pour de nombreux supports et langages. 
Le framework [Swagger](http://developers.helloreverb.com/swagger/) est utilisé pour documenter, de façon vraiment élégante et pratique, l'API REST exposée. Il met également à disposition une « sandbox » pour pouvoir tester les requêtes directement via l'interface de documentation.
Un outil en ligne de commande est aussi fourni , codé grâce à **Spring Shell**, il permet de scripter et d'automatiser tout les traitements.

###Docker le socle du PAAS
CloudUnit s'appuie, pour fournir les serveurs d'application (**Tomcat**, **Jboss** …) et les systèmes de gestion de base de données, non pas sur un système de virtualisation « classique » mais sur une technologie récente [Docker](https://www.docker.io/). 
Docker a fortement modernisé la technologie LXC (Linux container), des containers sont donc créés par opposition aux machines virtuelles (VM). Les processus de chaque container sont « cloisonnés »  sur l'hôte, on parle de virtualisation « légère ». La grande différence avec les VM est que tous les containers partagent le système d'exploitation (OS) de la machine hôte.
La virtualisation légère a de multiples avantages :
- Grâce à ce partage l'initialisation d'un container est quasi instantanée.
- Les containers n'entraînent pas d'utilisation de ressources inutiles.
- Gestion, sauvegarde et monitoring simplifiés 
- Utilisation d'API spécifique pour piloter Docker (une API a été développée en Java pour que CloudUnit communique avec Docker)

Pour le client cela entraîne un gain de temps et une baisse du prix induit par la diminution des ressources utilisées.

###Conclusion
Les maître mots lors de l'élaboration de CloudUnit ont été simplicité, ergonomie et automatisation pour gagner en productivité en pensant toujours à l'utilisateur final. J'espère que vous aurez autant de plaisir à l'utiliser que l'équipe de Treepik en a eu à le réaliser : )









