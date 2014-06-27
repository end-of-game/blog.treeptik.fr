---
layout: post
title:  LightAdmin et Cloudunit, déploiement rapide d'applications web
author: Guillaume Martial
tags:
- Java
- Spring
- JPA
- Hibernate
- LightAdmin
- Generator
- Cloudunit
- Cloudcomputing
published: true
excerpt: 
comments: true
description: Exemple de mise en oeuvre du framework de génération de projet de démonstration en Spring/JPA avec un déploiement sur le Paas Cloudunit de Treeptik.
---

###Présentation du framework

LightAdmin (http://lightadmin.org/) est un framework permettant de développer très rapidement des applications Spring/JPA avec une configuration minimaliste. Le développeur doit uniquement renseigner les entités qu'il souhaite manipuler, les liens existants entre elles, et le framework se charge de générer les classes nécessaires à une véritable interface d'administration permettant de manipuler ces objets (opérations du CRUD, recherche, filtres, formulaire de validation...). Le framework gère aussi les accès de cette interface d'administration grâce à Spring Security.

###Mise en oeuvre d'un projet LightAdmin

L'intérêt premier de LightAdmin pour le développeur est qu'il peut rapidement manipuler tous les objets qu'il souhaite utiliser dans son application en créant uniquement les entités d'une part et en configurant l'affichage dans l'interface web de ces entités d'autre part. Tout le code lié à l'accès aux données (requêtes, transactions), aux services et aux controllers est généré au runtime par le framework. De plus, LightAdmin peut s'intégrer très facilement dans une application existante, pour visualiser par exemple toutes les entités présentes en base et pouvoir les manipuler en dehors du contexte métier de l'application.

Pour se faire, il faut tout d'abord ajouter la dépendance dans le fichier pom.xml (Nous allons travailler ici avec une base de données MySQL) :

{% highlight xml %}

<repositories>
	<repository>
		<id>lightadmin-nexus-releases</id>
		<url>http://lightadmin.org/nexus/content/repositories/releases</url>
		<releases>
			<enabled>true</enabled>
			<updatePolicy>always</updatePolicy>
		</releases>
	</repository>
</repositories>


<dependencies>

	<dependency>
		<groupId>org.lightadmin</groupId>
		<artifactId>lightadmin</artifactId>
		<version>1.0.0.M2</version>
	</dependency>

	<dependency>
		<groupId>commons-dbcp</groupId>
		<artifactId>commons-dbcp</artifactId>
		<version>1.4</version>
	</dependency>

	<dependency>
		<groupId>mysql</groupId>
		<artifactId>mysql-connector-java</artifactId>
		<version>5.1.22</version>
		<scope>compile</scope>
	</dependency>

	<dependency>
		<groupId>org.hibernate</groupId>
		<artifactId>hibernate-entitymanager</artifactId>
		<version>4.2.6.Final</version>
	</dependency>

</dependencies>

{% endhighlight %}

Dans le fichier web.xml (/WEB-INF/web.xml), il faut ensuite activer le module LightAdmin : 

{% highlight xml %}

<?xml version="1.0" encoding="UTF-8"?>
<web-app xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://java.sun.com/xml/ns/javaee" xmlns:web="http://java.sun.com/xml/ns/javaee/web-app_2_5.xsd" xsi:schemaLocation="http://java.sun.com/xml/ns/javaee http://java.sun.com/xml/ns/javaee/web-app_3_0.xsd" version="3.0">

<display-name>CloudUnit</display-name>

	<context-param>
		<param-name>light:administration:base-url</param-name>
		<param-value>/admin</param-value>
	</context-param>

	<context-param>	
		<param-name>light:administration:security</param-name>
		<param-value>true</param-value>
	</context-param>

	<context-param>
		<param-name>light:administration:base-package</param-name>
		<param-value>fr.treeptik.conf</param-value>
	</context-param>

	<context-param>
		<param-name>contextConfigLocation</param-name>
		<param-value>classpath:/spring/spring-context.xml</param-value>
	</context-param>

	<listener>
		<listener-class>org.springframework.web.context.ContextLoaderListener</listener-class>
	</listener>

</web-app>

{% endhighlight %} 

Dans le fichier spring-context.xml (à placer dans 'src/main/resources/spring/'), on peut à présent configurer JPA avec l'implémentation de notre choix (Hibernate, EclipseLink, OpenJpa). On utilisera ici Hibernate avec une datasource MySQL: 

{% highlight xml %}

<?xml version="1.0" encoding="UTF-8"?>
<beans xmlns="http://www.springframework.org/schema/beans"	xmlns:p="http://www.springframework.org/schema/p" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"	xmlns:jdbc="http://www.springframework.org/schema/jdbc" xmlns:context="http://www.springframework.org/schema/context"	xsi:schemaLocation="http://www.springframework.org/schema/beans http://www.springframework.org/schema/beans/spring-beans-3.1.xsd		  http://www.springframework.org/schema/jdbc http://www.springframework.org/schema/jdbc/spring-jdbc.xsd http://www.springframework.org/schema/context http://www.springframework.org/schema/context/spring-context.xsd">


	<bean id="transactionManager" class="org.springframework.orm.jpa.JpaTransactionManager">
		<property name="entityManagerFactory" ref="entityManagerFactory" />
	</bean>


	<bean id="dataSource" class="org.apache.commons.dbcp.BasicDataSource">
		<property name="driverClassName" value="com.mysql.jdbc.Driver" />
		<property name="url" value="jdbc:mysql://mysql1:3306/lightadmin" />
		<property name="username" value="adminnc0v40qf" />
		<property name="password" value="tos2tuio" />
	</bean>

	<bean id="entityManagerFactory"class="org.springframework.orm.jpa.LocalContainerEntityManagerFactoryBean">
		<property name="dataSource" ref="dataSource" />	
		<property name="packagesToScan" value="fr.treeptik.model" />
		<property name="jpaVendorAdapter">
		<bean class="org.springframework.orm.jpa.vendor.HibernateJpaVendorAdapter">
			<property name="showSql" value="true" />
			<property name="generateDdl" value="true" />
			<property name="databasePlatform" value="org.hibernate.dialect.MySQLDialect" />
		</bean>

		</property>
	</bean>

</beans>

{% endhighlight %}

On va ensuite créer deux entités simples : User et Project :

* Project.java dans le package fr.treeptik.model  :

{% highlight java %}

package fr.treeptik.model;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;

@Entity
public class User {
	@Id	
	@GeneratedValue(strategy = GenerationType.AUTO)	
	private Integer id;
	private String firstname;
	private String lastname;

	public Integer getId() {
		return id;	
	}

	public void setId(Integer id) {		
		this.id = id;	
	}

	public String getFirstname() {		
		return firstname;	
	}

	public void setFirstname(String firstname) {		
		this.firstname = firstname;	
	}

	public String getLastname() {		
		return lastname;	
	}

	public void setLastname(String lastname) {		
		this.lastname = lastname;	
	}
}

{% endhighlight %}

* Project.java dans le package fr.treeptik.model  :

{% highlight java %}

package fr.treeptik.model;

import java.util.List;

import javax.persistence.Entity;
import javax.persistence.GeneratedValue;
import javax.persistence.GenerationType;
import javax.persistence.Id;
import javax.persistence.OneToMany;

@Entity
public class Project {

	@Id	
	@GeneratedValue(strategy = GenerationType.AUTO)
	private Integer id;
	private String name;

	@OneToMany	
	private List<User> user;

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

	public List<User> getUser() {		
		return user;	
	}

	public void setUser(List<User> user) {		
		this.user = user;	
	}
}

{% endhighlight %}

Enfin, on configure ce que l'on choisit d'afficher dans l'interface d'administration dans des classes héritant de l'objet AdministrationConfiguration : 

* ProjectAdministration.java : 

{% highlight java %}

package fr.treeptik.conf;

import org.lightadmin.api.config.AdministrationConfiguration;
import org.lightadmin.api.config.builder.EntityMetadataConfigurationUnitBuilder;
import org.lightadmin.api.config.builder.FieldSetConfigurationUnitBuilder;
import org.lightadmin.api.config.builder.ScreenContextConfigurationUnitBuilder;
import org.lightadmin.api.config.unit.EntityMetadataConfigurationUnit;
import org.lightadmin.api.config.unit.FieldSetConfigurationUnit;
import org.lightadmin.api.config.unit.ScreenContextConfigurationUnit;

import fr.treeptik.model.Project;

public class ProjectAdministration extends AdministrationConfiguration<Project> {

	public EntityMetadataConfigurationUnit configuration(
			EntityMetadataConfigurationUnitBuilder configurationBuilder) {
		return configurationBuilder.nameField("name").build();
	}

	public ScreenContextConfigurationUnit screenContext(
			ScreenContextConfigurationUnitBuilder screenContextBuilder) {
		return screenContextBuilder.screenName("Users Administration").build();
	}

	public FieldSetConfigurationUnit listView(
			final FieldSetConfigurationUnitBuilder fragmentBuilder) {
		return fragmentBuilder.field("user").caption("Utilisateurs")
				.field("name").caption("nom").build();
	}

}

{% endhighlight %}

* UserConfiguration.java :

{% highlight java %}

package fr.treeptik.conf;

import org.lightadmin.api.config.AdministrationConfiguration;
import org.lightadmin.api.config.builder.EntityMetadataConfigurationUnitBuilder;
import org.lightadmin.api.config.builder.FieldSetConfigurationUnitBuilder;
import org.lightadmin.api.config.builder.ScreenContextConfigurationUnitBuilder;
import org.lightadmin.api.config.unit.EntityMetadataConfigurationUnit;
import org.lightadmin.api.config.unit.FieldSetConfigurationUnit;
import org.lightadmin.api.config.unit.ScreenContextConfigurationUnit;

import fr.treeptik.model.User;

public class UserAdministration extends AdministrationConfiguration<User> {

	public EntityMetadataConfigurationUnit configuration(
			EntityMetadataConfigurationUnitBuilder configurationBuilder) {
		return configurationBuilder.nameField("firstname").build();
	}

	public ScreenContextConfigurationUnit screenContext(
			ScreenContextConfigurationUnitBuilder screenContextBuilder) {
		return screenContextBuilder.screenName("Users Administration").build();
	}

	public FieldSetConfigurationUnit listView(
			final FieldSetConfigurationUnitBuilder fragmentBuilder) {
		return fragmentBuilder.field("firstname").caption("Nom")
				.field("lastname").caption("Prénom").build();
	}

}

{% endhighlight %}

Enfin, si on choisit d'activer la sécurité sur la page d'administation, il suffit d'ajouter un fichier users.properties à la racine du classpath (dans 'src/main/resources') afin de créer un utilisateur admin :

{% highlight properties %}

#<username>=<SHA-1 of password>,<is-active-user>,<role>
admin=d033e22ae348aeb5660fc2140aec35850c4da997,enabled,ROLE_ADMIN

{% endhighlight %}

Enfin, il suffit juste de déployer le projet avec maven et de déployer le war dans CloudUnit. Avec le client shell, on se connecte aux serveurs Cloudunit et on crée une nouvelle application web basée sur tomcat 7 :

{% include image-fancy.html url="/images/post/IMAGE1.png" %}

On ajoute une base de données MySQL à l'application :

{% include image-fancy.html url="/images/post/IMAGE2.png" %}

On affiche ensuite les informations de la datasource afin de renseigner notre datasource :

{% include image-fancy.html url="/images/post/IMAGE3.png" %}

On déploie l'application dans le serveur grâce à la commande deploy:

{% include image-fancy.html url="/images/post/IMAGE4.png" %}

L'application est désormais accessible sur l'url http://lightadmin-johndoe-anonymous.cloudunit.dev/admin (login : admin, password : admin)

{% include image-fancy.html url="/images/post/IMAGE5.png" %}

L'interface d'administration permet d'afficher les objets User et Project, et le nombre d'entrées de chacun en base : 

{% include image-fancy.html url="/images/post/IMAGE6.png" %}

On peut créer des entités, les lister, les éditer, les supprimer. Le framework gère parfaitement les relations entre objets (ici, l'objet Project possède une liste d'Users, qu'on peut renseigner en piochant dans la liste des Users existants : 

{% include image-fancy.html url="/images/post/IMAGE7.png" %}

{% include image-fancy.html url="/images/post/IMAGE8.png" %}

{% include image-fancy.html url="/images/post/IMAGE9.png" %}

###Les apports de LightAdmin :

* Rapide à prendre en main (configuration minimaliste), idéal pour visualiser un modèle objet, les relations entre les entités au travers d'une interface graphique intuitive et épurée
* Tout le “glue code” est généré de façon automatique. L'utilisateur ne crée que les entités et renseigne les données à afficher dans les classes de configuration de l'inteface
* Facilement intégrable (pluggable) à une application Spring/JPA existante pour visualiser les entités et les données en base en dehors du contexte métier
* Parfaitement adapté au Paas CloudUnit avec une mise à disposition quasi instantanée de votre application en ligne.

[Récupérer le projet d'exemple sur GitHub](https://github.com/Treeptik/LightAdminSample)

[Visualiser le projet déployé sur notre plateforme](http://lightadmin-gmartial-treeptik.cloudunit.io/admin/)



