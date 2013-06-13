---
layout: post
title: Les logs avec Spring AOP
author: Guillaume Martial
tags:
- Spring AOP
- log
- Java EE
published: true
excerpt: 
comments: true
description: Cet article propose un exemple d'utilisation de <strong>Spring AOP</strong> pour enregistrer l'évènement correspondant à la sauvegarde d'une entité en base de donnée en Spring IOC et JPA (avec une base MySQL). L'exemple propose de créer un objet Client et de l'enregistrer en base de données. Nous utiliserons un tissage dynamique, c'est à dire que l'aspect sera mis implémenter lors de l'exécution du logiciel.
---
  

Il est parfois nécessaire de faire un historique des actions réalisées par un utilisateur d'une application Java EE. La **programmation orientée aspect** est particulièrement bien adaptée à cette problématique dans la mesure où notre système de logs n'apparaîtra pas dans la partie métier de l'application et que l'enregistrement des évènements sera fait de manière automatique, dès que l'utilisateur appelera le service que l'on veut surveiller.

L'article ci-dessous propose un exemple d'utilisation de **Spring AOP** pour enregistrer l'évènement correspondant à la sauvegarde d'une entité en base de donnée en Spring IOC et JPA (avec une base MySQL). L'exemple propose de créer un objet Client et de l'enregistrer en base de données. Nous utiliserons un tissage dynamique, c'est à dire que l'aspect sera implémenté lors de l'exécution du logiciel.

####Le modèle objet : Client.java
{% highlight java %}
package fr.treeptik.models;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;


@Entity
public class Client {

	@Id
	@GeneratedValue(strategy = GenerationType.AUTO)
	private Integer id;

	private String name;
	private String adresse;

	public Integer getId() {
		return id;
	}

	public void setId(Integer id) {
		this.id = id;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public String getAdresse() {
		return adresse;
	}
{% endhighlight %}

####La couche DAO :

###L'interface : ClientDAO.java
{% highlight java %}
package fr.treeptik.dao;

import fr.treeptik.models.Client;

public interface ClientDAO {

	Client save(Client client);
}
{% endhighlight %}
###L'implémentation JPA : ClientJPADAO.java
{% highlight java %}
package fr.treeptik.dao.impl;

import javax.persistence.EntityManager;
import javax.persistence.PersistenceContext;

import org.springframework.stereotype.Repository;

import fr.treeptik.dao.ClientDAO;
import fr.treeptik.models.Client;

@Repository
public class ClientJPADAO implements ClientDAO{
	
	@PersistenceContext
	private EntityManager entityManager;

	// Méthode pour sauvegarder un client en base de donnée

	@Override
	public Client save(Client client) {
		return entityManager.save(client);
		 
	}
}
{% endhighlight %}
####La couche Service : ClientService.java
{% highlight java %}
package fr.treeptik.service;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import fr.treeptik.dao.ClientDAO;
import fr.treeptik.models.Client;

@Service
public class ClientService {

	@Autowired
	private ClientDAO clientDAO;
	
	@Transactional
	public Client save(Client client){
		return clientDAO.save(client);
	}
{% endhighlight %}

####Configuration d'un aspect avec Spring AOC

Nous allons à présent configurer Spring AOC afin de déclencher un évènement à chaque fois que la méthode "save" de notre ClientService retourne un résultat. Pour cela, il faut créer un nouvel aspect et définir des "advices" qui définissent la ou les méthode appelées lors de l'exécution d'un aspect ainsi que des "points-cuts", à savoir la méthode de la couche service à intercepter pour déclencher l'aspect :


###applicationContext.xml :
{% highlight xml %}
<?xml  version="1.0" encoding="UTF-8"?>
<beans xmlns="http://www.springframework.org/schema/beans"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:aop="http://www.springframework.org/schema/aop"
	xmlns:context="http://www.springframework.org/schema/context"
	xmlns:mvc="http://www.springframework.org/schema/mvc" xmlns:jee="http://www.springframework.org/schema/jee"
	xmlns:lang="http://www.springframework.org/schema/lang" xmlns:p="http://www.springframework.org/schema/p"
	xmlns:tx="http://www.springframework.org/schema/tx" xmlns:util="http://www.springframework.org/schema/util"
	xmlns:jdbc="http://www.springframework.org/schema/jdbc" xmlns:security="http://www.springframework.org/schema/security"

	xsi:schemaLocation="http://www.springframework.org/schema/beans http://www.springframework.org/schema/beans/spring-beans.xsd
		http://www.springframework.org/schema/mvc http://www.springframework.org/schema/mvc/spring-mvc.xsd
		http://www.springframework.org/schema/aop http://www.springframework.org/schema/aop/spring-aop.xsd
		http://www.springframework.org/schema/context http://www.springframework.org/schema/context/spring-context.xsd
		http://www.springframework.org/schema/jee http://www.springframework.org/schema/jee/spring-jee.xsd
		http://www.springframework.org/schema/lang http://www.springframework.org/schema/lang/spring-lang.xsd
		http://www.springframework.org/schema/tx http://www.springframework.org/schema/tx/spring-tx.xsd
		http://www.springframework.org/schema/util http://www.springframework.org/schema/util/spring-util.xsd 
		http://www.springframework.org/schema/jdbc http://www.springframework.org/schema/jdbc/spring-jdbc.xsd
		http://www.springframework.org/schema/security http://www.springframework.org/schema/security/spring-security.xsd">


	<context:annotation-config />
	<context:component-scan base-package="fr.treeptik" />

	<!-- CONFIGURATION SPRING -->
		<!-- ... -->
		<!-- ... -->
		<!-- ... -->
		<!-- ... -->
		<!-- ... -->
	<!-- CONFIGURATION SPRING AOP -->

	<!-- Création d'un nouveau bean indiquant la classe qui va gérer nos aspects -->

	<bean id="monitoraspect" class="fr.treeptik.aop.MonitorAspect">
	<property name="clientService" ref="clientService" />
	</bean>

	<!-- Création de l'aspect référencé au niveau de la classe MonitorAspect.java, appelé au niveau du point-cut "clientService" à chaque fois que la méthode 		"save" retourne un résultat -->

	<aop:config>
		<aop:aspect id="myAspect" ref="monitoraspect">

		<!-- L'expression "expression="execution(* fr.treeptik.service.*.save(..)" signifie qu'un point-cut (interception) se fera au niveau de la méthode 			"save" de toutes les classes contenues dans le package "fr.treeptik.service -->

			<aop:pointcut id="businessService"
				expression="execution(* fr.treeptik.service.*.save(..)) " />

		<!-- APrès l'appel de la méthode save, la méthode afterReturning est appelée et le résultat de la méthode save (result) lui est passé en argument -->

			<aop:after-returning pointcut-ref="businessService"
				method="afterReturning" returning="result" />

		</aop:aspect>
	</aop:config>

	<!-- ... -->
	<!-- ... -->
	<!-- ... -->
	<!-- ... -->

</beans>
{% endhighlight %}
Nous allons à présent créer la classe MonitorAspect dans le package fr.treeptik.aop qui contient la méthode afterReturning qui sera appelée à chaque fois que la méthode save, définie précédemment, aura retourné un résultat :

###MonitorAspect.java :
{% highlight java %}
package fr.treeptik.aop;

import org.aspectj.lang.JoinPoint;
import org.aspectj.lang.JoinPoint.StaticPart;

import fr.treeptik.models.Client;

public class MonitorAspect {

private ClientService clientService;

public void afterReturning(StaticPart staticPart, Object result){

		// Si le résultat est une instance de l'objet Client,
		// on récupère ce client

			if(result instanceof Client){
			
			Client client = (Client) result;

		// On affiche un log dans la console pour préciser que le client a été enregistré

			System.out.println("Client enregistré - ID n° : " + client.getId() );

		// Appels d'autres méthodes, pour enregistrer, par exemple la log en base de donnée en récupération l'instance de l'utilisateur en session et ainsi 			// savoir quel utilisateur à appeler cette méthode
		
		// Getters et setters
				
		// Méthodes
		//
		// .........
		//

			}
		
	}

}
{% endhighlight %}
Vous pouvez encore améliorer ce modèle très simple de log en créant, par exemple, un advice en cas de levée d'une exception grâce à une méthode "afterThrowing".   

###Dépendance Maven
Pour les projets utilisant Maven, la dépendance à ajouter est:
{% highlight xml %}
		<dependency>
			<groupId>org.aspectj</groupId>
			<artifactId>aspectjweaver</artifactId>
			<version>1.7.0</version>
		</dependency> 
{% endhighlight %}

####Avantages :

- Le système de log est complètement détaché de la couche service.
- L'aspect est appelé de façon automatique à chaque fois que le point-cut est appelé.
- Un même aspect peut s'appliquer à des méthodes situées dans des classes différentes d'un même package, il suffit ensuite d'imposer des conditions sur l'objet retourné par la méthode interceptée dans nos advices.
