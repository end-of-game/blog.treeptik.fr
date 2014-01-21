---
layout: post
title: Correction de “cannot simultaneously fetch multiple bags”
author: Guillaume Martial
tags:
- JPA
- Hibernate
published: true
excerpt: 
comments: true
description: Avec <strong>JPA 2.0</strong> et <strong>Hibernate</strong>, une erreur très récurrente peut survenir lorsque l'on utilise une requête de récupération d'un objet parent avec au moins deux listes de noeuds fils, <strong>“cannot simultaneously fetch multiple bags”</strong>.
---

###Le problème :

Avec **JPA 2.0** et **Hibernate**, une erreur très récurrente peut survenir lorsque l'on utilise une requête de récupération d'un objet parent avec au moins deux listes de noeuds fils.

En effet, si l'on considère le mapping classique suivant : 
{% highlight java %}
@Entity
public Parent implements Serialisable {

 @Id
 @GeneratedValue(strategy=GenerationType.AUTO)
 private Long id;

 @OneToMany(mappedBy="parent")
 private List<Child> children;

 @OneToMany(mappedBy="parent")
 private List<Animal> animals;

  // Getters and Setters

}
{% endhighlight %}
La requête JPQL suivante : 

{% highlight sql %}
Select distinct p from Parent p left join fetch p.children c left join fetch p.animals a
{% endhighlight %}
conduit à l'erreur suivante : 
{% highlight java %}
org.hibernate.loader.MultipleBagFetchException : cannot simultaneously fetch multiple bags
{% endhighlight %}
###La solution :

Une des solutions envisageable est d'utiliser des `SET` à la place des `LIST`. Ainsi :
{% highlight java %}
@Entity
public Parent implements Serialisable {

 @Id
 @GeneratedValue(strategy=GenerationType.AUTO)
 private Long id;

 @OneToMany(mappedBy="parent")
 private Set<Child> children;

 @OneToMany(mappedBy="parent")
 private Set<Animal> animals;

  // Getters and Setters

}
{% endhighlight %}
