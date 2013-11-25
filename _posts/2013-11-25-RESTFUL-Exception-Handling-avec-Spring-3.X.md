---
layout: post
title: RESTFUL Exception Handling avec Spring 3.X
author: Guillaume Martial
tags:
- Spring 
- REST
- Exception
published: true
excerpt: 
comments: true
description: Cet article va aborder la gestion des exceptions avec <strong>Spring</strong> 3.X et création d'un message personnalisé injecté dans le body de la réponse REST pouvant être récupéré par le client.
---

Pour gérer les exceptions levées par une application REST développée avec Spring MVC, plusieurs techniques peuvent être appliquées. 

Il est intéressant de récupérer dans le body de la réponse l'exception précise qui est levée et le HTTP status code de l'erreur, pour l'afficher au client.

####1- Pour chaque controller avec l'annotation @ExceptionHandler

La première méthode, la plus simple, est de définir pour chaque controller le comportement à adopter pour chaque exception levée. Il faut donc cibler le type d'exception au niveau de chaque controller à l'aide d'une méthode annotée avec **@ExceptionHandler**. Cette méthode sera alors active uniquement au niveau du controller dans laquelle elle est définie :

{% highlight java %}
@Controller
public class toDoController{ 
    ... 
    @ExceptionHandler({ Exception1.class, Exception2.class }) 
    public void handleException(Throwable exception, HttpServletResponse response) { 
        ...
    } 
}
{% endhighlight %}

Pour ensuite formater le message contenu dans le body de la réponse, il convient de formater une page error.jsp qui a pour rôle l'affichage du HTTP status code et du message d'erreur. La plus simple à mettre en oeuvre consiste à générer une page jsp qui sera appelée par la servlet de tomcat à chaque fois qu'une exception sera levée.

Dans le web.xml, il faut déclarer la page à appeler pour chaque code erreur et définir le type d'exception à intercepter :

{% highlight xml %}
<web-app> 
... 
<servlet-mapping> 
      <servlet-name>jsp</servlet-name> 
      <url-pattern>/error.jsp</url-pattern> 
   	</servlet-mapping> 
 		 <error-page> 
   			 <error-code>404</error-code> 
  			 <location>/error.jsp</location> 
  		</error-page> 
  
 		<error-page> 
   			 <error-code>405</error-code> 
   			 <location>/error.jsp</location> 
  		</error-page> 
  
 		<error-page> 
   			 <error-code>400</error-code> 
   			 <location>/error.jsp</location> 
 		</error-page> 

		<error-page> 
    		<error-code>500</error-code> 
    		<location>/error.jsp</location> 
  		</error-page> 
  <error-page> 
    <exception-type>java.lang.Exception</exception-type> 
    <location>/error.jsp</location> 
  </error-page> 
... 
</web-app>

{% endhighlight %}

Une page error.jsp, appelée par la Servlet du Tomcat à chaque fois qu'une erreur est levée dans l'application, doit être placée à la racine du classpath. On y affichera le HTTP status code et le message d'erreur levé.

{% highlight xml %}
<%@ page session="false" contentType="application/json" pageEncoding="UTF-8" isErrorPage="true"%> 
{ "status":"<%=request.getAttribute("javax.servlet.error.status_code") %>", 
  "reason":"<%=request.getAttribute("javax.servlet.error.message") %>" 
}
{% endhighlight %}

####2- Pour la totalité de l'application à l'aide du @ControllerAdvice

A partir de **Spring 3.2**, l'apparition de l'annotation **@ControllerAdvice** permet d'appliquer la méthode annotée @ExceptionHandler à l'ensemble des exceptions levées par notre application (quelque soit le controller impliqué).
Pour cela, une classe annotée @ControllerAdvice qui hérite de ResponseEntityExceptionHandler intercepte l'exception spécifiée et formate le message à injecter dans le body de la réponse. Il est possible pour chaque erreur levée d'indiquer le type de HTTP status code souhaité et le message à écrire. Plusieurs méthodes peuvent être définies si l'on souhaite intercepter de manière différente les exceptions succeptibles d'être levées :


{% highlight java %}
@ControllerAdvice 
public class RestHandlerException extends ResponseEntityExceptionHandler { 

	@ExceptionHandler(value = { Exception1.class, Exception2.class }) 
	protected ResponseEntity<Object> handleGlobalException(Exception ex, 
			WebRequest request) { 
		ex.printStackTrace(); 
		return handleExceptionInternal(ex, ex.getLocalizedMessage(), 
				new HttpHeaders(), HttpStatus.INTERNAL_SERVER_ERROR, request); 
	} 



	@ExceptionHandler(value = { NullPointerException.class }) 
	protected ResponseEntity<Object> handleNullPointerException(Exception ex, 
			WebRequest request) { 
		ex.printStackTrace(); 
		return handleExceptionInternal( 
				ex, 
				"An unkown error has occured! Server response : NullPointerException", 
				new HttpHeaders(), HttpStatus.INTERNAL_SERVER_ERROR, request); 
	}
{% endhighlight %}

Toutefois, pour toutes les exceptions survenant côté serveur, il est préférable de spécifier un statut 500 (INTERNAL SERVER ERROR) qui indique au client qu'il y a une erreur sur l'application REST.



