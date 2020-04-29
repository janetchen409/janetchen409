// Plugin Cookie: A simple, lightweight JavaScript API for handling browser cookies
// https://github.com/js-cookie/js-cookie
/*! js-cookie v3.0.0-beta.3 | MIT */
;
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = global || self, (function () {
    var current = global.Cookies;
    var exports = global.Cookies = factory();
    exports.noConflict = function () { global.Cookies = current; return exports; };
  }()));
}(this, function () { 'use strict';

  var rfc6265Converter = {
    read: function (value) {
      return value.replace(/(%[\dA-F]{2})+/gi, decodeURIComponent)
    },
    write: function (value) {
      return encodeURIComponent(value).replace(
        /%(2[346BF]|3[AC-F]|40|5[BDE]|60|7[BCD])/g,
        decodeURIComponent
      )
    }
  };

  function assign (target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];
      for (var key in source) {
        target[key] = source[key];
      }
    }
    return target
  }

  function init (converter, defaultAttributes) {
    function set (key, value, attributes) {
      if (typeof document === 'undefined') {
        return
      }

      attributes = assign({}, defaultAttributes, attributes);

      if (typeof attributes.expires === 'number') {
        attributes.expires = new Date(Date.now() + attributes.expires * 864e5);
      }
      if (attributes.expires) {
        attributes.expires = attributes.expires.toUTCString();
      }

      value = converter.write(value, key);

      key = encodeURIComponent(key)
        .replace(/%(2[346B]|5E|60|7C)/g, decodeURIComponent)
        .replace(/[()]/g, escape);

      var stringifiedAttributes = '';
      for (var attributeName in attributes) {
        if (!attributes[attributeName]) {
          continue
        }

        stringifiedAttributes += '; ' + attributeName;

        if (attributes[attributeName] === true) {
          continue
        }

        // Considers RFC 6265 section 5.2:
        // ...
        // 3.  If the remaining unparsed-attributes contains a %x3B (";")
        //     character:
        // Consume the characters of the unparsed-attributes up to,
        // not including, the first %x3B (";") character.
        // ...
        try{
          stringifiedAttributes += '=' + attributes[attributeName].split(';')[0];
        }catch(e){
          console.log("err-cookie: https://github.com/js-cookie/js-cookie");
          continue;
        }
      }

      return (document.cookie = key + '=' + value + stringifiedAttributes)
    }

    function get (key) {
      if (typeof document === 'undefined' || (arguments.length && !key)) {
        return
      }

      // To prevent the for loop in the first place assign an empty array
      // in case there are no cookies at all.
      var cookies = document.cookie ? document.cookie.split('; ') : [];
      var jar = {};
      for (var i = 0; i < cookies.length; i++) {
        var parts = cookies[i].split('=');
        var cookie = parts.slice(1).join('=');

        if (cookie[0] === '"') {
          cookie = cookie.slice(1, -1);
        }

        try {
          var name = rfc6265Converter.read(parts[0]);
          jar[name] = converter.read(cookie, name);

          if (key === name) {
            break
          }
        } catch (e) {}
      }

      return key ? jar[key] : jar
    }

    return Object.create(
      {
        set: set,
        get: get,
        remove: function (key, attributes) {
          set(
            key,
            '',
            assign({}, attributes, {
              expires: -1
            })
          );
        },
        withAttributes: function (attributes) {
          return init(this.converter, assign({}, this.attributes, attributes))
        },
        withConverter: function (converter) {
          return init(assign({}, this.converter, converter), this.attributes)
        }
      },
      {
        attributes: { value: Object.freeze(defaultAttributes) },
        converter: { value: Object.freeze(converter) }
      }
    )
  }

  var api = init(rfc6265Converter, { path: '/' });

  return api;

}));

/*
  Complete library for the management of GEO and
  Devices to save in the database.
 */
// FIXED: Add more documentation of the database
var pf_geo = function(){

  // --- Helpers for the end ---------------
  this.helpers = {
    // Get a cookie
    get_cookie: function( name ) {
      return Cookies.get( name );
    }.bind(this),

    // Set cookie
    set_cookie: function( name, value, expires, path = '', domain, secure ) {
      Cookies.set(name, value, { expires: expires });
    }.bind(this),

    // Remove a cookie
    remove_cookie: function( name, path, domain, secure ) {
      Cookies.remove( name );
    }
  }



  // --- Functions ---------------
  this.funcs = {
    getBrowserInfo : {
      options: [],
      header: [navigator.platform, navigator.userAgent, navigator.appVersion, navigator.vendor, window.opera],
      dataos: [
        { name: 'Windows Phone', value: 'Windows Phone', version: 'OS' },
        { name: 'Windows', value: 'Win', version: 'NT' },
        { name: 'iPhone', value: 'iPhone', version: 'OS' },
        { name: 'iPad', value: 'iPad', version: 'OS' },
        { name: 'Kindle', value: 'Silk', version: 'Silk' },
        { name: 'Android', value: 'Android', version: 'Android' },
        { name: 'PlayBook', value: 'PlayBook', version: 'OS' },
        { name: 'BlackBerry', value: 'BlackBerry', version: '/' },
        { name: 'Macintosh', value: 'Mac', version: 'OS X' },
        { name: 'Linux', value: 'Linux', version: 'rv' },
        { name: 'Palm', value: 'Palm', version: 'PalmOS' }
      ],
      databrowser: [
        { name: 'Chrome', value: 'Chrome', version: 'Chrome' },
        { name: 'Firefox', value: 'Firefox', version: 'Firefox' },
        { name: 'Safari', value: 'Safari', version: 'Version' },
        { name: 'Internet Explorer', value: 'MSIE', version: 'MSIE' },
        { name: 'Opera', value: 'Opera', version: 'Opera' },
        { name: 'BlackBerry', value: 'CLDC', version: 'CLDC' },
        { name: 'Mozilla', value: 'Mozilla', version: 'Mozilla' }
      ],
      init: function () {
        var agent = this.header.join(' '),
          os = this.matchItem(agent, this.dataos),
          browser = this.matchItem(agent, this.databrowser);

        return { os: os, browser: browser };
      },
      matchItem: function (string, data) {
        var i = 0,
          j = 0,
          html = '',
          regex,
          regexv,
          match,
          matches,
          version;

        for (i = 0; i < data.length; i += 1) {
          regex = new RegExp(data[i].value, 'i');
          match = regex.test(string);
          if (match) {
            regexv = new RegExp(data[i].version + '[- /:;]([\\d._]+)', 'i');
            matches = string.match(regexv);
            version = '';
            if (matches) { if (matches[1]) { matches = matches[1]; } }
            if (matches) {
              matches = matches.split(/[._]+/);
              for (j = 0; j < matches.length; j += 1) {
                if (j === 0) {
                  version += matches[j] + '.';
                } else {
                  version += matches[j];
                }
              }
            } else {
              version = '0';
            }
            return {
              name: data[i].name,
              version: parseFloat(version)
            };
          }
        }
        return { name: 'unknown', version: 0 };
      },
      printInfo: function () {
        var e = this.init();
        var a = 'os.name=' + e.os.name + '|' +
        'os.version=' + e.os.version + '|' +
        'browser.name=' + e.browser.name + '|' +
        'browser.version=' + e.browser.version + '|' +

        'navigator.userAgent=' + navigator.userAgent + '|' +
        'navigator.appVersion=' + navigator.appVersion + '|' +
        'navigator.platform=' + navigator.platform + '|' +
        'navigator.vendor=' + navigator.vendor + '|';
        //console.log( a );
        return a;
      }
    },

    device_detector : function() {
      var b = navigator.userAgent.toLowerCase(),
          a = function(a) {
            void 0 !== a && (b = a.toLowerCase());
            return /(ipad|tablet|(android(?!.*mobile))|(windows(?!.*phone)(.*touch))|kindle|playbook|silk|(puffin(?!.*(IP|AP|WP))))/.test(b) ? "tablet" : /(mobi|ipod|phone|blackberry|opera mini|fennec|minimo|symbian|psp|nintendo ds|archos|skyfire|puffin|blazer|bolt|gobrowser|iris|maemo|semc|teashark|uzard)/.test(b) ? "phone" : "desktop"
          };
      return {
        device: a(),
        detect: a,
        isMobile: "desktop" != a() ? !0 : !1,
        userAgent: b
      }
    }.bind(this),

    // Get country code
    get_country_code : function ( name_cookie_current_country = "wp_country" ){
      var prefix = name_cookie_current_country;
      var code   = this.helpers.get_cookie( prefix );
      if( ! code || code === undefined ){
        var helpers = this.helpers; // ← Because within success 'this' does not work anymore
        jQuery.ajax({
          /*url: "//gd.geobytes.com/GetCityDetails?callback=?",*/
          /*url: "//ip-api.com/json",*/
          /*url: "//freegeoip.app/json/",*/
          /*url: "//extreme-ip-lookup.com/",*/
          url: "https://json.geoiplookup.io",
          dataType: "json",
          success: function(e){
            helpers.set_cookie( prefix, e.country_code, 86400 * 30 );
            return e.country_code;
          }, error: function(XMLHttpRequest, textStatus, errorThrown) {
            console.log("Status: " + textStatus); console.log("Error: " + errorThrown);
            console.log( XMLHttpRequest );
          }
        });
      }
      return code;
    }.bind(this),

    country_validate : function( string_code_countries = '' ) {
      if( ! string_code_countries ) return true;
      if( ! ( string_code_countries.includes('all') ) ){
        var current_country_code =  this.get_country_code();
        if( current_country_code && string_code_countries.includes( current_country_code ) ){
          return true;
        }
      }else if( string_code_countries.includes('all') ) {
        return true;
      }
      return false;
    }
  }




  // --- Validators ---------------
  // Get the current device type
  this.validator = {
    get_device : function(){
      var r = this.funcs.device_detector();
      if( r.device == 'desktop' ) return 'd';
      else if( r.device == 'tablet' ) return 't';
      else return 'm';
    }.bind(this)
  }



  // --- Actions ---------------
  this.actions = {

    save_geo : function( data = {} ){
      // Defaults
      data.nonce    = data.nonce || '';
      data.action   = data.action || '';
      data.post_id  = data.post_id || '';
      data.url      = data.url || '';
      data.where_is = data.where_is || '';
      data.ajaxurl  = data.ajaxurl || '';
      data.others   = data.others || {};

      var browser_info = this.funcs.getBrowserInfo.printInfo();
      var device       = this.validator.get_device();
      //console.log( device, browser_info );
      jQuery.ajax({
        type:'POST',
        url: data.ajaxurl,
        data: {
          action         : data.action,
          post_id        : parseInt(data.post_id),
          url            : data.url,
          where_is       : data.where_is,
          browser_details: browser_info,
          device         : device,
          nonce          : data.nonce,
          others         : data.others, // Here you place all custom data to send to process
        },
        success: function (result) {
          console.log('result: ', result);
        },
        error: function (xhr, status) {
          console.log("Sorry, there was a problem! ", xhr, status);
        },
        complete: function (xhr, status) {
          null;
        }
      });
    }.bind(this),

    save_others : function( data = {} ){
      // Defaults
      data.nonce    = data.nonce || '';
      data.action   = data.action || '';
      data.post_id  = data.post_id || '';
      data.ajaxurl  = data.ajaxurl || '';
      data.others   = data.others || {};

      jQuery.ajax({
        type:'POST',
        url: data.ajaxurl,
        data: {
          action         : data.action,
          post_id        : parseInt(data.post_id),
          nonce          : data.nonce,
          others         : data.others, // Here you place all custom data to send to process
        },
        success: function (result) {
          console.log(result);
        },
        error: function (xhr, status) {
          console.log("Sorry, there was a problem! ", xhr, status);
        },
        complete: function (xhr, status) {
          null;
        }
      });
    }.bind(this)
  }


}