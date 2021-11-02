
(function($, document, window, viewport){


  var breakpointDetect = function() {

    // Executes in XS
    if( viewport.is('<sm') ) {

      /* Moure html - traductor */
      $('.traductor-checkbox').insertAfter('.primer-textarea');

    }


    // Executes in XS and SM breakpoint
    if( viewport.is("<=sm") ) {

      /**
      * Menu responsive - treure i afegir classe active
      */
      $("#accordion li a").click(function (e) {
        e.preventDefault();

        if(!$(this).parent().hasClass('active')){
          $(this).parent().siblings().removeClass('active');
          $(this).parent().addClass('active');
        }else{
          $(this).parent().removeClass('active');
        }

      });

      /**
      * Boto ralles - menu lateral esquerra
      */
      $('#menu-lateral').on('show.bs.collapse',function(){
        $('.bt-menu-lateral').addClass('active');
      });

      $('#menu-lateral').on('hide.bs.collapse',function(){
        $('.bt-menu-lateral').removeClass('active');
      });

      /**
      * Botons xs - collapse altres menus i afegir-treure classe al boto
      */
      $("[data-collapse-group='menu-xs']").click(function (e) {
        e.preventDefault();
        var $this = $(this);

        if(!$this.hasClass('active')){
          $("[data-collapse-group='menu-xs']").removeClass('active');
          $this.addClass('active');
        }else{
          $("[data-collapse-group='menu-xs']").removeClass('active');
          $this.removeClass('active');
        }

        $("[data-collapse-group='menu-xs']:not([data-target='" + $this.data("target") + "'])").each(function () {
          $($(this).data("target")).removeClass("in").addClass('collapse');
        });
      });

    }


    // Executes in SM, MD and LG breakpoints
    if( viewport.is('>=sm') ) {

      /* Moure html - traductor */
      $('.traductor-checkbox').insertAfter('.traductor-textarea');
    }


    // Executes in LG and MD breakpoints
    if( viewport.is('>=md') ) {

      /* Animacio rollover dropdown menu */
      $('ul.nav li.dropdown').hover(function() {
        $(this).find('.dropdown-hover').stop(true, true).delay(100).fadeIn(200);
        $(this).find('.dropdown-toggle').addClass("seleccionat active");
        $(this).addClass("open");
      }, function() {
        $(this).find('.dropdown-hover').stop(true, true).delay(100).fadeOut(200);
        $(this).find('.dropdown-toggle').removeClass("seleccionat active");
        $(this).removeClass("open");
      });

    }

   } // end var breakpointDetect

   // Executes once whole document has been loaded
   $(document).ready(function() {

      /* Detectar Breakpoint queries */
      breakpointDetect();
      // console.log('Current breakpoint:', viewport.current());


      /**
       * Afegeix classe 'touch' al body per a elements touch
       */
      document.addEventListener('touchstart', function addtouchclass(e){ // first time user touches the screen
          $('body').addClass('touch');
          document.removeEventListener('touchstart', addtouchclass, false) // de-register touchstart event
      }, false)

      /**
      /* Passar els select a dropdown menu
      */
      $('.selectpicker').selectpicker();


      /**
      * bt-versions - fitxa de programa
      */
      $('#versions').on('show.bs.collapse',function(){
        $('.bt-versions').addClass('hidden');
        $('.bt-download-hide').addClass('desactivat');
      });

      $('#versions').on('hide.bs.collapse',function(){
        $('.bt-versions').removeClass('hidden');
        $('.bt-download-hide').removeClass('desactivat');
      });


      /**
      * bt-mes - comentaris
      */
      $('#mescomentaris').on('show.bs.collapse',function(){
        $("#mescomentaris").next().find("button").addClass('bt-mes-disabled');
        $("#mescomentaris").next().find("button").attr('disabled', 'true');
      });


      /**
      * btns-llengues - traductor
      */
      $(".btns-llengues-origen .bt").click(function (e) {
        e.preventDefault();
        $('.btns-llengues-origen .bt').removeClass('select');
        $(this).addClass('select');
      });

      $(".btns-llengues-desti .bt").click(function (e) {
        e.preventDefault();
        if ( $(this).prop("tagName").toLowerCase() === 'div' ) {
            if ( $(this).find('button').length === 1 ) {
                if ( $($(this).find('button')[0]).is(':disabled') ) {
                    return
                }
            }
        }
        $('.btns-llengues-desti .bt').removeClass('select');
        $(this).addClass('select');
      });

      /**
      * boto respon - comentaris
      */
      $(".respon").click(function (e) {
        e.preventDefault();
        if(!$(this).hasClass('active')){
          $(this).addClass('active');
        }else{
          $(this).removeClass('active');
        }
      });


      /**
      * Animacio scroll-top - pmf
      */
      $(".nav-anchor ul li a[href^='#'], .bt-up").on('click', function(e) {
         e.preventDefault();
         $('html, body').animate({ scrollTop: $(this.hash).offset().top }, 600);
      });


      /**
      * Cercador lupa - escriptori
      */
      new UISearch(document.getElementById('sb-search'));

   }); // end executes once whole document has been loaded


   // Executes on resize window
   $(window).resize(
      viewport.changed(function(){

         /* -- Detectar Breakpoint queries -- */
         breakpointDetect();
         // console.log('Current breakpoint:', viewport.current());

      })
   );// end executes on resize window

    $(document).ready(function() {

        //Top search
        var $cerca_top_form = jQuery('#searchform_top_2');

        $cerca_top_form.on('submit', function (ev) {
            ev.preventDefault();

            var cerca = jQuery('#cerca_top_2').val();
            window.location.href = '/cerca/' + cerca + '/';

            return true;
        });

        var $cerca_top_form = jQuery('#searchform_top_1');

        $cerca_top_form.on('submit', function (ev) {
            ev.preventDefault();

            var cerca = jQuery('#cerca_top_1').val();
            window.location.href = '/cerca/' + cerca + '/';

            return true;
        });

    });

    //Top menu
    jQuery(function() {
        if(window.location.pathname != '/') {
            var element = decodeURIComponent('nav a[href^="' + window.location.pathname + '"]');
            if (element.indexOf('page') !=-1) {
                element = element.substring(0, element.indexOf('page'));
            }
            var top_menus = ['recursos', 'coneixeu', 'collaboreu'];

            top_menus.forEach(function(menuelement) {
                if(jQuery(element).parentsUntil('.navbar').parent('#'+menuelement+'').length) {
                    var elements = jQuery("body").find("[aria-controls='"+menuelement+"']");
                    elements.trigger('click');
                }
            });

            if(jQuery(element).parentsUntil('.nav-tabs').siblings('.dropdown-toggle').length) {
                jQuery(element).parentsUntil('.nav-tabs').siblings('.dropdown-toggle').addClass('active');
            } else {
                jQuery(element).addClass('active');
            }
            jQuery("#navbar-usuari-mobile").removeClass('active');
        } else {
            var OSName="Unknown OS";
            if (navigator.userAgent.indexOf("Win") != -1) OSName="windows";
            else if (navigator.userAgent.indexOf("iPad") != -1 || navigator.userAgent.indexOf("iPhone") != -1 || navigator.userAgent.indexOf("iPod") != -1) OSName="ios";
            else if (navigator.userAgent.indexOf("Mac") != -1) OSName="osx";
            else if (navigator.userAgent.indexOf("Android") != -1) OSName="android";
            else if (navigator.userAgent.indexOf("Linux") != -1) OSName="linux";

            jQuery(".tab-"+OSName+" > a").trigger('click');
        }
    });

    jQuery('#btn-home-programes').click(function() {
        platform = jQuery('.programari .tab-content .active').attr('id');
        jQuery(this).attr('href', '/programes/so/' + platform + '/');
    })

})(jQuery, document, window, ResponsiveBootstrapToolkit);

(function($) {
	/** Cookie messages **/
	$(document).ready(function () {
		if (typeof $.cookieCuttr == 'function') {
			$.cookieCuttr({
				cookieAnalyticsMessage: 'Aquest web utilitza galetes pròpies i de tercers per optimitzar i adaptar-se a la vostra navegació i les vostres preferències, entre altres tasques. Si continueu navegant, entendrem que accepteu la nostra política de privacitat.<br/>',
				cookieWhatAreTheyLink: '/avis-legal/',
				cookieAcceptButtonText: 'Accepta',
				cookieWhatAreLinkText: '<br/>Més informació...',
				cookieNotificationLocationBottom: true,
			});
		}
	});
})(jQuery);
