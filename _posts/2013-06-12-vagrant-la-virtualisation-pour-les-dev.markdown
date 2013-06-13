---
layout: post
title: Vagrant la virtualisation pour les dev
author: Fabien AMICO
date: 2013-06-12 12:12:39 +0200
description: Depuis quelque temps, nous utilisons <strong>Vagrant</strong> pour créer nos environnements de développement. Cet outil est extrêmement pratique car il permet de créer des machines virtuelles automatiquement provisionnées avec tous les outils nécessaires, grâce à une intégration avec <strong>Chef</strong> très réussie.
tags:
- cloud
- chef
- vagrant

---

Depuis quelque temps, nous utilisons [Vagrant](http://www.vagrantup.com) pour créer nos environnements de développement. Cet outil est extrêmement pratique car il permet de créer des machines virtuelles automatiquement provisionnées avec tous les outils nécessaires, grâce à une intégration avec [Chef](http://www.opscode.com/) très réussie.

### La virtualisation pour les développeurs ?

La virtualisation est un outil largement répandu dans les équipes de production. Cela leur permet de réduire les coûts en optimisant l'utilisation du matériel, de créer des environnements plus rapidement, de gagner en portabilité etc... Par contre, la virtualisation est beaucoup moins utilisée par les développeurs alors qu'elle présente là aussi de nombreux avantages. Si, comme moi, vous faites un peu de veille, vous devez souvent installer et tester des outils qui ne resteront pas trop longtemps sur votre machine et qui finiront même par polluer votre système. Avec la virtualisation, vous créez un environement pour vos tests, puis une fois terminés vous pouvez archiver les VM ou tout supprimer. 

Sur les projets aussi la virtualisation peut apporter beaucoup aux développeurs. Si pour faire fonctionner votre projet, il faut 3 JBoss, 1 Tomcat 1 Mysql et 1 Memcached avec la conf de tous ces outils il va vous falloir deux jours de conf sur chacun des postes de développement, et la même chose à chaque fois qu'un nouveau développeur intègre le projet. Alors qu'avec la virtualisation, vous allez pouvoir créer un environnement de développement pour le projet et le faire fonctionner sur l'ensemble des machines des développeurs. Un autre aspect intéressant de la virtualisation dans les équipes de développement, c'est la portabilité de l'ensemble du système. Cela permet par exemple à un commercial de faire tourner le système sur son portable pour aller faire des démos de votre super projet. 

### Vagrant la virtualisation facile

Vagrant simplifie énormément la virtualisation sur le poste du développeur. Une de ses forces, c'est qu'il se base sur des Boxes (VM décompressée) qui contiennent l'OS avec une configuration de base. Il est possible de trouver [sur ce site](http://www.vagrantbox.es/) tout un tas de Boxes avec différents OS et différents outils d'installé. Une fois la box lancée, il est possible d'installer automatiquement n'importe quel outil à l'aide de recette Chef que l'on trouve très facilement sur le site de [opscode](http://community.opscode.com/) ou même sur [github](https://github.com/opscode-cookbooks). 

### Un environement de Dev en 5 minutes


Je passe l'étape d'[installation de Vagrant](http://downloads.vagrantup.com/) pour aborder directement la création d'un environement de développement virtuel. Dans un premier temps, il faut récupérer les recettes chef que nous allons installer. 

{% highlight bash %}
	git clone https://github.com/Treeptik/cookbooks.git
	cd cookbooks
	git submodule init
	git submodule update
{% endhighlight %}   


Ensuite on ajoute la box que nous allons utiliser, qui est basée sur une VM Ubuntu 12.04.2 LTS 64. L'ajout de la box permet de garder dans le Home directory une copie de cette VM et de l'utiliser dans d'autres projets.  

{% highlight bash %}
	vagrant box add "precise64" http://files.vagrantup.com/precise64.box
{% endhighlight %} 

Il faut ensuite initialiser un nouvel environnement Vagrant 

{% highlight bash %}
	vagrant init
{% endhighlight %}

Cette commande crée le fichier de configuration  Vagrantfile qui contient, entre autre, le nom de la box utilisée, la configuration réseaux et toutes les recettes qu'il faut installer sur la machine par défaut. Dans notre exemple, nous allons installer une machine avec **Mysql, PhpMyadmin et Java**

Configuration du Vagrantfile : 
{% highlight ruby linenos %}
Vagrant.configure("2") do |config|

  # Nom de la boxe qu'on utilise (ajouté avec vagrant box add)
  config.vm.box = "cloudify"

  # Adresse IP public accessible sur le réseau comme une autre machine
  config.vm.network :public_network

  # Liste des applications à installer
  # elles doivent être présentes dans le répertoire cookbooks 
  config.vm.provision :chef_solo do |chef|
     	chef.cookbooks_path = "cookbooks"

		chef.add_recipe "apt"
     	chef.add_recipe "mysql"
     	chef.add_recipe "apache2"
     	chef.add_recipe "php"
		chef.add_recipe("apache2::mod_php5")
		chef.add_recipe("apache2::mod_rewrite")     	
    	chef.add_recipe "phpmyadmin"
    	chef.add_recipe "java"
     	
     	chef.json = {
      	"mysql" => {
        	"server_root_password" => "root",
        	"server_repl_password" => "root",
        	"server_debian_password" => "root",
      	},
        
    	}
  end

end
{% endhighlight %}

Vous pouvez ensuite lancer la machine vituelle avec la commande :

{% highlight bash %}
	vagrant up
{% endhighlight %}

Cette étape peut prendre plusieurs minutes car elle démarre le système puis procède à toutes les installations. Ensuite il est possible de se connecter à la machine en ssh avec la commande :  

{% highlight bash %}
	vagrant ssh
{% endhighlight %}

Vous pouvez aussi manipuler phpmyadmin directement dans votre navigateur avec l'adresse ip de la machine `http://[adresse-machine]/phpmyadmin`


### Vagrant Chef intégration

Cet article utilise la version 1.2.2 de Vagrant qui est la dernière version au moment de l'écriture. Cette version de vagrant utilise par défaut Chef client en version 10.14 pour provisionner les applications alors que la dernière version est la 11.4.4. Il est possible d'utiliser la dernière version de Chef en ajoutant la commande suivante au fichier Vagrantfile : 

{% highlight ruby  %}
config.vm.provision :shell, :inline => "gem install chef --version 11.4.4 --no-rdoc --no-ri --conservative
{% endhighlight %}

**Attention :** Un certain nombre d'évolutions de cette version cassent la compatibilité avec les anciennes versions de chef (cf. : [Breaking change](http://docs.opscode.com/breaking_changes_chef_11.html)) ce qui fait que les recettes ne sont plus fonctionnelles. Sur le site de opscode certaines recettes sont compatibles avec la version 10 et d'autre avec la version 11. De notre côté nous avons décidé de rester avec la version 10 pour éviter d'avoir à modifier l'ensemble des recettes. 







