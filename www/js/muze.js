/*
	core javascript library for muze modules
	----------------------------------------

	object namespace(string module, function implementaion)
		This method checks if the namespace 'module' is available, and if not
		creates it and registers it. It returns the object the namespace points
		to, so you can create a shorthand for it.
		If you specify an implementation method, this method will be called with
		'this' pointing to the namespace object.
		examples:
			muze.namespace('muze.test');	
			
			var myMod = muze.namespace('muze.temp.my.module.with.a.long.name');
			
			muze.namespace('muze.test', function() { this.test = function() { alert 'test'; } }  );

	object require(mixed modules, function continuation) 
		This method checks if the given module is available (registered).
		If not, it will try to load the module, if it can or throw an error with the 
		missing module, and return false.
		If it is available, it will return the module object, for a single module.
		If a continuation function is supplied, that function will be called if and when the
		required namespaces are available. 
		If you require multiple modules, pass an array with all required modules or a string 
		with all modules seperated with a ','. Extra spaces will be trimmed of.
		You can specify a url for any given module instead of a module name or in addition of
		a module name by seperating them with a '|' character:
			muze.require('jquery|//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js', ...)
			muze.require('//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js', ...)
		
		muze.require('module.not.available');
		
		will throw an error, so you can do this:
		
		try {
			muze.require('muze.event');
			// do stuff
		} catch(e) {
			// module is not available
		}

		or:
		
		muze.require('muze.event, muze.env', function() {
			// do stuff, this function is called in the global scope
		});
		
	object include(string url, string namespace)
		This method checks whether the given namespace is already registered. If
		so it doesn't do anything.
		If the namespace is not registered (or not entered), it dynamically loads
		the url as a javascript object (script tag).
		In both cases the method returns the onload handler object. This object
		has one method 'onload', which allows you to specify a function that should
		be run when the javascript is loaded. This function is also run if the
		javascript was already loaded and include didn't actually do anything.
		examples:
			// this will only load muze.test.js if namespace muze.test isn't already
			// loaded and muze.test.js isn't already loaded
			muze.include('muze.test.js', 'muze.test').onload(function() {
				muze.test.run();
			});
			
			// this will load muze.test.js, even if muze.test is already available
			// if muze.test.js uses the muze.namespace() method correctly, it
			// can then extend muze.test
			muze.include('muze.test.js').onload(...);
			
			// this will load the script if the url isn't already loaded
			muze.include('random.script.js').onload(function() {
				// script is available but is not registered with a namespace
			})

	mixed load(string url, bool waitforme, bool cached)
		This method allows you to easily do ajax calls. If 'waitforme' is true,
		the ajax call is done synchronously, and load will return the responseText.
		Otherwise the call is done asynchronously, and load will return an onload
		handler object, just like include, only in this case the function you
		specify in onload will be called with one argument, namely the responseText.
		If you set 'cached' to true, the url won't be extended with a timestamp,
		allowing the browser to cache the response.
		examples:
			var response = muze.load('ajax.call.html', true);

			muze.load('ajax.call.html')
			.onload(function(response) {
				myDiv.innerHTML = response;
			})
			.ontimeout( 1000, function() {
				myDiv.innerHTML = 'timed out';
				this.clear();
			} );
			
	object loader(object)
		This method allows you to easily implement your own loader handler, with onload and
		ontimeout methods. If you pass an object to loader, the onload handler will be called
		with that object defined as this. The ontimeout handler won't, it will allways use the loader as this.
		You must keep an internal reference to the loader object and call loader.loaded() manually
		to trigger the onload. Any arguments passed to loaded() will be passed on to an onload handler
		set throught loader.onload.
		If a timeout handler is set through loader.ontimeout(timer, method) than it will be called if
		the loader.loaded() method isn't called before the timeout.
		example:
			function myAjaxyThing() {
				var loader = muze.loader();
				// do some stuff
				mything.onload = function() {
					loader.loaded(response);
				}
				return loader;
			}
		methods:
			onload(callback)
			ontimeout(timer, callback)
			loaded()
			clear()
*/

var muze = this.muze = {};
muze.global = this;
muze.url = (function() {
	var scripts = document.getElementsByTagName('script');
	var index = scripts.length - 1;
	var urlHelper = document.createElement('a');
	urlHelper.href = scripts[index].src;
	return urlHelper;
})();

(function() {

	/* private methods */

	function _getHTTPObject(cors) { //FIXME: check if rearranged thing work 
		var xmlhttp = null;
		if (typeof XMLHttpRequest == 'undefined') {
			if (typeof ActiveXObject != 'undefined') {
				try {
					xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					try {
						xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
					} catch (E)  {
						xmlhttp = null;
					}
				}
			}
		} else {
			try {
				if ( cors && typeof XDomainRequest != 'undefined' ) {
					xmlhttp = new XDomainRequest();
				} else {
					xmlhttp = new XMLHttpRequest();
				}
			} catch (e) {
				xmlhttp = null;
			}
		}
		return xmlhttp;
	}

	function _namespaceWalk( module, handler ) {
		var rest	= module.replace(/^\s+|\s+$/g, ''); //trim
		var name	= '';
		var temp	= muze.global;
		var i 		= rest.indexOf( '.' );
		while ( i != -1 ) {
			name	= rest.substring( 0, i );
			if ( !temp[name])  {
				temp = handler(temp, name);
				if (!temp) {
					return temp;
				}
			}
			temp	= temp[name];
			rest	= rest.substring( i + 1 );
			i		= rest.indexOf( '.' );
		}
		if ( rest ) {
			if ( !temp[rest] ) {
				temp = handler(temp, rest);
				if (!temp) {
					return temp;
				}
			}
			temp	= temp[rest];
		}
		return temp;
	}
	
	/* private variables */

	var included	= {};
	var registered	= {};
	
	muze.namespace = function( module, implementation ) {
		var moduleInstance = _namespaceWalk( module, function(ob, name) {
			ob[name] = {};
			return ob;
		});
		registered[module]=true;
		if (typeof implementation == 'function') {
			implementation.call(moduleInstance);
		}
		return moduleInstance;
	};

	function _parseModuleInfo( moduleInfo ) {
		var pipePos = moduleInfo.indexOf('|');
		var slashPos = moduleInfo.indexOf('/');
		if ( pipePos != -1 ) {
			var module = moduleInfo.substring(0, pipePos);
			var url = moduleInfo.substring(pipePos+1);
		} else if ( slashPos != -1 ) {
			var module = false;
			var url = moduleInfo;
		} else {
			var module = moduleInfo;
			var url = document.createElement('a');
			url.href = muze.url.href;
			if ( url.search.match('muze') ) {
				url.search = '?'+module;
			} else if ( url.pathname.match('muze.js') ) {
				url.pathname = url.pathname.replace('muze.js', module.replace('.','/')+'.js' );
			} else {
				url.href = '';
			}
			url = url.href;
		}
		return {
			module: module,
			url: url
		};
	}

	function _parseModulesList( modules ) {
		// the continuation is a function which is only run if all requirements are met
		if (typeof modules == 'string') {
			var modulesList = (/,/.test(modules)) ? modules.split(',') : [ modules ];
		} else if (typeof modules.length != 'undefined') {
			var modulesList = modules;
		} else {
			throw('Incorrect argument 1 (required modules): '+modules);
			return false;
		}
		var scriptsToCheck = [];
		for ( var i=0; i<modulesList.length; i++ ) {
			scriptsToCheck[i] = _parseModuleInfo( modulesList[i] );
		}
		return scriptsToCheck;		
	}

	muze.require = function( modules, continuation ) {
		modules = _parseModulesList( modules );
		var scriptsToLoad = [];
		for (var i=0; i<modules.length; i++) {
			if ( modules[i].module ) {
				var moduleInstance = _namespaceWalk( modules[i].module, function(ob, name) {
					if (typeof continuation == 'undefined' || !modules[i].url ) {
						throw 'namespace ' + name + ' not found ';
					} else {
						scriptsToLoad[ scriptsToLoad.length ] = modules[i];
					}
				});
			} else if ( !included[ modules[i].url ] ) {
				if (typeof continuation == 'undefined' || !modules[i].url ) {
					throw 'namespace ' + name + ' not found ';
				} else {
					scriptsToLoad[ scriptsToLoad.length ] = modules[i];
				}
			}
		}
		if ( typeof continuation == 'function' ) {
			if ( scriptsToLoad.length ) {
				var loadedScripts = 0;
				for ( var i = 0; i<scriptsToLoad.length; i++ ) {
					muze.include( scriptsToLoad[i].url, scriptsToLoad[i].module ).onload( (function(module, url) {
						return function() {
							if ( module ) {
								registered[module] = url;
							}
							loadedScripts++
							if ( loadedScripts == scriptsToLoad.length ) {
								continuation.call(muze.global);
							}
						};
					})(scriptsToLoad[i].module, scriptsToLoad[i].url) );
				}
			} else {
				continuation.call(muze.global);
			}
		}
		return moduleInstance;
	};

	muze.include = function(url, module) {
		var loader = muze.loader();
		if (!included[url] && (!module || !registered[module])) {
			var handleOnLoad = function() {
				included[url] = true;
				if ( module ) {
					registered[module] = url;
				}
				loader.loaded();
			};
			var script = document.createElement('SCRIPT');
			script.src = url;
			try {
				script.addEventListener('load', handleOnLoad, false);
			} catch(e) {
				script.onreadystatechange = function() { 
					if (script.readyState == 'loaded' || script.readyState == 'complete') {
						handleOnLoad();
						script.onreadystatechange = null;
					}
				};
			}
			document.getElementsByTagName('HEAD')[0].appendChild(script); // FIXME: make this more cross browser
		} else {
			// setTimeout is not optional here, since we have to return
			// (this) first, before the _onload method is called, otherwise
			// there is no way for a user to change 'onload_handler'.
			setTimeout(loader.loaded, 1);
		}
		return loader;
	};
	
	muze.load = function(url, waitforme, cached) {
		var _isCorsURL = function(url) {
			var urlHelper = document.createElement('a');
			urlHelper.href = url;
			var newHost = url.hostname;
			var currentHost = document.location.href.hostname;
			return ( newHost && newHost!=currentHost);
		}
		var loader = muze.loader();
		var timestamp = null;
		// get content from url
		if (!cached) {
			timestamp = new Date();
			if ( url.match( /\?/ ) ) {
				timestamp = '&t=' + timestamp.getTime();
			} else {
				timestamp = '?t=' + timestamp.getTime();
			}
		} else {
			timestamp = '';
		}

		var http = _getHTTPObject(_isCorsURL(url));
		http.open( 'GET', url + timestamp, !waitforme );
		if ( !waitforme ) {
			http.onreadystatechange = function() {
				if (http.readyState == 4) {
					loader.loaded( http.responseText );
				}
			};
		}
		http.send( null );
		if ( waitforme ) {
			return http.responseText;
		} else {
			return loader;
		}
	};
	
	muze.loader = function() {
		var _continue = function( continuation ) {
			return function() {
				if (typeof continuation == 'function') {
					continuation.apply( this, arguments );
					continuation = null;
				}
			};
		};
		var loaded = false;
		var onload_handler = null;
		var ontimeout_handler = null;
		var loader = {};
		loader.onload = function(handler) {
			onload_handler = handler;
			return this;
		};
		loader.ontimeout = function( timer, handler ) {
			muze.global.setTimeout( function() {
				if (!loaded) {
					_continue(handler)();
				}
			}, timer );
			return this;
		};
		loader.loaded = function() {
			loaded = true;
			_continue(onload_handler).apply(this, arguments);
		};
		loader.clear = function() {
			onload_handler = null;
			ontimeout_handler = null;
		};
		return loader;
	};
	
})();

/*
 * Capabilities testing, verbatim from:
 * Env - v0.1
 * Copyright (c) 2009 Ryan Morr (ryanmorr.com)
 * Some techniques and content inspired and derived from:
 * John Resig and jQuery (ejohn.org, jquery.com), Juriy Zaytsev (thinkweb2.com/projects/prototype), Andrea Giammarchi (webreflection.blogspot.com), 
 * Peter Michaux (peter.michaux.ca), and Diego Perini (iport.it)
 * Licensed under the MIT license.
 */

 muze.namespace('muze.env');
 
 muze.env = (function() {
	//define the global Env object
	var Env = {};
	//define the bugs object
	Env.bugs = {};
	//define the support object
	Env.support = {};

	//isHostMethod, isHostCollection, and isHostObject are courtesy of Peter Michaux and his exellecent post on
	//feature detection (http://peter.michaux.ca/articles/feature-detection-state-of-the-art-browser-scripting)

	
	//Is the member a callable method of its host (element.hasAttribute)
	Env.isHostMethod = function(o, prop){
		return Env.isHostCollection(o, prop) || typeof o[prop] === "unknown";
	};
	
	//Is the member a collection object of its host (element.childNodes)
	Env.isHostCollection = function(o, prop){
		return Env.isHostObject(o, prop) || Env.isFunction(o[prop]);
	};
	
	//Is the member a non-callable property of its host (element.offsetWidth)
	Env.isHostObject = function(o, prop){
		return !!(Env.isObject(o[prop]) && o[prop]);
	};
	
	//Is the object defined
	Env.isUndefined = function(o){
		return typeof o === 'undefined';
	};
	
	//Is the object a boolean (true/false)
	Env.isBoolean = function(o){
		return typeof o === 'boolean';
	};
	
	//Is the object a number
	Env.isNumber = function(o){
		return typeof o === 'number' && isFinite(o);
	};
	
	//Is the object a string
	Env.isString = function(o){
		return typeof o === 'string';
	};
	
	//Is the object null
	Env.isNull = function(o){
		return o === null;
	};
	
	//Is the object a native method/function of the browser environment
	//Modified version of Diego Perini's implementation (http://github.com/dperini/nwevents/blob/ac33e52c1ed1c1c3a1bb1612384ca5b2f7a9b3ef/src/nwmatcher.js#L41)
	Env.isNative = function(o){		
		return (/\{\s*\[native code\]\s*\}|^\[function\]$/).test(String(o));
	};
	
	//Is the object a function or method
	Env.isFunction = function(o){
		return Object.prototype.toString.call(o) === "[object Function]";
	};
	
	//Is the obect an array
	Env.isArray = function(o){ 
		return Object.prototype.toString.call(o) === "[object Array]";
	};
	
	//Is the object a date object
	Env.isDate = function(o){
		return Object.prototype.toString.call(o) === "[object Date]";
	};

	//Is the object a regular expression
	Env.isRegExp = function(o){
		return Object.prototype.toString.call(o) === "[object RegExp]";
	};

	//Is the object an element of an HTML/XHTML document (div)
	Env.isElement = function(o){
		return (window.Element && o instanceof window.Element) || (!!o.htmlElement || (o.nodeName && o.nodeType === 1));
	};
	
	//Is the object a document object
	Env.isDocument = function(o){
		return Object.prototype.toString.call(o) === '[object HTMLDocument]' || o.nodeType === 9;
	};
	
	//Is the object an event object (click)
	Env.isEvent = function(o){
		return Object.prototype.toString.call(o) === '[object Event]' || (Env.isObject(o) && Env.isUndefined(o.constructor) && (window.event && o.clientX && o.clientX === window.event.clientX));
	};
	
	//Is the object a window
	Env.isWindow = function(o){
		var type = Object.prototype.toString.call(o);
		if(type === "[object Window]" || type === "[object DOMWindow]" || type === "[object global]"){
			return true;
		}else if(Env.isObject(o) && !!(o.Array && o.String)){
			return true;
		}else{
			return false;
		}
    };
	
	//Is the object a node of any kind (document, comment node, element, etc...)
	Env.isNode = function(o){
		return (o.nodeName && o.nodeType && (/1|2|3|4|5|6|7|8|9|10|11|12/).test(o.nodeType));
	};
	
	//Is the object a nodelist (anything return by getElementsByTagName)
	Env.isNodeList = function(o){
		var type = Object.prototype.toString.call(o);
		if(type === "[object NodeList]" || type === "[object HTMLCollection]"){
			return true;
		}else if(Env.isArrayLike(o) && o.item){
			for(var i=0, length = o.length; i < length; i++){
				if(!Env.isNode(o[i])){
					return false;
				}
			}
			return true;
		}else{
			return false;
		}
	};
	
	//Does the object share many of the same characteristics as an array (nodelist, arguments)
	Env.isArrayLike = function(o){
		return (Env.isNumber(o.length) && !Env.isFunction(o) && !Env.isString(o)) || Env.isArray(o);
	};
	
	//Is the object alien to the browser (unknown, undefinable)
	Env.isAlien = function(o){
		return o && (!Env.isFunction(o) && Env.isNative(o)) || (Env.isObject(o) && !Env.isFunction(o.constructor));
	};
	
	//Is the object a object
	Env.isObject = function(o){
		return typeof o === 'object';
	};
	
	//Is the object empty, will return true if it is undefined, null, an empty string (no characters), number 0, 
	//array or nodelist with no indexed values, and an object with no key/value pairs
	Env.isEmpty = function(o){
		switch(Env.type(o)){
			case 'undefined':
				return true;
			case 'null':
				return true;
			case 'boolean':
				return o === false;
			case 'string':
				return (/^\s*$/).test(o);
			case 'number':
				return o === 0;
			case 'array':
			case 'nodelist':
			case 'arraylike':
				return o.length === 0;
			case 'object':
				for(var prop in o){ 
					return false;
				}
 				return true;
		}
	};
	
	//Improved version of typeof - returns exactly what the object is (array, nodelist, event, etc...)
	Env.type = function(o){
		for(var method in Env){
			//Loop through each method with a name that starts with is (isArray) but not isHost (isHostMethod)
			//Each method was ordered specifically to prevent false positives (an array recognized as an object)
			if((/^is(?!Host|Empty)/).test(method)){
				if(Env[method](o)){
					//string the first to letters (is) of the method and lowercase it, that is the name of the object (isArray = array)
					return method.substr(2).toLowerCase();
				}
			}
		}
		//If all else fails, default to typeof
		return typeof o;
	};
		
	//Does the browser support a specific event of a specific node (div, iframe, img, input, document, window, etc...)
	//Some of the techniques utilized in this method are courtesy of Juriy Zaytsev and his recent article of the very same method
	//(http://thinkweb2.com/projects/prototype/detecting-event-support-without-browser-sniffing/)
	Env.support.event = (function(){
		//Cached results
		var cache = {};
		//Get a tokenized string unique to the node and event type
		var getKey = function(type, el){
			return (el ? (Env.isElement(el) ? el.tagName.toLowerCase() : Env.type(el)) : 'div') + ':' + type;
		};
		//The first parameter is the event type(click, mouseenter, load, abort, change, etc...);
		//The second parameter is optional and can either be a raw node (div, document, window, iframe, img) or a 
		//tag name that will be create via document.createElement
		return function(type, source){
			//Get the key to compare against the cache
			var key = getKey(type, source || false);
			if(key in cache){
				//If the key exists within the cache return the result and avoid the full test sequence to improve performance
				return cache[key];
			}else if(type in Env.support){
				//Test to see if any of the mutation events (tested for outside the context of this method) exist within the Env.support cache
				//and return the result - if none is found the test will run and will fail as these tests are not optimized for mutation events.
				//Currently the only known detect for mutation events is to acutally try them as you will see below
				//Also element inference for mutation events is not supported
				return Env.support[type];
			}else{
				//If the second parameter was provided - determine what it is and return either the raw node or a new element
				var el = !!source && !Env.isString(source) ? source : document.createElement(source || 'div');
				//first perform a simple "in" check to see if the event is pre-defined
				var supported = ("on"+type in el);
				//Next try using setAttribute to create a new event which will only work for supported events in some browsers such as Firefox
				if(!supported && el.setAttribute){
					el.setAttribute("on"+type, 'return;');
					//If the attribute is a function, it must be a supported event
					supported = Env.isFunction(el["on"+type]);
					//Remove the attribute for a node that exists outside this methods context
					if(!Env.isString(source)){
						el.removeAttribute("on"+type);
					}
				}
				//in a test only supported by IE, we try to actually fire the specified event which only works for supported events of that node
				//Caution! this will actually fire the event if the node exists outside the context of this method and there is a handler listening to this event!!
				if(!supported && document.createEventObject){
					//First create an event object
					var evt = document.createEventObject();
					try{
						//Fire the event
						el.fireEvent("on"+type, evt);
						supported = true;
					}catch(e){}
				}
				//Finally, for any browsers that support the global Event object we check to see if the event is a property
				//We keep this technique for last because it does not allow for node inference, it merely tests whether the 
				//browser supports the specified event in some capacity
				if(!supported && window.Event){
					supported = !!window.Event[type.toUpperCase()];
				}
				//Get the key and cache the result for future tests
				cache[getKey(type, el)] = supported;
				//If the node is unique to this execution context, null it out and save the memory
				el = !!source ? (Env.isString(source) ? null : source) : null;
				//Return the result
				return supported;
			}
		}
	})();
	
	//Does the browser support a specific CSS style and/or style value
	Env.support.style = (function(){
		//To improve performance against multiple tests of the same parameters, we employ caching
		var cache = {};
		//Create the element that will be used for all testing within this method
		var el = document.createElement('div');
		//Get the unique key for this style/value pair for caching
		var getKey = function(style, value){
			return style + ":" + value || "null";
		};
		
		//Convert a CSS style string to camel case so it can be used in JavaScript
		var toCamelCase = (function(){
			//Create a cache to hold the camel-cased strings and improve performance
			var cache = {};
			return function(str){
				//If the style doesn't exists within the cache, do it
				if(!cache[str]){
					//We use our own feature testing to see if strings will accept functions as a second argument (Safari 2.0.2)
					if(Env.support.stringReplaceFunction){
						//If functions are supported, make the new string, cache it, and return it
						return cache[str] = str.replace(/-([a-z])/g, function(word, letter){
							return letter.toUpperCase();
						});
					}else{
						//Fallback to generic code that should work on any browser
						var parts = str.split('-'), camel = parts[0];
						//If there is only one part to the split, than the style must not need to be converted to camel case (width)
						if(parts.length > 1){
							for(var i=1, len=parts.length; i < len; i++){
									camel += parts[i].charAt(0).toUpperCase() + parts[i].substring(1);
							}
						}
						//Cache it and return it
						return cache[str] = camel;
					}
				}else{
					//If the string exists within the cache, return the cached value
					return cache[str];
				}
			}
		})();
		
		//First argument is the style to be tested (opacity, minHeight, etc.)
		//The second argument is an optional argument to test support for a style value (position: fixed)
		return function(style, value){
			//get the key that will be used for caching
			var key = getKey(style, value);
			if(key in cache){
				//If the key exists within the cache, return the held value
				return cache[key];
			}else{
				//Default to false, support must be proven
				var supported = false;
				//Camel-case the style for use against raw objects
				var camel = toCamelCase(style);
				//If the native runtime style exists than proceed (IE)
				if(el.runtimeStyle){
					//We use a try-catch block because IE will throw an error on unsupported style values
					try{
						//We set the style against the elements style object and if the value is not supplied we 
						//default to an empty string (works against all styles)
						el.style[camel] = value || "";
						//Unsupported styles and style values will always return undefined
						supported = !Env.isUndefined(el.runtimeStyle[camel]);
					}catch(e){};
				}else{
					//Must be a standards compliant browser (FF, Opera, Safari, Chrome, etc.)
					//Set the elements style, if value is not supplied we default to inherit which is supported
					//by all standards compliant browsers
					el.style[style] = value || "inherit";
					//Get the view for a little short form
					var view = document.defaultView;
					//If the browser supportes document.defaultView and its getComputedStyle method than proceed
					//Currently there is not fallback for this
					if(view && view.getComputedStyle){
						//Get the computed style
						var cs = view.getComputedStyle(el, "")[camel];
						//Unsupported styles always return undefined
						supported = !Env.isUndefined(cs);
						//If a value was supplied than we have to test it separately because the getComputedStyle method will often parse the value into
						//something much harder to detect i.e. (color: 1px returns rgb(0,0,0))
						if(value){
							//We create a new div as the innerHTML within our main element
							//Any hyphenated styles must be supplied in that fashion for these tests(vertical-align not verticalAlign)
							//a little syntastic sugar too
							el.innerHTML = '<div style="'+style+':'+value+'"></div>';
							//Unsupported style values will always return an empty string
							supported = supported && el.firstChild.style[camel] !== "";
						}
					}
				}
				//Cache the value and return it
				return cache[key] = supported;
			}
		}
	})();
	


	//Does the browser support cookies
	//Env.support.cookies = !!navigator.cookieEnabled && navigator.cookieEnabled;
	//The above check generates a security violation in IE when used inside a Modal Dialog, hence the adjusted check below
	document.cookie="muzeEnvTestCookie=1; path=/";
	Env.support.cookies = (document.cookie.indexOf("muzeEnvTestCookie") != -1) ? true : false;
	//Is the browser in standards mode or quirks mode
	Env.support.strict = !!document.compatMode && document.compatMode == "CSS1Compat";
	//Is the current page hosted on a secure server
	Env.support.secure = !!window.location.href && window.location.href.toLowerCase().indexOf("https") === 0;	
	//Does the browser support canvas
	Env.support.canvas = !!document.createElement("canvas").getContext;
	//Does the browser support SVG
	Env.support.svg = !!(document.createElementNS && document.createElementNS('http://www.w3.org/2000/svg', 'svg').width);
	//Does the browser XPath queries on the HTML/XHTML document
	Env.support.xpath = !!document.evaluate;
	
	//Default to false, their support must be proved
	//Mutation event fired when an attribute is changed
	Env.support.DOMAttrModified = false;
	//Mutation event fired when a node is inserted into the DOM
	Env.support.DOMNodeInserted = false;
	//Mutation event fired when a node is removed from the DOM
	Env.support.DOMNodeRemoved = false;
	//Mutation event fired when something changes to one of the elements descendents
	Env.support.DOMSubtreeModified = false;
	
	//Does the browser support VML (vector graphics)
	//Inspired by Google Maps (http://maps.google.com/intl/en_ALL/mapfiles/73/maps2.api/main.js)
	Env.support.vml = (function(){
		//Create an element to act as a container
		var el = document.createElement('div');
		//Try adding a VML object via element's innerHTML
		el.innerHTML = '<v:shape adj="1" />';
		//Get the VML object
		var vml = el.firstChild;
		//Declare VML bevaviour 
		vml.style.behavior = "url(#default#VML)";
		//If the VML object exists and the attribute is an object than VML is supported
		return vml && Env.isObject(vml.adj);
	})();
	
	//Does the browser support flash
	Env.support.flash = (function(){
		//Check for ActiveX first because some versions of IE support navigator.plugins, just not the same as other browsers
		if(window.ActiveXObject){
			try{
				//try to create a flash instance
				new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
				return true;
			}catch(e){};
			//If the try-catch fails, return false
			return false;
		}else if(navigator.plugins){
			//Loop through all the plugins
			for(var i=0, length = navigator.plugins.length; i < length; i++){
				//test to see if any plugin names contain the word flash, if so it must support it - return true
				if((/flash/gi).test(navigator.plugins[i].name)){
					return true;
				}
			}
			//return false if no plugins match
			return false;
		}
		//Return false if ActiveX and nagivator.plugins are not supported
		return false;
	})();
	
	//Can the browser convert array-like objects (nodelist, arguments) to an array using native methods
	Env.support.nativeArrayConversion = (function(){
		try{
			//get the child nodes of the html element
			var children = document.documentElement.childNodes;
			//hijack the slice and use it for the conversion - make sure the result is an array
			return Env.isArray(Array.prototype.slice.call(children));
		}catch(e){}
		//return false if the try-catch block fails
		return false;
	})();
	
	//Does the string replace method support functions as a second argument (Safari 2.0.2)
	Env.support.stringReplaceFunction = (function(){
		var str = "";
		if(str.replace){
			//If the string equals success as returned by the function, then the replace method of strings must 
			//accept functions as second parameters, otherwise it is false
			return str.replace(str, function(){return 'success';}) === 'success';
		}else{
			//Return false if the strings to not support a replace method
			return false;
		}
	})();
	
	//Does the browser allow textnode appending, and eval the text
	Env.support.scriptEval = (function(){
		//Create a script element
		var script = document.createElement("script");
		try{
			//Try to append a textnode that will hopefully be evaluated and create a new property on the Env namespace
			script.appendChild(document.createTextNode("muze.env.test = true;"));
		}catch(e){
			//If an exception is caught, it must be IE and not supported
			return false;
		};
		//Append the script to the root element (<html>) to allow an eval
		document.documentElement.appendChild(script);
		//Quickly remove the script
		document.documentElement.removeChild(script);
		//We are done with the script so we null it out to save memory
		script = null;
		//Was the eval successful? Does the property exist
		var supported = !!Env.test;
		//Delete the property
		delete Env.test;
		//Return the supported value
		return supported;
	})();
	
	//Does the browser allow DOM manipulation of tables via innerHTML
	Env.bugs.tableInnerHTML = (function(){
		//Create the table
		var el = document.createElement("table");
		try{
			//Use a try catch block because IE throws errors
			//Append a row to the innerHTML of the table
			el.innerHTML = "<tr><td>test</td></tr>";
			//Query the table for a td element to check if the insert worked
			return el.getElementsByTagName('td').length === 0;
		}catch(e){};
		//The try-catch block fails, there must be a bug
		return true;
	})();
	
	//Does the browser allow DOM manipulation of selects via innerHTML
	Env.bugs.selectInnerHTML = (function(){
		 //Create the select
		var el = document.createElement("select");
		//Try appending to the innerHTML
		el.innerHTML = '<option value="test">test</option>';
		if(el.options && el.options[0]){
			//Does the option exist within the select, if not there is a bug
			return el.options[0].nodeName.toUpperCase() !== "OPTION";
		}else{
			//If no options are found, the insert must have failed
			return true;
		}
	})();
	
	Env.bugs.getElementById = (function(){
		//Create a container for everything
		var el = document.createElement('div');
		//Create a unique ID for the input we are about to create
		var id = "input" + new Date().getTime();
		//Add an input to test a bug with getElementById
		el.innerHTML = '<form><input type="hidden" name="'+ id +'" /></form>';
		//Get the head element
		var head = document.getElementsByTagName('head')[0];
		//append the container element to the head
		head.appendChild(el);
		//Does getElementById return elements with the same defined name
		var supported = !Env.isNull(document.getElementById(id));
		//remove the el from the head
		head.removeChild(el);
		//we are done with the test, null out the element and save memory
		el = null;
		//return the supported value
		return supported;
	})();
	
	//Create an element to perform various DOM and CSS related capabilities tests against
	var el = document.createElement("div");
	//Add an attribute which will later be changed to trigger a mutation event
	el.id = new Date().getTime() + Math.random();
	//Quickly create a child node that will be used to test various CSS styles
	el.innerHTML = '<div class="TEST unique" style="width:2px ; height:2px ; padding:1px;"></div><div class="random"></div>';
	//Add a comment node to the element
	el.appendChild(document.createComment('test'));
	
	//Test support for these various mutation events
	//Does the browser support DOMAttrModified
	addEvent(el, 'DOMAttrModified', handler);
	//Does the browser support DOMNodeInserted
	addEvent(el, 'DOMNodeInserted', handler);
	//Does the browser support DOMNodeRemoved
	addEvent(el, 'DOMNodeRemoved', handler);
	//Does the browser support DOMSubtreeModified
	addEvent(el, 'DOMSubtreeModified', handler);
			
	//Does the browser support cssFloat as the proxy for CSS float
	Env.support.cssFloat = "cssFloat" in el.style;
	//Does the browser support CSS transforms
	Env.support.cssTransform = "WebkitTransform" in el.style || "MozTransform" in el.style;

	
	//Change the id to trigger the DOMAttrModified and DOMSubtreeModified (except in Safari) event if it is supported
	el.id = new Date().getTime() + Math.random();	
	//Create a form element to test a bug in IE
	//Alternatively, appending a new element will trigger the DOMSubtreeModified event is it is supported (Safari);
	var form = el.appendChild(document.createElement('form'));
	//Add an input to test a bug with setting the name attribute
	form.innerHTML = '<input type="hidden" />';
	//Get the input element for testing
	var input = el.getElementsByTagName('input')[0];
	//try to set the name of the input via setAttribute
	input.setAttribute('name', 'test');
	
	
	//Does getElementsByTagName return comment nodes (IE)
	Env.bugs.getElementsByTagName = (function(){
		//Get all descending elements of the root element via "*"
		var elements = el.getElementsByTagName('*');
		//Loop the elements and look for comment nodes
		for(var i=0, length = elements.length; i < length; i++){
			if(elements[i].nodeType == 8){
				//If a comment node is found, its a bug
				return true;
			}
		}
		//If the loop completes without finding any comment nodes then no bug
		return false;
	})();
	
	Env.bugs.querySelectorAll = (function(){
		//Does this browser support querySelectorAll
		if(el.querySelectorAll){
			//Safari sometimes doesn't respond to case sensitivity
			return el.querySelectorAll(".TEST").length === 0;
		}else{
			//If querySelectorAll isn't supported return false for no bug
			return false;
		}
	})();
	
	Env.bugs.getElementsByClassName = (function(){
		//Does this browser support getElementsByClassName
		if(el.getElementsByClassName){
			//Opera 9.6 doesn't find elements by their second class name
			return el.getElementsByClassName("unique").length === 0;
		}else{
			//if getElementsByClassName isn't supported return false for no bug
			return false;
		}
	})();
	
	Env.bugs.cachedClassNames = (function(){
		//Does this browser support getElementsByClassName				  
		if(el.getElementsByClassName){
			//change the class name of the last element to test 
			el.firstChild.className = "random";
			//Safari 3.2 caches class names
			return el.getElementsByClassName("random").length === 1;
		}else{
			//If getElementsByClassName isn't supported return false for no bug
			return false;
		}
	})();

	
	//These tests can only be determined once they are included in the DOM and rendered
	//For now we just set them to false, they must be proven
	
	Env.support.boxModel = false;
	Env.bugs.setNameAttribute = false;
		
	//Env is not ready until a few tests involving insertion into the DOM are performed	
	Env.ready = false;
	//Certain properties need to be added to the DOM and rendered to be tested accurately	
	//This method can be called manually in your scripts once the DOM has finished loading or it can be left up to the automatic polling for DOM completion
	//The option is available because in some cases where the DOM is small (low bytes and less to load time), the poll will not trigger the method and complete 
	//the tests until, in some cases, after window.onload is fired, so we provide this method to manually invoke the method as a last resort in such a case
	Env.onReady = function(){
		if(!Env.ready){
			//Query the DOM for the body
			var body = document.getElementsByTagName('body')[0];
			//If the body and appendChild and removeChild methods are recognized, than proceed
			if(body && body.appendChild && body.removeChild){
				//append the element to the DOM
				body.appendChild(el);
				//Is the box model support 
				Env.support.boxModel = el.firstChild.offsetWidth === 4;
				//Query for the input based on the name to see if setAttribute on the name worked
				Env.bugs.setNameAttribute = form.elements ? Env.isUndefined(form.elements.test) : false;
				//Remove the element from the dom
				body.removeChild(el);
				//clear the interval
				clearTimeout(poll);
				//make the elements and the interval null to save memory
				el = poll = null;
				//All tests are done and Env is ready to be used
				Env.ready = true;
			}
		}
	};
	
	//poll Env.ready
	var poll = setTimeout(Env.onReady, 1);

	//Helper functions purely for testing support of the mutation events
	function handler(e){
		Env.support[e.type] = true;
		removeEvent(e.target, e.type, arguments.callee);
	};
	
	function addEvent(el, type, fn){
		if(el.addEventListener){
			el.addEventListener(type, fn, false);
		}else if(el.attachEvent){
			el.attachEvent("on" + type, fn);
		}else{
			el["on"+type] = fn;
		}
	};
	
	function removeEvent(el, type, fn){
		if(el.removeEventListener){
			el.removeEventListener(type, fn, false);
		}else if(el.detachEvent){
			el.detachEvent('on' + type, fn);
		}else{
			el["on"+type] = null;
			delete el["on"+type];
		}
	};
	return Env;
})();
