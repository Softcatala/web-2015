

## Workflow
------------

NODE     (https://nodejs.org/)
GRUNT    (http://gruntjs.com/) The JavaScript Task Runner
BOWER    (http://bower.io) per a la instal·lació de terceres parts que s'utilitzen en el projecte.



## Estructura de carpetes del projecte
---------------------------------------

softcatala-projecte/
	│
	├──app/
	│	│
	│	└── assets/								 ---------------> Arxius less i js creats pel projecte.
	│		│
	│	    ├── js/
	│		│	└── main.js					     ---------------> Javascript escrit pel projecte.
	│		│
	│	    └── less/
	│	 	    ├── components/
	│	 		│   ├── botons.less
	│	 		│   ├── formulari.less
	│	 		│   ├── modals.less
	│	 		│   └── taules.less
	│	 		│
	│	 	   	├── comuns/
	│	 		│   ├── continguts.less
	│	 		│   ├── footer.less
	│	 		│   ├── global.less
	│	 		│   ├── header.less
	│	 		│   └── variables.less
	│	 		│
	│	 	   	├── layouts/
	│	 	   	│	├── columna-dreta.less
	│	 		│   ├── comentaris.less
	│	 	   	│	├── layout-aparells-plus.less
	│	 		│   ├── layout-blog.less
	│	 		│   ├── layout-eines.less
	│	  		│   ├── layout-home.less
	│	 		│   ├── layout-membre.less
	│	 		│   ├── layout-pmf.less
	│	 		│   ├── layout-programa.less
	│	 		│   ├── llistes.less
	│	 		│   ├── menu-esquerra.less
	│	 		│   └── thumbnails.less
	│			│
	│		   	└── main.less                   ---------------> Arxiu on importem tots els less que utilitzem en el projecte (organitzats en les carpetes anteriors).
	│
	│
	│
	├── bower_components/                       ---------------> Components instal·lats mitjançant Bower.
	│   │
	│   ├── bootstrap/		                    ---------------> Bootstrap
	│	├── bootstrap-select/                 	---------------> A custom select / multiselect for Bootstrap using button dropdown (https://github.com/kartik-v/bootstrap-star-rating)
	│   ├── bootstrap-star-rating/				---------------> A simple yet powerful JQuery star rating (https://github.com/kartik-v/bootstrap-star-rating)
	│   ├── expanding-search-bar/				---------------> Search (http://tympanus.net/codrops/2013/06/26/expanding-search-bar-deconstructed/)
	│   ├── fontawesome/		           		---------------> Font Fontawesome
	│	├── jquery/		            			---------------> Jquery (instal·lat amb Bootstrap)
	│	├── mega-site-navigation/			    ---------------> Responsive navigation - lateral (http://codyhouse.co/gem/css-mega-site-navigation/)
	│   ├── open-sans-fontface/		            ---------------> Font Open Sans
	│   ├── responsive-toolkit/					---------------> Breakpoint detection in JavaScript (https://github.com/maciej-gurban/responsive-bootstrap-toolkit)
	│   └── ubuntu-font/			            ---------------> Font Ubuntu
	│
	│
	│
	├── public/                                 ---------------> Carpeta amb els arxius html i css/js/fonts/img definitius.
	│	├── dist/
	│	│   ├── css/
	│	│	│	├── main.min.css
	│	│	│	└── main.min.css.map            ---------------> Arxiu map per localitzar els estils dins dels arxius less "copilats". (Eliminar abans de pujar el web)
	│	│	│
	│	│	│
	│	│   ├── fonts/
	│	│   ├── img/
	│	│   └── js/
	│	│		├── main.js                     ---------------> Arxiu javascript "copilat" abans de ser minificat. (Eliminar abans de pujar el web)
	│	│		└── main.min.js
	│	│
	│	├── 00-home.html
	│	├── 01-plantilla-text.html
	│	├── ...
	│
	│
	│
	├── bower.json                              ---------------> Llista dels components instal·lats mitjançant Bower.
	│
	├── Gruntfile.js                            ---------------> Configuració de tasques del projecte (js/less/grunt watch).
	│
	└── package.json                            ---------------> Informació del projecte.