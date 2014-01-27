---
layout: post
title: Configurer Spring Security 3.2 en Java
author: Guillaume Martial
tags:
- Java
- Spring
- Spring Security
published: true
excerpt: 
comments: true
description: Cet article présente un exemple de configuration de <strong>Spring Security 3.2</strong> avec authentification de type JDBC entièrement réalisé en JAVA. L'avantage est que ce type de configuration nous affranchit de l'utilisation de fichiers XML externalisés. Pour l'exemple, nous utiliserons une authentification par formulaire qui ira chercher un utilisateur enregistré dans une base de données MYSQL.
---

Cet article présente un exemple de configuration de **Spring Security 3.2** avec authentification de type JDBC entièrement réalisé en JAVA. L'avantage de ce type de configuration est qu'il nous affranchit de l'utilisation de fichiers XML externalisés. Pour l'exemple, nous utiliserons une authentification par formulaire qui ira chercher un utilisateur enregistré dans une base de données MYSQL.



###Dépendances 

Il faut tout d'abord ajouter les dépendances de **Spring Security** dans le fichier **pom.xml** :

{% highlight xml %}
<dependencies> 
<!-- ... other dependency elements ... --> 
	<dependency> 
		<groupId>org.springframework.security</groupId> 
		<artifactId>spring-security-web</artifactId> 
		<version>3.2.0.RELEASE</version> 
	</dependency> 
	<dependency> 
		<groupId>org.springframework.security</groupId> 
		<artifactId>spring-security-config</artifactId>
 		<version>3.2.0.RELEASE</version> 
	</dependency>
 </dependencies>
{% endhighlight %}

###Configuration de la DataSource

Il faudra avant toute chose créer un objet dataSource, qui permettra à l’utilisateur de se connecter à la base mysql contenant une table Utilisateur (« id », « username », « password », « role_id ») et une classe Role (« id », « description »). Dans cette exemple, on utilisera le pool de connexion [Hikari](http://brettwooldridge.github.io/HikariCP/), un pool ultra rapide.Toutes les informations concernant la connexion à la base de données seront externalisées dans un fichier de properties (application.properties). On crée alors une classe `DataBaseConfiguration` du package org.configuration.conf :

{% highlight java %}
@Configuration
@PropertySource({ "classpath:/application.properties" })
@EnableTransactionManagement
public class DatabaseConfiguration {

@Inject
private Environment env;

@Bean
public DataSource dataSource() {
HikariConfig config = new HikariConfig();
config.setDataSourceClassName(env
.getProperty("datasource.driverclassname"));
config.addDataSourceProperty("url", env.getProperty("datasource.url"));
config.addDataSourceProperty("user",
env.getProperty("datasource.username"));
config.addDataSourceProperty("password",
env.getProperty("datasource.password"));
return new HikariDataSource(config);
}
{% endhighlight %}

###Configuration de Spring Security...

Dans le package org.mycompany.conf, créer une classe `SecurityConfiguration` qui hérite de la classe `WebSecurityConfigurerAdapter`. Celle-ci contient toutes les méthodes nécessaires pour configurer SpringSecurity. La classe `SecurityConfiguration` doit être annotée `@Configuration` pour être prise en charge lors du démarrage du serveur et `@EnableWebSecurity` afin d’activer la sécurité des pages prises en charge par Spring MVC. Les objets Environment et DataSource (faisant référence au bean DataSource créé pour la connexion à la base de données) doivent être ensuite injectés :

{% highlight java %}
@Configuration
@EnableWebSecurity
public class SecurityConfiguration extends WebSecurityConfigurerAdapter {

@Inject
private Environment env;

@Inject
private DataSource dataSource;

//Methodes
//…
}
{% endhighlight %}

###... en 3 méthodes configure()

####configure(HttpSecurity http)
Une première méthode `configure()` qui prend en paramètre un objet HttpSecurity qui va nous permettre de définir les urls interceptées, le type d'authentification souhaité... Dans l’exemple ci-dessous, on configure une authentification par formulaire avec les caractéristiques suivantes :

La page d’authentification login.do contient deux champs nommés j_username et j_password. Cette page est accessible à tous les utilisateurs, connectés ou non. En cas d’échec de l’authentification, l’utilisateur est redirigé sur cette même page et en cas de succès, il accède à la page index.do, qui n’est accessible qu’aux utilisateurs connectés.
L’accès à la page /logout entraîne la déconnexion de l’utilisateur.
Les pages contenues dans le dossier /admin/ sont accessibles uniquement aux utilisateurs ayant le rôle ADMIN.
Attention à ne pas oublier de faire le mapping dans un controller pour accéder en GET aux pages login.do et index.do.

{% highlight java %}
@Override
protected void configure(HttpSecurity http) throws Exception {
http.formLogin().loginPage("/login.do").usernameParameter("j_username")
.passwordParameter("j_password")
.defaultSuccessUrl("/index.do").failureUrl("/login.do")
.and().logout().logoutUrl("/logout")
.deleteCookies("JSESSIONID").permitAll().and().csrf().disable()
.authorizeRequests().antMatchers("/login.do").permitAll()
.antMatchers("/index.do”).denyAll()
.antMatchers("/admin/**").hasRole("ADMIN")
anyRequest()
.authenticated();
}
{% endhighlight %}

####configure(AuthenticationManagerBuilder auth)

Une seconde méthode `configure(AuthenticationManagerBuilder auth)` qui prend en paramètre un objet AuthenticationManagerBuilder, sert à configurer la connexion à la base de données permettant de récupérer l'utilisateur et le rôle qui lui est affecté. La première requête va rechercher l’utilisateur ayant l’username et le password correspondant, et la seconde recherchée son rôle :
{% highlight java %}
	@Override
public void configure(AuthenticationManagerBuilder auth) throws Exception {
auth.jdbcAuthentication()
.dataSource(dataSource)
.usersByUsernameQuery(
"Select email, password, true as enabled from Utilisateur where email=?")
.authoritiesByUsernameQuery(
"Select u.email, r.description From Role r join Utilisateur u on u.role_id=r.id where u.email=?");

}
{% endhighlight %}

####configure(WebSecurity web)

Enfin, il faut ajouter une dernière méthode qui prend en paramètre un objet WebSecurity et qui permet d'enlever certains éléments statiques du contexte de Spring Security via la méthode ignore(). Parmi ces élements, on retrouvera les images, le css , les scripts javascript ainsi que les polices d'écriture :

{% highlight java %}
@Override
public void configure(WebSecurity web) throws Exception {
web.ignoring().antMatchers("/font/**").antMatchers("/images/**")
.antMatchers("/js/**").antMatchers("/css/**");
}
{% endhighlight %}

###La page de login

Il nous faut à présent créer une page contenant un simple formulaire, nommée login.do et placée à la racine du projet qui permettra aux utilisateurs de se connecter à leur application via SpringSecurity et d'accéder aux différentes ressources protégées : 

{% highlight html %}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title> Bienvenue sur ma superpage de login</title>
<meta name="description" content="Connectez-vous">
<meta name="viewport"
content="width=device-width, initial-scale=1, maximum-scale=1">
</head>
<body>
<header > Connectez-vous
</header>
<form action="login.do" class="panel-body" method="POST">
<div>
<label Email</label> <input type="email"
placeholder="test@example.com" 
name="j_username">
</div>
<div class="block">
<label >Mot de passe</label> <input
type="password" id="inputPassword" placeholder="Password"
name="j_password" >
</div>
</div>
<button type="submit" >Connexion</button>
</p>
</form>
</body>
</html>
{% endhighlight %}


###Conclusion :

- L'utilisation d'annotations et de builders permet d'ajouter très facilement divers paramètres de configuration.
- Plus besoin de plusieurs fichiers de configuration XML complexes.



