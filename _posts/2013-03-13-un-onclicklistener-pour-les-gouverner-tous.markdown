---
layout: post
title: Un onClickListener pour les gouverner tous !!
author: Loïc Carnot
tags:
- mobile
- android
published: true
excerpt: 
comments: true
description: Lors du développement d'applications <strong>Android</strong>, il est très vite nécessaire de définir des actions lors du click de souris sur différents composants de notre application. Pour se faire, on utilise une méthode "onClick" fournie par l'interface <strong>onClickListener</strong>. Dans de nombreux cas on définit cette méthode de façon anonyme pour chaque composant implémentant la méthode "onClick". Cette surcharge inutile du code peut être évitée afin d'obtenir un code plus lisible.
---

Lors du développement d'applications **Android**, il est très vite nécessaire de définir des actions lors du click de souris sur différents composants de notre application. Pour se faire, on utilise une méthode "onClick" fournie par l'interface **onClickListener**. Dans de nombreux cas on définit cette méthode de façon anonyme pour chaque composant implémentant la méthode "onClick". Cette surcharge inutile du code peut être évitée afin d'obtenir un code plus lisible.  

### Implémentation 

Cet article propose, au travers d'une application simple comprenant 3 boutons , d'illustrer l'implémentation de cette solution.

L'activité principale, appelée Frodo , doit implémenter l'interface *onClickListener* afin de pouvoir ajouter des listeners aux 3 boutons de notre application, mais aussi de définir la méthode *"onClick"*. Voici le code de notre activité Frodo:

{% highlight java %}

public class Frodo extends Activity implements OnClickListener{

	private TextView textView;
	private Button gandalf;
	private Button legolas;
	private Button aragorn;
	
	
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_frodo);
				
		//on associe les boutons disposés dans le layout aux instances créées ci-dessus 
		gandalf = (Button) findViewById(R.id.button1);
		aragorn = (Button) findViewById(R.id.button2);
		legolas = (Button) findViewById(R.id.button3);
		
		//on ajoute un onClickListener à chacun des boutons
		gandalf.setOnClickListener(this);
		aragorn.setOnClickListener(this);
		legolas.setOnClickListener(this);
		
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.frodo, menu);
		return true;
	}

	@Override
	public void onClick(View v) {
			
		// Objet Bundle pour transmettre des informations à l'activité ultérieurement appelée
		Bundle bundle1 = new Bundle();
		//Intent pour appeler l'activité appelée PersonnageDesc
		Intent intent1 = new Intent(this, PersonnageDesc.class);
				
		switch (v.getId()){
				
		// Click sur le bouton 1
		case (R.id.button1):
			bundle1.putString("image", "Gandalf");
			intent1.putExtras(bundle1);
			startActivity(intent1);
			break;
				
		// Click sur le bouton 2
		case (R.id.button2):
			bundle1.putString("image", "Aragorn");
			intent1.putExtras(bundle1);
			startActivity(intent1);
			break;
					
		// Click sur le bouton 3
		case (R.id.button3):
			bundle1.putString("image", "Legolas");
			intent1.putExtras(bundle1);
			startActivity(intent1);
			break;
		}
				
	}
}

{% endhighlight %}


On effectue le lien entre les boutons définis dans le *layout* de l'activité et les instances gandalf, aragorn et legolas dans la méthode *onCreate* afin d'effectuer cette liaison dès la création de l'activité. On ajoute ensuite un *listener* à chaque bouton grâce à la méthode *setOnClickListener*. Ce *listener* va détecter chaque click de souris sur ces boutons et appeler la méthode *onClick* que l'on a codée.

Cependant, l'implémentation de la méthode *onClick* souvent rencontrée est la suivante:

{% highlight java %}
		
		Bundle bundle1 = new Bundle();
		Intent intent1 = new Intent(this, PersonnageDesc.class);

		gandalf.setOnClickListener( (new View.OnClickListener() {
        	public void onClick(View v) {
        		bundle1.putString("image", "Gandalf");
			intent1.putExtras(bundle1);
			startActivity(intent1);
		}
        	});


		aragorn.setOnClickListener((new View.OnClickListener() {
		public void onClick(View v) {
        		bundle1.putString("image", "Aragorn");
			intent1.putExtras(bundle1);
			startActivity(intent1);
		}
		});


		legolas.setOnClickListener((new View.OnClickListener() {
		public void onClick(View v) {
        		bundle1.putString("image", "Legolas");
			intent1.putExtras(bundle1);
			startActivity(intent1);
		}
		});



{% endhighlight %}

Chaque bouton possède alors sa méthode anonyme *"onClick"*, ce qui rajoute du code que l'on peut factoriser en une seule et même méthode à l'aide d'un *switch* comme indiqué dans le code ci dessous:


{% highlight java %}
	
	@Override
	public void onClick(View v) {
		
		Bundle bundle1 = new Bundle();
		Intent intent1 = new Intent(this, PersonnageDesc.class);
				
		switch (v.getId()){
				
		case (R.id.button1):
			bundle1.putString("image", "Gandalf");
			break;
				
		case (R.id.button2):
			bundle1.putString("image", "Aragorn");
			break;
					
		case (R.id.button3):
			bundle1.putString("image", "Legolas");
			break;
		}
		
		intent1.putExtras(bundle1);
		startActivity(intent1);
	}

{% endhighlight %}


Si le gain en terme de longueur de code n'est pas flagrant avec seulement 3 boutons, le gain en clarté de l'application est indéniable. De plus, la factorisation du code est un processus que je vous encourage à privilégier autant que possible, ceci améliorant non seulement la lisibilité du code mais aussi la rapidité avec laquelle vous pourrez le faire évoluer dans le futur.

### Avantages :

* Meilleure clarté du code
* Moins de code
* Evolution plus rapide du code

