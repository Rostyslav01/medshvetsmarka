<?php if ( 'on' == et_get_option( 'divi_back_to_top', 'false' ) ) : ?>

	<span class="et_pb_scroll_top et-pb-icon"></span>

<?php endif;

if ( ! is_page_template( 'page-template-blank.php' ) ) : ?>

			<footer id="main-footer">
				<?php get_sidebar( 'footer' ); ?>


		<?php
			if ( has_nav_menu( 'footer-menu' ) ) : ?>

				<div id="et-footer-nav">
					<div class="container">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'footer-menu',
								'depth'          => '1',
								'menu_class'     => 'bottom-nav',
								'container'      => '',
								'fallback_cb'    => '',
							) );
						?>
					</div>
				</div> <!-- #et-footer-nav -->

			<?php endif; ?>

				<div id="footer-bottom">
					<div class="container clearfix">
				<?php
					if ( false !== et_get_option( 'show_footer_social_icons', true ) ) {
						get_template_part( 'includes/social_icons', 'footer' );
					}
				?>

						<p id="footer-info"><a href="https://vk.com/shvetska_marka" title="Website Development">Мы в ВК</a></p>
					</div>	<!-- .container -->
				</div>
			</footer> <!-- #main-footer -->
		</div> <!-- #et-main-area -->

<?php endif; // ! is_page_template( 'page-template-blank.php' ) ?>

	</div> <!-- #page-container -->
    <!-- Start SiteHeart code -->
<script>
    (function(){
    var widget_id = 800861;
    _shcp =[{widget_id : widget_id}];
    var lang =(navigator.language || navigator.systemLanguage 
    || navigator.userLanguage ||"en")
    .substr(0,2).toLowerCase();
    var url ="widget.siteheart.com/widget/sh/"+ widget_id +"/"+ lang +"/widget.js";
    var hcc = document.createElement("script");
    hcc.type ="text/javascript";
    hcc.async =true;
    hcc.src =("https:"== document.location.protocol ?"https":"http")
    +"://"+ url;
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hcc, s.nextSibling);
    })();
    </script>
     <!-- End SiteHeart code -->      
      <!-- Yandex.Metrika counter --><script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter32779635 = new Ya.Metrika({ id:32779635, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="https://mc.yandex.ru/watch/32779635" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
      <!-- /Yandex.Metrika counter -->
      
<!-- Google Analitics -->
      <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){	  
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })
  (window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-45777165-1', 'auto');
  ga('send', 'pageview');
</script>
<!-- End Google Analitics -->
	<?php wp_footer();  ?>
    <script type="text/javascript" src='http://medshvetsmarka.com.ua/wp-content/plugins/fancybox/jquery-1.4.3.min.js'></script>
<script type="text/javascript" src='http://medshvetsmarka.com.ua/wp-content/plugins/fancybox/jquery.mousewheel-3.0.4.pack.js'></script>
<script type="text/javascript" src='http://medshvetsmarka.com.ua/wp-content/plugins/fancybox/jquery.fancybox-1.3.4.pack.js'></script>
<link rel='stylesheet' id='divi-style-css'  href='http://medshvetsmarka.com.ua/wp-content/themes/MedMarka/style.css?ver=2.4.6.2' type='text/css' media='all' />
<script type="text/javascript">
$(document).ready(function() {
   $("a.big_photo").fancybox({
	   transitionIn: 'elastic',
	   transitionOut: 'elastic',
	   easingIn: 'swing', 
	   easingOut: 'swing'
   }); 
}); 
</script>
</body>
</html>