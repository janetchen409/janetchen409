/*
|--------------------------------------------------------------------------
| YUZO SCRIPT
|--------------------------------------------------------------------------
|
| Libraries and functions that allow the correct functioning.
| All based on the front-end.
|
*/
/**
 * @since   6.0   2019-05-03 01:24:30   Release
 */
;(function( $, window, document, undefined ) {
  'use strict';

  // --- Constants ---------------
  var YUZO = YUZO || {};

  YUZO.vars = {
    onloaded : false,
    $window  : $(window),
    $document: $(document),
    geo      : new pf_geo(),
  };

  // --- Functions declare ---------------
  YUZO.funcs = {};

  // --- Helpers ---------------
  YUZO.helper = {

    // uID user
    uid: function ( prefix ) {
      return ( prefix || '' ) + Math.random().toString(36).substr(2, 9);
    },

    // Quote regular expression characters
    preg_quote: function( str ) {
      return (str+'').replace(/(\[|\-|\])/g, "\\$1");
    },

  };

  // --- Functions ---------------
  YUZO.funcs.isCookieActive = function( name_cookie = 'yuzo' ){
    if( YUZO.vars.geo.helpers.get_cookie( name_cookie ) ) return true;
  };

  YUZO.funcs.save_click = function( type = 'c', post_id_click = 0, yuzo_id = 0, current_article_level = 1 ){ // c=under content, i=inline, w=widget
    YUZO.vars.geo.actions.save_geo({
      action  : 'yuzo-save-click',
      post_id : yuzo_vars.post_id,
      url     : yuzo_vars.url,
      where_is: yuzo_vars.where_is,
      ajaxurl : yuzo_vars.ajaxurl,
      nonce   : yuzo_vars.nonce, //yuzo_vars.nonce,
      others  : JSON.stringify({
                  type                 : type,
                  post_id_click        : post_id_click,
                  yuzo_id              : yuzo_id,
                  is_logged            : yuzo_vars.is_logged,
                  level_article        : yuzo_vars.level_article,
                  current_article_level: current_article_level
                }),
    });
  }


  // --- Yuzo exec ---------------
  $.fn.yuzo_register_click = function() {
    return this.each( function() {
      var $this = $(this);
      $this.find('li').off('click').on('click', function(){

        event.preventDefault();

        var link    = $(this).find('a').attr("data-href") ? $(this).find('a').attr("data-href") : $(this).find('a').attr("href"),
            post_id = $(this).attr("post-id"),
            target  = $(this).find('a').attr("target");

        YUZO.funcs.save_click(  $this.closest('[data-type]').data('type'),
                                post_id,
                                $this.closest('[data-id]').data('id'),
                                $this.closest('[data-level]').data('level') );
        setTimeout( function(){
          if( target == '_blank' ){
            window.open(link,'_blank');
          }else{
            window.location.href = link;
          }
        }, 0);

      });
    } );
  };

  $.fn.save_view = function(){
    if( yuzo_vars.off_views != 1 && yuzo_vars.disabled_counter != 1 && ! (yuzo_vars.off_views_logged == 1 && yuzo_vars.is_logged == 1) && ( yuzo_vars.post_id > 0 || yuzo_vars.post_id != null ) && yuzo_vars.allows_to_count_visits == 1 ){
      setTimeout( function(){
        YUZO.vars.geo.actions.save_others({
          action   : 'yuzo-save-view',
          post_id  : yuzo_vars.post_id,
          ajaxurl  : yuzo_vars.ajaxurl,
          nonce    : yuzo_vars.nonce2,
          others  : JSON.stringify({is_logged: yuzo_vars.is_logged}),
        });
      }, 0)
    }
  };

  // --- Document ready and run scripts ---------------
  $(document).ready( function() {
    $('.wp-yuzo').yuzo_register_click();
    $('body.single, body.page').save_view();
  });

} )( jQuery, window, document );