=== Easy VKontakte Connect ===
Contributors: alekseysolo
Tags: vkontakte, vk, autopublish, post, social, share, wall, analytics, comments, polls, surveys, woocommerce
Requires at least: 3.5
Tested up to: 4.6
Stable tag: 2.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Автопубликация записей с фото на стене ВКонтакте, анализ групп, кнопки, виджеты...

== Description ==

Весь API ВКонтакте. 

* Автопубликация записей с фото на стену группы и на **!!!** **личные страницы** ВКонтакте. Поддерживаются post_type.
* Шорткод для **!!!** **нового виджета ВКонтакте** &laquo;[Напишите нам](http://ukraya.ru/1607/vk_contact_us "“Напишите нам” – новый виджет ВКонтакте и шорткод в плагине Easy VKontakte Connect")&raquo;.
* **!!!** ФотоАльбомы ВКонтакте теперь на сайте!
* Кнопки Поделиться: 7 социальных сетей, интерактивный настройщик, 4 темы и множество вариантов отображения. Сети: ВКонтакте, Одноклассники, Мой Мир, Facebook, Google+, Twitter, Pinterest.
* Социальный замок: чтобы увидеть закрытое содержимое на сайте, нужно подписаться на группу ВКонтакте.
* Авторизация через ВКонтакте.
* Опросы ВКонтакте: создать, добавить на сайт, **!!!** отправить в группу, поделиться.
* Виджет комментариев ВКонтакте; респонсивный. **!!!** Оповещение на почту о комментариях из виджета.
* Индексация & импорт комментариев, оставленных через виджет комментариев ВКонтакте.
* Виджет сообществ ВКонтакте.
* **!!!** Поддержка авторизации через ВК для **WooCommerce**.
* Невероятная четверка сайдбаров: всплывающий, выезжающий, до и после контента.
* Анализ групп ВКонтакте.

Подробности и техническая поддержка [на сайте плагина](http://ukraya.ru/428/easy-vkontakte-connect-evc "Техническая поддержка"). 


This plugin allows you to publish posts on the VKontakte wall in automatic or manual mode, along with the images attached to post and provide VKontakte Wall Analytics.

* Uses the API VKontakte
* **!!!** Social share buttons with interactive builder. jQuery part based on the Social Likes library by Artem Sapegin, [git](https://github.com/sapegin/social-likes "Social Likes library by Artem Sapegin").
* VK Community Widget
* Sidebars: overlay, slide, before and after posts; triggered by timeout or scrolling actions.
* Provide VKontakte Wall Analytics: Sort group wall posts by: likes, reposts, comments, publish time
* Automatically publish new posts on the VKontakte wall
* Manually publish posts on the VKontakte wall
* Publish images attached to the posts on the VKontakte wall 
* Note categories of posts which are ecluded from autopublish to VKontakte wall

Requires WordPress 3.2 and PHP 5.

== Installation ==

1. Upload all files to the `/wp-content/plugins/easy-vkontakte-connect/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Follow the instruction of plugin configuration

== Screenshots ==

1. Social Likes Buttons Themes and Variation.
2. VK API Settings.
3. Sidebars Settings.
4. Autopost Settings.
5. VK Community Widget.
6. Edit Post Page.
7. VKontakte Wall Analytics page.

== Changelog ==

= 2.2.1 /  2016-08-17 =
* Changed widgets headers from h1 to h2. / 2016-07-07 / 2.2.001
* IMPORTANT!!! Fixed layout compatibility with WP 4.6. / 2016-08-17 / 2.2.002

= 2.2 / 2016-07-05 =
* Added evc activation date as option. / 2016-06-16 / 2.1.201
* Added the ability to send only attachments without text. / 2016-06-27 / 2.1.202
* Added compatibility with evc-multigroup. / 2016-06-30 / 2.1.203
* Added new VK widget Contact Us. / 2016-07-05 / 2.1.204

= 2.1.2 / 2016-06-15 =
* Fixed minor bug in Log display. / 2016-02-17 / 2.1.102
* Removed unnecessary files. / 2016-02-22 / 2.1.103
* Fixed (maybe) problem with blog installation in subfolder (access token). / 2016-02-22 / 2.1.104
* Fixed problem with blog installation in subfolder (access token). / 2016-03-19 / 2.1.105
* Added minor improvements. / 2016-04-11 / 2.1.106
* Changed transport for sending photo to VK. / 2016-04-17 / 2.1.107
* Optimized work of social buttons. / 2016-06-14 / 2.1.108
* Added requests timout as option. / 2016-06-14 / 2.1.109

= 2.1.1 / 2016-02-17 =
* Fixed important bug with cache request to vk when use vk-albums. / 2016-02-17 / 2.1.101

= 2.1 / 2016-02-16 =
* Fixed bug (removed double option autopost_old_order in evc-autopost page). / 2015-12-24 / 2.0.101
* Removed top admin panel ads. / 2015-12-25 / 2.0.102
* Fixed file upload to vk via curl in php >= 5.5 (CURLFile). / 2016-01-20 / 2.0.103
* Updated social-likes to 3.1. / 2016-01-21 / 2.0.104
* Fixed sidebar order in widget settings page. / 2016-01-22 / 2.0.105
* Added is_singular filter to buttons. / 2016-02-07 / 2.0.106
* Added option to disable evc_buttons_load_scripts on frontend. / 2016-02-07 / 2.0.107
* Updated jquery.sticky-kit.js to v1.1.2 / 2016-02-07 / 2.0.108

= 2.0.1 / 2015-12-22 =
* Fixed bug (display collage_gallery shortcode in new post even vk photoalbum url not included in post).

= 2.0 / 2015-12-22 =
* Added Autoposting to personal pages. / 2015-11-23
* Fixed refresh comments from vk post. / 2015-11-24
* Fixed ajaxurl. / 2015-11-28
* Added WooCommerce support (authorization, checkout button and personal data storage). / 2015-12-07 / 1.9.52
* Fixed VK Group Widget for display Personal Pages / 2015-12-16 / 1.9.53
* Show photos from VK Photoalbums on site. / 2015-12-20 / 2.0

= 1.9.4 / 2015-11-02 =
* Fixed get_avatar filter for comments.  / 2015-08-08
* Remove new-line and carriage return replace by double new-line in evc_make_excerpt.  / 2015-09-04
* Added new masks for Autoposting: teaser, teaserORexcerpt. / 2015-09-27
* Added scope video to vk api for autoposting . / 2015-09-27
* Fixed evc_vkapi_resolve_screen_name, now works with any token (site or autopost). / 2015-09-29
* Added shortcode [vk_subscribe]. / 2015-09-29
* Change add_menu_page position. / 2015-10-20

= 1.9 / 2015-07-24 =
* Added VK Comments Browser Widget in admin side.
* Fixed post author and moderator notification about new comments added via VK Comments Widget.
* Added comments compability with another plugin. / 2015-04-08
* Added capability disable / enable share buttons on page types. / 2015-04-08
* Fixed post_ID for comments in some themes. / 2015-03-27

= 1.8.3.1 / 2015-03-26 =
* Added superglobal options for buttons inserting.

= 1.8.3 / 2015-03-18 =
* Added post_types filters for autoposting.
* Fixed Emoji in Groups Analytics.
* Fixed quotes in social buttons.
* Added overlay-sidebar responsivity.
* Added social-likes 2015-03-10 v3.0.14

= 1.8.2 / 2014-12-30 =
* Added features setting VK Comments widget and Share Buttons for each pages and posts separetly.
* Added Responsivity for VK Comments Widget.
* Added ability to place shortcode in widgets.
* Fixed problem with ad column.
* Fixed wrong shortcode for polls in All Poll page.
* Added evc-polls vk error 17 handler.
* Added social-likes 2014-12-11 v3.0.10

= 1.8.1 / 2014-10-27 =
* New sidebar action: when leave the page. Increase your conversion!
* Fix minor bugs.

= 1.8 / 2014-09-29 =
* Added social share buttons with interactive builder.
* Added slide sidebar responsive width.
* Added vk community shortcode.
* Fixed minor bugs in comments widget.

= 1.7.1 / 2014-08-05 =
* **!!!** Added compatibility with Amazing Group Members Online Stats in PRO version.
* Added missing option Show VK login button.
* Changed autopost method, maybe increased posted text size.
* Added additional error handler.

= 1.7 / 2014-07-14 =
* Added VK Athorization.
* Added Social Locker.
* Etc...

= 1.6 / 2014-07-01 =
* Add VK Polls widget.
* Fix error in VK Community Widget.
* Etc...

= 1.5.1 / 2014-05-06 =
* Fix undefined variable in evc_share_meta.

= 1.5 =
* **Important** Added VK Comments Indexation feature.
* Return parameters wide in VK Cummunity Widget settings.

= 1.4 =
* Add VK Comments Widget.

= 1.3.1 =
* Correct links in message.
* Add dashicons to front page.

= 1.3 =
* VK Community Widget
* Sidebars: overlay, slide, before and after posts; triggered by timeout or scrolling actions.

= 1.1 =
* **Important:** Correct to correspond VK API changes in photos.getWallUploadServer, photos.saveWallPhoto.
* **Important:** Correct access token scopes.
* Set sslverify = false in wp_remote_get.
* Add capability to show link to Group Analytics in admin bar.

= 1.0 =
* **New:** Provide VKontakte Wall Analytics.
* Process captcha if needed.
* New tags %link% in wall post publish mask.
* Cut posts in accordance with the VKontakte limits.
* Paragraph tags now are replaced by \n\n.

= 0.2 =
* Fix minor bugs.

= 0.1 =
* First stable release.
