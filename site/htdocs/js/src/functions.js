
/*
function viewport() {
 
// http://stackoverflow.com/questions/11309859/css-media-queries-and-javascript-window-width-do-not-match
    var e = window, a = 'inner';
    if (!('innerWidth' in window )) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
}
 */
//Then use it with something like
//if (viewport().width > 749) {
//}
/*
  function isNumeric(n) {
        // http://stackoverflow.com/questions/9716468/is-there-any-function-like-isnumeric-in-javascript-to-validate-numbers
        return !isNaN(parseFloat(n)) && isFinite(n);
    }
  */
// helpers:
/*function supportsSVG() {
  return !!document.createElementNS && !!document.createElementNS('http://www.w3.org/2000/svg', "svg").createSVGRect;
}
*/


/*
function detectKonamiCode() {
    // http://codepen.io/jacknumber/pen/BKBjvM
    var user_keys = [],
        konami = '38,38,40,40,37,39,37,39,66,65';
    document.onkeydown = function(e) {
        user_keys.push(e.keyCode)

        if (user_keys.toString().indexOf(konami) >= 0) {
            alert('KONAMI!');
            user_keys = [];
        }
    }
}
*/




    function whichAnimationEvent() {
        // https://davidwalsh.name/css-animation-callback
        var t;
        var el = document.createElement('fakeelement');
        var animations = {
            'animation': 'animationend',
            'OAnimation': 'oAnimationEnd',
            'MozAnimation': 'animationend',
            'WebkitAnimation': 'webkitAnimationEnd'
        }
        for (t in animations) {
            if (el.style[t] !== undefined) {
                return animations[t];
            }
        }
    }


    function whichTransitionEvent() {
        // https://davidwalsh.name/css-animation-callback
        var t;
        var el = document.createElement('fakeelement');
        var transitions = {
            'transition': 'transitionend',
            'OTransition': 'oTransitionEnd',
            'MozTransition': 'transitionend',
            'WebkitTransition': 'webkitTransitionEnd'
        }
        for (t in transitions) {
            if (el.style[t] !== undefined) {
                return transitions[t];
            }
        }
    }



function createCookie(name,value,days) {
  // http://www.quirksmode.org/js/cookies.html
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else var expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

function supportsCanvas() {
  // http://diveintohtml5.info/detect.html#canvas
 // return !!document.createElement('canvas').getContext;
 // http://stackoverflow.com/questions/2745432/best-way-to-detect-that-html5-canvas-is-not-supported#answer-2745459 
 // faster way of doing it as no element is created:
 return !!window.HTMLCanvasElement;
}



function domReady() {
setUpNavigation();
}

// http://stackoverflow.com/questions/1795089/how-can-i-detect-dom-ready-and-add-a-class-without-jquery/1795167#1795167
// Mozilla, Opera, Webkit 
if ( document.addEventListener ) {
  document.addEventListener( "DOMContentLoaded", function(){
    document.removeEventListener( "DOMContentLoaded", arguments.callee, false);
    domReady();
  }, false );

// If IE event model is used
} else if ( document.attachEvent ) {
  // ensure firing before onload
  document.attachEvent("onreadystatechange", function(){
    if ( document.readyState === "complete" ) {
      document.detachEvent( "onreadystatechange", arguments.callee );
      domReady();
    }
  });
}






/*
function viewport() {
  // https://andylangton.co.uk/blog/development/get-viewportwindow-size-width-and-height-javascript
    var e = window, a = 'inner';
    if (!('innerWidth' in window)) {
        a = 'client';
        e = document.documentElement || document.body;
    }
    return { width: e[a + 'Width'], height: e[a + 'Height'] }
}

// viewport width = viewport().width;
*/









/*
// https://davidwalsh.name/javascript-debounce-function
function debounce(func, wait, immediate) {
  var timeout;
  return function() {
    var context = this, args = arguments;
    var later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
};




var throttledResize = debounce(function() {
//    checkMenuConfig();
}, 250);

window.addEventListener('resize', throttledResize);
*/

// check for flexbox support:
var doc = document.body || document.documentElement;
var style = doc.style;
if (style.webkitFlexWrap == '' || style.msFlexWrap == '' || style.flexWrap == '') {
    document.documentElement.className += " supports-flex";
}




function getStyle(el,styleProp) {
  // http://www.quirksmode.org/dom/getstyles.html
  var x = document.getElementById(el);
  if (x.currentStyle)
    var y = x.currentStyle[styleProp];
  else if (window.getComputedStyle)
    var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
  return y;
}


/*
    if(supportsCanvas) {
document.documentElement.className +=" canvas" ;
}

*/

// create AE object for scoping:
var ae = new Object();


// ---------------------
// http://youmightnotneedjquery.com/ - IE8+
function addClass(whichElement, className) {
  if (whichElement.classList) {
    whichElement.classList.add(className);
  } else {
    whichElement.className += ' ' + className;
  }
}

function removeClass(el, className) {
  if (el.classList) {
    el.classList.remove(className);
  } else {
    el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ');
  }
}

function hasParent(element, id) {
  if (element) {
    do {
      if (element.id === id) {
        return true;
      }
      if (element.nodeType === 9) {
        // root node
        break;
      }
    }
    while ((element = element.parentNode));
  }
  return false;
}


function hasClass(element, className) {
    if (element.classList) {
        return element.classList.contains(className);
    } else {
        return new RegExp('(^| )' + className + '( |$)', 'gi').test(element.className);
    }
}






/* Modernizr 2.8.3 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-load
 */
/*
;window.Modernizr=function(a,b,c){function t(a){i.cssText=a}function u(a,b){return t(prefixes.join(a+";")+(b||""))}function v(a,b){return typeof a===b}function w(a,b){return!!~(""+a).indexOf(b)}function x(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:v(f,"function")?f.bind(d||b):f}return!1}var d="2.8.3",e={},f=b.documentElement,g="modernizr",h=b.createElement(g),i=h.style,j,k={}.toString,l={},m={},n={},o=[],p=o.slice,q,r={}.hasOwnProperty,s;!v(r,"undefined")&&!v(r.call,"undefined")?s=function(a,b){return r.call(a,b)}:s=function(a,b){return b in a&&v(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=p.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(p.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(p.call(arguments)))};return e});for(var y in l)s(l,y)&&(q=y.toLowerCase(),e[q]=l[y](),o.push((e[q]?"":"no-")+q));return e.addTest=function(a,b){if(typeof a=="object")for(var d in a)s(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof enableClasses!="undefined"&&enableClasses&&(f.className+=" "+(b?"":"no-")+a),e[a]=b}return e},t(""),h=j=null,e._version=d,e}(this,this.document),function(a,b,c){function d(a){return"[object Function]"==o.call(a)}function e(a){return"string"==typeof a}function f(){}function g(a){return!a||"loaded"==a||"complete"==a||"uninitialized"==a}function h(){var a=p.shift();q=1,a?a.t?m(function(){("c"==a.t?B.injectCss:B.injectJs)(a.s,0,a.a,a.x,a.e,1)},0):(a(),h()):q=0}function i(a,c,d,e,f,i,j){function k(b){if(!o&&g(l.readyState)&&(u.r=o=1,!q&&h(),l.onload=l.onreadystatechange=null,b)){"img"!=a&&m(function(){t.removeChild(l)},50);for(var d in y[c])y[c].hasOwnProperty(d)&&y[c][d].onload()}}var j=j||B.errorTimeout,l=b.createElement(a),o=0,r=0,u={t:d,s:c,e:f,a:i,x:j};1===y[c]&&(r=1,y[c]=[]),"object"==a?l.data=c:(l.src=c,l.type=a),l.width=l.height="0",l.onerror=l.onload=l.onreadystatechange=function(){k.call(this,r)},p.splice(e,0,u),"img"!=a&&(r||2===y[c]?(t.insertBefore(l,s?null:n),m(k,j)):y[c].push(l))}function j(a,b,c,d,f){return q=0,b=b||"j",e(a)?i("c"==b?v:u,a,b,this.i++,c,d,f):(p.splice(this.i++,0,a),1==p.length&&h()),this}function k(){var a=B;return a.loader={load:j,i:0},a}var l=b.documentElement,m=a.setTimeout,n=b.getElementsByTagName("script")[0],o={}.toString,p=[],q=0,r="MozAppearance"in l.style,s=r&&!!b.createRange().compareNode,t=s?l:n.parentNode,l=a.opera&&"[object Opera]"==o.call(a.opera),l=!!b.attachEvent&&!l,u=r?"object":l?"script":"img",v=l?"script":u,w=Array.isArray||function(a){return"[object Array]"==o.call(a)},x=[],y={},z={timeout:function(a,b){return b.length&&(a.timeout=b[0]),a}},A,B;B=function(a){function b(a){var a=a.split("!"),b=x.length,c=a.pop(),d=a.length,c={url:c,origUrl:c,prefixes:a},e,f,g;for(f=0;f<d;f++)g=a[f].split("="),(e=z[g.shift()])&&(c=e(c,g));for(f=0;f<b;f++)c=x[f](c);return c}function g(a,e,f,g,h){var i=b(a),j=i.autoCallback;i.url.split(".").pop().split("?").shift(),i.bypass||(e&&(e=d(e)?e:e[a]||e[g]||e[a.split("/").pop().split("?")[0]]),i.instead?i.instead(a,e,f,g,h):(y[i.url]?i.noexec=!0:y[i.url]=1,f.load(i.url,i.forceCSS||!i.forceJS&&"css"==i.url.split(".").pop().split("?").shift()?"c":c,i.noexec,i.attrs,i.timeout),(d(e)||d(j))&&f.load(function(){k(),e&&e(i.origUrl,h,g),j&&j(i.origUrl,h,g),y[i.url]=2})))}function h(a,b){function c(a,c){if(a){if(e(a))c||(j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}),g(a,j,b,0,h);else if(Object(a)===a)for(n in m=function(){var b=0,c;for(c in a)a.hasOwnProperty(c)&&b++;return b}(),a)a.hasOwnProperty(n)&&(!c&&!--m&&(d(j)?j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}:j[n]=function(a){return function(){var b=[].slice.call(arguments);a&&a.apply(this,b),l()}}(k[n])),g(a[n],j,b,n,h))}else!c&&l()}var h=!!a.test,i=a.load||a.both,j=a.callback||f,k=j,l=a.complete||f,m,n;c(h?a.yep:a.nope,!!i),i&&c(i)}var i,j,l=this.yepnope.loader;if(e(a))g(a,0,l,0);else if(w(a))for(i=0;i<a.length;i++)j=a[i],e(j)?g(j,0,l,0):w(j)?B(j):Object(j)===j&&h(j,l);else Object(a)===a&&h(a,l)},B.addPrefix=function(a,b){z[a]=b},B.addFilter=function(a){x.push(a)},B.errorTimeout=1e4,null==b.readyState&&b.addEventListener&&(b.readyState="loading",b.addEventListener("DOMContentLoaded",A=function(){b.removeEventListener("DOMContentLoaded",A,0),b.readyState="complete"},0)),a.yepnope=k(),a.yepnope.executeStack=h,a.yepnope.injectJs=function(a,c,d,e,i,j){var k=b.createElement("script"),l,o,e=e||B.errorTimeout;k.src=a;for(o in d)k.setAttribute(o,d[o]);c=j?h:c||f,k.onreadystatechange=k.onload=function(){!l&&g(k.readyState)&&(l=1,c(),k.onload=k.onreadystatechange=null)},m(function(){l||(l=1,c(1))},e),i?k.onload():n.parentNode.insertBefore(k,n)},a.yepnope.injectCss=function(a,c,d,e,g,i){var e=b.createElement("link"),j,c=i?h:c||f;e.href=a,e.rel="stylesheet",e.type="text/css";for(j in d)e.setAttribute(j,d[j]);g||(n.parentNode.insertBefore(e,n),m(c,0))}}(this,document),Modernizr.load=function(){yepnope.apply(window,[].slice.call(arguments,0))};



   Modernizr.load([
                {
                    // load additional assets needed for the enhanced experience:
                    load: ['/js/jquery-1.11.1.min.js',
                    '/js/enhancements.js'
                    ],
                    complete: function () {
                    enhancedLoaded = true;
                        initEnhancements();
                    }
                }
            ]);
            */
// -----------------------------------------------
// cssTransitionSupport = false;
// http://davidwalsh.name/css-animation-callback
/* From Modernizr */
/*
function whichTransitionEvent(){
    var t;
    var el = document.getElementById("wrapper");
    var transitions = {
      'transition':'transitionend',
      'OTransition':'oTransitionEnd',
      'MozTransition':'transitionend',
      'WebkitTransition':'webkitTransitionEnd'
    }

    for(t in transitions){
        if( el.style[t] !== undefined ){
        //  cssTransitionSupport = true;
            return transitions[t];
        }
    }
}
*/
/*
// Listen for a transition on the login panel
var transitionEvent = whichTransitionEvent();
transitionEvent && navigationInner.addEventListener(transitionEvent, function() {
  // transition ended:

if(loginExpanded) {
addClass(loginPanel,"focussed");
}

});



*/
/*


     var contactBlockHeaders = document.querySelectorAll("#contactBlocks h4");
     var contactBlockListItems = document.querySelectorAll("#contactBlocks li");
     var contactBlockListItemsLength = contactBlockListItems.length;
     
     for(var i = 0; i < contactBlockHeaders.length; i++) {
     
     contactBlockHeaders[i].addEventListener("click", function(e) {
       
       // remove class from siblings:
       for(var j = 0; j < contactBlockListItemsLength; j++) {
       removeClass(contactBlockListItems[j], "active");
       }
       addClass(this.parentNode.parentNode, "active");
       
     }, false);
     
 
     }


*/

/*

if (supportsSVG()) {
  document.documentElement.className += " svg";
}

*/




/*
use the visibility API to change the page title when the user moves to another tab
var title = document.title,
newTitle = "Remember to read this " + title;
document.addEventListener("visibilitychange", function() {
  document.title = ((document.hidden) ? newTitle : title);
});
*/




// https://mathiasbynens.be/notes/xhr-responsetype-json
var getJSON = function(url, successHandler, errorHandler) {
    var xhr = typeof XMLHttpRequest != 'undefined' ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
    xhr.open('get', url, true);
    xhr.onreadystatechange = function() {
        var status;
        var data;
        // https://xhr.spec.whatwg.org/#dom-xmlhttprequest-readystate
        if (xhr.readyState == 4) { // `DONE`
            status = xhr.status;
            var wasParsedOk = true;
            if (status == 200) {
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (e) {
                    // JSON parse error:
                    wasParsedOk = false;
                    errorHandler && errorHandler(status);
                }
                if (wasParsedOk) {
                    successHandler && successHandler(data);
                }
            } else {
                errorHandler && errorHandler(status);
            }
        }
    };
    xhr.send();
};