/* EVC-Share */ 

jQuery(document).ready(function($) {
    /*
    var oTimeout = 5000;
    var oScreens = 4/5;
    var oCookieExpires = 2;
    var oAction = 'scroll'; 
    
    
    var sTimeout = 5000;
    var sScreens = 3/4;
    var sCookieExpires = 365;
    var sSpeed = 800;
    var sAction = 'scroll'; 
    */
    
    if ($('#overlay-sidebar-wrap').length) {
    
      var oWrap = $('#overlay-sidebar-wrap');
      // Close button
      oWrap.find('.overlay-sidebar-close').on( 'click', function() {
        oWrap.fadeOut( 200, function() {
          oWrap.addClass('hide');
        });
      });  
      
      var oOpen = false;

      if($(window).width() < $( "#overlay-sidebar" ).outerWidth()) {      
             
        $( "#overlay-sidebar" ).css({
          "width": $(window).width(),
          "margin": '0 0 0 -' + (parseInt($(window).width()) / 2) + 'px' 
        });      
      } 

      if($(window).height() < (parseInt($( "#overlay-sidebar" ).outerHeight())) + parseInt(oTop) ) {      
        
        if($(window).height() < $( "#overlay-sidebar" ).outerHeight() ) { 
          $( "#overlay-sidebar" ).css({
            "overflow-y": "scroll"
          });   
        }
        
        $( "#overlay-sidebar" ).css({
          "height": $(window).height()
        });      
        
        oTop = 0;
      }  
      
      if (oAction == 'timeout') {
        setTimeout( function() {
          if ( !oOpen ) {
            oInit();
            oOpen = true;
          }
        }, oTimeout );
      }    
      
      if (oAction == 'scroll') {
        $(document).scroll(function () {
          var docViewHeight = $(window).height();
          var docViewTop = $(window).scrollTop();
          if ( docViewTop > docViewHeight * oScreens  && !oOpen ) {
            oInit();
            oOpen = true;  
          }
        });    
      }    

      if (oAction == 'getaway') {
        $(document).on('mouseleave', function(e) {
          if( e.clientY < oSensitivity )
            oInit ()
        });   
      }  
              
      function oInit () {    
        // Open          
        $('#overlay-sidebar-bg').fadeIn( {
          duration: 200,
          complete: function() {
            $('#overlay-sidebar-bg').removeClass('hide');
            $('#overlay-sidebar').css('top', oTop);
          },
          done: function() {
            $('#overlay-sidebar').css('top', oTop);
          }
        });
        
        if ($.cookie('oSidebar') == 'undefined' || !$.cookie('oSidebar'))
          $.cookie('oSidebar', '1', { expires: oCookieExpires, path: '/' });
        else
          $.cookie('oSidebar', parseInt($.cookie('oSidebar')) + 1, { expires: oCookieExpires, path: '/' });    
      }    
      
    } // Overlay Sidebar End
    
    if ($('#slide-sidebar-wrap').length) {
    
      var sOpen = false;
      var sWrap = $('#slide-sidebar-wrap');
      
      if($(window).width() < $( "#slide-sidebar" ).outerWidth()) {      
             
        $( "#slide-sidebar" ).css({
          width: $(window).width(),
          right: '-' + $(window).outerWidth()
        });      
      }    
           
      var sWidth = $( "#slide-sidebar" ).css('right');
      sWrap.find('.slide-sidebar-close').on( 'click', function() {
        $( "#slide-sidebar" ).animate({ "right": sWidth}, sSpeed );
      });
      
      
      if (sAction == 'timeout') {
        setTimeout( function() {
          if ( !oOpen ) {
            sInit();
            sOpen = true;
          }
        }, sTimeout );
      }    
      
      if (sAction == 'scroll') {
        $(document).scroll(function () {
          var docViewHeight = $(window).height();
          var docViewTop = $(window).scrollTop();
          if ( docViewTop > docViewHeight * sScreens  && !sOpen ) {
            sInit();
            sOpen = true;  
          }
        });    
      }      


      if (sAction == 'getaway') {
        $(document).on('mouseleave', function(e) {
          if( e.clientY < sSensitivity )
            sInit ()
        });   
      }       
          
      function sInit () {
        $( "#slide-sidebar" ).animate({ "right": 0}, sSpeed );
            
        if ($.cookie('sSidebar') == 'undefined' || !$.cookie('sSidebar')) {
          $.cookie('sSidebar', '1', { expires: sCookieExpires, path: '/' });
          //console.log($.cookie('sSidebar'));
          }
        else
          $.cookie('sSidebar', parseInt($.cookie('sSidebar')) + 1, { expires: sCookieExpires, path: '/' });
      }
    }  // Slide Sidebar End

    
    
    if (typeof vkUnLock !== 'undefined' && vkUnLock.length > 0){
      for (index = 0; index < vkUnLock.length; ++index) {
        if ($.cookie('vkUnLock' + vkUnLock[index]) == 'undefined' || !$.cookie('vkUnLock' + vkUnLock[index]) || $.cookie('vkUnLock' + vkUnLock[index]) !=  vkUnLock[index] ) {
          $.cookie('vkUnLock' + vkUnLock[index], vkUnLock[index], { expires: subscribeCookieExpires, path: '/' });
        }        
      }      
    }
  

  if ($(".evc-box").length) {

/*
    $("#col-right, #col-left").stick_in_parent({
      //parent: '#col-container',
      parent: '#wpbody',
      offset_top: $('#wpadminbar').height() + 10
    });
*/
  }
  
  
  }); // End jQuery 
  
 
  

