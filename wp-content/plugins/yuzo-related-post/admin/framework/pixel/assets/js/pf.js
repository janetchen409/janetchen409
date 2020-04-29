/*
|--------------------------------------------------------------------------
| Pixel Framework Scripts
|--------------------------------------------------------------------------
|
| Libraries and functions that allow the correct functioning of Pixel.
| All based on the backend.
|
*/
;(function( $, window, document, undefined ) {
  'use strict';

  // --- Constants ---------------
  var PF = PF || {};

  PF.vars = {
    onloaded : false,
    $body: $('body'),
    $window  : $(window),
    $document: $(document),
    is_rtl: $('body').hasClass('rtl'),
    code_themes: [],
  };

  PF.funcs = {};

  // --- Helpers ---------------
  PF.helper = {

    // uID user
    uid: function ( prefix ) {
      return ( prefix || '' ) + Math.random().toString(36).substr(2, 9);
    },

    // Quote regular expression characters
    preg_quote: function( str ) {
      return (str+'').replace(/(\[|\-|\])/g, "\\$1");
    },

    // Reneme input names
    name_nested_replace: function( $selector, field_id ) {

      var checks = [];
      var regex  = new RegExp('('+ PF.helper.preg_quote(field_id) +')\\[(\\d+)\\]', 'g');

      $selector.find(':radio').each(function() {
        if( this.checked || this.orginal_checked ) {
          this.orginal_checked = true;
        }
      });

      $selector.each( function( index ) {
        $(this).find(':input').each(function() {
          this.name = this.name.replace(regex, field_id +'['+ index +']');
          if( this.orginal_checked ) {
            this.checked = true;
          }
        });
      });

    },

    // Debounce
    debounce: function( callback, threshold, immediate ) {
      var timeout;
      return function() {
        var context = this, args = arguments;
        var later = function() {
          timeout = null;
          if( !immediate ) {
            callback.apply(context, args);
          }
        };
        var callNow = ( immediate && !timeout );
        clearTimeout( timeout );
        timeout = setTimeout( later, threshold );
        if( callNow ) {
          callback.apply(context, args);
        }
      };
    },

    // Get a cookie
    get_cookie: function( name ) {

      var e, b, cookie = document.cookie, p = name + '=';
      if( ! cookie ) {
        return;
      }

      b = cookie.indexOf( '; ' + p );
      if( b === -1 ) {
        b = cookie.indexOf(p);
        if( b !== 0 ) {
          return null;
        }
      } else {
        b += 2;
      }

      e = cookie.indexOf( ';', b );
      if( e === -1 ) {
        e = cookie.length;
      }

      return decodeURIComponent( cookie.substring( b + p.length, e ) );
    },

    // Set cookie
    set_cookie: function( name, value, expires, path, domain, secure ) {

      var d = new Date();

      if( typeof( expires ) === 'object' && expires.toGMTString ) {
        expires = expires.toGMTString();
      } else if( parseInt( expires, 10 ) ) {
        d.setTime( d.getTime() + ( parseInt( expires, 10 ) * 1000 ) );
        expires = d.toGMTString();
      } else {
        expires = '';
      }

      document.cookie = name + '=' + encodeURIComponent( value ) +
        ( expires ? '; expires=' + expires : '' ) +
        ( path    ? '; path=' + path       : '' ) +
        ( domain  ? '; domain=' + domain   : '' ) +
        ( secure  ? '; secure'             : '' );

    },

    // Remove a cookie
    remove_cookie: function( name, path, domain, secure ) {
      PF.helper.set_cookie( name, '', -1000, path, domain, secure );
    },

    // Parse JSON with functions
    fix: function(obj){
      for (var property in obj) {
        if (obj.hasOwnProperty(property)) {
          obj[property] = eval("(" + obj[property] + ")");
        }
      }
    }
  }

  // --- Custom clone for textarea and select clone() bug ---------------
  $.fn.pf_clone = function() {

    var base   = $.fn.clone.apply(this, arguments),
        clone  = this.find('select').add(this.filter('select')),
        cloned = base.find('select').add(base.filter('select'));

    for( var i = 0; i < clone.length; ++i ) {
      for( var j = 0; j < clone[i].options.length; ++j ) {

        if( clone[i].options[j].selected === true ) {
          cloned[i].options[j].selected = true;
        }

      }
    }

    this.find(':radio').each( function() {
      this.orginal_checked = this.checked;
    });

    return base;

  };

  // --- Expand All Options ---------------
  $.fn.pf_expand_all = function() {
    return this.each( function() {
      $(this).on('click', function( e ) {

        e.preventDefault();
        $('.pf-wrapper').toggleClass('pf-show-all');
        $('.pf-section').pf_reload_script();
        $(this).find('.fa').toggleClass('fa-indent').toggleClass('fa-outdent');

      });
    });
  };

  // --- Navigation options ---------------
  $.fn.pf_nav_options = function(){

    var id_object = $(".pf").data("unique");
    $(".pf.pf-" + id_object + " .pf-nav-options a.nav-tab").on("click", function() {
      var tab_id = $(this).attr("data-tab-id");
      PF.helper.set_cookie('pf-last-options-tab-' + id_object, tab_id);
    });

    return this.each( function () {

      var $nav          = $(this),
          $links        = $nav.find('a'),
          $hidden       = $nav.closest('.pf').find('.pf-section-id'),
          $last_section = null;

      $(window).on('hashchange', function () {

        var hash  = window.location.hash.match(new RegExp('tab=([^&]*)'));
        var slug  = hash ? hash[1] : $links.first().attr('href').replace('#tab=', '');
        var $link = $('#pf-tab-link-' + ( !hash ? 1 : slug ) );

        if( $link.length > 0 ){

          $link.closest('.pf-tab-depth-0')
            .find("a.nav-tab")
            .addClass('nav-tab-active')
            .siblings()
            .removeClass('nav-tab-active');

          $links.removeClass('pf-section-active');
          $links.removeClass('nav-tab-active');
          $link.addClass('pf-section-active');
          $link.addClass('nav-tab-active');

          // Also activate the parent link in case of being a child
          var $parent_link = $link.closest('.pf-tab-depth-0');
          if( $parent_link.children('a.nav-tab').length > 0 ){
            $parent_link.children('a.nav-tab').addClass('pf-section-active nav-tab-active');
            $parent_link.addClass('nav-tab-parent-active').siblings().removeClass('nav-tab-parent-active');
          }

          if( $last_section !== undefined ) {
            $($last_section).hide();
          }

          var $section = $('#pf-section-'+slug);
          $section.css({display: 'block'});
          $section.pf_reload_script();

          $hidden.val(slug);

          $last_section = $section;

        }

      }).trigger('hashchange');

    });
  }

  // --- Metabox Tabs ---------------
  $.fn.pf_nav_metabox = function() {
    return this.each( function() {

      var $nav      = $(this),
          $links    = $nav.find('a'),
          unique_id = $nav.data('unique'),
          post_id   = $('#post_ID').val() || 'global',
          $last_section,
          $last_link;

      $links.on('click', function( e ) {

        e.preventDefault();

        var $link      = $(this),
            section_id = $link.data('section');

        if( $last_link !== undefined ) {
          $last_link.removeClass('pf-section-active');
        }

        if( $last_section !== undefined ) {
          $last_section.hide();
        }

        $link.addClass('pf-section-active');

        var $section = $('#pf-section-'+section_id);
        $section.css({display: 'block'});
        $section.pf_reload_script();

        PF.helper.set_cookie('pf-last-metabox-tab-'+ post_id +'-'+ unique_id, section_id);

        $last_section = $section;
        $last_link    = $link;

      });

      var get_cookie = PF.helper.get_cookie('pf-last-metabox-tab-'+ post_id +'-'+ unique_id);

      if( get_cookie ) {
        $nav.find('a[data-section="'+ get_cookie +'"]').trigger('click');
      } else {
        $links.first('a').trigger('click');
      }

    });
  };

  // --- Metabox Page Templates Listener ---------------
  $.fn.pf_page_templates = function() {
    if( this.length ) {

      $(document).on('change', '.editor-page-attributes__template select, #page_template', function() {

        var maybe_value = $(this).val() || 'default';

        $('.pf-page-templates').removeClass('pf-show').addClass('pf-hide');
        $('.pf-page-'+maybe_value.toLowerCase().replace(/[^a-zA-Z0-9]+/g,'-')).removeClass('pf-hide').addClass('pf-show');

      });

    }
  };

  // --- Metabox Post Formats Listener ---------------
  $.fn.pf_post_formats = function() {
    if( this.length ) {

      $(document).on('change', '.editor-post-format select, #formatdiv input[name="post_format"]', function() {

        var maybe_value = $(this).val() || 'default';

        // Fallback for classic editor version
        maybe_value = ( maybe_value === '0' ) ? 'default' : maybe_value;

        $('.pf-post-formats').removeClass('pf-show').addClass('pf-hide');
        $('.pf-post-format-'+maybe_value).removeClass('pf-hide').addClass('pf-show');

      });

    }
  };

  // --- Search ---------------
  $.fn.pf_search = function() {
    return this.each( function() {

      var $this    = $(this),
          $input   = $this.find('input');

      $input.on('change keyup', function() {

        var value    = $(this).val(),
            $wrapper = $('.pf-wrapper'),
            $section = $wrapper.find('.pf-section'),
            $fields  = $section.find('> .pf-field:not(.hidden)'),
            $titles  = $fields.find('> .pf-title, .pf-search-tags');

        if( value.length > 3 ) {

          $fields.addClass('pf-hidden');
          $wrapper.addClass('pf-search-all');

          $titles.each( function() {

            var $title = $(this);

            if( $title.text().match( new RegExp('.*?' + value + '.*?', 'i') ) ) {

              var $field = $title.closest('.pf-field');

              $field.removeClass('pf-hidden');
              $field.parent().pf_reload_script();

            }

          });

        } else {

          $fields.removeClass('pf-hidden');
          $wrapper.removeClass('pf-search-all');

        }

      });

    });
  };

  $.fn.pf_sticky = function() {
    return this.each( function() {

      var $this     = $(this),
          $window   = $(window),
          $inner    = $this.find('.pf-header-inner'),
          padding   = parseInt( $inner.css('padding-left') ) + parseInt( $inner.css('padding-right') ),
          padding   = 0,
          offset    = 32,
          scrollTop = 0,
          lastTop   = 0,
          ticking   = false,
          stickyUpdate = function() {

            var offsetTop = $this.offset().top,
                stickyTop = Math.max(offset, offsetTop - scrollTop ),
                winWidth  = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);

            if( stickyTop <= offset && winWidth > 782 ) {
              $inner.css({width: $this.outerWidth()-padding});
              $this.css({height: $this.outerHeight()}).addClass( 'pf-sticky' );
            } else {
              $inner.removeAttr('style');
              $this.removeAttr('style').removeClass( 'pf-sticky' );
            }

          },
          requestTick = function() {

            if( !ticking ) {
              requestAnimationFrame( function() {
                stickyUpdate();
                ticking = false;
              });
            }

            ticking = true;

          },
          onSticky  = function() {

            scrollTop = $window.scrollTop();
            requestTick();

          };

      $window.on( 'scroll resize', onSticky);

      onSticky();

    });
  };

  // --- Dependency System ---------------
  $.fn.pf_dependency = function() {
    return this.each( function() {

      var $this   = $(this),
          ruleset = $.pf_deps.createRuleset(),
          depends   = [],
          is_global = false;

      $this.children('[data-controller]').each( function() {

        var $field      = $(this),
            controllers = $field.data('controller').split('|'),
            conditions  = $field.data('condition').split('|'),
            values      = $field.data('value').toString().split('|'),
            rules       = ruleset;

        if( $field.data('depend-global') ) {
          is_global = true;
        }

        $.each(controllers, function( index, depend_id ) {

          var value     = values[index] || '',
              condition = conditions[index] || conditions[0];

          rules = rules.createRule('[data-depend-id="'+ depend_id +'"]', condition, value);

          rules.include($field);

          depends.push(depend_id);

        });

      });

      if( depends.length ) {
        if( is_global ) {
          $.pf_deps.enable(PF.vars.$body, ruleset, depends);
        } else {
          $.pf_deps.enable($this, ruleset, depends);
        }

      }

    });
  };

  // --- Field Accordion ---------------
  $.fn.pf_field_accordion = function() {
    return this.each( function() {

      var $titles         = $(this).find('.pf-accordion-title'),
          $all_items      = $(this).find('.pf-accordion-items');

      // check if a panel is open
      $all_items.find('.pf-accordion-item').each(function(){
        if( $(this).hasClass('pf-accordion-item-open') ){
          $(this).find('.pf-accordion-open').pf_reload_script();
        }
      });

      $titles.on('click', function() {

        var $title   = $(this),
            $icon    = $title.find('.pf-accordion-icon'),
            $content = $title.next(),
            item_id  = $title.parent().data('id'),
            $is_collapsible_in = $title.closest('.pf-accordion-items.pf-accordion-collapsible').length;

        if( $icon.hasClass('fa-angle-right') ) {
          $icon.removeClass('fa-angle-right').addClass('fa-angle-down');
        } else {
          $icon.removeClass('fa-angle-down').addClass('fa-angle-right');
        }

        if( $is_collapsible_in ){
          $all_items.find('.pf-accordion-item').each(function(){
            if( item_id != $(this).data('id') ){
              $(this).find('.pf-accordion-title').removeClass('pf-accordion-open-title');
              $(this).find('.pf-accordion-content').removeClass('pf-accordion-open');
            }
          });
        }else{
          if( !$content.data( 'opened' ) ) {
            $content.data( 'opened', true );
          }
        }

        $title.toggleClass('pf-accordion-open-title');
        $content.toggleClass('pf-accordion-open');

        $content.pf_reload_script();
      });

    });
  };

  // --- Field backup ---------------
  $.fn.pf_field_backup = function() {
    return this.each( function() {

      if( window.wp.customize === undefined ) { return; }

      var base    = this,
          $this   = $(this),
          $body   = $('body'),
          $import = $this.find('.pf-import'),
          $reset  = $this.find('.pf-reset');

      base.notification = function( message_text ) {

        if( wp.customize.notifications && wp.customize.OverlayNotification ) {

          // clear if there is any saved data.
          if( !wp.customize.state('saved').get() ) {
            wp.customize.state('changesetStatus').set('trash');
            wp.customize.each( function( setting ) { setting._dirty = false; });
            wp.customize.state('saved').set(true);
          }

          // then show a notification overlay
          wp.customize.notifications.add( new wp.customize.OverlayNotification('pf_field_backup_notification', {
            type: 'info',
            message: message_text,
            loading: true
          }));

        }

      };

      $reset.on('click', function( e ) {

        e.preventDefault();

        if( PF.vars.is_confirm ) {

          base.notification( window.PF_vars.i18n.reset_notification );

          window.wp.ajax.post( 'pf-reset', {
            unique: $reset.data('unique'),
            nonce: $reset.data('nonce')
          })
          .done( function( response ) {
            window.location.reload(true);
          })
          .fail( function( response ) {
            alert( response.error );
            wp.customize.notifications.remove('pf_field_backup_notification');
          });

        }

      });

      $import.on('click', function( e ) {

        e.preventDefault();
        if( PF.vars.is_confirm ) {

          base.notification( window.pf_vars.i18n.import_notification );
          window.wp.ajax.post( 'pf-import', {
            unique: $import.data('unique'),
            nonce: $import.data('nonce'),
            import_data: $this.find('.pf-import-data').val(),
            where: $this.find('.pf-backup-where').val(),
            post_id: $this.find('.pf-backup-post_id').val()
          }).done( function( response ) {
            window.location.reload(true);
          }).fail( function( response ) {
            alert( response.error );
            wp.customize.notifications.remove('pf_field_backup_notification');
          });

        }

      });

    });
  };

  // --- Field background ---------------
  $.fn.pf_field_background = function() {
    return this.each( function() {
      $(this).find('.pf--media').pf_reload_script();
    });
  };

  // --- Field Code Editor ---------------
  $.fn.pf_field_code_editor = function() {
    return this.each( function() {

      if( typeof CodeMirror !== 'function' ) { return; }

      var $this       = $(this),
          $textarea   = $this.find('textarea'),
          $inited     = $this.find('.CodeMirror'),
          data_editor = $textarea.data('editor');

      if( $inited.length ) {
        $inited.remove();
      }

      var interval = setInterval(function () {
        if( $this.is(':visible') ) {

          var code_editor = CodeMirror.fromTextArea( $textarea[0], data_editor );

          // load code-mirror theme css.
          if( data_editor.theme !== 'default' && PF.vars.code_themes.indexOf(data_editor.theme) === -1 ) {

            var $cssLink = $('<link>');

            $('#pf-codemirror-css').after( $cssLink );

            $cssLink.attr({
              rel: 'stylesheet',
              id: 'pf-codemirror-'+ data_editor.theme +'-css',
              href: data_editor.cdnURL +'/theme/'+ data_editor.theme +'.min.css',
              type: 'text/css',
              media: 'all'
            });

            PF.vars.code_themes.push(data_editor.theme);

          }

          CodeMirror.modeURL = data_editor.cdnURL +'/mode/%N/%N.min.js';
          CodeMirror.autoLoadMode(code_editor, data_editor.mode);

          code_editor.on( 'change', function( editor, event ) {
            $textarea.val( code_editor.getValue() ).trigger('change');
          });

          clearInterval(interval);

        }
      });

    });
  };

  // --- Field Date ---------------
  $.fn.pf_field_date = function() {
    return this.each( function() {

      var $this    = $(this),
          $inputs  = $this.find('input'),
          settings = $this.find('.pf-date-settings').data('settings'),
          wrapper  = '<div class="pf-datepicker-wrapper"></div>',
          $datepicker;

      var defaults = {
        showAnim: '',
        beforeShow: function(input, inst) {
          $(inst.dpDiv).addClass('pf-datepicker-wrapper');
        },
        onClose: function( input, inst ) {
          $(inst.dpDiv).removeClass('pf-datepicker-wrapper');
        },
      };

      settings = $.extend({}, settings, defaults);

      if( $inputs.length === 2 ) {

        settings = $.extend({}, settings, {
          onSelect: function( selectedDate ) {

            var $this  = $(this),
                $from  = $inputs.first(),
                option = ( $inputs.first().attr('id') === $(this).attr('id') ) ? 'minDate' : 'maxDate',
                date   = $.datepicker.parseDate( settings.dateFormat, selectedDate );

            $inputs.not(this).datepicker('option', option, date );

          }
        });

      }

      $inputs.each( function(){

        var $input = $(this);

        if( $input.hasClass('hasDatepicker') ) {
          $input.removeAttr('id').removeClass('hasDatepicker');
        }

        $input.datepicker(settings);

      });

    });
  };

  // --- Field fieldset ---------------
  $.fn.pf_field_fieldset = function() {
    return this.each( function() {
      $(this).find('.pf-fieldset-content').pf_reload_script();
    });
  };

  // ---  Field gallery ---------------
  $.fn.pf_field_gallery = function() {
    return this.each( function() {

      var $this  = $(this),
          $edit  = $this.find('.pf-edit-gallery'),
          $clear = $this.find('.pf-clear-gallery'),
          $list  = $this.find('ul'),
          $input = $this.find('input'),
          $img   = $this.find('img'),
          wp_media_frame;

      $this.on('click', '.pf-button, .pf-edit-gallery', function( e ) {

        var $el   = $(this),
            ids   = $input.val(),
            what  = ( $el.hasClass('pf-edit-gallery') ) ? 'edit' : 'add',
            state = ( what === 'add' && !ids.length ) ? 'gallery' : 'gallery-edit';

        e.preventDefault();

        if( typeof window.wp === 'undefined' || ! window.wp.media || ! window.wp.media.gallery ) { return; }

        // Open media with state
        if( state === 'gallery' ) {

          wp_media_frame = window.wp.media({
            library: {
              type: 'image'
            },
            frame: 'post',
            state: 'gallery',
            multiple: true
          });

          wp_media_frame.open();

        } else {

          wp_media_frame = window.wp.media.gallery.edit( '[gallery ids="'+ ids +'"]' );

          if( what === 'add' ) {
            wp_media_frame.setState('gallery-library');
          }

        }

        // Media Update
        wp_media_frame.on( 'update', function( selection ) {

          $list.empty();

          var selectedIds = selection.models.map( function( attachment ) {

            var item  = attachment.toJSON();
            var thumb = ( item.sizes && item.sizes.thumbnail && item.sizes.thumbnail.url ) ? item.sizes.thumbnail.url : item.url;

            $list.append('<li><img src="'+ thumb +'"></li>');

            return item.id;

          });

          $input.val( selectedIds.join( ',' ) ).trigger('change');
          $clear.removeClass('hidden');
          $edit.removeClass('hidden');

        });

      });

      $clear.on('click', function( e ) {
        e.preventDefault();
        $list.empty();
        $input.val('').trigger('change');
        $clear.addClass('hidden');
        $edit.addClass('hidden');
      });

    });

  };

  // --- Field group ---------------
  $.fn.pf_field_group = function() {
    return this.each( function() {

      var $this     = $(this),
          $fieldset = $this.children('.pf-fieldset'),
          $group    = $fieldset.length ? $fieldset : $this,
          $wrapper  = $group.children('.pf-cloneable-wrapper'),
          $hidden   = $group.children('.pf-cloneable-hidden'),
          $max      = $group.children('.pf-cloneable-max'),
          $min      = $group.children('.pf-cloneable-min'),
          field_id  = $wrapper.data('field-id'),
          unique_id = $wrapper.data('unique-id'),
          is_number = Boolean( Number( $wrapper.data('title-number') ) ),
          max       = parseInt( $wrapper.data('max') ),
          min       = parseInt( $wrapper.data('min') );

      // clear accordion arrows if multi-instance
      if( $wrapper.hasClass('ui-accordion') ) {
        $wrapper.find('.ui-accordion-header-icon').remove();
      }

      var update_title_numbers = function( $selector ) {
        $selector.find('.pf-cloneable-title-number').each( function( index ) {
          $(this).html( ( $(this).closest('.pf-cloneable-item').index()+1 ) + '.' );
        });
      };

      $wrapper.accordion({
        header: '> .pf-cloneable-item > .pf-cloneable-title',
        collapsible : true,
        active: false,
        animate: false,
        heightStyle: 'content',
        icons: {
          'header': 'pf-cloneable-header-icon fa fa-angle-right',
          'activeHeader': 'pf-cloneable-header-icon fa fa-angle-down'
        },
        activate: function( event, ui ) {

          var $panel  = ui.newPanel;
          var $header = ui.newHeader;

          if( $panel.length && !$panel.data( 'opened' ) ) {

            var $fields = $panel.children();
            var $first  = $fields.first().find(':input').first();
            var $title  = $header.find('.pf-cloneable-value');

            $first.on('keyup', function( event ) {
              $title.text($first.val());
            });

            $panel.pf_reload_script();
            $panel.data( 'opened', true );
            $panel.data( 'retry', false );

          } else if( $panel.data( 'retry' ) ) {

            $panel.pf_reload_script_retry();
            $panel.data( 'retry', false );

          }

        }
      });

      $wrapper.sortable({
        axis: 'y',
        handle: '.pf-cloneable-title,.pf-cloneable-sort',
        helper: 'original',
        cursor: 'move',
        placeholder: 'widget-placeholder',
        start: function( event, ui ) {

          $wrapper.accordion({ active:false });
          $wrapper.sortable('refreshPositions');
          ui.item.children('.pf-cloneable-content').data('retry', true);

        },
        update: function( event, ui ) {

          PF.helper.name_nested_replace( $wrapper.children('.pf-cloneable-item'), field_id );
          $wrapper.pf_customizer_refresh();

          if( is_number ) {
            update_title_numbers($wrapper);
          }

        },
      });

      $group.children('.pf-cloneable-add').on('click', function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.pf-cloneable-item').length;

        $min.hide();

        if( max && (count+1) > max ) {
          $max.show();
          return;
        }

        var new_field_id = unique_id + field_id + '['+ count +']';

        var $cloned_item = $hidden.pf_clone(true);

        $cloned_item.removeClass('pf-cloneable-hidden');

        $cloned_item.find(':input[name!="_pseudo"]').each( function() {
          this.name = new_field_id + this.name.replace( ( this.name.startsWith('_nonce') ? '_nonce' : unique_id ), '');
        });

        $cloned_item.find('.pf-data-wrapper').each( function(){
          $(this).attr('data-unique-id', new_field_id );
        });

        $wrapper.append($cloned_item);
        $wrapper.accordion('refresh');
        $wrapper.accordion({active: count});
        $wrapper.pf_customizer_refresh();
        $wrapper.pf_customizer_listen({closest: true});
        if( is_number ) {
          update_title_numbers($wrapper);
        }

      });

      var event_clone = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.pf-cloneable-item').length;

        $min.hide();

        if( max && (count+1) > max ) {
          $max.show();
          return;
        }

        var $this           = $(this),
            $parent         = $this.parent().parent(),
            $cloned_helper  = $parent.children('.pf-cloneable-helper').pf_clone(true),
            $cloned_title   = $parent.children('.pf-cloneable-title').pf_clone(),
            $cloned_content = $parent.children('.pf-cloneable-content').pf_clone(),
            cloned_regex    = new RegExp('('+ PF.helper.preg_quote(field_id) +')\\[(\\d+)\\]', 'g');

        $cloned_content.find('.pf-data-wrapper').each( function(){
          var $this = $(this);
          $this.attr('data-unique-id', $this.attr('data-unique-id').replace(cloned_regex, field_id +'['+ ($parent.index()+1) +']') );
        });

        var $cloned = $('<div class="pf-cloneable-item" />');

        $cloned.append($cloned_helper);
        $cloned.append($cloned_title);
        $cloned.append($cloned_content);

        $wrapper.children().eq($parent.index()).after($cloned);

        PF.helper.name_nested_replace( $wrapper.children('.pf-cloneable-item'), field_id );

        $wrapper.accordion('refresh');
        $wrapper.pf_customizer_refresh();
        $wrapper.pf_customizer_listen({closest: true});
        if( is_number ) {
          update_title_numbers($wrapper);
        }

      };

      $wrapper.children('.pf-cloneable-item').children('.pf-cloneable-helper').on('click', '.pf-cloneable-clone', event_clone);
      $group.children('.pf-cloneable-hidden').children('.pf-cloneable-helper').on('click', '.pf-cloneable-clone', event_clone);

      var event_remove = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.pf-cloneable-item').length;

        $max.hide();
        $min.hide();

        if( min && (count-1) < min ) {
          $min.show();
          return;
        }

        $(this).closest('.pf-cloneable-item').remove();

        PF.helper.name_nested_replace( $wrapper.children('.pf-cloneable-item'), field_id );
        $wrapper.pf_customizer_refresh();
        if( is_number ) {
          update_title_numbers($wrapper);
        }

      };

      $wrapper.children('.pf-cloneable-item').children('.pf-cloneable-helper').on('click', '.pf-cloneable-remove', event_remove);
      $group.children('.pf-cloneable-hidden').children('.pf-cloneable-helper').on('click', '.pf-cloneable-remove', event_remove);

    });
  };

  // --- Field icon ---------------
  $.fn.pf_field_icon = function() {
    return this.each( function() {

      var $this = $(this);

      $this.on('click', '.pf-icon-add', function( e ) {

        e.preventDefault();

        var $button = $(this);
        var $modal  = $('#pf-modal-icon');

        $modal.show();

        PF.vars.$icon_target = $this;

        if( !PF.vars.icon_modal_loaded ) {

          $modal.find('.pf-modal-loading').show();

          window.wp.ajax.post( 'pf-get-icons', { nonce: $button.data('nonce') } ).done( function( response ) {

            $modal.find('.pf-modal-loading').hide();

            PF.vars.icon_modal_loaded = true;

            var $load = $modal.find('.pf-modal-load').html( response.content );

            $load.on('click', 'a', function( e ) {

              e.preventDefault();

              var icon = $(this).data('pf-icon');

              PF.vars.$icon_target.find('i').removeAttr('class').addClass(icon);
              PF.vars.$icon_target.find('input').val(icon).trigger('change');
              PF.vars.$icon_target.find('.pf-icon-preview').removeClass('hidden');
              PF.vars.$icon_target.find('.pf-icon-remove').removeClass('hidden');

              $modal.hide();

            });

            $modal.on('change keyup', '.pf-icon-search', function() {

              var value  = $(this).val(),
                  $icons = $load.find('a');

              $icons.each( function() {

                var $elem = $(this);

                if( $elem.data('pf-icon').search( new RegExp( value, 'i' ) ) < 0 ) {
                  $elem.hide();
                } else {
                  $elem.show();
                }

              });

            });

            $modal.on('click', '.pf-modal-close, .pf-modal-overlay', function() {
              $modal.hide();

            });

          }).fail( function( response ) {
            $modal.find('.pf-modal-loading').hide();
            $modal.find('.pf-modal-load').html( response.error );
            $modal.on('click', function() { $modal.hide(); });
          });

        }

      });

      $this.on('click', '.pf-icon-remove', function( e ) {

        e.preventDefault();

        $this.find('.pf-icon-preview').addClass('hidden');
        $this.find('input').val('').trigger('change');
        $(this).addClass('hidden');

      });

    });
  };

  // --- Field media ---------------
  $.fn.pf_field_media = function() {
    return this.each( function() {

      var $this          = $(this),
          $upload_button = $this.find('.pf--button'),
          $remove_button = $this.find('.pf--remove'),
          $library       = $upload_button.data('library') && $upload_button.data('library').split(',') || '',
          wp_media_frame;

      $upload_button.on('click', function( e ) {

        e.preventDefault();

        if( typeof window.wp === 'undefined' || ! window.wp.media || ! window.wp.media.gallery ) {
          return;
        }

        if( wp_media_frame ) {
          wp_media_frame.open();
          return;
        }

        wp_media_frame = window.wp.media({
          library: {
            type: $library
          }
        });

        wp_media_frame.on( 'select', function() {

          var thumbnail;
          var attributes   = wp_media_frame.state().get('selection').first().attributes;
          var preview_size = $upload_button.data('preview-size') || 'thumbnail';

          if( $library.length && $library.indexOf(attributes.subtype) === -1 && $library.indexOf(attributes.type) === -1 ) {
            return;
          }

          $this.find('.pf--id').val( attributes.id );
          $this.find('.pf--width').val( attributes.width );
          $this.find('.pf--height').val( attributes.height );
          $this.find('.pf--alt').val( attributes.alt );
          $this.find('.pf--title').val( attributes.title );
          $this.find('.pf--description').val( attributes.description );

          if( typeof attributes.sizes !== 'undefined' && typeof attributes.sizes.thumbnail !== 'undefined' && preview_size === 'thumbnail' ) {
            thumbnail = attributes.sizes.thumbnail.url;
          } else if( typeof attributes.sizes !== 'undefined' && typeof attributes.sizes.full !== 'undefined' ) {
            thumbnail = attributes.sizes.full.url;
          } else {
            thumbnail = attributes.icon;
          }

          $remove_button.removeClass('hidden');
          $this.find('.pf--preview').removeClass('hidden');
          $this.find('.pf--src').attr('src', thumbnail);
          $this.find('.pf--thumbnail').val( thumbnail );
          $this.find('.pf--url').val( attributes.url ).trigger('change');

        });

        wp_media_frame.open();

      });

      $remove_button.on('click', function( e ) {
        e.preventDefault();
        $remove_button.addClass('hidden');
        $this.find('input').val('');
        $this.find('.pf--preview').addClass('hidden');
        $this.find('.pf--thumbnail');
        $this.find('.pf--url').trigger('change');
      });

    });

  };

  // --- Field Repeater ---------------
  $.fn.pf_field_repeater = function() {
    return this.each( function() {

      var $this     = $(this),
          $fieldset = $this.children('.pf-fieldset'),
          $repeater = $fieldset.length ? $fieldset : $this,
          $wrapper  = $repeater.children('.pf-repeater-wrapper'),
          $hidden   = $repeater.children('.pf-repeater-hidden'),
          $max      = $repeater.children('.pf-repeater-max'),
          $min      = $repeater.children('.pf-repeater-min'),
          field_id  = $wrapper.data('field-id'),
          unique_id = $wrapper.data('unique-id'),
          max       = parseInt( $wrapper.data('max') ),
          min       = parseInt( $wrapper.data('min') );

      $wrapper.children('.pf-repeater-item').children('.pf-repeater-content').pf_reload_script();

      $wrapper.sortable({
        axis: 'y',
        handle: '.pf-repeater-sort',
        helper: 'original',
        cursor: 'move',
        placeholder: 'widget-placeholder',
        update: function( event, ui ) {

          PF.helper.name_nested_replace( $wrapper.children('.pf-repeater-item'), field_id );
          $wrapper.pf_customizer_refresh();
          ui.item.pf_reload_script_retry();

        }
      });

      $fieldset.children('.pf-repeater-add').on('click', function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.pf-repeater-item').length;

        $min.hide();

        if( max && (count+1) > max ) {
          $max.show();
          return;
        }
        //console.log( count );
        var new_field_id = unique_id + field_id + '['+ count +']';
        var $cloned_item = $hidden.pf_clone(true);

        $cloned_item.removeClass('pf-repeater-hidden');

        $cloned_item.find(':input[name!="_pseudo"]').each( function() {
          this.name = new_field_id + this.name.replace( ( this.name.startsWith('_nonce') ? '_nonce' : unique_id ), '');
        });

        $cloned_item.find('.pf-data-wrapper').each( function(){
          $(this).attr('data-unique-id', new_field_id );
        });

        $wrapper.append($cloned_item);
        $cloned_item.children('.pf-repeater-content').pf_reload_script();
        $wrapper.pf_customizer_refresh();
        $wrapper.pf_customizer_listen({closest: true});
      });

      var event_clone = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.pf-repeater-item').length;

        $min.hide();

        if( max && (count+1) > max ) {
          $max.show();
          return;
        }

        var $this           = $(this),
            $parent         = $this.parent().parent().parent(),
            $cloned_content = $parent.children('.pf-repeater-content').pf_clone(),
            $cloned_helper  = $parent.children('.pf-repeater-helper').pf_clone(true),
            cloned_regex    = new RegExp('('+ PF.helper.preg_quote(field_id) +')\\[(\\d+)\\]', 'g');

        $cloned_content.find('.pf-data-wrapper').each( function(){
          var $this = $(this);
          $this.attr('data-unique-id', $this.attr('data-unique-id').replace(cloned_regex, field_id +'['+ ($parent.index()+1) +']') );
        });

        var $cloned = $('<div class="pf-repeater-item" />');

        $cloned.append($cloned_content);
        $cloned.append($cloned_helper);

        $wrapper.children().eq($parent.index()).after($cloned);

        $cloned.children('.pf-repeater-content').pf_reload_script();

        PF.helper.name_nested_replace( $wrapper.children('.pf-repeater-item'), field_id );

        $wrapper.pf_customizer_refresh();
        $wrapper.pf_customizer_listen({closest: true});
      };

      $wrapper.children('.pf-repeater-item').children('.pf-repeater-helper').on('click', '.pf-repeater-clone', event_clone);
      $repeater.children('.pf-repeater-hidden').children('.pf-repeater-helper').on('click', '.pf-repeater-clone', event_clone);

      var event_remove = function( e ) {

        e.preventDefault();

        var count = $wrapper.children('.pf-repeater-item').length;

        $max.hide();
        $min.hide();

        if( min && (count-1) < min ) {
          $min.show();
          return;
        }

        $(this).closest('.pf-repeater-item').remove();

        PF.helper.name_nested_replace( $wrapper.children('.pf-repeater-item'), field_id );
        $wrapper.pf_customizer_refresh();
      };

      $wrapper.children('.pf-repeater-item').children('.pf-repeater-helper').on('click', '.pf-repeater-remove', event_remove);
      $fieldset.children('.pf-repeater-hidden').children('.pf-repeater-helper').on('click', '.pf-repeater-remove', event_remove);

    });
  };

  // --- Field slider ---------------
  $.fn.pf_field_slider = function() {
    return this.each( function() {

      var $this   = $(this),
          $input  = $this.find('input'),
          $slider = $this.find('.pf-slider-ui'),
          data    = $input.data(),
          value   = $input.val() || 0;

      if( $slider.hasClass('ui-slider') ) {
        $slider.empty();
      }

      $slider.slider({
        range: 'min',
        value: value,
        min: data.min,
        max: data.max,
        step: data.step,
        slide: function( e, o ) {
          $input.val( o.value ).trigger('change');
        }
      });

      $input.keyup( function() {
        $slider.slider('value', $input.val());
      });

    });
  };

  // --- Field sortable ---------------
  $.fn.pf_field_sortable = function() {
    return this.each( function() {

      var $sortable = $(this).find('.pf--sortable');

      $sortable.sortable({
        axis: 'y',
        helper: 'original',
        cursor: 'move',
        placeholder: 'widget-placeholder',
        update: function( event, ui ) {
          $sortable.pf_customizer_refresh();
        }
      });

      $sortable.find('.pf--sortable-content').pf_reload_script();

    });
  };

  // --- Field sorter ---------------
  $.fn.pf_field_sorter = function() {
    return this.each( function() {

      var $this         = $(this),
          $enabled      = $this.find('.pf-enabled'),
          $has_disabled = $this.find('.pf-disabled'),
          $disabled     = ( $has_disabled.length ) ? $has_disabled : false;

      $enabled.sortable({
        connectWith: $disabled,
        placeholder: 'ui-sortable-placeholder',
        update: function( event, ui ) {

          var $el = ui.item.find('input');

          if( ui.item.parent().hasClass('pf-enabled') ) {
            $el.attr('name', $el.attr('name').replace('disabled', 'enabled'));
          } else {
            $el.attr('name', $el.attr('name').replace('enabled', 'disabled'));
          }
          $this.pf_customizer_refresh();
        }
      });

      if( $disabled ) {

        $disabled.sortable({
          connectWith: $enabled,
          placeholder: 'ui-sortable-placeholder',
          update: function( event, ui ) {
            $this.pf_customizer_refresh();
          }
        });

      }

    });
  };

  // --- Field spinner ---------------
  $.fn.pf_field_spinner = function() {
    return this.each( function() {

      var $this   = $(this),
          $input  = $this.find('input'),
          $inited = $this.find('.ui-spinner-button'),
          $unit   = $input.data('unit');

      if( $inited.length ) {
        $inited.remove();
      }
      $input.spinner({
        max: $input.data('max') || 100,
        min: $input.data('min') || 0,
        step: $input.data('step') || 1,
        create: function( event, ui ) {
          if( $unit.length ) {
            $this.find('.ui-spinner-up').after('<span class="ui-button-text-only pf--unit">'+ $unit +'</span>');
          }
        },
        spin: function (event, ui ) {
          $input.val(ui.value).trigger('change');
        }
      });

    });
  };

  // --- Field switcher ---------------
  $.fn.pf_field_switcher = function() {
    return this.each( function() {

      if( $(this).hasClass('pf-field-switcher-disabled') ) return;

      var $switcher = $(this).find('.pf--switcher');

      $switcher.unbind("click").on('click', function() {

      if( $(this).hasClass('pf-field-switcher-disabled') ) return;

        var value  = 0;
        var $input = $switcher.find('input');
        if( $switcher.hasClass('pf--active') ) {
          $switcher.removeClass('pf--active');
        } else {
          value = 1;
          $switcher.addClass('pf--active');
        }

        $input.val(value).trigger('change');

      });

      $switcher.pf_reload_script();

    });
  };

  // --- Field tab ---------------
  $.fn.pf_field_tab = function() {
    return this.each( function() {

      var $this     = $(this),
          $links    = $this.find('.pf-tab-nav a'),
          $sections = $this.find('.pf-tab-section');

      $sections.eq(0).pf_reload_script();

      $links.on( 'click', function( e ) {

        e.preventDefault();

        var $link    = $(this),
            index    = $link.index(),
            $section = $sections.eq(index);

        $link.addClass('pf-tab-active').siblings().removeClass('pf-tab-active');
        $section.pf_reload_script();
        $section.removeClass('hidden').siblings().addClass('hidden');

      });

    });
  };

  // --- Field typography ---------------
  $.fn.pf_field_typography = function() {
    return this.each(function () {

      var base          = this;
      var $this         = $(this);
      var loaded_fonts  = [];
      var webfonts      = pf_typography_json.webfonts;
      var googlestyles  = pf_typography_json.googlestyles;
      var defaultstyles = pf_typography_json.defaultstyles;

      //
      //
      // Sanitize google font subset
      base.sanitize_subset = function( subset ) {
        subset = subset.replace('-ext', ' Extended');
        subset = subset.charAt(0).toUpperCase() + subset.slice(1);
        return subset;
      };

      //
      //
      // Sanitize google font styles (weight and style)
      base.sanitize_style = function( style ) {
        return googlestyles[style] ? googlestyles[style] : style;
      };

      //
      //
      // Load google font
      base.load_google_font = function( font_family, weight, style ) {

        if( font_family && typeof WebFont === 'object' ) {

          weight = weight ? weight.replace('normal', '') : '';
          style  = style ? style.replace('normal', '') : '';

          if( weight || style ) {
            font_family = font_family +':'+ weight + style;
          }

          if( loaded_fonts.indexOf( font_family ) === -1 ) {
            WebFont.load({ google: { families: [font_family] } });
          }

          loaded_fonts.push( font_family );

        }

      };

      //
      //
      // Append select options
      base.append_select_options = function( $select, options, condition, type, is_multi ) {

        $select.find('option').not(':first').remove();

        var opts = '';

        $.each( options, function( key, value ) {

          var selected;
          var name = value;

          // is_multi
          if( is_multi ) {
            selected = ( condition && condition.indexOf(value) !== -1 ) ? ' selected' : '';
          } else {
            selected = ( condition && condition === value ) ? ' selected' : '';
          }

          if( type === 'subset' ) {
            name = base.sanitize_subset( value );
          } else if( type === 'style' ){
            name = base.sanitize_style( value );
          }

          opts += '<option value="'+ value +'"'+ selected +'>'+ name +'</option>';

        });

        $select.append(opts).trigger('pf.change').trigger('chosen:updated');

      };

      base.init = function () {

        //
        //
        // Constants
        var selected_styles = [];
        var $typography     = $this.find('.pf--typography');
        var $type           = $this.find('.pf--type');
        var $styles         = $this.find('.pf--block-font-style');
        var unit            = $typography.data('unit');
        var exclude_fonts   = $typography.data('exclude') ? $typography.data('exclude').split(',') : [];

        //
        //
        // Chosen init
        if( $this.find('.pf--chosen').length ) {

          var $chosen_selects = $this.find('select');

          $chosen_selects.each( function(){

            var $chosen_select = $(this),
                $chosen_inited = $chosen_select.parent().find('.chosen-container');

            if( $chosen_inited.length ) {
              $chosen_inited.remove();
            }

            $chosen_select.chosen({
              allow_single_deselect: true,
              disable_search_threshold: 15,
              width: '100%'
            });

          });

        }

        //
        //
        // Font family select
        var $font_family_select = $this.find('.pf--font-family');
        var first_font_family   = $font_family_select.val();

        // Clear default font family select options
        $font_family_select.find('option').not(':first-child').remove();

        var opts = '';

        $.each(webfonts, function( type, group ) {

          // Check for exclude fonts
          if( exclude_fonts && exclude_fonts.indexOf(type) !== -1 ) { return; }

          opts += '<optgroup label="' + group.label + '">';

          $.each(group.fonts, function( key, value ) {

            // use key if value is object
            value = ( typeof value === 'object' ) ? key : value;
            var selected = ( value === first_font_family ) ? ' selected' : '';
            opts += '<option value="'+ value +'" data-type="'+ type +'"'+ selected +'>'+ value +'</option>';

          });

          opts += '</optgroup>';

        });

        // Append google font select options
        $font_family_select.append(opts).trigger('chosen:updated');

        //
        //
        // Font style select
        var $font_style_block = $this.find('.pf--block-font-style');

        if( $font_style_block.length ) {

          var $font_style_select = $this.find('.pf--font-style-select');
          var first_style_value  = $font_style_select.val() ? $font_style_select.val().replace(/normal/g, '' ) : '';

          //
          // Font Style on on change listener
          $font_style_select.on('change pf.change', function( event ) {

            var style_value = $font_style_select.val();

            // set a default value
            if( !style_value && selected_styles && selected_styles.indexOf('normal') === -1 ) {
              style_value = selected_styles[0];
            }

            // set font weight, for eg. replacing 800italic to 800
            var font_normal = ( style_value && style_value !== 'italic' && style_value === 'normal' ) ? 'normal' : '';
            var font_weight = ( style_value && style_value !== 'italic' && style_value !== 'normal' ) ? style_value.replace('italic', '') : font_normal;
            var font_style  = ( style_value && style_value.substr(-6) === 'italic' ) ? 'italic' : '';

            $this.find('.pf--font-weight').val( font_weight );
            $this.find('.pf--font-style').val( font_style );

          });

          //
          //
          // Extra font style select
          var $extra_font_style_block = $this.find('.pf--block-extra-styles');

          if( $extra_font_style_block.length ) {
            var $extra_font_style_select = $this.find('.pf--extra-styles');
            var first_extra_style_value  = $extra_font_style_select.val();
          }

        }

        //
        //
        // Subsets select
        var $subset_block = $this.find('.pf--block-subset');
        if( $subset_block.length ) {
          var $subset_select = $this.find('.pf--subset');
          var first_subset_select_value = $subset_select.val();
          var subset_multi_select = $subset_select.data('multiple') || false;
        }

        //
        //
        // Backup font family
        var $backup_font_family_block = $this.find('.pf--block-backup-font-family');

        //
        //
        // Font Family on Change Listener
        $font_family_select.on('change pf.change', function( event ) {

          // Hide subsets on change
          if( $subset_block.length ) {
            $subset_block.addClass('hidden');
          }

          // Hide extra font style on change
          if( $extra_font_style_block.length ) {
            $extra_font_style_block.addClass('hidden');
          }

          // Hide backup font family on change
          if( $backup_font_family_block.length ) {
            $backup_font_family_block.addClass('hidden');
          }

          var $selected = $font_family_select.find(':selected');
          var value     = $selected.val();
          var type      = $selected.data('type');

          if( type && value ) {

            // Show backup fonts if font type google or custom
            if( ( type === 'google' || type === 'custom' ) && $backup_font_family_block.length ) {
              $backup_font_family_block.removeClass('hidden');
            }

            // Appending font style select options
            if( $font_style_block.length ) {

              // set styles for multi and normal style selectors
              var styles = defaultstyles;

              // Custom or gogle font styles
              if( type === 'google' && webfonts[type].fonts[value][0] ) {
                styles = webfonts[type].fonts[value][0];
              } else if( type === 'custom' && webfonts[type].fonts[value] ) {
                styles = webfonts[type].fonts[value];
              }

              selected_styles = styles;

              // Set selected style value for avoid load errors
              var set_auto_style  = ( styles.indexOf('normal') !== -1 ) ? 'normal' : styles[0];
              var set_style_value = ( first_style_value && styles.indexOf(first_style_value) !== -1 ) ? first_style_value : set_auto_style;

              // Append style select options
              base.append_select_options( $font_style_select, styles, set_style_value, 'style' );

              // Clear first value
              first_style_value = false;

              // Show style select after appended
              $font_style_block.removeClass('hidden');

              // Appending extra font style select options
              if( type === 'google' && $extra_font_style_block.length && styles.length > 1 ) {

                // Append extra-style select options
                base.append_select_options( $extra_font_style_select, styles, first_extra_style_value, 'style', true );

                // Clear first value
                first_extra_style_value = false;

                // Show style select after appended
                $extra_font_style_block.removeClass('hidden');

              }

            }

            // Appending google fonts subsets select options
            if( type === 'google' && $subset_block.length && webfonts[type].fonts[value][1] ) {

              var subsets          = webfonts[type].fonts[value][1];
              var set_auto_subset  = ( subsets.length < 2 && subsets[0] !== 'latin' ) ? subsets[0] : '';
              var set_subset_value = ( first_subset_select_value && subsets.indexOf(first_subset_select_value) !== -1 ) ? first_subset_select_value : set_auto_subset;

              // check for multiple subset select
              set_subset_value = ( subset_multi_select && first_subset_select_value ) ? first_subset_select_value : set_subset_value;

              base.append_select_options( $subset_select, subsets, set_subset_value, 'subset', subset_multi_select );

              first_subset_select_value = false;

              $subset_block.removeClass('hidden');

            }

          } else {

            // Clear Styles
            $styles.find(':input').val('');

            // Clear subsets options if type and value empty
            if( $subset_block.length ) {
              $subset_select.find('option').not(':first-child').remove();
              $subset_select.trigger('chosen:updated');
            }

            // Clear font styles options if type and value empty
            if( $font_style_block.length ) {
              $font_style_select.find('option').not(':first-child').remove();
              $font_style_select.trigger('chosen:updated');
            }

          }

          // Update font type input value
          $type.val(type);

        }).trigger('pf.change');

        //
        //
        // Preview
        var $preview_block = $this.find('.pf--block-preview');

        if( $preview_block.length ) {

          var $preview = $this.find('.pf--preview');

          // Set preview styles on change
          $this.on('change', PF.helper.debounce( function( event ) {

            $preview_block.removeClass('hidden');

            var font_family       = $font_family_select.val(),
                font_weight       = $this.find('.pf--font-weight').val(),
                font_style        = $this.find('.pf--font-style').val(),
                font_size         = $this.find('.pf--font-size').val(),
                font_variant      = $this.find('.pf--font-variant').val(),
                line_height       = $this.find('.pf--line-height').val(),
                text_align        = $this.find('.pf--text-align').val(),
                text_transform    = $this.find('.pf--text-transform').val(),
                text_decoration   = $this.find('.pf--text-decoration').val(),
                text_color        = $this.find('.pf--color').val(),
                word_spacing      = $this.find('.pf--word-spacing').val(),
                letter_spacing    = $this.find('.pf--letter-spacing').val(),
                custom_style      = $this.find('.pf--custom-style').val(),
                type              = $this.find('.pf--type').val();

            if( type === 'google' ) {
              base.load_google_font(font_family, font_weight, font_style);
            }

            var properties = {};

            if( font_family     ) { properties.fontFamily     = font_family;           }
            if( font_weight     ) { properties.fontWeight     = font_weight;           }
            if( font_style      ) { properties.fontStyle      = font_style;            }
            if( font_variant    ) { properties.fontVariant    = font_variant;          }
            if( font_size       ) { properties.fontSize       = font_size + unit;      }
            if( line_height     ) { properties.lineHeight     = line_height + unit;    }
            if( letter_spacing  ) { properties.letterSpacing  = letter_spacing + unit; }
            if( word_spacing    ) { properties.wordSpacing    = word_spacing + unit;   }
            if( text_align      ) { properties.textAlign      = text_align;            }
            if( text_transform  ) { properties.textTransform  = text_transform;        }
            if( text_decoration ) { properties.textDecoration = text_decoration;       }
            if( text_color      ) { properties.color          = text_color;            }

            $preview.removeAttr('style');

            // Customs style attribute
            if( custom_style ) { $preview.attr('style', custom_style); }

            $preview.css(properties);

          }, 100 ) );

          // Preview black and white backgrounds trigger
          $preview_block.on('click', function() {

            $preview.toggleClass('pf--black-background');

            var $toggle = $preview_block.find('.pf--toggle');

            if( $toggle.hasClass('fa-toggle-off') ) {
              $toggle.removeClass('fa-toggle-off').addClass('fa-toggle-on');
            } else {
              $toggle.removeClass('fa-toggle-on').addClass('fa-toggle-off');
            }

          });

          if( !$preview_block.hasClass('hidden') ) {
            $this.trigger('change');
          }

        }

      };

      base.init();

    });
  };

  // --- Field upload ---------------
  $.fn.pf_field_upload = function() {
    return this.each( function() {

      var $this          = $(this),
          $input         = $this.find('input'),
          $upload_button = $this.find('.pf--button'),
          $remove_button = $this.find('.pf--remove'),
          $library       = $upload_button.data('library') && $upload_button.data('library').split(',') || '',
          wp_media_frame;

      $input.on('change', function( e ) {
        if( $input.val() ) {
          $remove_button.removeClass('hidden');
        } else {
          $remove_button.addClass('hidden');
        }
      });

      $upload_button.on('click', function( e ) {

        e.preventDefault();

        if( typeof window.wp === 'undefined' || ! window.wp.media || ! window.wp.media.gallery ) {
          return;
        }

        if( wp_media_frame ) {
          wp_media_frame.open();
          return;
        }

        wp_media_frame = window.wp.media({
          library: {
            type: $library
          },
        });

        wp_media_frame.on( 'select', function() {

          var attributes = wp_media_frame.state().get('selection').first().attributes;

          if( $library.length && $library.indexOf(attributes.subtype) === -1 && $library.indexOf(attributes.type) === -1 ) {
            return;
          }

          $input.val(attributes.url).trigger('change');

        });

        wp_media_frame.open();

      });

      $remove_button.on('click', function( e ) {
        e.preventDefault();
        $input.val('').trigger('change');
      });

    });

  };

  // --- Field WP Editor ---------------
  $.fn.pf_field_wp_editor = function() {
    return this.each( function() {

      if( typeof window.wp.editor === 'undefined' || typeof window.tinyMCEPreInit === 'undefined' || typeof window.tinyMCEPreInit.mceInit.pf_wp_editor === 'undefined' ) {
        return;
      }

      var $this     = $(this),
          $editor   = $this.find('.pf-wp-editor'),
          $textarea = $this.find('textarea');

      // If there is wp-editor remove it for avoid dupliated wp-editor conflicts.
      var $has_wp_editor = $this.find('.wp-editor-wrap').length || $this.find('.mce-container').length;

      if( $has_wp_editor ) {
        $editor.empty();
        $editor.append($textarea);
        $textarea.css('display', '');
      }

      // Generate a unique id
      var uid = PF.helper.uid('pf-editor-');

      $textarea.attr('id', uid);

      // Get default editor settings
      var default_editor_settings = {
        tinymce: window.tinyMCEPreInit.mceInit.pf_wp_editor,
        quicktags: window.tinyMCEPreInit.qtInit.pf_wp_editor
      };

      // Get default editor settings
      var field_editor_settings = $editor.data('editor-settings');

      // Add on change event handle
      var editor_on_change = function( editor ) {
        editor.on('change', PF.helper.debounce( function() {
          editor.save();
          $textarea.trigger('change');
        }, 250 ) );
      };

      // Callback for old wp editor
      var wpEditor = wp.oldEditor ? wp.oldEditor : wp.editor;

      if( wpEditor && wpEditor.hasOwnProperty('autop') ) {
        wp.editor.autop = wpEditor.autop;
        wp.editor.removep = wpEditor.removep;
        wp.editor.initialize = wpEditor.initialize;
      }

      // Extend editor selector and on change event handler
      default_editor_settings.tinymce = $.extend( {}, default_editor_settings.tinymce, { selector: '#'+ uid, setup: editor_on_change } );

      // Override editor tinymce settings
      if( field_editor_settings.tinymce === false ) {
        default_editor_settings.tinymce = false;
        $editor.addClass('pf-no-tinymce');
      }

      // Override editor quicktags settings
      if( field_editor_settings.quicktags === false ) {
        default_editor_settings.quicktags = false;
        $editor.addClass('pf-no-quicktags');
      }

      // Wait until :visible
      var interval = setInterval(function () {
        if( $this.is(':visible') ) {
          window.wp.editor.initialize(uid, default_editor_settings);
          clearInterval(interval);
        }
      });

      // Add Media buttons
      if( field_editor_settings.media_buttons && window.pf_media_buttons ) {

        var $editor_buttons = $editor.find('.wp-media-buttons');

        if( $editor_buttons.length ) {

          $editor_buttons.find('.pf-shortcode-button').data('editor-id', uid);

        } else {

          var $media_buttons = $(window.pf_media_buttons);

          $media_buttons.find('.pf-shortcode-button').data('editor-id', uid);

          $editor.prepend( $media_buttons );

        }

      }

    });

  };

  // --- Confirm ---------------
  $.fn.pf_confirm = function() {
    return this.each( function() {
      $(this).on('click', function( e ) {
        var confirm_text = $(this).data('confirm') || window.pf_vars.i18n.confirm;
        var confirm_answer  = confirm( confirm_text );
        PF.vars.is_confirm = true;

        if( !confirm_answer ) {
          e.preventDefault();
          PF.vars.is_confirm = false;
          return false;
        }
      });
    });
  };

  $.fn.serializeObject = function(){

    var obj = {};

    $.each( this.serializeArray(), function(i,o){
      var n = o.name,
        v = o.value;

        obj[n] = obj[n] === undefined ? v
          : $.isArray( obj[n] ) ? obj[n].concat( v )
          : [ obj[n], v ];
    });

    return obj;

  };

  // --- Options Save ---------------
  $.fn.pf_save = function() {
    return this.each( function() {

      var $this    = $(this),
          $buttons = $('.pf-save'),
          $panel   = $('.pf-options'),
          flooding = false,
          timeout;

      $this.on('click', function( e ) {

        if( !flooding ) {

          var $text  = $this.data('save'),
              $value = $this.val();

          $buttons.attr('value', $text);

          if( $this.hasClass('pf-save-ajax') ) {

            e.preventDefault();

            $panel.addClass('pf-saving');
            $buttons.prop('disabled', true);

            window.wp.ajax.post( 'pf_'+ $panel.data('unique') +'_ajax_save', {
              data: $('#pf-form').serializeJSONPF()
            })
            .done( function( response ) {

              clearTimeout(timeout);

              var $result_success = $('.pf-form-success');

              $result_success.empty().append(response.notice).slideDown('fast', function() {
                timeout = setTimeout( function() {
                  $result_success.slideUp('fast');
                }, 2000);
              });

              // clear errors
              $('.pf-error').remove();

              var $append_errors = $('.pf-form-error');

              $append_errors.empty().hide();

              if( Object.keys( response.errors ).length ) {

                var error_icon = '<i class="pf-label-error pf-error">!</i>';

                $.each(response.errors, function( key, error_message ) {

                  var $field = $('[data-depend-id="'+ key +'"]'),
                      $link  = $('#pf-tab-link-'+ ($field.closest('.pf-section').index()+1)),
                      $tab   = $link.closest('.pf-tab-depth-0');

                  $field.closest('.pf-fieldset').append( '<p class="pf-text-error pf-error">'+ error_message +'</p>' );

                  if( !$link.find('.pf-error').length ) {
                    $link.append( error_icon );
                  }

                  if( !$tab.find('.pf-arrow .pf-error').length ) {
                    $tab.find('.pf-arrow').append( error_icon );
                  }

                  console.log(error_message);

                  $append_errors.append( '<div>'+ error_icon +' '+ error_message + '</div>' );

                });

                $append_errors.show();

              }

              $panel.removeClass('pf-saving');
              $buttons.prop('disabled', false).attr('value', $value);
              flooding = false;

            })
            .fail( function( response ) {
              alert( response.error );
            });

          }

        }

        flooding = true;

      });

    });
  };

  // --- Shortcode ---------------
  $.fn.pf_shortcode = function() {

    var base = this;

    base.shortcode_parse = function( serialize, key ) {

      var shortcode = '';

      $.each(serialize, function( shortcode_key, shortcode_values ) {

        key = ( key ) ? key : shortcode_key;

        shortcode += '[' + key;

        $.each(shortcode_values, function( shortcode_tag, shortcode_value ) {

          if( shortcode_tag === 'content' ) {

            shortcode += ']';
            shortcode += shortcode_value;
            shortcode += '[/'+ key +'';

          } else {

            shortcode += base.shortcode_tags( shortcode_tag, shortcode_value );

          }

        });

        shortcode += ']';

      });

      return shortcode;

    };

    base.shortcode_tags = function( shortcode_tag, shortcode_value ) {

      var shortcode = '';

      if( shortcode_value !== '' ) {

        if( typeof shortcode_value === 'object' && !$.isArray( shortcode_value ) ) {

          $.each(shortcode_value, function( sub_shortcode_tag, sub_shortcode_value ) {

            // sanitize spesific key/value
            switch( sub_shortcode_tag ) {

              case 'background-image':
                sub_shortcode_value = ( sub_shortcode_value.url  ) ? sub_shortcode_value.url : '';
              break;

            }

            if( sub_shortcode_value !== '' ) {
              shortcode += ' ' + sub_shortcode_tag.replace('-', '_') + '="' + sub_shortcode_value.toString() + '"';
            }

          });

        } else {

          shortcode += ' ' + shortcode_tag.replace('-', '_') + '="' + shortcode_value.toString() + '"';

        }

      }

      return shortcode;

    };

    base.insertAtChars = function( _this, currentValue ) {

      var obj = ( typeof _this[0].name !== 'undefined' ) ? _this[0] : _this;

      if( obj.value.length && typeof obj.selectionStart !== 'undefined' ) {
        obj.focus();
        return obj.value.substring( 0, obj.selectionStart ) + currentValue + obj.value.substring( obj.selectionEnd, obj.value.length );
      } else {
        obj.focus();
        return currentValue;
      }

    };

    base.send_to_editor = function( html, editor_id ) {

      var tinymce_editor;

      if( typeof tinymce !== 'undefined' ) {
        tinymce_editor = tinymce.get( editor_id );
      }

      if( tinymce_editor && !tinymce_editor.isHidden() ) {
        tinymce_editor.execCommand( 'mceInsertContent', false, html );
      } else {
        var $editor = $('#'+editor_id);
        $editor.val( base.insertAtChars( $editor, html ) ).trigger('change');
      }

    };

    return this.each( function() {

      var $modal   = $(this),
          $load    = $modal.find('.pf-modal-load'),
          $content = $modal.find('.pf-modal-content'),
          $insert  = $modal.find('.pf-modal-insert'),
          $loading = $modal.find('.pf-modal-loading'),
          $select  = $modal.find('select'),
          modal_id = $modal.data('modal-id'),
          nonce    = $modal.data('nonce'),
          editor_id,
          target_id,
          gutenberg_id,
          sc_key,
          sc_name,
          sc_view,
          sc_group,
          $cloned,
          $button;

      $(document).on('click', '.pf-shortcode-button[data-modal-id="'+ modal_id +'"]', function( e ) {

        e.preventDefault();

        $button      = $(this);
        editor_id    = $button.data('editor-id')    || false;
        target_id    = $button.data('target-id')    || false;
        gutenberg_id = $button.data('gutenberg-id') || false;

        $modal.show();

        // single usage trigger first shortcode
        if( $modal.hasClass('pf-shortcode-single') && sc_name === undefined ) {
          $select.trigger('change');
        }

      });

      $select.on( 'change', function() {

        var $option   = $(this);
        var $selected = $option.find(':selected');

        sc_key   = $option.val();
        sc_name  = $selected.data('shortcode');
        sc_view  = $selected.data('view') || 'normal';
        sc_group = $selected.data('group') || sc_name;

        $load.empty();

        if( sc_key ) {

          $loading.show();

          window.wp.ajax.post( 'pf-get-shortcode-'+ modal_id, {
            shortcode_key: sc_key,
            nonce: nonce
          })
          .done( function( response ) {

            $loading.hide();

            var $appended = $(response.content).appendTo($load);

            $insert.parent().removeClass('hidden');

            $cloned = $appended.find('.pf--repeat-shortcode').pf_clone();

            $appended.pf_reload_script();
            $appended.find('.pf-fields').pf_reload_script();

          });

        } else {

          $insert.parent().addClass('hidden');

        }

      });

      $insert.on('click', function( e ) {

        e.preventDefault();

        var shortcode = '';
        var serialize = $modal.find('.pf-field:not(.hidden)').find(':input:not(.ignore)').serializeObjectPF();

        switch ( sc_view ) {

          case 'contents':
            var contentsObj = ( sc_name ) ? serialize[sc_name] : serialize;
            $.each(contentsObj, function( sc_key, sc_value ) {
              var sc_tag = ( sc_name ) ? sc_name : sc_key;
              shortcode += '['+ sc_tag +']'+ sc_value +'[/'+ sc_tag +']';
            });
          break;

          case 'group':

            shortcode += '[' + sc_name;
            $.each(serialize[sc_name], function( sc_key, sc_value ) {
              shortcode += base.shortcode_tags( sc_key, sc_value );
            });
            shortcode += ']';
            shortcode += base.shortcode_parse( serialize[sc_group], sc_group );
            shortcode += '[/' + sc_name + ']';

          break;

          case 'repeater':
            shortcode += base.shortcode_parse( serialize[sc_group], sc_group );
          break;

          default:
            shortcode += base.shortcode_parse( serialize );
          break;

        }

        shortcode = ( shortcode === '' ) ? '['+ sc_name +']' : shortcode;

        if( gutenberg_id ) {

          var content = window.pf_gutenberg_props.attributes.hasOwnProperty('shortcode') ? window.pf_gutenberg_props.attributes.shortcode : '';
          window.pf_gutenberg_props.setAttributes({shortcode: content + shortcode});

        } else if( editor_id ) {

          base.send_to_editor( shortcode, editor_id );

        } else {

          var $textarea = (target_id) ? $(target_id) : $button.parent().find('textarea');
          $textarea.val( base.insertAtChars( $textarea, shortcode ) ).trigger('change');

        }

        $modal.hide();

      });

      $modal.on('click', '.pf--repeat-button', function( e ) {

        e.preventDefault();

        var $repeatable = $modal.find('.pf--repeatable');
        var $new_clone  = $cloned.pf_clone();
        var $remove_btn = $new_clone.find('.pf-repeat-remove');

        var $appended = $new_clone.appendTo( $repeatable );

        $new_clone.find('.pf-fields').pf_reload_script();

        PF.helper.name_nested_replace( $modal.find('.pf--repeat-shortcode'), sc_group );

        $remove_btn.on('click', function() {

          $new_clone.remove();

          PF.helper.name_nested_replace( $modal.find('.pf--repeat-shortcode'), sc_group );

        });

      });

      $modal.on('click', '.pf-modal-close, .pf-modal-overlay', function() {
        $modal.hide();
      });

    });
  };

  // --- WP Color Picker ---------------
  if( typeof Color === 'function' ) {

    Color.fn.toString = function() {

      if( this._alpha < 1 ) {
        return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
      }

      var hex = parseInt( this._color, 10 ).toString( 16 );

      if( this.error ) { return ''; }

      if( hex.length < 6 ) {
        for (var i = 6 - hex.length - 1; i >= 0; i--) {
          hex = '0' + hex;
        }
      }

      return '#' + hex;

    };

  }

  PF.funcs.parse_color = function( color ) {

    var value = color.replace(/\s+/g, ''),
        trans = ( value.indexOf('rgba') !== -1 ) ? parseFloat( value.replace(/^.*,(.+)\)/, '$1') * 100 ) : 100,
        rgba  = ( trans < 100 ) ? true : false;

    return { value: value, transparent: trans, rgba: rgba };

  };

  $.fn.pf_color = function() {
    return this.each( function() {

      var $input        = $(this),
          picker_color  = PF.funcs.parse_color( $input.val() ),
          palette_color = window.pf_vars.color_palette.length ? window.pf_vars.color_palette : true,
          $container;

      // Destroy and Reinit
      if( $input.hasClass('wp-color-picker') ) {
        $input.closest('.wp-picker-container').after($input).remove();
      }

      $input.wpColorPicker({
        palettes: palette_color,
        change: function( event, ui ) {

          var ui_color_value = ui.color.toString();

          $container.removeClass('pf--transparent-active');
          $container.find('.pf--transparent-offset').css('background-color', ui_color_value);
          $input.val(ui_color_value).trigger('change');

        },
        create: function() {

          $container = $input.closest('.wp-picker-container');

          var a8cIris = $input.data('a8cIris'),
              $transparent_wrap = $('<div class="pf--transparent-wrap">' +
                                '<div class="pf--transparent-slider"></div>' +
                                '<div class="pf--transparent-offset"></div>' +
                                '<div class="pf--transparent-text"></div>' +
                                '<div class="pf--transparent-button">transparent <i class="fa fa-toggle-off"></i></div>' +
                                '</div>').appendTo( $container.find('.wp-picker-holder') ),
              $transparent_slider = $transparent_wrap.find('.pf--transparent-slider'),
              $transparent_text   = $transparent_wrap.find('.pf--transparent-text'),
              $transparent_offset = $transparent_wrap.find('.pf--transparent-offset'),
              $transparent_button = $transparent_wrap.find('.pf--transparent-button');

          if( $input.val() === 'transparent' ) {
            $container.addClass('pf--transparent-active');
          }

          $transparent_button.on('click', function() {
            if( $input.val() !== 'transparent' ) {
              $input.val('transparent').trigger('change').removeClass('iris-error');
              $container.addClass('pf--transparent-active');
            } else {
              $input.val( a8cIris._color.toString() ).trigger('change');
              $container.removeClass('pf--transparent-active');
            }
          });

          $transparent_slider.slider({
            value: picker_color.transparent,
            step: 1,
            min: 0,
            max: 100,
            slide: function( event, ui ) {

              var slide_value = parseFloat( ui.value / 100 );
              a8cIris._color._alpha = slide_value;
              $input.wpColorPicker( 'color', a8cIris._color.toString() );
              $transparent_text.text( ( slide_value === 1 || slide_value === 0 ? '' : slide_value ) );

            },
            create: function() {

              var slide_value = parseFloat( picker_color.transparent / 100 ),
                  text_value  = slide_value < 1 ? slide_value : '';

              $transparent_text.text(text_value);
              $transparent_offset.css('background-color', picker_color.value);

              $container.on('click', '.wp-picker-clear', function() {

                a8cIris._color._alpha = 1;
                $transparent_text.text('');
                $transparent_slider.slider('option', 'value', 100);
                $container.removeClass('pf--transparent-active');
                $input.trigger('change');

              });

              $container.on('click', '.wp-picker-default', function() {

                var default_color = PF.funcs.parse_color( $input.data('default-color') ),
                    default_value = parseFloat( default_color.transparent / 100 ),
                    default_text  = default_value < 1 ? default_value : '';

                a8cIris._color._alpha = default_value;
                $transparent_text.text(default_text);
                $transparent_slider.slider('option', 'value', default_color.transparent);

              });

            }
          });
        }
      });

    });
  };

  // --- plugin: ChosenJS ---------------
  $.fn.pf_chosen = function() {
    return this.each( function() {

      var $this       = $(this),
          $inited     = $this.parent().find('.chosen-container'),
          is_sortable = $this.hasClass('pf-chosen-sortable') || false,
          is_ajax     = $this.hasClass('pf-chosen-ajax') || false,
          is_multiple = $this.attr('multiple') || false,
          set_width   = is_multiple ? '100%' : 'auto',
          set_options = $.extend({
            allow_single_deselect: true,
            disable_search_threshold: 10,
            width: set_width,
            no_results_text: window.pf_vars.i18n.no_results_text,
          }, $this.data('chosen-settings'));

      if( $inited.length ) {
        $inited.remove();
      }

      // Chosen ajax
      if( is_ajax ) {

        var set_ajax_options = $.extend({
          data: {
            type: 'post',
            nonce: '',
          },
          allow_single_deselect: true,
          disable_search_threshold: -1,
          width: '100%',
          min_length: 3,
          type_delay: 500,
          typing_text: window.pf_vars.i18n.typing_text,
          searching_text: window.pf_vars.i18n.searching_text,
          no_results_text: window.pf_vars.i18n.no_results_text,
        }, $this.data('chosen-settings'));

        $this.PFAjaxChosen(set_ajax_options);

      } else {

        $this.chosen(set_options);

      }

      // Chosen keep options order
      if( is_multiple ) {

        var $hidden_select = $this.parent().find('.pf-hidden-select');
        var $hidden_value  = $hidden_select.val() || [];

        $this.on('change', function(obj, result) {

          if( result && result.selected ) {
            $hidden_select.append( '<option value="'+ result.selected +'" selected="selected">'+ result.selected +'</option>' );
          } else if( result && result.deselected ) {
            $hidden_select.find('option[value="'+ result.deselected +'"]').remove();
          }

          // Force customize refresh
          if( $hidden_select.children().length === 0 && window.wp.customize !== undefined ) {
            window.wp.customize.control( $hidden_select.data('customize-setting-link') ).setting.set('');
          }

          $hidden_select.trigger('change');

        });

        // Chosen order abstract
        $this.PFChosenOrder($hidden_value, true);

      }

      // Chosen sortable
      if( is_sortable ) {

        var $chosen_container = $this.parent().find('.chosen-container');
        var $chosen_choices   = $chosen_container.find('.chosen-choices');

        $chosen_choices.bind('mousedown', function( event ) {
          if( $(event.target).is('span') ) {
            event.stopPropagation();
          }
        });

        $chosen_choices.sortable({
          items: 'li:not(.search-field)',
          helper: 'orginal',
          cursor: 'move',
          placeholder: 'search-choice-placeholder',
          start: function(e,ui) {
            ui.placeholder.width( ui.item.innerWidth() );
            ui.placeholder.height( ui.item.innerHeight() );
          },
          update: function( e, ui ) {

            var select_options = '';
            var chosen_object  = $this.data('chosen');
            var $prev_select   = $this.parent().find('.pf-hidden-select');

            $chosen_choices.find('.search-choice-close').each( function() {
              var option_array_index = $(this).data('option-array-index');
              $.each(chosen_object.results_data, function(index, data) {
                if( data.array_index === option_array_index ){
                  select_options += '<option value="'+ data.value +'" selected>'+ data.value +'</option>';
                }
              });
            });

            $prev_select.children().remove();
            $prev_select.append(select_options);
            $prev_select.trigger('change');

          }
        });

      }

    });
  };

  // --- Field Checkbox ---------------
  $.fn.pf_checkbox = function() {
    return this.each( function() {

      var $this     = $(this),
          $input    = $this.find('.pf--input'),
          $checkbox = $this.find('.pf--checkbox');

      $checkbox.on('click', function() {
        $input.val( Number( $checkbox.prop('checked') ) ).trigger('change');
      });

    });
  };

  // --- Siblings ---------------
  $.fn.pf_siblings = function() {
    return this.each( function() {

      var $this     = $(this),
          $siblings = $this.find('.pf--sibling'),
          multiple  = $this.data('multiple') || false;

      $siblings.on('click', function() {

        var $sibling = $(this);
        if( multiple ) {

          if( $sibling.hasClass('pf--active') ) {
            $sibling.removeClass('pf--active');
            $sibling.find('input').prop('checked', false).trigger('change');
          } else {

            $sibling.addClass('pf--active');
            $sibling.find('input').prop('checked', true).trigger('change');
          }

        } else {

          $this.find('input').prop('checked', false);
          $sibling.find('input').prop('checked', true).trigger('change');
          $sibling.addClass('pf--active').siblings().removeClass('pf--active');

        }

      });

    });
  };

  // --- Tooltip ---------------
  $.fn.pf_help = function() {
    return this.each( function() {

      var $this = $(this),
          $tooltip,
          offset_left;

      $this.on({
        mouseenter: function() {

          $tooltip = $( '<div class="pf-tooltip"></div>' ).html( $this.attr('data-tooltip') ).appendTo('body');
          offset_left = ( PF.vars.is_rtl ) ? ( $this.offset().left + 24 ) : ( $this.offset().left + ( $this.outerWidth() / 2 ) - ($tooltip.outerWidth() / 2)  );

          $tooltip.css({
            top: $this.offset().top - ( ( $tooltip.outerHeight() + 5 ) ),
            left: offset_left,
          });

          $tooltip.addClass("show");

        },
        mouseleave: function() {

          if( $tooltip !== undefined ) {
            $tooltip.remove();
            $tooltip.removeClass("show");
          }

        }

      });

    });
  };

  // --- Customize Refresh ---------------
  $.fn.pf_customizer_refresh = function() {
    return this.each( function() {

      var $this    = $(this),
          $complex = $this.closest('.pf-customize-complex');

      if( $complex.length ) {

        var $input  = $complex.find(':input'),
            $unique = $complex.data('unique-id'),
            $option = $complex.data('option-id'),
            obj     = $input.serializeObjectPF(),
            data    = ( !$.isEmptyObject(obj) ) ? obj[$unique][$option] : '',
            control = window.wp.customize.control($unique +'['+ $option +']');

        // clear the value to force refresh.
        control.setting._value = null;

        control.setting.set( data );

      } else {

        $this.find(':input').first().trigger('change');

      }

      $(document).trigger('pf-customizer-refresh', $this);

    });
  };

  // --- Customize Listen Form Elements ---------------
  $.fn.pf_customizer_listen = function( options ) {

    var settings = $.extend({
      closest: false,
    }, options );

    return this.each( function() {

      if( window.wp.customize === undefined ) { return; }

      var $this     = ( settings.closest ) ? $(this).closest('.pf-customize-complex') : $(this),
          $input    = $this.find(':input'),
          unique_id = $this.data('unique-id'),
          option_id = $this.data('option-id');

      if( unique_id === undefined ) { return; }

      $input.on('change keyup', PF.helper.debounce( function() {

        var obj = $this.find(':input').serializeObjectPF();

        var val = ( !$.isEmptyObject(obj) && obj[unique_id] && obj[unique_id][option_id] ) ? obj[unique_id][option_id] : '';

        window.wp.customize.control( unique_id +'['+ option_id +']' ).setting.set( val );

      }, 250 ) );

    });
  };

  // --- Customizer Listener for Reload JS ---------------
  $(document).on('expanded', '.control-section-pf', function() {

    var $this  = $(this);

    if( $this.hasClass('open') && !$this.data('inited') ) {

      var $fields  = $this.find('.pf-customize-field');
      var $complex = $this.find('.pf-customize-complex');

      if( $fields.length ) {
        $this.pf_dependency();
        $fields.pf_reload_script({dependency: false});
        $complex.pf_customizer_listen();
      }

      $this.data('inited', true);

    }

  });

  // --- Window on resize ---------------
  PF.vars.$window.on('resize pf.resize', PF.helper.debounce( function( event ) {

    var window_width = navigator.userAgent.indexOf('AppleWebKit/') > -1 ? PF.vars.$window.width() : window.innerWidth;

    if( window_width <= 782 && !PF.vars.onloaded ) {
      $('.pf-section').pf_reload_script();
      PF.vars.onloaded  = true;
    }

  }, 200)).trigger('pf.resize');

  // --- Widgets Framework ---------------
  $.fn.pf_widgets = function() {
    if( this.length ) {

      $(document).on('widget-added widget-updated', function( event, $widget ) {
        $widget.find('.pf-fields').pf_reload_script();
      });

      $('.widgets-sortables, .control-section-sidebar').on('sortstop', function( event, ui ) {
        ui.item.find('.pf-fields').pf_reload_script_retry();
      });

      $(document).on('click', '.widget-top', function( event ) {
        $(this).parent().find('.pf-fields').pf_reload_script();
      });

    }
  };

  // --- Retry Plugins ---------------
  $.fn.pf_reload_script_retry = function() {
    return this.each( function() {

      var $this = $(this);

      if( $this.data('inited') ) {
        $this.children('.pf-field-wp_editor').pf_field_wp_editor();
      }

    });
  };

  // --- Field Taxonomies ---------------
  $.fn.pf_taxonomies = function() {
    return this.each( function() {

      var $this     = $(this),
          $nav      = $this.find(".pf-taxonomy-wrap-nav"),
          unique_id = $this.closest('.pf-content').prev('.pf-nav-metabox').attr('data-unique'),
          id_field  = $this.attr("data-id");
      // Onclick button select all
      $(this).find('.pf-taxonomy-content-tab .btn-select-all').on("click", function(e){
        e.preventDefault();
        $(this).parent().find("input[type='checkbox']").prop( "checked", true );
      });

      $nav.find('.pf-taxonomy-nav-tab').on('click', function( e ) {
        e.preventDefault();

        var tab_content_id = $(this).attr("data-id-content");
        $(this).closest('.pf-taxonomy-wrap-nav').children('.pf-taxonomy-nav').removeClass('pf-taxonomy-nav-active');
        $(this).parent().addClass('pf-taxonomy-nav-active');

        $(this).closest('.pf-taxonomy-wrap').find('.pf-taxonomy-content-tab').css("display","none");
        $(this).closest('.pf-taxonomy-wrap').find('.pf-taxonomy-content-tab-id-' + tab_content_id).css("display","block");

        PF.helper.set_cookie('pf-lasttab-taxonomies-id-' + id_field + '-' + unique_id, tab_content_id);
      });

      // Set input tag
      if( $this.find('textarea').length > 0 ){
        $this.find('.pf-taxonomy-content .pf-taxonomy-content-tab').each(function(){
          var placeholder = $(this).children('.tag-editor').attr("placeholder") || '',
              is_ajax     = $(this).children('.tag-editor').attr("ajax") || null,
              taxonomy    = $(this).children('.tag-editor').attr("data-cpt") || null;

          $(this).children('.tag-editor').tagEditor({
            beforeTagSave: function(field, editor, tags, tag, val) {
              return (val + '|' + field.attr("data-cpt")); //.replace(',','');
            },
            forceLowercase: false,
            placeholder: placeholder,
            autocomplete: {

              delay: 0, position: { collision: 'flip' },
              source: function(request, response) {
                if( is_ajax ){
                  window.wp.ajax.post( 'pf-taxonomy-ajax', {
                    tax: taxonomy,
                    s  : request.term
                  })
                  .done( function( data ) {
                    console.log('data: ', data);
                    response($.map(data, function(c) {
                      console.log(c);
                      return {
                          value: c.name,
                          label: c.name
                      }
                    }));
                  }).fail( function( response ) {
                    console.log( response );
                  });;
                }else{
                  return {}
                }
              },
            },
          });
        });
      }

      // Select all ir individual
      $(this).find('.pf-taxonomy-content-tab .pf-taxonomy-selected-all .pf--button').on("click", function(e){
        if( $(this).find("input[type='radio']").val() ){
          $(this).parent().parent().next('.pf-taxonomy-collapse').css("display","none");
        }else{
          $(this).parent().parent().next('.pf-taxonomy-collapse').css("display","block").removeClass('hidden');
        }
      });

    });
  };

  // --- Field TagEditor ---------------
  $.fn.pf_tags = function() {
    return this.each( function() {

      var $this   = $(this).find('.tag-editor'),
          attr_js = $this.attr("data-attributes-js") || "{}";
      $this.tagEditor('destroy');
      $this.tagEditor( JSON.parse(attr_js) );
      $this.pf_reload_script();


    });
  }

  // --- Field spinner ---------------
  $.fn.pf_field_styling = function() {
    return this.each( function() {

      var $this       = $(this),
          $nav_device = $this.find('.pf--styling__nav');

      // Actions of navigation by device
      $nav_device.find('li').on('click', function(){
        $nav_device.find('li').removeClass('active');
        $(this).addClass('active');
        var content_active = $(this).attr("data-id");
        $nav_device.closest('.pf--styling').find('.pf--styling__wrap').addClass('hidden');
        $nav_device.closest('.pf--styling').find('.pf--styling__wrap_'+content_active).removeClass('hidden');
      });

      // Actions of navigation by device
      $('.pf--styling__tab').find('li').on('click', function(){
        var $wrap_content = $(this).closest('.pf--styling__wrap');
        $wrap_content.find('.pf--styling__tab li').removeClass('active');
        $(this).addClass('active');
        var content_active = $(this).attr("data-id");

        $wrap_content.find('.pf--block').css("display","none");
        $wrap_content.find('.pf--block.pf--tabs__'+content_active+'_content').css("display","block");
      });

      // Code Editor
      var code_editor = null;
      $this.find('textarea').each( function() {

        if( typeof CodeMirror !== 'function' ) { return; }

        var $inited     = $this.find('.CodeMirror'),
            $textarea   = $(this),
            data_editor = $textarea.data('editor');
        if( $inited.length ) {
          //$inited.remove();
        }

        code_editor = CodeMirror.fromTextArea( $textarea[0], data_editor );
        // load code-mirror theme css.
        if( data_editor.theme !== 'default' && PF.vars.code_themes.indexOf(data_editor.theme) === -1 ) {

          var $cssLink = $('<link>');

          $('#pf-codemirror-css-2').after( $cssLink );

          $cssLink.attr({
            rel: 'stylesheet',
            id: 'pf-codemirror-2-'+ data_editor.theme +'-css',
            href: data_editor.cdnURL +'/theme/'+ data_editor.theme +'.min.css',
            type: 'text/css',
            media: 'all'
          });

          PF.vars.code_themes.push(data_editor.theme);

        }

        CodeMirror.modeURL = data_editor.cdnURL +'/mode/%N/%N.min.js';
        CodeMirror.autoLoadMode(code_editor, data_editor.mode);

        code_editor.on( 'change', function( editor, event ) {
          $textarea.val( code_editor.getValue() ).trigger('change');
        });

        code_editor.refresh();

      });

      // Refresh CodeMirrors
      $this.find('.pf--tabs__custom').on('click', function(){
        if( typeof CodeMirror !== 'function' ) { return; }
        $this.find('.CodeMirror').each(function(i, el){
          el.CodeMirror.refresh();
        });
      });

    });
  };

  // --- plugin: Select2 ---------------
  $.fn.pf_select2 = function() {
    return this.each( function() {

      var $this       = $(this),
          is_multi    = $this.attr('multiple') || false;
          //set_width   = is_multi ? '100%' : 'auto',

      $this.select2();

    });
  };

  // --- plugin: Autocomplete ---------------
  $.fn.pf_autocomplete = function() {

    return this.each( function() {

      var $this          = $(this),
          $input         = $this.find('.pf-search-post-inputs-wrap .pf-search-input'),
          $input_value   = $this.find('.pf-search-post-inputs-wrap .pf-search-input-value'),
          $input_data    = $this.find('.pf-search-post-inputs-wrap .pf-search-input-data'),
          $select_cpt    = $this.find('.pf-search-post-inputs-wrap .pf-search-select'),
          $button_add    = $this.find('.pf-search-post-inputs-wrap .pf-search-post-button'),
          $list_item     = $this.find('.pf-search-post-results'),
          $input_values  = $this.find('.pf-search-post-values'),
          $wrapper       = $this.find('.pf-search-post-results'),

          nonce          = $(this).find('.pf-search-post-nonce').val(),
          imagen_default = $this.find('.pf-search-post-image-default').val(),
          is_image       = $this.find('.pf-search-post-is-image').val(),
          is_filter      = $this.find('.pf-search-post-is-filter').val(),
          is_sortable    = $this.find('.pf-search-post-is-sortable').val();

      // Prevent the input from submitting
      $input.keydown(function(event){
        if(event.keyCode == 13) {
          event.preventDefault();
          return false;
        }
      });

      // Execute autocomplete
      $input.autocomplete({
        source: function(req, response){
          var cpt = $select_cpt.length ? $select_cpt.val() : null;
          jQuery.ajax({
            type    : 'post',
            dataType: 'json',
            url     : ajaxurl,
            data    : 'action=pf-search-post-ajax&nonce=' + nonce +'&q='+ req.term + '&cpt='+ cpt + '&exclude='+ $input_values.val().replace("undefined","") + '&filter=' + is_filter,
            success : function(data) {
              console.log( data );
              response( $.map( data.data , function(item, i) {
                return {
                  label: item.title,
                  value: parseInt(item.id),
                  image: item.image
                }
              }) );

            }
          });
        },
        select: function(event, ui) {
          if( typeof  ui.item.value == "number" ){
            $input_value.val( ui.item.value );
            $input.val( ui.item.label );
            // add input data
            $input_data.val( ui.item.value + '|' + ui.item.label + '|' + ui.item.image );
          }else{
            $input_value.val( 0 );
            $input.val( '' );
            $input_data.val( '' );
          }
          return false;
        },
        minLength: 3,
        autoFocus: true,
        focus: function (event, ui) {
          return false;
        },
        fail : function ( jqXHR, textStatus, errorThrown ) {
          console.log(jqXHR);
          console.log(textStatus);
          console.log(errorThrown);
        },
        appendTo:  $this.find(".pf-search-post-custom-result-wrap"),
        position: { my : "right-0 top+30", at: "right top" }
      })
      .data( "ui-autocomplete" )._renderItem = function( ul, item ) {
        ul.addClass('pf-autocomplete-item');
        //var $div_result = $this.find('.pf-search-post-custom-result ul');

        if( item.image === undefined || ! item.image ){
          item.image = imagen_default;
        }

        var html_image = '';
        if( is_image != 0 ){
          html_image = "<div class='pf-autocomplete-item-img'><img src='"+ item.image +"' /></div>";
        }

        return $("<li/>" )
        .data( "ui-autocomplete-item", item )
        .append( html_image )
        .append( "<div class='pf-autocomplete-item-content'><p>" + item.label + "</p></div>" )
        .appendTo( ul );
      }

      // Add result
      $button_add.on('click', function( e ){

        e.preventDefault();

        if( $input_value.val() != 0 ){
          var data_split = $input_data.val().split('|');

          var id    = data_split[0],
              title = data_split[1],
              image = data_split[2]

          if( image === undefined || ! image ){
            image = imagen_default;
          }

          var html_image = '';
          if( is_image != 0 ){
            html_image = '<div class="pf-search-post-item-index-img"><img src="'+ image +'" /></div>';
          }

          var html_icon_sortable = '';
          if( is_sortable != 0 ){
            html_icon_sortable = '<i class="pf-sp-sort fa fa-arrows ui-sortable-handle"></i>';
          }

          $( '<li class="pf-search-post-item-index pf-search-post-item-id-'+id+'">' )
          .append( html_image )
          .append( '<div class="pf-autocomplete-item-content"><p>'+title+'</p></div>' )
          .append( '<div class="pf-search-post-helper">'+ html_icon_sortable +'<i data-id="'+ id +'" class="pf-sp-remove fa fa-times"></i></div>' )
          .append( '</li>' )
          .appendTo( $list_item )
          ;
          $input_value.val( '' );
          $input.val( '' );
          $input_data.val( '' );
          // Add in value field
          var $input_values = $button_add.closest('.pf--search-post-wrap').find('.pf-search-post-values');
          $input_values.val( $input_values.val() + ',' + id );
        }

      });

      // Remove item
      $(document).on("click", '.pf-search-post-results .pf-search-post-item-index .pf-sp-remove' , function() {
        var id  = $(this).attr("data-id"),
            ids = $input_values.val();
        var ids_array = ids.split(","),
            new_values = '';
        for (let i = 0; i < ids_array.length; i++) {
          const element = ids_array[i];
          if( element != id )
            new_values += element + ",";
        }
        $input_values.val( new_values );
        $(this).closest('.pf-search-post-item-index').remove();
      });

      if( is_sortable != 0 ){
        $wrapper.sortable({
          axis: 'y',
          handle: '.pf-sp-sort',
          helper: 'original',
          cursor: 'move',
          placeholder: 'widget-placeholder',
          start: function( event, ui ) {
            //$wrapper.sortable('refreshPositions');
          },
          update: function( event, ui ) {
            post_ids_order();
          },
        });
      }

      // Sort the Ids values ​​each time the post is ordered
      var post_ids_order = function() {
        var ids_update = '';
        $($wrapper).children('.pf-search-post-item-index').each( function() {
          ids_update += $(this).attr("data-id") + ",";
        });
        $input_values.val('').val( ids_update );
      };

    });
  };

  // --- Reload Plugins ---------------
  $.fn.pf_reload_script = function( options ) {

    var settings = $.extend({
      dependency: true,
    }, options );

    return this.each( function() {

      var $this = $(this);

      // Avoid for conflicts
      if( !$this.data('inited') ) {

        // --Field plugins
        // Field Accordion
        $this.children('.pf-field-accordion').pf_field_accordion();
        // Field repeatear
        $this.children('.pf-field-repeater').pf_field_repeater();
        // Field Checkbox
        $this.children('.pf-field-checkbox').find('.pf-checkbox').pf_checkbox();
        // Field Group
        $this.children('.pf-field-group').pf_field_group();
        // Field Tab
        $this.children('.pf-field-tab').pf_field_tab();
        // Field Fieldset
        $this.children('.pf-field-fieldset').pf_field_fieldset();
        // Field Upload
        $this.children('.pf-field-upload').pf_field_upload();
        // Field Media
        $this.children('.pf-field-media').pf_field_media();
        // Field Gallery
        $this.children('.pf-field-gallery').pf_field_gallery();
        // Field Code Editor
        $this.children('.pf-field-code_editor').pf_field_code_editor();
        // Field WP Editor
        $this.children('.pf-field-wp_editor').pf_field_wp_editor();
        // Field Slider
        $this.children('.pf-field-slider').pf_field_slider();
        // Field Background
        $this.children('.pf-field-background').pf_field_background();
        // Field Typography
        $this.children('.pf-field-typography').pf_field_typography();
        // Field Spinner
        $this.children('.pf-field-spinner').pf_field_spinner();
        // Field Sorter
        $this.children('.pf-field-sorter').pf_field_sorter();
        // Field Sortable
        $this.children('.pf-field-sortable').pf_field_sortable();
        // Field Switcher
        $this.children('.pf-field-switcher').pf_field_switcher();
        // Field Icon
        $this.children('.pf-field-icon').pf_field_icon();
        // Field Date
        $this.children('.pf-field-date').pf_field_date();
        // Field Backup
        $this.children('.pf-field-backup').pf_field_backup();
        // Field Taxonomies
        $this.children('.pf-field-taxonomies').pf_taxonomies();
        // Field Tag
        $this.children('.pf-field-tag').pf_tags();
        // Field Styling
        $this.children('.pf-field-styling').pf_field_styling();

        // Field Colors
        $this.children('.pf-field-border').find('.pf-color').pf_color();
        $this.children('.pf-field-background').find('.pf-color').pf_color();
        $this.children('.pf-field-color').find('.pf-color').pf_color();
        $this.children('.pf-field-color_group').find('.pf-color').pf_color();
        $this.children('.pf-field-link_color').find('.pf-color').pf_color();
        $this.children('.pf-field-typography').find('.pf-color').pf_color();
        $this.children('.pf-field-styling').find('.pf-color').pf_color();

        // Field Siblings
        $this.children('.pf-field-palette').find('.pf-siblings').pf_siblings();
        $this.children('.pf-field-image_select').find('.pf-siblings').pf_siblings();
        $this.children('.pf-field-button_set').find('.pf-siblings').pf_siblings();
        $this.children('.pf-field-taxonomies').find('.pf-siblings').pf_siblings();

        // Help Tooptip
        $this.children('.pf-field').find('[data-tooltip]').pf_help();

        // Field code editor
        //$this.children('.pf-field-styling').pf_field_code_editor();

        // Plugin chosenjs
        $this.children('.pf-field-select').find('.pf-chosen').pf_chosen();

        // Plugin select2
        $this.children('.pf-field-select2').find('select').pf_select2();

        // Plugin tinyAutocomplete
        $this.children('.pf-field-search_post').pf_autocomplete();

        // Load dependency
        if( settings.dependency ) {
          $this.pf_dependency();
        }

        $this.data('inited', true);

        $(document).trigger('pf-reload-script', $this);

      }

    });
  };

  // --- Document ready and run scripts ---------------
  $(document).ready( function() {

    $('.pf-save').pf_save();
    $('.pf-confirm').pf_confirm();
    $('.pf-expand-all').pf_expand_all();
    $('.pf-search').pf_search();
    $('.pf-sticky-header').pf_sticky();
    $('.pf-shortcode').pf_shortcode();
    $('.pf-nav-options').pf_nav_options();
    $('.pf-nav-metabox').pf_nav_metabox();
    $('.pf-page-templates').pf_page_templates();
    $('.pf-post-formats').pf_post_formats();
    $('.pf-onload').pf_reload_script();
    $('.widget').pf_widgets();

  });

} )( jQuery, window, document );;