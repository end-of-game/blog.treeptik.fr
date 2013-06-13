---
layout: post
title: Le generic DAO oui mais avec un generic service
published: true
excerpt: 
author: Fabien Amico
tags:
- java
- developpement
comments: true
description: Très souvent on trouve sur les projets l'implémentation du <strong>design pattern GenericDAO</strong> qui permet de ne pas répéter le code des méthodes CRUD pour l'ensemble des entités. Très bien mais dans une architecture en couche de service (qu'on retrouve encore très souvent dans les projets) ce pattern doit être couplé avec un <strong>GenerciService</strong> si on ne veut pas répéter le <em>"glue code"</em> si problématique dans les services.
---

Très souvent on trouve sur les projets l'implémentation du **design pattern GenericDAO** qui permet de ne pas répéter le code des méthodes CRUD pour l'ensemble des entités. Très bien mais dans une architecture en couche de service (qu'on retrouve encore très souvent dans les projets) ce pattern doit être couplé avec un **GenerciService** si on ne veut pas répéter le *"glue code"* si problématique dans les services.  

### Implémentation 

Ici nous allons voir une implémentation avec des *EJB Session* mais vous pouvez tout à fait l'adapter avec d'autre framework d'injection comme Spring par exemple.

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
	
	public E update(E entite) throws ServiceException{
		E updatedEntite = null;
		try{
			updatedEntite = getDao().update(entite);
		}catch(DAOException e){
			throw new ServiceException(e.getMessage(), e);
		}
		return updatedEntite;
	}
	
	public List<E> findAll() throws ServiceException{
		List<E> findAll = null;
		try{
			findAll = getDao().findAll();
			return findAll;
		}catch(DAOException e){
			throw new ServiceException(e.getMessage(), e);
		}
	}
	
	public E findById(K id) throws ServiceException{
		try{
			return getDao().findById(id);
		}catch(DAOException e){
			throw new ServiceException(e.getMessage(), e);
		}
	}

	public void remove(K id) throws ServiceException{
		try{
			getDao().remove(id);
		}catch(DAOException e){
			throw new ServiceException(e.getMessage(), e);
		}
	}
	
}


{% endhighlight %}

### Avantages :

* Classe réutilisable.
* Factorise le CRUD dans la couche de service.
* Evite de répéter le même code dans chaque classe.


