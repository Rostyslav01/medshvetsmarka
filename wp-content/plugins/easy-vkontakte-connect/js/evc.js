/* EVC */ 
 
jQuery(document).ready(function($) {

  $(".totop").click(function() {
    $("html, body").animate({ scrollTop: 0 }, "slow");
    return false;
  });     
    
  $('.a01').live( "mouseenter", function(){
    $(this).addClass('active'); 
  });
  $('.a01').live( "mouseleave", function(){
    $(this).removeClass('active'); 
  });  

  
  $('body').tooltip({selector: '[rel=tooltip]'});
  
  $('.navbar').affix({
    offset: $('.navbar').position()
  });
  
      
  var $container = $('.vkposts');
  
  $container.imagesLoaded( function(){
    $container.masonry({
      itemSelector : '.a01',
      columnWidth : 256,
      isAnimated: true   
    });
  });    

  //$(".vkposts>.a01").tsort('.date', {order: 'desc'});
  //$container.masonry('reload');      
      
      
  var $optSets = $('.evc-stats-options'),
  $optLinks = $optSets.find('a');
                
  $optLinks.click(function(){
    var $this = $(this);
        
    if ($this.hasClass('external'))
      return false;
        
    if ( $this.parent().hasClass('active') ) {
      return false;
    }

    var $optSet = $this.parents('ul');

    $optSet.find('[data-key="' + $this.parent().attr('data-key') +'"]').removeClass('active');
    $this.parent('li').addClass('active');  
      
    var sortBase = $optSets.find('li.active[data-key="sortBy"]', 0).attr('data-option-value');
    var sortOrder = $optSets.find('li.active[data-key="sortAscending"] a', 0).attr('data-value');
    sortOrder = sortOrder === 'false' ? 'desc' : 'asc';
    
    $(".vkposts>.a01").tsort('.' + sortBase, {order: sortOrder});
    $container.masonry('reload');   
             
    return false;
  });  
 
  
  // Isotope DEPRECATED because:
  // https://make.wordpress.org/plugins/2012/12/20/gpl-and-the-repository/
  
  /*   
  var $container = $('.vkposts');
  $container.imagesLoaded( function(){
    $container.isotope({
      itemSelector : '.a01',
      masonry: {
        columnWidth : 256 
      },
      sortBy : 'dates',
      sortAscending : false,

      getSortData : {
        likes : function( $elem ) {
          return parseInt( $elem.find('.likes').text(), 10 );
        },
        reposts : function( $elem ) {
          return parseInt( $elem.find('.reposts').text(), 10 );
        },
        comments : function( $elem ) {
          return parseInt( $elem.find('.comments').text(), 10 );
        },                    
        dates : function( $elem ) {
          return parseInt( $elem.find('.a01_date').attr('data-date-gmt'), 10 );
        },                            
      }
    });
  });
      

  var $optSets = $('.evc-stats-options'),
  $optLinks = $optSets.find('a');
                
  $optLinks.click(function(){
    var $this = $(this);
        
    if ($this.hasClass('external'))
      return false;
        
    if ( $this.parent().hasClass('active') ) {
      return false;
    }

    var $optSet = $this.parents('ul');
    //console.log($optSet);
    $optSet.find('[data-key="' + $this.parent().attr('data-key') +'"]').removeClass('active');
    $this.parent('li').addClass('active');
  
    
    var options = {},
      key = $this.parent().attr('data-key'),
      value = $this.attr('data-value');
    value = value === 'false' ? false : value;
    options[ key ] = value;
    $container.isotope( options );
        
    return false;
  });      
      
  */       
  
  
}); // End
