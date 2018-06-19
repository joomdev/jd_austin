/*!
---
provides: moofx
version: 3.1.0
description: A CSS3-enabled javascript animation library
homepage: http://moofx.it
author: Valerio Proietti <@kamicane> (http://mad4milk.net)
license: MIT (http://mootools.net/license.txt)
includes: cubic-bezier by Arian Stolwijk (https://github.com/arian/cubic-bezier)
...
*/

(function(modules) {
    var cache = {}, require = function(id) {
        var module = cache[id];
        if (!module) {
            module = cache[id] = {};
            var exports = module.exports = {};
            modules[id].call(exports, require, module, exports, window);
        }
        return module.exports;
    };
    window["moofx"] = require("0");
})({
    "0": function(require, module, exports, global) {
        /*          .-   3
.-.-..-..-.-|-._.
' ' '`-'`-' ' ' '
*/
                "use strict";

        // color and timer
        var color = require("1"), frame = require("2");

        // if we're in a browser we need ./browser, otherwise ./fx
        var moofx = typeof document !== "undefined" ? require("7") : require("b");

        moofx.requestFrame = function(callback) {
            frame.request(callback);
            return this;
        };

        moofx.cancelFrame = function(callback) {
            frame.cancel(callback);
            return this;
        };

        moofx.color = color;

        // and export moofx
        module.exports = moofx;
    },
    "1": function(require, module, exports, global) {
        /*
color
*/
                "use strict";

        var colors = {
            maroon: "#800000",
            red: "#ff0000",
            orange: "#ffA500",
            yellow: "#ffff00",
            olive: "#808000",
            purple: "#800080",
            fuchsia: "#ff00ff",
            white: "#ffffff",
            lime: "#00ff00",
            green: "#008000",
            navy: "#000080",
            blue: "#0000ff",
            aqua: "#00ffff",
            teal: "#008080",
            black: "#000000",
            silver: "#c0c0c0",
            gray: "#808080",
            transparent: "#0000"
        };

        var RGBtoRGB = function(r, g, b, a) {
            if (a == null || a === "") a = 1;
            r = parseFloat(r);
            g = parseFloat(g);
            b = parseFloat(b);
            a = parseFloat(a);
            if (!(r <= 255 && r >= 0 && g <= 255 && g >= 0 && b <= 255 && b >= 0 && a <= 1 && a >= 0)) return null;
            return [ Math.round(r), Math.round(g), Math.round(b), a ];
        };

        var HEXtoRGB = function(hex) {
            if (hex.length === 3) hex += "f";
            if (hex.length === 4) {
                var h0 = hex.charAt(0), h1 = hex.charAt(1), h2 = hex.charAt(2), h3 = hex.charAt(3);
                hex = h0 + h0 + h1 + h1 + h2 + h2 + h3 + h3;
            }
            if (hex.length === 6) hex += "ff";
            var rgb = [];
            for (var i = 0, l = hex.length; i < l; i += 2) rgb.push(parseInt(hex.substr(i, 2), 16) / (i === 6 ? 255 : 1));
            return rgb;
        };

        // HSL to RGB conversion from:
        // http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript
        // thank you!
        var HUEtoRGB = function(p, q, t) {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 6) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };

        var HSLtoRGB = function(h, s, l, a) {
            var r, b, g;
            if (a == null || a === "") a = 1;
            h = parseFloat(h) / 360;
            s = parseFloat(s) / 100;
            l = parseFloat(l) / 100;
            a = parseFloat(a) / 1;
            if (h > 1 || h < 0 || s > 1 || s < 0 || l > 1 || l < 0 || a > 1 || a < 0) return null;
            if (s === 0) {
                r = b = g = l;
            } else {
                var q = l < .5 ? l * (1 + s) : l + s - l * s;
                var p = 2 * l - q;
                r = HUEtoRGB(p, q, h + 1 / 3);
                g = HUEtoRGB(p, q, h);
                b = HUEtoRGB(p, q, h - 1 / 3);
            }
            return [ r * 255, g * 255, b * 255, a ];
        };

        var keys = [];

        for (var c in colors) keys.push(c);

        var shex = "(?:#([a-f0-9]{3,8}))", sval = "\\s*([.\\d%]+)\\s*", sop = "(?:,\\s*([.\\d]+)\\s*)?", slist = "\\(" + [ sval, sval, sval ] + sop + "\\)", srgb = "(?:rgb)a?", shsl = "(?:hsl)a?", skeys = "(" + keys.join("|") + ")";

        var xhex = RegExp(shex, "i"), xrgb = RegExp(srgb + slist, "i"), xhsl = RegExp(shsl + slist, "i");

        var color = function(input, array) {
            if (input == null) return null;
            input = (input + "").replace(/\s+/, "");
            var match = colors[input];
            if (match) {
                return color(match, array);
            } else if (match = input.match(xhex)) {
                input = HEXtoRGB(match[1]);
            } else if (match = input.match(xrgb)) {
                input = match.slice(1);
            } else if (match = input.match(xhsl)) {
                input = HSLtoRGB.apply(null, match.slice(1));
            } else return null;
            if (!(input && (input = RGBtoRGB.apply(null, input)))) return null;
            if (array) return input;
            if (input[3] === 1) input.splice(3, 1);
            return "rgb" + (input.length === 4 ? "a" : "") + "(" + input + ")";
        };

        color.x = RegExp([ skeys, shex, srgb + slist, shsl + slist ].join("|"), "gi");

        module.exports = color;
    },
    "2": function(require, module, exports, global) {
        /*
requestFrame / cancelFrame
*/
                "use strict";

        var array = require("3");

        var requestFrame = global.requestAnimationFrame || global.webkitRequestAnimationFrame || global.mozRequestAnimationFrame || global.oRequestAnimationFrame || global.msRequestAnimationFrame || function(callback) {
            return setTimeout(callback, 1e3 / 60);
        };

        var callbacks = [];

        var iterator = function(time) {
            var split = callbacks.splice(0, callbacks.length);
            for (var i = 0, l = split.length; i < l; i++) split[i](time || (time = +new Date()));
        };

        var cancel = function(callback) {
            var io = array.indexOf(callbacks, callback);
            if (io > -1) callbacks.splice(io, 1);
        };

        var request = function(callback) {
            var i = callbacks.push(callback);
            if (i === 1) requestFrame(iterator);
            return function() {
                cancel(callback);
            };
        };

        exports.request = request;

        exports.cancel = cancel;
    },
    "3": function(require, module, exports, global) {
        /*
array
 - array es5 shell
*/
                "use strict";

        var array = require("4")["array"];

        var names = ("pop,push,reverse,shift,sort,splice,unshift,concat,join,slice,toString,indexOf,lastIndexOf,forEach,every,some" + ",filter,map,reduce,reduceRight").split(",");

        for (var methods = {}, i = 0, name, method; name = names[i++]; ) if (method = Array.prototype[name]) methods[name] = method;

        if (!methods.filter) methods.filter = function(fn, context) {
            var results = [];
            for (var i = 0, l = this.length >>> 0; i < l; i++) if (i in this) {
                var value = this[i];
                if (fn.call(context, value, i, this)) results.push(value);
            }
            return results;
        };

        if (!methods.indexOf) methods.indexOf = function(item, from) {
            for (var l = this.length >>> 0, i = from < 0 ? Math.max(0, l + from) : from || 0; i < l; i++) {
                if (i in this && this[i] === item) return i;
            }
            return -1;
        };

        if (!methods.map) methods.map = function(fn, context) {
            var length = this.length >>> 0, results = Array(length);
            for (var i = 0, l = length; i < l; i++) {
                if (i in this) results[i] = fn.call(context, this[i], i, this);
            }
            return results;
        };

        if (!methods.every) methods.every = function(fn, context) {
            for (var i = 0, l = this.length >>> 0; i < l; i++) {
                if (i in this && !fn.call(context, this[i], i, this)) return false;
            }
            return true;
        };

        if (!methods.some) methods.some = function(fn, context) {
            for (var i = 0, l = this.length >>> 0; i < l; i++) {
                if (i in this && fn.call(context, this[i], i, this)) return true;
            }
            return false;
        };

        if (!methods.forEach) methods.forEach = function(fn, context) {
            for (var i = 0, l = this.length >>> 0; i < l; i++) {
                if (i in this) fn.call(context, this[i], i, this);
            }
        };

        var toString = Object.prototype.toString;

        array.isArray = Array.isArray || function(self) {
            return toString.call(self) === "[object Array]";
        };

        module.exports = array.implement(methods);
    },
    "4": function(require, module, exports, global) {
        /*
shell
*/
                "use strict";

        var prime = require("5"), type = require("6");

        var slice = Array.prototype.slice;

        var ghost = prime({
            constructor: function ghost(self) {
                this.valueOf = function() {
                    return self;
                };
                this.toString = function() {
                    return self + "";
                };
                this.is = function(object) {
                    return self === object;
                };
            }
        });

        var shell = function(self) {
            if (self == null || self instanceof ghost) return self;
            var g = shell[type(self)];
            return g ? new g(self) : self;
        };

        var register = function() {
            var g = prime({
                inherits: ghost
            });
            return prime({
                constructor: function(self) {
                    return new g(self);
                },
                define: function(key, descriptor) {
                    var method = descriptor.value;
                    this[key] = function(self) {
                        return arguments.length > 1 ? method.apply(self, slice.call(arguments, 1)) : method.call(self);
                    };
                    g.prototype[key] = function() {
                        return shell(method.apply(this.valueOf(), arguments));
                    };
                    prime.define(this.prototype, key, descriptor);
                    return this;
                }
            });
        };

        for (var types = "string,number,array,object,date,function,regexp".split(","), i = types.length; i--; ) shell[types[i]] = register();

        module.exports = shell;
    },
    "5": function(require, module, exports, global) {
        /*
prime
 - prototypal inheritance
*/
                "use strict";

        var has = function(self, key) {
            return Object.hasOwnProperty.call(self, key);
        };

        var each = function(object, method, context) {
            for (var key in object) if (method.call(context, object[key], key, object) === false) break;
            return object;
        };

        if (!{
            valueOf: 0
        }.propertyIsEnumerable("valueOf")) {
            // fix for stupid IE enumeration bug
            var buggy = "constructor,toString,valueOf,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString".split(",");
            var proto = Object.prototype;
            each = function(object, method, context) {
                for (var key in object) if (method.call(context, object[key], key, object) === false) return object;
                for (var i = 0; key = buggy[i]; i++) {
                    var value = object[key];
                    if ((value !== proto[key] || has(object, key)) && method.call(context, value, key, object) === false) break;
                }
                return object;
            };
        }

        var create = Object.create || function(self) {
            var constructor = function() {};
            constructor.prototype = self;
            return new constructor();
        };

        var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

        var define = Object.defineProperty;

        try {
            var obj = {
                a: 1
            };
            getOwnPropertyDescriptor(obj, "a");
            define(obj, "a", {
                value: 2
            });
        } catch (e) {
            getOwnPropertyDescriptor = function(object, key) {
                return {
                    value: object[key]
                };
            };
            define = function(object, key, descriptor) {
                object[key] = descriptor.value;
                return object;
            };
        }

        var implement = function(proto) {
            each(proto, function(value, key) {
                if (key !== "constructor" && key !== "define" && key !== "inherits") this.define(key, getOwnPropertyDescriptor(proto, key) || {
                    writable: true,
                    enumerable: true,
                    configurable: true,
                    value: value
                });
            }, this);
            return this;
        };

        var prime = function(proto) {
            var superprime = proto.inherits;
            // if our nice proto object has no own constructor property
            // then we proceed using a ghosting constructor that all it does is
            // call the parent's constructor if it has a superprime, else an empty constructor
            // proto.constructor becomes the effective constructor
            var constructor = has(proto, "constructor") ? proto.constructor : superprime ? function() {
                return superprime.apply(this, arguments);
            } : function() {};
            if (superprime) {
                var superproto = superprime.prototype;
                // inherit from superprime
                var cproto = constructor.prototype = create(superproto);
                // setting constructor.parent to superprime.prototype
                // because it's the shortest possible absolute reference
                constructor.parent = superproto;
                cproto.constructor = constructor;
            }
            // inherit (kindof inherit) define
            constructor.define = proto.define || superprime && superprime.define || function(key, descriptor) {
                define(this.prototype, key, descriptor);
                return this;
            };
            // copy implement (this should never change)
            constructor.implement = implement;
            // finally implement proto and return constructor
            return constructor.implement(proto);
        };

        prime.has = has;

        prime.each = each;

        prime.create = create;

        prime.define = define;

        module.exports = prime;
    },
    "6": function(require, module, exports, global) {
        /*
type
*/
                "use strict";

        var toString = Object.prototype.toString, types = /number|object|array|string|function|date|regexp|boolean/;

        var type = function(object) {
            if (object == null) return "null";
            var string = toString.call(object).slice(8, -1).toLowerCase();
            if (string === "number" && isNaN(object)) return "null";
            if (types.test(string)) return string;
            return "object";
        };

        module.exports = type;
    },
    "7": function(require, module, exports, global) {
        /*
MooFx
*/
                "use strict";

        // requires
        var color = require("1"), frame = require("2");

        var cancelFrame = frame.cancel, requestFrame = frame.request;

        var prime = require("5"), array = require("3"), string = require("8");

        var camelize = string.camelize, clean = string.clean, capitalize = string.capitalize;

        var map = array.map, forEach = array.forEach, indexOf = array.indexOf;

        var elements = require("a");

        var fx = require("b");

        // util
        var hyphenated = {};

        var hyphenate = function(self) {
            return hyphenated[self] || (hyphenated[self] = string.hyphenate(self));
        };

        var round = function(n) {
            return Math.round(n * 1e3) / 1e3;
        };

        // compute > node > property
        var compute = global.getComputedStyle ? function(node) {
            var cts = getComputedStyle(node);
            return function(property) {
                return cts ? cts.getPropertyValue(hyphenate(property)) : "";
            };
        } : /*(css3)?*/ function(node) {
            var cts = node.currentStyle;
            return function(property) {
                return cts ? cts[camelize(property)] : "";
            };
        };

        /*:null*/
        // pixel ratio retriever
        var test = document.createElement("div");

        var cssText = "border:none;margin:none;padding:none;visibility:hidden;position:absolute;height:0;";

        // returns the amount of pixels that takes to make one of the unit
        var pixelRatio = function(element, u) {
            var parent = element.parentNode, ratio = 1;
            if (parent) {
                test.style.cssText = cssText + ("width:100" + u + ";");
                parent.appendChild(test);
                ratio = test.offsetWidth / 100;
                parent.removeChild(test);
            }
            return ratio;
        };

        // mirror 4 values
        var mirror4 = function(values) {
            var length = values.length;
            if (length === 1) values.push(values[0], values[0], values[0]); else if (length === 2) values.push(values[0], values[1]); else if (length === 3) values.push(values[1]);
            return values;
        };

        // regular expressions strings
        var sLength = "([-.\\d]+)(%|cm|mm|in|px|pt|pc|em|ex|ch|rem|vw|vh|vm)", sLengthNum = sLength + "?", sBorderStyle = "none|hidden|dotted|dashed|solid|double|groove|ridge|inset|outset|inherit";

        // regular expressions
        var rgLength = RegExp(sLength, "g"), rLengthNum = RegExp(sLengthNum), rgLengthNum = RegExp(sLengthNum, "g"), rBorderStyle = RegExp(sBorderStyle);

        // normalize > css
        var parseString = function(value) {
            return value == null ? "" : value + "";
        };

        var parseOpacity = function(value, normalize) {
            if (value == null || value === "") return normalize ? "1" : "";
            return isFinite(value = +value) ? value < 0 ? "0" : value + "" : "1";
        };

        try {
            test.style.color = "rgba(0,0,0,0.5)";
        } catch (e) {}

        var rgba = /^rgba/.test(test.style.color);

        var parseColor = function(value, normalize) {
            var black = "rgba(0,0,0,1)", c;
            if (!value || !(c = color(value, true))) return normalize ? black : "";
            if (normalize) return "rgba(" + c + ")";
            var alpha = c[3];
            if (alpha === 0) return "transparent";
            return !rgba || alpha === 1 ? "rgb(" + c.slice(0, 3) + ")" : "rgba(" + c + ")";
        };

        var parseLength = function(value, normalize) {
            if (value == null || value === "") return normalize ? "0px" : "";
            var match = string.match(value, rLengthNum);
            return match ? match[1] + (match[2] || "px") : value;
        };

        var parseBorderStyle = function(value, normalize) {
            if (value == null || value === "") return normalize ? "none" : "";
            var match = value.match(rBorderStyle);
            return match ? value : normalize ? "none" : "";
        };

        var parseBorder = function(value, normalize) {
            var normalized = "0px none rgba(0,0,0,1)";
            if (value == null || value === "") return normalize ? normalized : "";
            if (value === 0 || value === "none") return normalize ? normalized : value + "";
            var c;
            value = value.replace(color.x, function(match) {
                c = match;
                return "";
            });
            var s = value.match(rBorderStyle), l = value.match(rgLengthNum);
            return clean([ parseLength(l ? l[0] : "", normalize), parseBorderStyle(s ? s[0] : "", normalize), parseColor(c, normalize) ].join(" "));
        };

        var parseShort4 = function(value, normalize) {
            if (value == null || value === "") return normalize ? "0px 0px 0px 0px" : "";
            return clean(mirror4(map(clean(value).split(" "), function(v) {
                return parseLength(v, normalize);
            })).join(" "));
        };

        var parseShadow = function(value, normalize, len) {
            var transparent = "rgba(0,0,0,0)", normalized = len === 3 ? transparent + " 0px 0px 0px" : transparent + " 0px 0px 0px 0px";
            if (value == null || value === "") return normalize ? normalized : "";
            if (value === "none") return normalize ? normalized : value;
            var colors = [], value = clean(value).replace(color.x, function(match) {
                colors.push(match);
                return "";
            });
            return map(value.split(","), function(shadow, i) {
                var c = parseColor(colors[i], normalize), inset = /inset/.test(shadow), lengths = shadow.match(rgLengthNum) || [ "0px" ];
                lengths = map(lengths, function(m) {
                    return parseLength(m, normalize);
                });
                while (lengths.length < len) lengths.push("0px");
                var ret = inset ? [ "inset", c ] : [ c ];
                return ret.concat(lengths).join(" ");
            }).join(", ");
        };

        var parse = function(value, normalize) {
            if (value == null || value === "") return "";
            // cant normalize "" || null
            return value.replace(color.x, function(match) {
                return parseColor(match, normalize);
            }).replace(rgLength, function(match) {
                return parseLength(match, normalize);
            });
        };

        // get && set
        var getters = {}, setters = {}, parsers = {}, aliases = {};

        var getter = function(key) {
            return getters[key] || (getters[key] = function() {
                var alias = aliases[key] || key, parser = parsers[key] || parse;
                return function() {
                    return parser(compute(this)(alias), true);
                };
            }());
        };

        var setter = function(key) {
            return setters[key] || (setters[key] = function() {
                var alias = aliases[key] || key, parser = parsers[key] || parse;
                return function(value) {
                    this.style[alias] = parser(value, false);
                };
            }());
        };

        // parsers
        var trbl = [ "Top", "Right", "Bottom", "Left" ], tlbl = [ "TopLeft", "TopRight", "BottomRight", "BottomLeft" ];

        forEach(trbl, function(d) {
            var bd = "border" + d;
            forEach([ "margin" + d, "padding" + d, bd + "Width", d.toLowerCase() ], function(n) {
                parsers[n] = parseLength;
            });
            parsers[bd + "Color"] = parseColor;
            parsers[bd + "Style"] = parseBorderStyle;
            // borderDIR
            parsers[bd] = parseBorder;
            getters[bd] = function() {
                return [ getter(bd + "Width").call(this), getter(bd + "Style").call(this), getter(bd + "Color").call(this) ].join(" ");
            };
        });

        forEach(tlbl, function(d) {
            parsers["border" + d + "Radius"] = parseLength;
        });

        parsers.color = parsers.backgroundColor = parseColor;

        parsers.width = parsers.height = parsers.minWidth = parsers.minHeight = parsers.maxWidth = parsers.maxHeight = parsers.fontSize = parsers.backgroundSize = parseLength;

        // margin + padding
        forEach([ "margin", "padding" ], function(name) {
            parsers[name] = parseShort4;
            getters[name] = function() {
                return map(trbl, function(d) {
                    return getter(name + d).call(this);
                }, this).join(" ");
            };
        });

        // borders
        // borderDIRWidth, borderDIRStyle, borderDIRColor
        parsers.borderWidth = parseShort4;

        parsers.borderStyle = function(value, normalize) {
            if (value == null || value === "") return normalize ? mirror4([ "none" ]).join(" ") : "";
            value = clean(value).split(" ");
            return clean(mirror4(map(value, function(v) {
                parseBorderStyle(v, normalize);
            })).join(" "));
        };

        parsers.borderColor = function(value, normalize) {
            if (!value || !(value = string.match(value, color.x))) return normalize ? mirror4([ "rgba(0,0,0,1)" ]).join(" ") : "";
            return clean(mirror4(map(value, function(v) {
                return parseColor(v, normalize);
            })).join(" "));
        };

        forEach([ "Width", "Style", "Color" ], function(name) {
            getters["border" + name] = function() {
                return map(trbl, function(d) {
                    return getter("border" + d + name).call(this);
                }, this).join(" ");
            };
        });

        // borderRadius
        parsers.borderRadius = parseShort4;

        getters.borderRadius = function() {
            return map(tlbl, function(d) {
                return getter("border" + d + "Radius").call(this);
            }, this).join(" ");
        };

        // border
        parsers.border = parseBorder;

        getters.border = function() {
            var pvalue;
            for (var i = 0; i < trbl.length; i++) {
                var value = getter("border" + trbl[i]).call(this);
                if (pvalue && value !== pvalue) return null;
                pvalue = value;
            }
            return pvalue;
        };

        // zIndex
        parsers.zIndex = parseString;

        // opacity
        parsers.opacity = parseOpacity;

        /*(css3)?*/
        var filterName = test.style.MsFilter != null && "MsFilter" || test.style.filter != null && "filter";

        if (filterName && test.style.opacity == null) {
            var matchOp = /alpha\(opacity=([\d.]+)\)/i;
            setters.opacity = function(value) {
                value = (value = parseOpacity(value)) === "1" ? "" : "alpha(opacity=" + Math.round(value * 100) + ")";
                var filter = compute(this)(filterName);
                return this.style[filterName] = matchOp.test(filter) ? filter.replace(matchOp, value) : filter + " " + value;
            };
            getters.opacity = function() {
                var match = compute(this)(filterName).match(matchOp);
                return (!match ? 1 : match[1] / 100) + "";
            };
        }

        /*:*/
        var parseBoxShadow = parsers.boxShadow = function(value, normalize) {
            return parseShadow(value, normalize, 4);
        };

        var parseTextShadow = parsers.textShadow = function(value, normalize) {
            return parseShadow(value, normalize, 3);
        };

        // Aliases
        forEach([ "Webkit", "Moz", "ms", "O", null ], function(prefix) {
            forEach([ "transition", "transform", "transformOrigin", "transformStyle", "perspective", "perspectiveOrigin", "backfaceVisibility" ], function(style) {
                var cc = prefix ? prefix + capitalize(style) : style;
                if (prefix === "ms") hyphenated[cc] = "-ms-" + hyphenate(style);
                if (test.style[cc] != null) aliases[style] = cc;
            });
        });

        var transitionName = aliases.transition, transformName = aliases.transform;

        // manually disable css3 transitions in Opera, because they do not work properly.
        if (transitionName === "OTransition") transitionName = null;

        // this takes care of matrix decomposition on browsers that support only 2d transforms but no CSS3 transitions.
        // basically, IE9 (and Opera as well, since we disabled CSS3 transitions manually)
        var parseTransform2d, Transform2d;

        /*(css3)?*/
        if (!transitionName && transformName) (function() {
            var unmatrix = require("d");
            var v = "\\s*([-\\d\\w.]+)\\s*";
            var rMatrix = RegExp("matrix\\(" + [ v, v, v, v, v, v ] + "\\)");
            var decomposeMatrix = function(matrix) {
                var d = unmatrix.apply(null, matrix.match(rMatrix).slice(1)) || [ [ 0, 0 ], 0, 0, [ 0, 0 ] ];
                return [ "translate(" + map(d[0], function(v) {
                    return round(v) + "px";
                }) + ")", "rotate(" + round(d[1] * 180 / Math.PI) + "deg)", "skewX(" + round(d[2] * 180 / Math.PI) + "deg)", "scale(" + map(d[3], round) + ")" ].join(" ");
            };
            var def0px = function(value) {
                return value || "0px";
            }, def1 = function(value) {
                return value || "1";
            }, def0deg = function(value) {
                return value || "0deg";
            };
            var transforms = {
                translate: function(value) {
                    if (!value) value = "0px,0px";
                    var values = value.split(",");
                    if (!values[1]) values[1] = "0px";
                    return map(values, clean) + "";
                },
                translateX: def0px,
                translateY: def0px,
                scale: function(value) {
                    if (!value) value = "1,1";
                    var values = value.split(",");
                    if (!values[1]) values[1] = values[0];
                    return map(values, clean) + "";
                },
                scaleX: def1,
                scaleY: def1,
                rotate: def0deg,
                skewX: def0deg,
                skewY: def0deg
            };
            Transform2d = prime({
                constructor: function(transform) {
                    var names = this.names = [];
                    var values = this.values = [];
                    transform.replace(/(\w+)\(([-.\d\s\w,]+)\)/g, function(match, name, value) {
                        names.push(name);
                        values.push(value);
                    });
                },
                identity: function() {
                    var functions = [];
                    forEach(this.names, function(name) {
                        var fn = transforms[name];
                        if (fn) functions.push(name + "(" + fn() + ")");
                    });
                    return functions.join(" ");
                },
                sameType: function(transformObject) {
                    return this.names.toString() === transformObject.names.toString();
                },
                // this is, basically, cheating.
                // retrieving the matrix value from the dom, rather than calculating it
                decompose: function() {
                    var transform = this.toString();
                    test.style.cssText = cssText + hyphenate(transformName) + ":" + transform + ";";
                    document.body.appendChild(test);
                    var m = compute(test)(transformName);
                    if (!m || m === "none") m = "matrix(1, 0, 0, 1, 0, 0)";
                    document.body.removeChild(test);
                    return decomposeMatrix(m);
                }
            });
            Transform2d.prototype.toString = function(clean) {
                var values = this.values, functions = [];
                forEach(this.names, function(name, i) {
                    var fn = transforms[name];
                    if (!fn) return;
                    var value = fn(values[i]);
                    if (!clean || value !== fn()) functions.push(name + "(" + value + ")");
                });
                return functions.length ? functions.join(" ") : "none";
            };
            Transform2d.union = function(from, to) {
                if (from === to) return;
                // nothing to do
                var fromMap, toMap;
                if (from === "none") {
                    toMap = new Transform2d(to);
                    to = toMap.toString();
                    from = toMap.identity();
                    fromMap = new Transform2d(from);
                } else if (to === "none") {
                    fromMap = new Transform2d(from);
                    from = fromMap.toString();
                    to = fromMap.identity();
                    toMap = new Transform2d(to);
                } else {
                    fromMap = new Transform2d(from);
                    from = fromMap.toString();
                    toMap = new Transform2d(to);
                    to = toMap.toString();
                }
                if (from === to) return;
                // nothing to do
                if (!fromMap.sameType(toMap)) {
                    from = fromMap.decompose();
                    to = toMap.decompose();
                }
                if (from === to) return;
                // nothing to do
                return [ from, to ];
            };
            // this parser makes sure it never gets "matrix"
            parseTransform2d = parsers.transform = function(transform) {
                if (!transform || transform === "none") return "none";
                return new Transform2d(rMatrix.test(transform) ? decomposeMatrix(transform) : transform).toString(true);
            };
            // this getter makes sure we read from the dom only the first time
            // this way we save the actual transform and not "matrix"
            // setting matrix() will use parseTransform2d as well, thus setting the decomposed matrix
            getters.transform = function() {
                var s = this.style;
                return s[transformName] || (s[transformName] = parseTransform2d(compute(this)(transformName)));
            };
        })();

        /*:*/
        // tries to match from and to values
        var prepare = function(node, property, to) {
            var parser = parsers[property] || parse, from = getter(property).call(node), // "normalized" by the getter
            to = parser(to, true);
            // normalize parsed property
            if (from === to) return;
            if (parser === parseLength || parser === parseBorder || parser === parseShort4) {
                var toAll = to.match(rgLength), i = 0;
                // this should always match something
                if (toAll) from = from.replace(rgLength, function(fromFull, fromValue, fromUnit) {
                    var toFull = toAll[i++], toMatched = toFull.match(rLengthNum), toUnit = toMatched[2];
                    if (fromUnit !== toUnit) {
                        var fromPixels = fromUnit === "px" ? fromValue : pixelRatio(node, fromUnit) * fromValue;
                        return round(fromPixels / pixelRatio(node, toUnit)) + toUnit;
                    }
                    return fromFull;
                });
                if (i > 0) setter(property).call(node, from);
            } else if (parser === parseTransform2d) {
                // IE9/Opera
                return Transform2d.union(from, to);
            }
            /*:*/
            return from !== to ? [ from, to ] : null;
        };

        // BrowserAnimation
        var BrowserAnimation = prime({
            inherits: fx,
            constructor: function BrowserAnimation(node, property) {
                var _getter = getter(property), _setter = setter(property);
                this.get = function() {
                    return _getter.call(node);
                };
                this.set = function(value) {
                    return _setter.call(node, value);
                };
                BrowserAnimation.parent.constructor.call(this, this.set);
                this.node = node;
                this.property = property;
            }
        });

        var JSAnimation;

        /*(css3)?*/
        JSAnimation = prime({
            inherits: BrowserAnimation,
            constructor: function JSAnimation() {
                return JSAnimation.parent.constructor.apply(this, arguments);
            },
            start: function(to) {
                this.stop();
                if (this.duration === 0) {
                    this.cancel(to);
                    return this;
                }
                var fromTo = prepare(this.node, this.property, to);
                if (!fromTo) {
                    this.cancel(to);
                    return this;
                }
                JSAnimation.parent.start.apply(this, fromTo);
                if (!this.cancelStep) return this;
                // the animation would have started but we need additional checks
                var parser = parsers[this.property] || parse;
                // complex interpolations JSAnimation can't handle
                // even CSS3 animation gracefully fail with some of those edge cases
                // other "simple" properties, such as `border` can have different templates
                // because of string properties like "solid" and "dashed"
                if ((parser === parseBoxShadow || parser === parseTextShadow || parser === parse) && this.templateFrom !== this.templateTo) {
                    this.cancelStep();
                    delete this.cancelStep;
                    this.cancel(to);
                }
                return this;
            },
            parseEquation: function(equation) {
                if (typeof equation === "string") return JSAnimation.parent.parseEquation.call(this, equation);
            }
        });

        /*:*/
        // CSSAnimation
        var remove3 = function(value, a, b, c) {
            var index = indexOf(a, value);
            if (index !== -1) {
                a.splice(index, 1);
                b.splice(index, 1);
                c.splice(index, 1);
            }
        };

        var CSSAnimation = prime({
            inherits: BrowserAnimation,
            constructor: function CSSAnimation(node, property) {
                CSSAnimation.parent.constructor.call(this, node, property);
                this.hproperty = hyphenate(aliases[property] || property);
                var self = this;
                this.bSetTransitionCSS = function(time) {
                    self.setTransitionCSS(time);
                };
                this.bSetStyleCSS = function(time) {
                    self.setStyleCSS(time);
                };
                this.bComplete = function() {
                    self.complete();
                };
            },
            start: function(to) {
                this.stop();
                if (this.duration === 0) {
                    this.cancel(to);
                    return this;
                }
                var fromTo = prepare(this.node, this.property, to);
                if (!fromTo) {
                    this.cancel(to);
                    return this;
                }
                this.to = fromTo[1];
                // setting transition styles immediately will make good browsers behave weirdly
                // because DOM changes are always deferred, so we requestFrame
                this.cancelSetTransitionCSS = requestFrame(this.bSetTransitionCSS);
                return this;
            },
            setTransitionCSS: function(time) {
                delete this.cancelSetTransitionCSS;
                this.resetCSS(true);
                // firefox flickers if we set css for transition as well as styles at the same time
                // so, other than deferring transition styles we defer actual styles as well on a requestFrame
                this.cancelSetStyleCSS = requestFrame(this.bSetStyleCSS);
            },
            setStyleCSS: function(time) {
                delete this.cancelSetStyleCSS;
                var duration = this.duration;
                // we use setTimeout instead of transitionEnd because some browsers (looking at you foxy)
                // incorrectly set event.propertyName, so we cannot check which animation we are canceling
                this.cancelComplete = setTimeout(this.bComplete, duration);
                this.endTime = time + duration;
                this.set(this.to);
            },
            complete: function() {
                delete this.cancelComplete;
                this.resetCSS();
                this.callback(this.endTime);
            },
            stop: function(hard) {
                if (this.cancelExit) {
                    this.cancelExit();
                    delete this.cancelExit;
                } else if (this.cancelSetTransitionCSS) {
                    // if cancelSetTransitionCSS is set, means nothing is set yet
                    this.cancelSetTransitionCSS();
                    //so we cancel and we're good
                    delete this.cancelSetTransitionCSS;
                } else if (this.cancelSetStyleCSS) {
                    // if cancelSetStyleCSS is set, means transition css has been set, but no actual styles.
                    this.cancelSetStyleCSS();
                    delete this.cancelSetStyleCSS;
                    // if its a hard stop (and not another start on top of the current animation)
                    // we need to reset the transition CSS
                    if (hard) this.resetCSS();
                } else if (this.cancelComplete) {
                    // if cancelComplete is set, means style and transition css have been set, not yet completed.
                    clearTimeout(this.cancelComplete);
                    delete this.cancelComplete;
                    // if its a hard stop (and not another start on top of the current animation)
                    // we need to reset the transition CSS set the current animation styles
                    if (hard) {
                        this.resetCSS();
                        this.set(this.get());
                    }
                }
                return this;
            },
            resetCSS: function(inclusive) {
                var rules = compute(this.node), properties = (rules(transitionName + "Property").replace(/\s+/g, "") || "all").split(","), durations = (rules(transitionName + "Duration").replace(/\s+/g, "") || "0s").split(","), equations = (rules(transitionName + "TimingFunction").replace(/\s+/g, "") || "ease").match(/cubic-bezier\([\d-.,]+\)|([a-z-]+)/g);
                remove3("all", properties, durations, equations);
                remove3(this.hproperty, properties, durations, equations);
                if (inclusive) {
                    properties.push(this.hproperty);
                    durations.push(this.duration + "ms");
                    equations.push("cubic-bezier(" + this.equation + ")");
                }
                var nodeStyle = this.node.style;
                nodeStyle[transitionName + "Property"] = properties;
                nodeStyle[transitionName + "Duration"] = durations;
                nodeStyle[transitionName + "TimingFunction"] = equations;
            },
            parseEquation: function(equation) {
                if (typeof equation === "string") return CSSAnimation.parent.parseEquation.call(this, equation, true);
            }
        });

        // elements methods
        var BaseAnimation = transitionName ? CSSAnimation : JSAnimation;

        var moofx = function(x, y) {
            return typeof x === "function" ? fx(x) : elements(x, y);
        };

        elements.implement({
            // {properties}, options or
            // property, value options
            animate: function(A, B, C) {
                var styles = A, options = B;
                if (typeof A === "string") {
                    styles = {};
                    styles[A] = B;
                    options = C;
                }
                if (options == null) options = {};
                var type = typeof options;
                options = type === "function" ? {
                    callback: options
                } : type === "string" || type === "number" ? {
                    duration: options
                } : options;
                var callback = options.callback || function() {}, completed = 0, length = 0;
                options.callback = function(t) {
                    if (++completed === length) callback(t);
                };
                for (var property in styles) {
                    var value = styles[property], property = camelize(property);
                    this.forEach(function(node) {
                        length++;
                        var self = elements(node), anims = self._animations || (self._animations = {});
                        var anim = anims[property] || (anims[property] = new BaseAnimation(node, property));
                        anim.setOptions(options).start(value);
                    });
                }
                return this;
            },
            // {properties} or
            // property, value
            style: function(A, B) {
                var styles = A;
                if (typeof A === "string") {
                    styles = {};
                    styles[A] = B;
                }
                for (var property in styles) {
                    var value = styles[property], set = setter(property = camelize(property));
                    this.forEach(function(node) {
                        var self = elements(node), anims = self._animations, anim;
                        if (anims && (anim = anims[property])) anim.stop(true);
                        set.call(node, value);
                    });
                }
                return this;
            },
            compute: function(property) {
                property = camelize(property);
                var node = this[0];
                // return default matrix for transform, instead of parsed (for consistency)
                if (property === "transform" && parseTransform2d) return compute(node)(transformName);
                var value = getter(property).call(node);
                // unit conversion to `px`
                return value != null ? value.replace(rgLength, function(match, value, unit) {
                    return unit === "px" ? match : pixelRatio(node, unit) * value + "px";
                }) : "";
            }
        });

        moofx.parse = function(property, value, normalize) {
            return (parsers[camelize(property)] || parse)(value, normalize);
        };

        module.exports = moofx;
    },
    "8": function(require, module, exports, global) {
        /*
string methods
 - string shell
*/
                "use strict";

        var string = require("9");

        string.implement({
            clean: function() {
                return string.trim((this + "").replace(/\s+/g, " "));
            },
            camelize: function() {
                return (this + "").replace(/-\D/g, function(match) {
                    return match.charAt(1).toUpperCase();
                });
            },
            hyphenate: function() {
                return (this + "").replace(/[A-Z]/g, function(match) {
                    return "-" + match.toLowerCase();
                });
            },
            capitalize: function() {
                return (this + "").replace(/\b[a-z]/g, function(match) {
                    return match.toUpperCase();
                });
            },
            escape: function() {
                return (this + "").replace(/([-.*+?^${}()|[\]\/\\])/g, "\\$1");
            },
            number: function() {
                return parseFloat(this);
            }
        });

        if (typeof JSON !== "undefined") string.implement({
            decode: function() {
                return JSON.parse(this);
            }
        });

        module.exports = string;
    },
    "9": function(require, module, exports, global) {
        /*
string
 - string es5 shell
*/
                "use strict";

        var string = require("4")["string"];

        var names = ("charAt,charCodeAt,concat,contains,endsWith,indexOf,lastIndexOf,localeCompare,match,replace,search,slice,split" + ",startsWith,substr,substring,toLocaleLowerCase,toLocaleUpperCase,toLowerCase,toString,toUpperCase,trim,valueOf").split(",");

        for (var methods = {}, i = 0, name, method; name = names[i++]; ) if (method = String.prototype[name]) methods[name] = method;

        if (!methods.trim) methods.trim = function() {
            return (this + "").replace(/^\s+|\s+$/g, "");
        };

        module.exports = string.implement(methods);
    },
    a: function(require, module, exports, global) {
        /*
elements
*/
                "use strict";

        var prime = require("5"), array = require("3").prototype;

        // uniqueID
        var uniqueIndex = 0;

        var uniqueID = function(n) {
            return n === global ? "global" : n.uniqueNumber || (n.uniqueNumber = "n:" + (uniqueIndex++).toString(36));
        };

        var instances = {};

        // elements prime
        var $ = prime({
            constructor: function $(n, context) {
                if (n == null) return this && this.constructor === $ ? new elements() : null;
                var self = n;
                if (n.constructor !== elements) {
                    self = new elements();
                    var uid;
                    if (typeof n === "string") {
                        if (!self.search) return null;
                        self[self.length++] = context || document;
                        return self.search(n);
                    }
                    if (n.nodeType || n === global) {
                        self[self.length++] = n;
                    } else if (n.length) {
                        // this could be an array, or any object with a length attribute,
                        // including another instance of elements from another interface.
                        var uniques = {};
                        for (var i = 0, l = n.length; i < l; i++) {
                            // perform elements flattening
                            var nodes = $(n[i], context);
                            if (nodes && nodes.length) for (var j = 0, k = nodes.length; j < k; j++) {
                                var node = nodes[j];
                                uid = uniqueID(node);
                                if (!uniques[uid]) {
                                    self[self.length++] = node;
                                    uniques[uid] = true;
                                }
                            }
                        }
                    }
                }
                if (!self.length) return null;
                // when length is 1 always use the same elements instance
                if (self.length === 1) {
                    uid = uniqueID(self[0]);
                    return instances[uid] || (instances[uid] = self);
                }
                return self;
            }
        });

        var elements = prime({
            inherits: $,
            constructor: function elements() {
                this.length = 0;
            },
            unlink: function() {
                return this.map(function(node, i) {
                    delete instances[uniqueID(node)];
                    return node;
                });
            },
            // straight es5 prototypes (or emulated methods)
            forEach: array.forEach,
            map: array.map,
            filter: array.filter,
            every: array.every,
            some: array.some
        });

        module.exports = $;
    },
    b: function(require, module, exports, global) {
        /*
fx
*/
                "use strict";

        var prime = require("5"), requestFrame = require("2").request, bezier = require("c");

        var map = require("3").map;

        var sDuration = "([\\d.]+)(s|ms)?", sCubicBezier = "cubic-bezier\\(([-.\\d]+),([-.\\d]+),([-.\\d]+),([-.\\d]+)\\)";

        var rDuration = RegExp(sDuration), rCubicBezier = RegExp(sCubicBezier), rgCubicBezier = RegExp(sCubicBezier, "g");

        // equations collection
        var equations = {
            "default": "cubic-bezier(0.25, 0.1, 0.25, 1.0)",
            linear: "cubic-bezier(0, 0, 1, 1)",
            "ease-in": "cubic-bezier(0.42, 0, 1.0, 1.0)",
            "ease-out": "cubic-bezier(0, 0, 0.58, 1.0)",
            "ease-in-out": "cubic-bezier(0.42, 0, 0.58, 1.0)"
        };

        equations.ease = equations["default"];

        var compute = function(from, to, delta) {
            return (to - from) * delta + from;
        };

        var divide = function(string) {
            var numbers = [];
            var template = (string + "").replace(/[-.\d]+/g, function(number) {
                numbers.push(+number);
                return "@";
            });
            return [ numbers, template ];
        };

        var Fx = prime({
            constructor: function Fx(render, options) {
                // set options
                this.setOptions(options);
                // renderer
                this.render = render || function() {};
                // bound functions
                var self = this;
                this.bStep = function(t) {
                    return self.step(t);
                };
                this.bExit = function(time) {
                    self.exit(time);
                };
            },
            setOptions: function(options) {
                if (options == null) options = {};
                if (!(this.duration = this.parseDuration(options.duration || "500ms"))) throw new Error("invalid duration");
                if (!(this.equation = this.parseEquation(options.equation || "default"))) throw new Error("invalid equation");
                this.callback = options.callback || function() {};
                return this;
            },
            parseDuration: function(duration) {
                if (duration = (duration + "").match(rDuration)) {
                    var time = +duration[1], unit = duration[2] || "ms";
                    if (unit === "s") return time * 1e3;
                    if (unit === "ms") return time;
                }
            },
            parseEquation: function(equation, array) {
                var type = typeof equation;
                if (type === "function") {
                    // function
                    return equation;
                } else if (type === "string") {
                    // cubic-bezier string
                    equation = equations[equation] || equation;
                    var match = equation.replace(/\s+/g, "").match(rCubicBezier);
                    if (match) {
                        equation = map(match.slice(1), function(v) {
                            return +v;
                        });
                        if (array) return equation;
                        if (equation.toString() === "0,0,1,1") return function(x) {
                            return x;
                        };
                        type = "object";
                    }
                }
                if (type === "object") {
                    // array
                    return bezier(equation[0], equation[1], equation[2], equation[3], 1e3 / 60 / this.duration / 4);
                }
            },
            cancel: function(to) {
                this.to = to;
                this.cancelExit = requestFrame(this.bExit);
            },
            exit: function(time) {
                this.render(this.to);
                delete this.cancelExit;
                this.callback(time);
            },
            start: function(from, to) {
                this.stop();
                if (this.duration === 0) {
                    this.cancel(to);
                    return this;
                }
                this.isArray = false;
                this.isNumber = false;
                var fromType = typeof from, toType = typeof to;
                if (fromType === "object" && toType === "object") {
                    this.isArray = true;
                } else if (fromType === "number" && toType === "number") {
                    this.isNumber = true;
                }
                var from_ = divide(from), to_ = divide(to);
                this.from = from_[0];
                this.to = to_[0];
                this.templateFrom = from_[1];
                this.templateTo = to_[1];
                if (this.from.length !== this.to.length || this.from.toString() === this.to.toString()) {
                    this.cancel(to);
                    return this;
                }
                delete this.time;
                this.length = this.from.length;
                this.cancelStep = requestFrame(this.bStep);
                return this;
            },
            stop: function() {
                if (this.cancelExit) {
                    this.cancelExit();
                    delete this.cancelExit;
                } else if (this.cancelStep) {
                    this.cancelStep();
                    delete this.cancelStep;
                }
                return this;
            },
            step: function(now) {
                this.time || (this.time = now);
                var factor = (now - this.time) / this.duration;
                if (factor > 1) factor = 1;
                var delta = this.equation(factor), from = this.from, to = this.to, tpl = this.templateTo;
                for (var i = 0, l = this.length; i < l; i++) {
                    var f = from[i], t = to[i];
                    tpl = tpl.replace("@", t !== f ? compute(f, t, delta) : t);
                }
                this.render(this.isArray ? tpl.split(",") : this.isNumber ? +tpl : tpl, factor);
                if (factor !== 1) {
                    this.cancelStep = requestFrame(this.bStep);
                } else {
                    delete this.cancelStep;
                    this.callback(now);
                }
            }
        });

        var fx = function(render) {
            var ffx = new Fx(render);
            return {
                start: function(from, to, options) {
                    var type = typeof options;
                    ffx.setOptions(type === "function" ? {
                        callback: options
                    } : type === "string" || type === "number" ? {
                        duration: options
                    } : options).start(from, to);
                    return this;
                },
                stop: function() {
                    ffx.stop();
                    return this;
                }
            };
        };

        fx.prototype = Fx.prototype;

        module.exports = fx;
    },
    c: function(require, module, exports, global) {
                module.exports = function(x1, y1, x2, y2, epsilon) {
            var curveX = function(t) {
                var v = 1 - t;
                return 3 * v * v * t * x1 + 3 * v * t * t * x2 + t * t * t;
            };
            var curveY = function(t) {
                var v = 1 - t;
                return 3 * v * v * t * y1 + 3 * v * t * t * y2 + t * t * t;
            };
            var derivativeCurveX = function(t) {
                var v = 1 - t;
                return 3 * (2 * (t - 1) * t + v * v) * x1 + 3 * (-t * t * t + 2 * v * t) * x2;
            };
            return function(t) {
                var x = t, t0, t1, t2, x2, d2, i;
                // First try a few iterations of Newton's method -- normally very fast.
                for (t2 = x, i = 0; i < 8; i++) {
                    x2 = curveX(t2) - x;
                    if (Math.abs(x2) < epsilon) return curveY(t2);
                    d2 = derivativeCurveX(t2);
                    if (Math.abs(d2) < 1e-6) break;
                    t2 = t2 - x2 / d2;
                }
                t0 = 0, t1 = 1, t2 = x;
                if (t2 < t0) return curveY(t0);
                if (t2 > t1) return curveY(t1);
                // Fallback to the bisection method for reliability.
                while (t0 < t1) {
                    x2 = curveX(t2);
                    if (Math.abs(x2 - x) < epsilon) return curveY(t2);
                    if (x > x2) t0 = t2; else t1 = t2;
                    t2 = (t1 - t0) * .5 + t0;
                }
                // Failure
                return curveY(t2);
            };
        };
    },
    d: function(require, module, exports, global) {
        /*
Unmatrix 2d
 - a crude implementation of the slightly bugged pseudo code in http://www.w3.org/TR/css3-2d-transforms/#matrix-decomposition
*/
                "use strict";

        // returns the length of the passed vector
        var length = function(a) {
            return Math.sqrt(a[0] * a[0] + a[1] * a[1]);
        };

        // normalizes the length of the passed point to 1
        var normalize = function(a) {
            var l = length(a);
            return l ? [ a[0] / l, a[1] / l ] : [ 0, 0 ];
        };

        // returns the dot product of the passed points
        var dot = function(a, b) {
            return a[0] * b[0] + a[1] * b[1];
        };

        // returns the principal value of the arc tangent of
        // y/x, using the signs of both arguments to determine
        // the quadrant of the return value
        var atan2 = Math.atan2;

        var combine = function(a, b, ascl, bscl) {
            return [ ascl * a[0] + bscl * b[0], ascl * a[1] + bscl * b[1] ];
        };

        module.exports = function(a, b, c, d, tx, ty) {
            // Make sure the matrix is invertible
            if (a * d - b * c === 0) return false;
            // Take care of translation
            var translate = [ tx, ty ];
            // Put the components into a 2x2 matrix
            var m = [ [ a, b ], [ c, d ] ];
            // Compute X scale factor and normalize first row.
            var scale = [ length(m[0]) ];
            m[0] = normalize(m[0]);
            // Compute shear factor and make 2nd row orthogonal to 1st.
            var skew = dot(m[0], m[1]);
            m[1] = combine(m[1], m[0], 1, -skew);
            // Now, compute Y scale and normalize 2nd row.
            scale[1] = length(m[1]);
            // m[1] = normalize(m[1]) //
            skew /= scale[1];
            // Now, get the rotation out
            var rotate = atan2(m[0][1], m[0][0]);
            return [ translate, rotate, skew, scale ];
        };
    }
});
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};


	var ua = navigator.userAgent.toLowerCase(),
		platform = navigator.platform.toLowerCase(),
		UA = ua.match(/(opera|ie|trident|firefox|chrome|version)[\s\/:]([\w\d\.]+)?.*?(safari|version[\s\/:]([\w\d\.]+)|rv:(\d.?)|$)/) || [null, 'unknown', 0],
		mode = (UA[1] == 'ie' || UA[1] == 'trident') && document.documentMode;

	var Browser = this.Browser = {

		extend: Function.prototype.extend,

		name: (UA[1] == 'version') ? UA[3] : (UA[1] == 'trident' ? 'ie' : UA[1]),

		version: mode || parseFloat((UA[1] == 'opera' && UA[4]) ? UA[4] : ((UA[1] == 'trident' && UA[5]) ? UA[5] : UA[2])),

		Platform: {
			name: ua.match(/ip(?:ad|od|hone)/) ? 'ios' : (ua.match(/(?:webos|android)/) || platform.match(/mac|win|linux/) || ['other'])[0]
		},

		Features: {
			xpath: !!(document.evaluate),
			air: !!(window.runtime),
			query: !!(document.querySelector),
			json: !!(window.JSON)
		},

		Plugins: {}

	};

	Browser[Browser.name] = true;
	Browser[Browser.name + parseInt(Browser.version, 10)] = true;
	Browser.Platform[Browser.Platform.name] = true;

	// Request

	Browser.Request = (function(){

		var XMLHTTP = function(){
			return new XMLHttpRequest();
		};

		var MSXML2 = function(){
			return new ActiveXObject('MSXML2.XMLHTTP');
		};

		var MSXML = function(){
			return new ActiveXObject('Microsoft.XMLHTTP');
		};

		return Function.attempt(function(){
			XMLHTTP();
			return XMLHTTP;
		}, function(){
			MSXML2();
			return MSXML2;
		}, function(){
			MSXML();
			return MSXML;
		});

	})();

	Browser.Features.xhr = !!(Browser.Request);




	var OnInputEvent = (Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input';

	this.RokSprocket.dynamicfields = {
		list: {},
		add: function(id, fieldname){
			RokSprocket.dynamicfields.list[id] = fieldname;
		},
		attach: function(){
			Object.each(RokSprocket.dynamicfields.list, function(fieldname, id){
				RokSprocket.dynamicfields.addChange(id, fieldname);
			});

			//$$('.panel-right > ul > li:not([style])').setStyle('display', 'block');
		},
		attachLastFire: function(){
			document.getElements('.dynamicfield-last-fire').addEvent('change', function(){
				var params = RokSprocket.params;
				document.id(params + '_layout').fireEvent('change');
			});
		},
		addChange: function(id, fieldname){
			document.id(id).addEvent('change', function(){
				RokSprocket.dynamicfields.fireChange.apply(document.id(id), [id, fieldname]);
			}).fireEvent('change');
		},
		fireChange: function(id, fieldname){
			var element = this;

			if (typeOf(element) != 'element') element = document.id(id);
			if (!element.options.length || !element.options[element.selectedIndex]) return;
			var rel = document.id(element.options[element.selectedIndex]).get('rel'),
				fields = document.getElements('.'+fieldname+':not([data-dynamic=false])'),
				values = document.getElements('.' + rel + ':not([data-dynamic=false])');

			var fieldsDropdowns = fields.filter(function(value){
				return value.get('tag') == 'option';
			});
			var dropdowns = values.filter(function(value){
				return value.get('tag') == 'option';
			});

			var fieldsParent = fields.getParent('li'),
				valuesParent = values.getParent('li');

			fieldsParent.each(function(field){ if (field) field.setStyle('display','none'); });
			valuesParent.each(function(field){ if (field) field.setStyle('display','block'); });

			//fields.getParent('li').setStyle('display','none');
			//values.getParent('li').setStyle('display','block');

			// special case for dropdowns
			[dropdowns, fieldsDropdowns].flatten().each(function(option){
				var select = option.getParent('select'),
					activeOption = select.getElement('option[value='+select.get('value')+']:not(.'+rel+')');

				if (select.get('value') == option.get('value') && activeOption && !activeOption.hasClass(rel)){
					var firstValue = select.getFirst().get('value'),
						sprocketDropdown = select.getParent('.sprocket-dropdown [data-value='+firstValue+']');
					if (sprocketDropdown) sprocketDropdown.fireEvent('click', {target: sprocketDropdown});
					else select.set('value', firstValue).fireEvent('change');
				}
			});


			// let's fire all the subinstances of dynamicfields to clear possible conflicts
			document.getElements('.dynamicfield-subinstance.'+rel+':not(#'+id+')').fireEvent('change');
		},
		refreshProvider: function(){
			var provider = RokSprocket.params + '_provider';
			//RokSprocket.dynamicfields.fireChange(provider, RokSprocket.dynamicfields.list[provider]);
		}
	};

	this.RokSprocket.displayLimit = {
		char: String.fromCharCode(8734),
		field: null,
		attach: function(){
			var params = RokSprocket.params,
				field = document.id(params + '_display_limit');

			RokSprocket.displayLimit.field = field;

			if (field){
				field.addEvent('keyup', RokSprocket.displayLimit.attachInputEvent);
				field.addEvent('blur', RokSprocket.displayLimit.attachBlurEvent);
			}
		},
		attachInputEvent: function(){
			var value = RokSprocket.displayLimit.cleanValue(this.value);
			if (this.get('value') == '0') this.set('value', RokSprocket.displayLimit.char);
			RokSprocket.articles.updateLimit(value);
		},
		attachBlurEvent: function(){
			if (!this.value.length) this.set('value', RokSprocket.displayLimit.char).fireEvent('change');
			else this.set('value', RokSprocket.displayLimit.getValue() || RokSprocket.displayLimit.char);
		},
		cleanValue: function(value){
			value = value.match(/\d+/g);
			if (!value) value = ['0'];

			value = value.join('').replace(RokSprocket.displayLimit.char, 0).toInt() || 0;

			return value;
		},
		getValue: function(){
			return RokSprocket.displayLimit.cleanValue(RokSprocket.displayLimit.field.get('value'));
		}
	};

	this.RokSprocket.previewLength = {
		char: String.fromCharCode(8734),
		field: null,
		attach: function(){
			var params = RokSprocket.params,
				field = document.id(params + '_' + RokSprocket.content.getLayout() + '_previews_length');

			RokSprocket.previewLength.field = field;

			if (field){
				field.addEvent('keyup', RokSprocket.previewLength.attachInputEvent);
				field.addEvent('blur', RokSprocket.previewLength.attachBlurEvent);
			}
		},
		attachInputEvent: function(){
			var value = RokSprocket.previewLength.cleanValue(this.value);
			if (this.get('value') == '0') this.set('value', RokSprocket.previewLength.char);
		},
		attachBlurEvent: function(){
			if (!this.value.length) this.set('value', RokSprocket.previewLength.char).fireEvent('change');
			else this.set('value', RokSprocket.previewLength.getValue() || RokSprocket.previewLength.char);
		},
		cleanValue: function(value){
			value = value.match(/\d+/g);
			if (!value) value = ['0'];

			value = value.join('').replace(RokSprocket.previewLength.char, 0).toInt() || 0;

			return value;
		},
		getValue: function(){
			return RokSprocket.previewLength.cleanValue(RokSprocket.previewLength.field.get('value'));
		}
	};

	this.RokSprocket.layout = {
		flag: false,
		attach: function(){
			var layout = document.getElement('#'+RokSprocket.params+'_layout'),
				value = layout.get('value'),
				list = layout.getElements('!> .dropdown-original !~ .dropdown-menu li[data-value]');

			layout.addEvent('change', function(){
				if (!RokSprocket.layout.flag){
					moofx(document.getElement('.panel-left h6 i.spinner')).animate({opacity: 1}, {duration: '300ms'});
					RokSprocket.articles.getItems();
				}
			});
		}
	};

	this.RokSprocket.init = function(){
		this.RokSprocket.content = {
			getModuleId: function(){
				var field = document.id('jform_id') || document.id('id');
				return (field ? field.get('value') : 0).toInt();
			},
			getInstanceId: function(){
				var field = document.id('jform_uuid') || document.id('uuid');
				return (field ? field.get('value') : "0");
			},
			getLayout: function(){
				return document.getElement('#'+RokSprocket.params+'_layout').get('value');
			},
			getProvider: function(){
				return document.getElement('#'+RokSprocket.params+'_provider').get('value');
			},
			getFilters: function(type){
				var provider = RokSprocket.content.getProvider();

				return RokSprocket.filters.getFilters(provider + (type || '_filters')) || {};
			},
			getArticlesIDs: function(){
				var filters = RokSprocket.content.getFilters('_articles'),
					articles = [];

				Object.each(filters.object, function(value, key){
					articles.push(RokSprocket.content.getFormat() + Object.getFromPath(filters.object[key], 'root.article'));
				});

				filters.json = JSON.encode(articles);

				return filters;
			},
			getSort: function(){
				var provider = RokSprocket.content.getProvider(),
					sort = document.getElement('[id$=' + provider + '_sort]'),
					append = document.getElement('[id$=' + provider + '_sort_manual_append]'),
					filter = sort ? RokSprocket.content.getFilters('_sort_' + sort.get('value') + '_filters') : null,
					result = {
						type: sort ? sort.get('value') : '',
						rules: filter ? filter.object : {}
					};

				if (result.type == 'manual' && append) result.append = append.get('value');

				return {json: JSON.encode(result)};
			},
			getProviderSubmit: function(){
				var datasets = document.getElements('[data-provider-submit].provider_' + RokSprocket.content.getProvider()),
					extras = {},
					query = [];
				if (!datasets.length) return false;

				datasets.each(function(dataset){
					var key = dataset.get('data-provider-submit'),
						value = dataset.get('value');

					extras[key] = value;
					query.push(key + '=' + value);
				});

				return {object: extras, keyvalue: query.join('&')};
			},
			getFormat: function(){
				return RokSprocket.content.getProvider() + '-';
			}
		};

		if (this.RokSprocket.content.getModuleId()){
			this.RokSprocket.dynamicfields.attach();
			this.RokSprocket.dynamicfields.attachLastFire();
			this.RokSprocket.dynamicfields.refreshProvider();
		} else {
			this.RokSprocket.selector = {
				init: function(){
					var provider  = document.id('create-new-provider'),
						layout    = document.id('create-new-layout'),
						providers = $$('[data-sprocket-provider]'),
						layouts   = $$('[data-sprocket-layout]'),
						recommendedElement;


					moofx(document.getElement('[data-sprocket-notice]')).style({opacity: 0, transform: 'scale(0)', visibility: 'visible'});

					providers.addEvent('click', function(e){
						if (e) e.preventDefault();
						providers.removeClass('active');
						this.addClass('active');
						provider.set('value', this.get('data-sprocket-provider'));
					});

					layouts.addEvent('click', function(e){
						if (e) e.preventDefault();
						layouts.removeClass('active');
						this.addClass('active');
						layout.set('value', this.get('data-sprocket-layout'));

						if (recommendedElement) recommendedElement.removeClass('asterisk');

						var recommended        = JSON.parse(this.get('data-sprocket-recommended')),
							notice             = document.getElement('[data-sprocket-notice]');

						recommendedElement = document.getElement('[data-sprocket-provider="'+recommended+'"]');
						if (recommended){
							var strongs = notice.getElements('strong');
							strongs[0].set('text', this.get('text'));
							strongs[1].set('text', recommendedElement.get('text'));
						}

						if (!recommended){
							if (recommendedElement) recommendedElement.removeClass('asterisk');
							moofx(notice).animate({ transform: 'scale(0)', opacity: 0 }, { duration: '250ms' });
						} else {
							if (recommendedElement) recommendedElement.addClass('asterisk').fireEvent('click');
							moofx(notice).animate({ transform: 'scale(1)', opacity: 1 }, { duration: '200ms' });
						}
					});
				}
			};

			this.RokSprocket.selector.init();
		}

		this.RokSprocket.displayLimit.attach();
		this.RokSprocket.tabs = new Tabs();
		this.RokSprocket.dropdowns = new Dropdowns({
			onSelection: function(event, select, value, dropdown){
				if (select.getParent('[data-article-id]')){
					select.getParent('[data-article-id]').fireEvent('change:relay(.item-params select, .item-params input)');
				}
			}
		});
		this.RokSprocket.modal = new Modal();
		this.RokSprocket.popover = new Popover();

		this.RokSprocket.flag = new Flag(true, {
			onInitialize: function(){
				var _this = this;
				this.elements = document.getElements('[data-flag]');
				this.target = null;
				this.events = {
					'mousedown': function(e){
						e.stop();
						this.target = null;

						if (e && e.target){
							if (e.target.get('tag') == 'li' && e.target.get('data-value')) _this.target = e.target;
							if (e.target.getParent('li[data-value')) _this.target = e.target.getParent('li[data-value');
						}

						RokSprocket.modal.set({
							title: "Changes Detected",
							body: "Unsaved settings have been detected. If you continue you could loose them. Click Save to save all the settings now or Ignore to continue.",
							type: ["yesno", {labels: {yes: "Save", no: "Ignore"}}],
							kind: 'error',
							beforeShow: function(){
								var save = this.statusbar.getElement('.yes'),
									ignore = this.statusbar.getElement('.no');

								this.buttonsEvents = {
									ignore: {
										'click:once': function(){
											RokSprocket.articles.resetFlag();
											if (_this.target){
												var select = _this.target.getElement('!> .dropdown-menu ~ .dropdown-original select');
												if (select) RokSprocket.dropdowns.selection({target: _this.target}, select).hideAll();
												else document.fireEvent('click', {target: _this.target.getElement('[data-additem-action]')});
											}
										}
									},

									save: {
										'click': function(){
											if (RokSprocket.save.ajax.isRunning()) return false;

											this.indicator.setStyle('display', 'block')
												.getElement('.message');
											save.addClass('disabled');
											RokSprocket.save.button.fireEvent('click');
										}.bind(this)
									}
								};

								ignore.addEvents(this.buttonsEvents.ignore);
								save.addEvents(this.buttonsEvents.save);

								this.indicator = new Element('div.indicator', {styles: {'display': 'none'}}).inject(this.statusbar);
								new Element('span').adopt(new Element('i.icon.spinner.spinner-16')).inject(this.indicator);
								new Element('span.message').inject(this.indicator);

								this.popupSave = {
									request: function(){
										RokSprocket.messages.hide();
										this.indicator.getFirst('span').setStyle('display', 'block');
										this.indicator.getElement('.message').set('text', 'Saving...');
									}.bind(this),
									failure: function(xhr){
										var message = 'Error: ' + (xhr.status ? xhr.status + ' - ' + xhr.statusText : xhr);
										this.indicator.getElement('.message').set('text', message);
										this.indicator.getFirst('span').setStyle('display', 'none');
									}.bind(this),
									success: function(response){
										response = new Response(response, {onError: this.popupSave.failure.bind(this)});

										var status = response.getPath('status'),
											message = 'Success: All changes have been successfully saved';
										if (status == 'success'){
											RokSprocket.messages.show('message', message);

											this.indicator.getElement('.message').set('text', message);
											this.indicator.getFirst('span').setStyle('display', 'none');
											this.statusbar.getElement('.yes').setStyle('display', 'none');
											this.statusbar.getElement('.no').addClass('btn-primary').set('text', 'Close');

											// all good, let's reset the flag
											RokSprocket.articles.resetFlag();
										}

									}.bind(this),
									complete: function(){
										save.removeClass('disabled');
										this.indicator.getFirst('span').setStyle('display', 'none');
									}.bind(this)
								};
								RokSprocket.save.ajax.addEvents(this.popupSave);
							},

							beforeHide: function(){
								this.indicator.dispose();
								RokSprocket.save.ajax.removeEvents(this.popupSave);
								this.statusbar.getElement('.no').removeEvents(this.buttonsEvents.ignore);
								this.statusbar.getElement('.yes').removeEvents(this.buttonsEvents.save);
							}
						}).show();
					}
				};

				if (!this.elements.length){
					var params = RokSprocket.params,
						list = ['[data-filter]',
								'[data-additem-action="addItem"]',
								'#'+params+'_provider !> .dropdown-original !~ .dropdown-menu li[data-value]',
								'#'+params+'_layout !> .dropdown-original !~ .dropdown-menu li[data-value]',
								'#'+params+'_joomla_sort !> .dropdown-original !~ .dropdown-menu li[data-value]',
								'#'+params+'_joomla_sort_manual_append !> .dropdown-original !~ .dropdown-menu li[data-value]'];

					this.elements = document.getElements(list.join(', '));
				}
			},
			onStateChange: function(state){
				if (!this.elements.length) return;

				var selects = this.elements.filter(function(select){
						return select.get('tag') == 'select' || select.getElement('!> .dropdown-menu ~ .dropdown-original select');
					}).clean(),
					elements = [
						selects,
						RokSprocket.filters.containers,
						RokSprocket.filters.containers.getElements('a'),
						document.getElement('[data-additem-action]')
					].flatten();

				if (state){
					RokSprocket.filters.detachAll();
					RokSprocket.dropdowns.detach(selects);
					new Elements(elements).addEvents(this.events);
				} else {
					RokSprocket.filters.attachAll();
					RokSprocket.dropdowns.attach(selects);
					new Elements(elements).removeEvents(this.events);
				}
			}
		});

		if (RokSprocket.content.getModuleId()){
		this.RokSprocket.articles = new Articles('.articles', {
			onModelSuccess: function(response){
				//console.log(response && !response.data.payload);
				//if (response && !response.data.payload) return;
				moofx(document.getElement('.panel-left h6 i.spinner')).animate({opacity: 0}, {duration: '300ms'});
				RokSprocket.layout.flag = true;
				//document.getElement('.layoutselection').fireEvent('change');
				if (RokSprocket.content.getProvider() == 'simple') RokSprocket.dynamicfields.refreshProvider();
				RokSprocket.layout.flag = false;
				document.getElements('.articles .sprocket-tip').twipsy({placement: 'left', offset: 5});

				RokSprocket.dropdowns.reload();

				RokSprocket.updateOrder();

				//RokSprocket.dynamicfields.refreshProvider();

				// attach image and peritem pickers
				var peritempickers = document.getElements('.articles [data-peritempicker]'),
					peritempickerstags = document.getElements('.articles [data-peritempickertags]'),
					imagepickers = document.getElements('.articles [data-imagepicker]');

				RokSprocket.peritempicker.attach(peritempickers);
				RokSprocket.peritempickertags.attach(peritempickerstags);
				RokSprocket.imagepicker.attach(imagepickers);

				// reattach tags and multiselect
				if (RokSprocket.multiselect) RokSprocket.multiselect.reattach();
				if (RokSprocket.tags) RokSprocket.tags.reattach();

				if (document.getElements('.articles [data-article-id]').length > 1){
					this.sortables = new Sortables('.articles', {
						clone: true,
						opacity: (Browser.firefox) ? 1 : 0.5,
						handle: '.handle',
						onStart: function(element, clone){
							clone.setStyle('z-index', 800);
							RokSprocket.sorting.toManual();
							element.removeClass('display-limit-flag').removeClass('first-child').removeClass('last-child');
							clone.removeClass('display-limit-flag').removeClass('first-child').removeClass('last-child');
							clone.addClass('i-am-a-clone');
						},
						onSort: function(element, clone){
							element.removeClass('display-limit-flag').removeClass('first-child').removeClass('last-child');
							clone.removeClass('display-limit-flag').removeClass('first-child').removeClass('last-child');
							RokSprocket.articles.updateLimit(RokSprocket.displayLimit.getValue());
						},
						onComplete: function(element){
							RokSprocket.updateOrder();
						}.bind(this),
						dragOptions: {
							container: document.getElement('.articles'),
							includeMargins: false
						}
					});
				}
			}
		});
		}

		this.RokSprocket.updateOrder = function(){
			RokSprocket.articles.container.getElements('[data-article-id]').each(function(element, i){
				var orderElement = element.getElement('[data-order]');
				if (orderElement){
					orderElement.set('value', i.toString());
				}
			}, this);

			RokSprocket.articles.updateLimit(RokSprocket.displayLimit.getValue());
		};

		/*this.RokSprocket.additem = new AddItem('.articles', {
			onModelSuccess: function(response){
				console.log('response');
			}
		});*/

		document.addEvent('click:relay([data-additem-action])', function(e, element){
			moofx(document.getElement('.panel-left h6 i.spinner')).animate({opacity: 1}, {duration: '300ms'});
			RokSprocket.articles.getItemsWithNew();
		});

		this.RokSprocket.filters = new Filters({
			onFiltersChange: function(provider, filters){
				if (!provider || !provider.contains('_sort_manual_filters')){
					moofx(document.getElement('.panel-left h6 i.spinner')).animate({opacity: 1}, {duration: '300ms'});
					RokSprocket.articles.getItems();
				}
			}
		});

		if (typeof RokSprocketFilters != 'undefined' && RokSprocketFilters.filters){
			this.RokSprocket.filters.addDataSets(RokSprocketFilters.filters, RokSprocketFilters.template);
		}

		this.RokSprocket.datepicker = new Picker.Date($$('.date-picker !~ .dateselection'), {
			animationDuration: 200,
			format: '%Y-%m-%d',
			timePicker: false,
			positionOffset: {x: (-172 - 7), y: 5},
			pickerClass: 'datepicker_vista',
			useFadeInOut: !Browser.ie,
			toggle: $$('.date-picker'),
			onSelect: function(date, input){
				input.fireEvent((Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input');
			}
		});

		this.RokSprocket.messages = {
			// types: message / error
			show: function(type, message){
				var system = document.getElement('.sprocket-messages'),
					cls = 'dd.message' + (type == 'message' ? ':not(.error)' : '');

				system
					.setStyle('display', 'block')
					.getElement(cls).setStyle('display', 'block')
					.getElement('ul').empty().set('html', '<li>' + message + '</li>');

				if (document.body.get('data-spy'))
					system.addClass('alert').addClass('alert-' + (type == 'message' ? 'success' : 'error'));

			},
			hide: function(type){
				if (!type){
					['message', 'error'].each(function(type){
						RokSprocket.messages.hide(type);
					});
				}

				var system = document.getElement('.sprocket-messages');
				if (system.getElement('[class=error]')) system.getElement('[class=error]').setStyle('display', 'none');
				system
					.setStyle('display', 'none')
					.getElement('.message' + (type ? '.' + type : '')).setStyle('display', 'none');
			}
		};

		if (RokSprocket.content.getModuleId()) this.RokSprocket.previewLength.attach();

		this.RokSprocket.sorting = {
			init: function(){
				var value = 'manual',
					provider = RokSprocket.content.getProvider(),
					sortDropdown = document.getElement('[id$='+provider+'_sort]');

				RokSprocket.sorting.flag = new Flag(true, {
					onInitialize: function(){
						this.events = {
							'click': function(e){
								e.stop();
								var target = e.target.get('tag') == 'li' ? e.target : e.target.getParent('li');

								RokSprocket.modal.set({
									title: "Changes Detected",
									body: "By changing to '" + target.get('data-text') + "' you will loose any manual sorting you may have made. Do you wish to continue with this action?",
									type: ["yesno", {labels: {yes: "Yes", no: "No"}}],
									kind: 'error',
									beforeShow: function(){
										var yes = this.statusbar.getElement('.yes'),
											no = this.statusbar.getElement('.no');

										this.buttonsEvents = {
											no: {
												'click:once': function(){
													RokSprocket.sorting.flag.setState(true);
													RokSprocket.sorting.toManual();
													RokSprocket.dropdowns.hideAll();
												}
											},

											yes: {
												'click:once': function(){
													RokSprocket.sorting.flag.setState(true);

													var provider = RokSprocket.content.getProvider(),
														filter = document.getElement('[data-filter*='+provider+'_sort_manual_filters]');

													if (filter) RokSprocket.filters.empty(filter.get('data-filter'));
													RokSprocket.sorting.toItem(target.get('data-value'));
													if (filter) RokSprocket.filters.fireEvent('onFiltersChange');

													RokSprocket.dropdowns.hideAll();
													this.hide();
												}.bind(this)
											}
										};

										no.addEvents(this.buttonsEvents.no);
										yes.addEvents(this.buttonsEvents.yes);
									},

									beforeHide: function(){
										this.statusbar.getElement('.no').removeEvents(this.buttonsEvents.no);
										this.statusbar.getElement('.yes').removeEvents(this.buttonsEvents.yes);
									}
								}).show();
							}
						};
					},
					onStateChange: function(flag){
						if (!sortDropdown) return;

						if (!flag){
							RokSprocket.dropdowns.detach(sortDropdown);
							sortDropdown.getParent('.sprocket-dropdown').addEvents(this.events);
						} else {
							sortDropdown.getParent('.sprocket-dropdown').removeEvents(this.events);
							RokSprocket.dropdowns.attach(sortDropdown);
						}
					}
				});

				if (sortDropdown) sortDropdown.addEvent('beforeChange', RokSprocket.sorting.changeEvent);

			},
			changeEvent: function(event, select, value, selected){
				var target = event.target,
					provider = RokSprocket.content.getProvider(),
					filter = document.getElement('[data-filter*='+provider+'_sort_manual_filters]'),
					dataValue = target.getParent('[data-value]'),
					clicked = (dataValue && dataValue.get('data-value') != 'manual');

				if (value != 'manual' && clicked && select.get('value') != value){
					if (select.get('value') != 'random') RokSprocket.sorting.flag.setState(false);
				}
			},

			toItem: function(value){
				var provider = RokSprocket.content.getProvider(),
					filter = document.getElement('[data-filter*='+provider+'_sort_manual_filters]'),
					sortDropdown = document.getElement('[id$='+provider+'_sort]');

				if (sortDropdown && sortDropdown.get('value') != value){
					var li = sortDropdown.getParent('.sprocket-dropdown').getElement('[data-value='+value+']');
					if (li) RokSprocket.dropdowns.selection({target: li}, sortDropdown);
				}
			},

			toManual: function(){
				var value = 'manual',
					provider = RokSprocket.content.getProvider(),
					sortDropdown = document.getElement('[id$='+provider+'_sort]');

				if (sortDropdown && sortDropdown.get('value') != value){
					var li = sortDropdown.getParent('.sprocket-dropdown').getElement('[data-value='+value+']');
					if (li) RokSprocket.dropdowns.selection({target: li}, sortDropdown);
				}
			}
		};

		if (RokSprocket.content.getModuleId()){
			//document.getElement('.content_provider').addEvent('change', RokSprocket.sorting.init);
			this.RokSprocket.layout.attach();
		}

		this.RokSprocket.save = {
			ajax: null,
			button: null,
			init: function(){
				var save = document.getElement('#toolbar-apply a.toolbar') || document.getElement('#toolbar-apply button');
				if (!save) return;

				save.set('onclick', null);

				if (!RokSprocket.content.getModuleId()){
					var continueButton = document.getElement('.create-new .btn');
					RokSprocket.continueButton = continueButton;
					if (continueButton){
						continueButton.addEvent('click', function(e){
							if (e) e.preventDefault();
							if (this.hasClass('disabled')) return;

							RokSprocket.save.button.fireEvent('click');
							this.addClass('disabled');
						});
					}
				}

				// I don't know if I hate more Joomla! for the inline JS or IE for not handling properly the onclick
				// Workaround for IE that keeps dirty onclick events even if removed.
				if (Browser.ie){
					var clone = save.clone();
					clone.inject(save, 'after');
					save.dispose();
					clone.onclick = function(){return false;}
					save = clone;
				}

				RokSprocket.save.button = save;
				RokSprocket.save.ajax = new Request({
					url: (document.id('adminForm') || document.id('module-form')).get('action'),
					method: 'post',
					onRequest: RokSprocket.save.events.request,
					onComplete: RokSprocket.save.events.complete,
					onSuccess: RokSprocket.save.events.success,
					onFailure: RokSprocket.save.events.failure
				});

				//var onclick = save.get('onclick') || 'function(){ return false; }';

				save.addEvent('click', function(e){
					if (e) e.stop();

					if (save.hasClass('disabled') || save.hasClass('spinner')) return false;

					var form = document.id('module-form') || document.id('adminForm');

					if (form){
						document.adminForm.task.value = 'module.apply'; //'module.apply';
						RokSprocket.save.ajax.send({data: form.toQueryString()});
					}

					// in case it's needed, onclick is the original submit function...
					// eval(onclick);
					return false;
				});
			},
			events:{
				request: function(){
					RokSprocket.messages.hide();
					RokSprocket.save.button.addClass('disabled spinner spinner-32');
					if (RokSprocket.continueButton) RokSprocket.continueButton.addClass('disabled');
				},
				complete: function(){
					RokSprocket.save.button.removeClass('disabled spinner spinner-32');
					if (RokSprocket.continueButton) RokSprocket.continueButton.removeClass('disabled');
				},
				success: function(response){
					response = new Response(response, {onError: RokSprocket.save.events.failure.bind(this)});

					var status = response.getPath('status');
					if (status == 'success'){
						RokSprocket.messages.show('message', 'All changes have been successfully saved.');

						// all good, let's reset the flag
						if (RokSprocket.articles) RokSprocket.articles.resetFlag();

						// check if it's new module
						if (!RokSprocket.content.getModuleId()){
							RokSprocket.modal.set({
								'title': 'New RokSprocket Module Saved',
								'body': '<p>The module has been saved successfully and because it\'s been detected as new the page will be refreshed.</p><p>Please wait...</p>',
								kind: 'success',
								'beforeShow': function(){
									this.wrapper.getElements('[data-dismiss]').dispose();
									this.statusbar.empty().dispose();
								}
							}).show();
							window.location.href = window.location.href + '&id=' + response.getPath('payload.module_id');
						}
					}
				},
				failure: function(xhr){
					var status = xhr.status,
						statusText = xhr.statusText,
						body = xhr.response || xhr;

					if (!RokSprocket.modal.isShown){
						RokSprocket.modal.set({
							title: "Error while saving",
							body: body,
							kind: 'error',
							type: 'close'
						}).show();
					}
				}
			}
		};

		this.RokSprocket.save.init();

		this.RokSprocket.remove = {
			list: [],
			init: function(){
				RokSprocket.remove.attach();
			},

			attach: function(){
				var twoStepsClick = document.retrieve('roksprocket:simple:remove', function(e, element){
					e.preventDefault();
					var status = element.retrieve('roksprocket:simple:step', 0);
					if (!status) RokSprocket.remove.oneStep(element);
					else if (status == 1) RokSprocket.remove.deleteItem(element);
					else return false;
				});

				document.addEvents({
					'click:relay([data-article-id] .remove-wrapper)': twoStepsClick,
					'click:relay(:not([data-article-id] .remove-wrapper))': RokSprocket.remove.revertAll
				});
			},

			oneStep: function(element){
				element.store('roksprocket:simple:step', 1);
				element.getParent('.summary').getElement('.details').setStyle('display', 'block');
				element.getParent('.summary').getElement('.remove, .deleting').setStyle('display', 'none');
				element.getParent('.summary').getElement('.confirm').setStyle('display', 'inline-block');
				RokSprocket.remove.list.push(element);
			},

			deleteItem: function(element){
				var item = element.getParent('[data-article-id]'),
					id = item.get('data-article-id');

				element.store('roksprocket:simple:step', 2);
				element.getParent('.summary').getElement('.details').setStyle('display', 'none');
				element.getParent('.summary').getElements('.remove, .confirm').setStyle('display', 'none');
				element.getParent('.summary').getElement('.deleting').setStyle('display', 'inline-block');
				RokSprocket.articles.removeItem(id);
			},

			revertSingle: function(element){
				element.store('roksprocket:simple:step', 0);
				element.getParent('.summary').getElement('.details').setStyle('display', 'block');
				element.getParent('.summary').getElement('.confirm, .deleting').setStyle('display', 'none');
				element.getParent('.summary').getElement('.remove').setStyle('display', 'inline-block');
				RokSprocket.remove.list.erase(element);
			},

			revertAll: function(event, element){
				if (element.hasClass('details') || element.hasClass('remove-wrapper') || element.getParent('.remove-wrapper')) return true;
				for (var i = RokSprocket.remove.list.length - 1; i >= 0; i--) {
					RokSprocket.remove.revertSingle(RokSprocket.remove.list[i]);
				}
			}
		};

		this.RokSprocket.remove.init();

		this.RokSprocket.editTitle = {
			init: function(){
				RokSprocket.editTitle.attach();
			},
			attach: function(){
				var click = document.retrieve('roksprocket:edittitle:click', function(e, element){
						RokSprocket.editTitle.show(element);
					}),
					keyup = document.retrieve('roksprocket:edittitle:keyup', function(e, element){
						if (e.key == 'esc') RokSprocket.editTitle.hide(element);
						if (e.key == 'enter') RokSprocket.editTitle.save(element);
					}),
					check = document.retrieve('roksprocket:edittitle:check', function(e, element){
						RokSprocket.editTitle.save(element);
					}),
					cross = document.retrieve('roksprocket:edittitle:cross', function(e, element){
						RokSprocket.editTitle.hide(element);
					});

				document.addEvents({
					'click:relay([data-article-title-edit])': click,
					'click:relay([data-article-title-cross])': cross,
					'click:relay([data-article-title-check])': check,
					'keyup:relay([data-article-title-input])': keyup
				});
			},

			show: function(element){
				var parent = element.getParent('[data-article-title]'),
					input = parent.getElement('[data-article-title-input]'),
					text = parent.getElement('span'),
					edit = parent.getElements('[data-article-title-edit]'),
					actions = parent.getElements('[data-article-title-cross], [data-article-title-check]');

				text.setStyle('display', 'none');
				actions.setStyle('display', 'inline-block');
				edit.setStyle('display', 'none');
				input.set('type', 'text');
				input.focus();
				input.select();
			},

			hide: function(element){
				var parent = element.getParent('[data-article-title]'),
					input = parent.getElement('[data-article-title-input]'),
					text = parent.getElement('span'),
					edit = parent.getElements('[data-article-title-edit]'),
					actions = parent.getElements('[data-article-title-cross], [data-article-title-check]');

				text.setStyle('display', 'inline-block');
				edit.setStyle('display', 'inline-block');
				actions.setStyle('display', 'none');
				input.set('type', 'hidden').set('value', text.get('text').clean());
			},

			save: function(element){
				var parent = element.getParent('[data-article-title]'),
					input = parent.getElement('[data-article-title-input]'),
					text = parent.getElement('span');

				text.set('text', input.get('value'));
				RokSprocket.editTitle.hide(element);
			}
		};

		this.RokSprocket.editTitle.init();

		this.RokSprocket.CopyToClipboard = {
			button: null,
			copyElement: null,
			init: function(){
				var button = document.getElement('a.copy-to-clipboard'),
					copyElement = document.getElement('.shortcode');

				if (!Browser.Plugins.Flash || !button || !copyElement){
					if (button) button.dispose();
					return false;
				}

				RokSprocket.CopyToClipboard.button = button;
				RokSprocket.CopyToClipboard.copyElement = copyElement;

				var clip = button.retrieve('clip');

				if (!clip){
					clip = new ZeroClipboard.Client();
					ZeroClipboard.setMoviePath((RokSprocket.AdminURL || RokSprocket.SiteURL) + ZeroClipboard.moviePath);
					button.store('clip', clip);

					clip.glue(button, button);
					clip.setHandCursor(true);
					clip.addEventListener('onLoad', RokSprocket.CopyToClipboard.onLoad);
					clip.addEventListener('onMouseUp', RokSprocket.CopyToClipboard.onMouseUp);
					clip.addEventListener('onComplete', RokSprocket.CopyToClipboard.onComplete);
					if (Browser.ie) button.setStyle('opacity', 0).fade('in');
				}

				button.addEvents({
					click: function(e){ if (e) e.preventDefault(); },
					mouseleave: function(){
						this.set('data-original-title', 'Copy to Clipboard');
					}
				});

				return true;
			},

			onLoad: function(){
				var button = RokSprocket.CopyToClipboard.button,
					copyElement = RokSprocket.CopyToClipboard.copyElement;

				button.setStyle('opacity', 0).fade('in');
			},

			onMouseUp: function(){
				var button = RokSprocket.CopyToClipboard.button,
					copyElement = RokSprocket.CopyToClipboard.copyElement,
					clip = button.retrieve('clip');

				clip.setText(copyElement.get('text').clean());
			},

			onComplete: function(){
				var button = RokSprocket.CopyToClipboard.button,
					copyElement = RokSprocket.CopyToClipboard.copyElement;

				button.set('data-original-title', 'Copied!');
				button.retrieve('twipsy').show();
			}
		};

		this.RokSprocket.CopyToClipboard.init();

		// loadmore
		this.RokSprocket.more = {
			button: document.getElement('.load-more'),
			attach: function(){
				var button = RokSprocket.more.button,
					click = button.retrieve('roksprocket:loadmore:click', function(e){
						var next_page = RokSprocket.Paging.next_page,
							data = {page: next_page};

						if (e.shift) data.load_all = true;

						this.addClass('loader disabled');
						RokSprocket.articles.getItems(data);
					}),
					docDown = document.body.retrieve('roksprocket:loadmore:shift:down', function(e){
						button.addClass('load-all').getElement('span.text').set('text', 'load all');
					}),
					docUp = document.body.retrieve('roksprocket:loadmore:shift:up', function(e){
						button.removeClass('load-all').getElement('span.text').set('text', 'load more');
					});

				button.removeClass('disabled').addEvent('click', click);
				if (!document.body.retrieve('roksprocket:loadmore:attached'))
					document.body.store('roksprocket:loadmore:attached', true)
						.addEvent('keydown:keys(shift)', docDown)
						.addEvent('keyup', docUp);
			},
			detach: function(){
				var button = RokSprocket.more.button,
					click = button.retrieve('roksprocket:loadmore:click'),
					docDown = document.body.retrieve('roksprocket:loadmore:shift:down'),
					docUp = document.body.retrieve('roksprocket:loadmore:shift:up');

				button.addClass('disabled').removeEvent('click', click);
				document.body.store('roksprocket:loadmore:attached', false)
					.removeEvent('keydown:keys(shift)', docDown)
					.removeEvent('keyup', docUp);
			}
		};

		if (this.RokSprocket.more.button){
			RokSprocket.more.attach();
			if (RokSprocket.Paging.more) RokSprocket.more.button.removeClass('hide-load-more');
		}

		// tips for animations dropdown
		document.getElements('.animations-dropdown ! > .sprocket-dropdown li:not(.divider,[class^=random])').each(function(animation){
			var cls = animation.className.trim(),
				desc = '<div class="animations-tip-img"><i class="icon animation '+cls+'"></i></div>';

			animation.set('data-original-title', desc);
			animation.twipsy({placement: 'left', html: true});
		});

		document.getElements('.group_imageresize .group-label label').each(function(label){
			label.set('data-placement', 'above');
		});

		// fix for sort
		var sortElements = document.getElements('[id$=_sort]'),
			sortTypes = document.getElements('[id$=_manual_append]');

		if (sortElements.length && sortElements.length == sortTypes.length){
			sortTypes.each(function(select, i){
				var prefix = select.id.replace(/_manual_append$/, ''),
					sort = document.getElement('[id='+prefix+']'),

					sortType = select.getParent('li'),
					sortElement = sort.getParent('li'),

					ul = new Element('ul', {styles: {display: 'inline-block'}}).inject(sortElement);

				sortType.inject(ul);
				sortElement.getElements('label').setStyles({'margin-right': 5, width: 'auto'});
			});
		}
		// document.getElement('select.joomla_sort_manual').getParent('li').addClass('sorting-li');

		// twipsy tooltips
		$$(".sprocket-tip").twipsy({placement: 'left', offset: 5});
		$$('.hasTip').each(function(tip){
			tip.removeClass('hasTip').addClass('sprocket-tip');
			tip.set('title', tip.get('title').split('::').pop());
			tip.twipsy({placement: 'left', offset: 5, html: true});
		});


		// alerts close
		document.getElements('[data-dismiss]:not([data-dismiss=true])').each(function(action){
			var alert = action.getParent('.' + action.get('data-dismiss')) || action.getParent('#system-message-container').getElement('.' + action.get('data-dismiss'));

			if (alert.get('data-cookie')){
				if (Cookie.read(alert.get('data-cookie')) == 'hide') alert.setStyle('display', 'none');
			}

			action.addEvent('click', function(e){
				e.stop();

				var dismiss = action.get('data-dismiss'),
					wrapper = action.getParent('.' + dismiss) || action.getParent('#system-message-container').getElement('.' + dismiss);

				if (wrapper){
					action.dispose();
					wrapper.fx({opacity: 0}, {
						callback: function(){
							wrapper.dispose();
							Cookie.write(wrapper.get('data-cookie'), 'hide', {duration: 1, path: new URI(window.location.href).get('directory')});
						}
					});
				}
			});

		}, this);

		// toggler for per-items
		document.getElements('.articles-view-option li').each(function(button, i){
			button.addEvent('click', function(e){
				if (e) e.preventDefault();
				Cookie.write('roksprocket-showitems', i, {duration: 365, path: '/'});

				var items = document.getElements('.articles-view-option li'),
					articles = document.getElement('.articles');

				items.removeClass('active');
				items[i].addClass('active');
				articles[!i ? 'addClass' : 'removeClass']('hide-items');
			});
		});


	}.bind(this);

	window.addEvent('domready', this.RokSprocket.init);
	window.addEvent('load', function(){
		// fire the provider again
		if (!RokSprocket.content.getModuleId()) return;

		//document.getElement('.content_provider').fireEvent('change');

		RokSprocket.articles.fireEvent('onModelSuccess');

		// hooking up provider change event
		document.getElements('[data-refresher=true]').addEvent('change', function(){
			RokSprocket.filters.fireEvent('filtersChange');
		});
	});

//<1.4compat>

// Flash detection

var version = (Function.attempt(function(){
	return navigator.plugins['Shockwave Flash'].description;
}, function(){
	return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version');
}) || '0 r0').match(/\d+/g);

Browser.Plugins.Flash = {
	version: Number(version[0] || '0.' + version[1]) || 0,
	build: Number(version[2]) || 0
};

//</1.4compat>

// String scripts

Browser.exec = function(text){
	if (!text) return text;
	if (window.execScript){
		window.execScript(text);
	} else {
		var script = document.createElement('script');
		script.setAttribute('type', 'text/javascript');
		script.text = text;
		document.head.appendChild(script);
		document.head.removeChild(script);
	}
	return text;
};

/* Ugly workaround for data-sets issue for IE < 9 on Moo < 1.4.4 */
if (MooTools.version < "1.4.4" && (Browser.name == 'ie' && Browser.version < 9)){
	((function(){
		var dataList = [
			'rel', 'data-toggle', 'data-text', 'data-icon', 'data-value', 'data-filter',
			'data-filter-action', 'data-filter-name', 'data-filter-container', 'data-selector',
			'data-row', 'data-tab', 'data-panel', 'data-dynamic', 'data-key', 'data-value',
			'data-name', 'data-other', 'data-dismiss', 'data-article-id', 'data-cookie',
			'data-flag', 'data-refresher', 'data-order', 'data-imagepicker', 'data-imagepicker-id',
			'data-imagepicker-name', 'data-imagepicker-display', 'data-provider-submit',
			'data-peritempicker', 'data-peritempicker-id', 'data-peritempicker-name',
			'data-peritempicker-display', 'data-placement'
		];

		dataList.each(function(data){
			Element.Properties[data] = {get: function(){ return this.getAttribute(data); }};
		});
	})());
}

/* Implement moo into Moo! */
/*
---
provides: MooTools
requires: [Core/Element, fx]
author: "[Valerio Proietti](http://mad4milk.net)"
license: "[MIT](http://mootools.net/license.txt)"
...
*/

Element.implement({

	fx: function(){
		var mu = moofx(this);
		mu.animate.apply(mu, arguments);
		return this;
	},

	styles: function(){
		var mu = moofx(this), result = mu.style.apply(mu, arguments);
		if (arguments.length == 1 && typeof arguments[0] == 'string') return result;
		return this;
	}

});

var hasOwnProperty = Object.prototype.hasOwnProperty;

Object.extend({
	getFromPath: function(source, parts){
		if (typeof parts == 'string') parts = parts.split('.');
		for (var i = 0, check, l = parts.length; i < l; i++){
			if (!source) source = null;
			else if (hasOwnProperty.call(source, parts[i])) source = source[parts[i]];
			else return null;
		}

		return source;
	}
});

})());


/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Tabs = new Class({

		Implements: [Options, Events],

		options: {
			/*
				onBeforeAttach: function(tab){},
				onAfterAttach: function(tab){},
				onBeforeDetach: function(tab){},
				onAfterDetach: function(tab){},
				onBeforeChange: function(tab, panel, {current_tab, current_panel}){},
				onAfterChange: function(tab, panel){}
			*/
		},

		initialize: function(options){
			this.tabs = document.getElements('.tab[data-tab]');
			this.panels = document.getElements('[data-panel=' + this.tabs.get('data-tab').join('], [data-panel=') + ']');

			this.setOptions(options);

			this.tabs.each(function(tab){
				this.attach(tab);
			}, this);

		},

		attach: function(tab){
			var tabs = (tab ? Array.from(tab) : this.tabs);

			this.fireEvent('beforeAttach', tabs);

			tabs.each(function(tab){
				var click = tab.retrieve('roksprocket:tabs:click', function(event){
					this.click.call(this, event, tab);
				}.bind(this));
				tab.addEvent('click', click);
			}, this);

			this.fireEvent('afterAttach', tabs);
		},

		detach: function(tab){
			var tabs = (tab ? Array.from(tab) : this.tabs);

			this.fireEvent('beforeDetach', tabs);

			tabs.each(function(tab){
				var click = tab.retrieve('roksprocket:tabs:click');
				tab.removeEvent('click', click);
			}, this);

			this.fireEvent('afterDetach', tabs);
		},

		click: function(event, tab){
			event.preventDefault();

			var id = tab.get('data-tab'),
				panel = document.getElement('[data-panel='+id+']');

			if (panel){
				var active = {
					tab: this.tabs.filter(function(tab){
						return tab.hasClass('active');
					})[0],
					panel: this.panels.filter(function(panel){
						return panel.hasClass('active');
					})[0]
				};

				this.fireEvent('beforeChange', [tab, panel, active]);

				$$(this.tabs, this.panels).removeClass('active');
				this.panels.setStyle('display', 'none');

				$$(tab, panel).addClass('active');
				panel.setStyle('display', 'block');

				this.fireEvent('afterChange', [tab, panel]);
			}
		}
	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){

	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Dropdowns = new Class({

		Implements: [Options, Events],

		options: {
			/*
				onBeforeAttach: function(selects){},
				onAfterAttach: function(selects){},
				onBeforeDetach: function(selects){},
				onAfterDetach: function(selects){},
				onBeforeChange: function(select, value, dropdown){},
				onAfterChange: function(select, value, dropdown){},
				onSelection: function(select, value, dropdown){},
				onBeforeShow: function(select, dropdown){},
				onAfterShow: function(select, dropdown){},
				onBeforeHide: function(select, dropdown){},
				onAfterHide: function(select, dropdown){},
				onBeforeHideAll: function(selects, dropdowns){},
				onAfterHideAll: function(selects, dropdowns){},
			*/
		},

		initialize: function(options){
			this.selects = document.getElements('.dropdown-original select');

			this.setOptions(options);

			this.bounds = {
				document: this.hideAll.bind(this)
			};

			this.attach();
		},

		attach: function(select){
			var selects = (select ? new Elements([select]).flatten() : this.selects);

			this.fireEvent('beforeAttach', selects);

			selects.each(function(select){
				var click = select.retrieve('roksprocket:selects:click', function(event){
						this.click.call(this, event, select);
					}.bind(this)),

					selection = select.retrieve('roksprocket:selects:selection', function(event){
						this.selection.call(this, event, select);
					}.bind(this)),

					parent = select.getParent('.sprocket-dropdown');

				if (parent){
					// We now rely on the bootstrap ones for the open/close, if available
					if (typeof jQuery == 'undefined' || !jQuery.fn.dropdown) parent.addEvent('click', click);
					parent.getElements('.dropdown-menu > :not([data-divider])').addEvent('click', selection);

					if (!select.getElement('option[selected]') && !parent.getElement('.dropdown-original select option[selected]')){
						this.selection({target: parent.getElement('[data-value]')}, select);
					}
				}
			}, this);

			if (!document.retrieve('roksprocket:selects:document', false)){
				document.addEvent('click', this.bounds.document);
				document.store('roksprocket:selects:document', true);
			}

			this.fireEvent('afterAttach', selects);

			return this;
		},

		detach: function(select){
			var selects = (select ? new Elements([select]).flatten() : this.selects);

			this.fireEvent('beforeDetach', selects);

			selects.each(function(select){
				var click = select.retrieve('roksprocket:selects:click'),
					selection = select.retrieve('roksprocket:selects:selection'),
					parent = select.getParent('.sprocket-dropdown');

				if (parent){
					parent.removeEvent('click', click);
					parent.getElements('.dropdown-menu >').removeEvent('click', selection);
				}

			}, this);

			if (!select) document.store('roksprocket:selects:document', false).removeEvent('click', this.bounds.document);

			this.fireEvent('afterDetach', selects);

			return this;
		},

		reload: function(){
			this.selects = document.getElements('.dropdown-original select');
		},

		click: function(event, select){
			event.preventDefault();

			if (select.retrieve('roksprocket:selects:open', false)) this.hide(select);
			else this.show(select);
		},

		selection: function(event, select){
			if (event && event.preventDefault) event.preventDefault();

			if (!event || !event.target) return;

			var item = (event.target.get('tag') == 'li') ? event.target : event.target.getParent('li'),
				selected = item.getParent('.sprocket-dropdown').getElement('[data-toggle=dropdown]'),
				text = item.get('data-text'),
				icon = item.get('data-icon'),
				value = item.get('data-value');

			select.fireEvent('beforeChange', [event, select, value, selected]);

			selected.getElement('span').set('text', text);
			if (icon && icon.length) selected.getElement('i').set('class', 'icon ' + icon);

			select.set('value', value).fireEvent('change');
			select.fireEvent('click');

			select.fireEvent('afterChange', [event, select, value, selected]);
			this.fireEvent('selection', [event, select, value, selected]);

			return this;
		},

		show: function(select){
			this.hideAll();

			var dropdown = select.getParent('.sprocket-dropdown:not(.open)');
			select.store('roksprocket:selects:open', true);

			this.fireEvent('beforeShow', [select, dropdown]);

			if (dropdown) dropdown.addClass('open');

			this.fireEvent('afterShow', [select, dropdown]);
		},

		hide: function(select){
			var dropdown = select.getParent('.sprocket-dropdown.open');
			select.store('roksprocket:selects:open', false);

			this.fireEvent('beforeHide', [select, dropdown]);

			if (dropdown) dropdown.removeClass('open');

			this.fireEvent('afterHide', [select, dropdown]);
		},

		hideAll: function(event){
			if (event){
				var parents = this.selects.getParent('.sprocket-dropdown');

				if (RokSprocket.modal.isShown) return true;
				if (parents.contains(event.target) || parents.getElement(event.target).clean().length) return true;
			}

			var dropdowns = this.selects.getParent('.sprocket-dropdown.open').clean();
			this.selects.store('roksprocket:selects:open', false);

			this.fireEvent('beforeHideAll', [this.selects, dropdowns]);
			if (dropdowns.length) $$(dropdowns).removeClass('open');

			this.fireEvent('afterHideAll', [this.selects, dropdowns]);
		},

		redraw: function(select){
			var options = select.getChildren(),
				parent = select.getParent('.sprocket-dropdown'),
				list = parent.getElement('.dropdown-menu'),
				active = parent.getElement('[data-toggle]').getFirst('span');

			list.empty();
			options.each(function(option){
				var text = option.get('text'),
					value = option.get('value'),
					item;

				item = new Element('li[data-dynamic=false][data-text='+text+'][data-value='+value+']').adopt(
					new Element('a', {href: '#'}).adopt(new Element('span', {text: text}))
				);

				list.adopt(item);
			}, this);

			if (!select.getElement('option[selected]')){
				if (select.getElements('option').length)
					select.set('value', select.getFirst().get('value'));
			}

			this.attach(select);

			var item = list.getElement('[data-value='+select.get('value')+']');
			this.selection({target: item}, select);
		}

	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	var OnInputEvent = (Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input';

	Element.implement({
		filtersQueryString: function(){
			var queryString = [];
			this.getElements('input:not([data-selector]), select:not([data-selector]), textarea:not([data-selector])').each(function(el){
				var type = el.type;
				if (!el.name || el.disabled || type == 'submit' || type == 'reset' || type == 'file' || type == 'image') return;

				var value = (el.get('tag') == 'select') ? el.getSelected().map(function(opt){
					// IE
					return document.id(opt).get('value');
				}) : ((type == 'radio' || type == 'checkbox') && !el.checked) ? null : el.get('value');

				Array.from(value).each(function(val){
					if (typeof val != 'undefined') queryString.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(val));
				});
			});
			return queryString.join('&');
		},

		filtersQueryObject: function(){
			return decodeURIComponent(this.filtersQueryString()).parseQueryString();
		},

		restoreEvents: function(events){
			events = events || {};

			if (Object.getLength(events)) this.removeEvents();

			Object.each(events, function(event, type){
				var keys = event['keys'];
				keys.each(function(fn){
					this.addEvent(type, fn);
				}, this);
			}, this);

			return this;
		}
	});

	this.Filters = new Class({

		Implements: [Options, Events],

		options: {
			/*
				onBeforeAttach: function(elements, type){},
				onAfterAttach: function(elements, type){},
				onBeforeDetach: function(elements, type){},
				onAfterDetach: function(elements, type){},
				onBeforeAttachRow: function(selectors, actions){},
				onAfterAttachRow: function(selectors, actions){},
				onBeforeDetachRow: function(selectors, actions){},
				onAfterDetachRow: function(selectors, actions){},
				onAddRow: function(row){},
				onRemoveRow: function(){},
				onBeforeFilterChange: function(){},
				onFilterChange: function(row, chunk, render, dataset){},
				onBeforeFiltersChange: function(){},
				onFiltersChange: function(provider, filters){}
			*/
		},

		dataSets: {},

		initialize: function(options){
			this.setOptions(options);

			this.containers = document.getElements('[data-filter]');

			this.containers.each(function(container){
				var rows = container.getElements('[data-row=true]'),
					actions = container.getElements('[data-filter-action]');

				this.attachRows(rows, 'dontfire');
				this.attach(actions, 'action');
			}, this);

		},

		attach: function(elements, type, silent){
			elements = Array.from(elements);

			this.fireEvent('beforeAttach', [elements, type]);

			elements.each(function(element){
				if (!element.retrieve('roksprocket:filters:element:attached')) {
					element.store('roksprocket:filters:element:attached', true);
					var click = element.retrieve('roksprocket:filters:events:' + type, function(event){
						this[element.get('data-filter-' + type)].call(this, event, element, type);
					}.bind(this));

					element.addEvent('click', click);
				}
			}, this);

			this.fireEvent('afterAttach', [elements, type]);
		},

		detach: function(elements, type, silent){
			elements = Array.from(elements);

			this.fireEvent('beforeDetach', [elements, type]);

			elements.each(function(element){
				if (element.retrieve('roksprocket:filters:element:attached')) {
					element.store('roksprocket:filters:element:attached', false);
					var click = element.retrieve('roksprocket:filters:events:' + type);

					element.removeEvent('click', click);
				}
			}, this);

			this.fireEvent('beforeDetach', [elements, type]);
		},

		attachRow: function(row, nofire, silent){
			var actions = row.getElements('[data-filter-action]'),
				selectors = row.getElements('[data-selector=true]').filter(function(selector){
					return !selector.retrieve('roksprocket:filters:selector:attached');
				}),
				inputs = row.getElements('input[name], select:not([data-selector])'),
				links = row.getElements('a');

			this.fireEvent('beforeAttachRow', [selectors, actions]);

			selectors.each(function(selector){
				selector.store('roksprocket:filters:selector:attached', true);

				var change = selector.retrieve('roksprocket:filters:events:selector', function(event){
					this.changeFilter.call(this, event, selector);
				}.bind(this));

				selector.addEvent('change', change).set('disabled', null);
				if (typeof nofire == 'undefined') change();
				if (selector.getChildren().length == 1) selector.setStyle('display', 'none');
			}, this);

			inputs.each(function(input){
				input.store('roksprocket:filters:input:attached', true);

				var change = input.retrieve('roksprocket:filters:events:input', function(event){
					this.changeRowValue.call(this, event, input);
				}.bind(this)),
					eventType = (input.get('tag') == 'select') ? 'change' : OnInputEvent;

				input.addEvent(eventType, change).set('disabled', null);
				if (typeof nofire == 'undefined') change();
			}, this);

			links.each(function(link){
				var storage = 'roksprocket:filters:element:',
					events = link.retrieve(storage + 'attached:events', {});

				link.store(storage + 'attached', true);
				if (Object.getLength(events)) link.restoreEvents(events);
			}, this);

			this.attach(actions, 'action', silent);
			if (!silent) row.removeClass('detached');

			this.fireEvent('afterAttachRow', [selectors, actions]);
		},

		attachRows: function(rows, nofire, silent){
			rows.each(function(row){
				this.attachRow(row, nofire, silent);
			}, this);
		},

		detachRow: function(row, silent){
			var actions = row.getElements('[data-filter-action]'),
				selectors = row.getElements('[data-selector=true]').filter(function(selector){
					return selector.retrieve('roksprocket:filters:selector:attached');
				}),
				inputs = row.getElements('input[type=text], select:not([data-selector])'),
				links = row.getElements('a');

			this.fireEvent('beforeDetachRow', [selectors, actions]);

			selectors.each(function(selector){
				selector.store('roksprocket:filters:selector:attached', false);

				var change = selector.retrieve('roksprocket:filters:events:selector');
				selector.removeEvent('change', change);
				if (!silent) selector.set('disabled', 'disabled');
			}, this);

			inputs.each(function(input){
				input.store('roksprocket:filters:input:attached', false);

				var change = input.retrieve('roksprocket:filters:events:input'),
					eventType = (input.get('tag') == 'select') ? 'change' : OnInputEvent;
				input.removeEvent(eventType, change);
				if (!silent) input.set('disabled', 'disabled');
			}, this);

			links.each(function(link){
				var storage = 'roksprocket:filters:element:';
				if (link.retrieve(storage + 'attached')) link.store(storage + 'attached:events', Object.clone(link.retrieve('events')));
				link.store(storage + 'attached', false);
				link.removeEvents().addEvent('click', function(e){ e.stop(); });
			}, this);

			this.detach(actions, 'action', silent);
			if (!silent) row.addClass('detached');

			this.fireEvent('AfterDetachRow', [selectors, actions]);
		},

		detachRows: function(rows, nofire, silent){
			rows.each(function(row){
				this.detachRow(row, nofire, silent);
			}, this);
		},

		attachFilter: function(filterType, silent){
			var containerPosition = this.containers.get('data-filter').indexOf(filterType);
			if (containerPosition == '-1') return;

			var container = this.containers[containerPosition],
				rows = container.getElements('[data-row=true]'),
				actions = container.getElements('[data-filter-action]');

			this.attachRows(rows, 'dontfire', silent);
			this.attach(actions, 'action', silent);
		},

		detachFilter: function(filterType, silent){
			var containerPosition = this.containers.get('data-filter').indexOf(filterType);
			if (containerPosition == '-1') return;

			var container = this.containers[containerPosition],
				rows = container.getElements('[data-row=true]'),
				actions = container.getElements('[data-filter-action]');

			this.detachRows(rows, 'dontfire', silent);
			this.detach(actions, 'action', silent);
		},

		attachAll: function(silent){
			this.containers.each(function(container){
				this.attachFilter(container.get('data-filter'), silent);
			}, this);

			this.detached = false;
		},

		detachAll: function(silent){
			this.containers.each(function(container){
				this.detachFilter(container.get('data-filter'), silent);
			}, this);

			this.detached = true;
		},

		addRow: function(event, element, action){
			if (!Object.getLength(this.dataSets)) return;
			var parent = element.getParent('.create-new'),
				container = element.getParent('[data-filter]'),
				id = container.get('data-filter'),
				row = new Element('div', {html: this.dataSets[id].template}).getFirst(),
				wrapper = element.getParent('[data-row=true]') || parent;

			if (parent) container.removeClass('empty');

			var filtersContainer = row.getElement('[data-filter-container]');
			if (filtersContainer){
				var chunkBit = Array.from({chunk: this.dataSets[id].json.root}),
					assembled = this.assemble(chunkBit, this.render(id, chunkBit, row), row),
					render = assembled.html;
				filtersContainer.set('html', render);

				if (container.getChildren().length <= 1) row.addClass('first');

				row.set('data-row', true).inject(wrapper, 'after');
				this.updateRowNames(row);
				this.refreshRowNames(container);

				if (assembled.js){
					(function(){
						assembled.js = this.parseJavaScript(assembled.js, render);
						var evil = eval(assembled.js),
							link = render.getElement('a');

						link.store('roksprocket:filters:element:attached', true);
						link.removeClass('disabled');
						if (typeof evil == 'function') evil();
					}.bind(this).delay(10));
				}

				this.attachRow(row);
				this.fireEvent('addRow', row);
				// probably don't need this since we fire input/selects events already, when adding a row
				//this.fireEvent('filtersChange', [id, this.getFilters(id)]);
			}

		},

		removeRow: function(event, element, action){
			var row = element.getParent('[data-row=true]'),
				parent = element.getParent('[data-filter]'),
				id = parent.get('data-filter'),
				first = row.hasClass('first');

			if (first && row.getNext()) row.getNext().addClass('first');
			this.detachRow(row);
			row.dispose();

			if (parent.getChildren().length <= 1) parent.addClass('empty');

			this.refreshRowNames(parent);

			this.fireEvent('removeRow');
			this.fireEvent('filtersChange', [id, this.getFilters(id)]);
		},

		empty: function(id){
			var container = this.containers.filter(function(container){
				return container.get('data-filter') == id;
			});

			var rows = container.getElements('[data-row=true]');
			rows.each(function(row){
				this.detachRow(row);
				row.dispose();
			}, this);

			container.addClass('empty');
		},

		updateRowNames: function(row){
			var elements = row.getElements('[data-key]');
			elements.each(function(element){
				var name = element.get('name'),
					dataName;

				if (name == '|name|'){
					var parent = element.getParent('[data-filter-name]'),
						child = element.getParent('[data-row=true]'),
						filterName = parent.get('data-filter-name'),
						dataKey = element.get('data-key'),
						chunk = element.getParent('.chunk');

					var previous = chunk.getElement('!~ .chunk');

					if (!previous) element.set('name', filterName + '[0]' + '[' + dataKey + ']');
					else {
						var inherit = previous.getElement('[data-key]'),
							naming = inherit.get('name') + '[' + inherit.get('value') + ']';
						element.set('name', naming);
						if (element.get('data-value')) element.set('name', naming + '['+element.get('data-value')+']');
					}

					dataName = chunk.getElement('[data-name]');
					if (dataName){
						dataName.set('data-name', element.get('name'));

						if (dataName.get('id') == '|name|') dataName.set('id', dataName.get('data-name').replace(/\[|\]/g, ""));
						var links = chunk.getElements('[href*=|name|]');
						if (links.length) links.each(function(link){
							link.set('href', link.get('href').replace(/\|name\|/g, function(match, position){
								return dataName.get('data-name');
							}));
						}, this);

					}

				}

			});
		},

		refreshRowNames: function(container){
			var rows = container.getElements('[data-row=true]');

			rows.each(function(row, i){
				row.getElements('[data-key]').each(function(element){
					var name = element.get('name');
					name = name.replace(/\[(\d{1,})\]/, '['+(i+1)+']');

					element.set('name', name);

					if (element.get('data-name')){
						var chunk = element.getParent('.chunk'),
							links = chunk.getElements('a');

						links.each(function(link){
							var dataName = element.get('data-name'),
								newName = name.replace(/\[|\]/g, ""),
								href = link.get('href');

							href = href.replace(dataName, newName);
							link.set('href', href);
						});

						if (element.get('id')) element.set('id', name.replace(/\[|\]/g, ""));
						element.set('data-name', name);
					}
				}, this);
			}, this);
		},

		changeRowValue: function(event, element){
			this.fireEvent('onBeforeFiltersChange');

			if (this.detached) return;

			clearTimeout(this.timer);
			var parent = element.getParent('[data-filter]'),
				id = parent.get('data-filter');

			// let's delay a little bit the fireEvent so it will only fire once
			// in case of multiple values changes in a row
			var timer = function(){
				this.fireEvent('filtersChange', [id, this.getFilters(id)]);
			}.bind(this);

			this.timer = timer.delay(10, this);
		},

		changeFilter: function(event, element){
			this.fireEvent('onBeforeFiltersChange');

			if (this.detached) return;

			var chunk = element.get('class').replace(/\schzn-done/, ''),
				value = element.get('value'),
				container = element.getParent('[data-filter]'),
				id = container.get('data-filter'),
				dataset = this.dataSets[id].json[chunk];

			var row = element.getParent('[data-row=true]'),
				chunkBit = this.getChunk(id, dataset.selections[value].render),
				assembled = this.assemble(chunkBit, this.render(id, chunkBit, row), row),
				html = new Element('div', {html: assembled.html}),
				wrapper = element.getParent('.chunk'),
				render = html.getFirst();

			wrapper.getElements('~ .chunk').dispose();
			render.inject(wrapper, 'after');
			this.updateRowNames(row);

			if (assembled.js){
				(function(){
					assembled.js = this.parseJavaScript(assembled.js, render);
					var evil = eval(assembled.js),
						link = render.getElement('a');

					link.store('roksprocket:filters:element:attached', true);
					link.removeClass('disabled');
					if (typeof evil == 'function') evil();
				}.bind(this).delay(10));
			}

			this.attachRow(row);

			this.fireEvent('filterChange', [row, chunk, render, dataset]);
		},

		getFilters: function(provider){
			var container = document.getElement('[data-filter*=' + provider + ']');

			if (!container) return null;

			var object = Object.getFromPath(
					container.filtersQueryObject(),
					container.get('data-filter-name').replace(/\]\[|\[/g, '.').replace(']', '')
				);

			return {element: container, object: object, json: JSON.encode(object || {})};
		},

		parseJavaScript: function(js, render){
			// replaces %ID% with name without [ and ]
			var dataName = render.getElement('[data-name]');
			if (dataName) js = js.replace(/%ID%/g, dataName.get('data-name').replace(/\[|\]/g, ""));

			return js;
		},

		getChunk: function(id, render){
			var match = render.match(/\|[^|]+\|/g),
				chunks = [];

			if (match){
				match = match.join('').split('|').filter(function(item){ return item !== ""; });
				match.each(function(chunk){
					var split = chunk.split(':'),
						dataset = this.dataSets[id].json[split[0]],
						find = '|' + match.join('| |') + '|';

					if (split[1]) Array.push(chunks, {value: split[1], find: find, render: render, chunk: dataset});
					else Array.push(chunks, {find: find, render: render, chunk: dataset});

				}, this);
			}

			return chunks;
		},

		assemble: function(chunks, render, row){
			var div, js = null;

			chunks.each(function(chunk, i){
				if (chunk.chunk.javascript !== null){
					js = chunk.chunk.javascript.replace(/\\?\%([^%]+)\%/g, function(match, selector){
						if (match.charAt(0) == '\\') return match.slice(1);

						return (match === '%ID%') ? '%ID%' : 'row.getElements("' + selector + '")';
					});
				}

				if (chunk.value){
					if (!div) div = new Element('div', {html: render});

					var elements = div.getElements('[data-key]');
					if (elements && elements[i]){
						elements[i].set('data-value', chunk.value);
					}
				}
			}, this);

			return {html: (div) ? div.innerHTML : render, js: js};
		},

		render: function(id, bits, row){
			var chunk, render, html = '<span class="chunk">';

			bits.each(function(bit){
				chunk = bit.chunk;

				if (chunk){
					html += (bit.find && bit.render) ? bit.render.replace(bit.find, chunk.render) : chunk.render;
				}
			}, this);

			return html + '</span>';
		},

		/*addDataSet: function(key, json){
			var model = this.model;
			if (!this.model || this.model.isRunning){
				model = new Request({
					url: RokSprocket.URL,
					data: {model: 'filters', model_action: 'getFilters', params: {}},
					onRequest: this.onModelRequest.bind(this),
					onSuccess: this.onModelSuccess.bind(this)
				});
			}

			this.setParams(Object.merge(json, {key: key, template: json.template}), model).send();
		},*/

		addDataSets: function(list, template){
			var model = this.model;

			this.template = template || '';
			if (!this.model || this.model.isRunning){
				model = new Request({
					url: RokSprocket.URL,
					data: {model: 'filters', model_action: 'getFilters', params: {}},
					onRequest: this.onModelRequest.bind(this),
					onSuccess: this.onModelSuccess.bind(this)
				});
			}

			this.setParams({filters: list}, model).send();
		},

		onModelRequest: function(){
			this.fireEvent('modelRequest');
		},

		onModelSuccess: function(response){
			response = new Response(response, {onError: this.error.bind(this)});

			var json = response.getPath('payload.json'),
				key = response.getPath('payload.key'),
				template = response.getPath('payload.template');

			if (json !== null){
				Object.each(json, function(data, key){
					if (!this.dataSets) this.dataSets = {};
					this.dataSets[key] = {};
					this.dataSets[key].template = this.template;
					this.dataSets[key].json = JSON.decode(data);
				}, this);
			}

			this.fireEvent('modelSuccess', response);
		},

		setParams: function(params, model){
			var data = Object.merge(model.options.data, {params: params || {}});

			data.params = JSON.encode(data.params);

			return model.setOptions(data);
		},

		error: function(message){
			RokSprocket.modal.set({
				kind: 'error',
				title: 'Error',
				type: ['close', {labels: {close: 'Close'}}],
				body: '<p>Error while retrieving the Filters DataSet:</p>' + '<p><pre>' + message + '</pre></p>'
			}).show();
		}

	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Articles = new Class({

		Implements: [Options, Events],

		options:{
			/*
				onModelRequest: function(){},
				onModelSuccess: function(Response response){}
			*/
		},

		initialize: function(container, options){
			this.setOptions(options);

			this.container = document.id(container) || document.getElement(container) || null;
			this.flag = RokSprocket.flag;
			this.model = new Request({
				url: RokSprocket.URL,
				data: {model: 'articles', model_action: 'getItems', params: {}},
				onRequest: this.onModelRequest.bind(this),
				onSuccess: this.onModelSuccess.bind(this)
			});

			this.bounds = {
				article: {
					getInfo: this.getItemInfo.bind(this),
					getInfoOn: this.showPopover.bind(this),
					getInfoOff: this.hidePopover.bind(this),
					getPreview: this.getItemPreview.bind(this),
					setFlag: this.setFlag.bind(this)
				}
			};

			this.articles = [];
			this.loadmore = false;

			if (!this.container) throw new Error('Articles container "' + container.toString() + '" not found in the DOM.');
			else {
				this.container.getElements('[data-article-id]').each(this.addArticle.bind(this));
			}

			return this;
		},

		getItem: function(){

		},

		getItemInfo: function(element, html){
			RokSprocket.popover.setBody(html);
		},

		getItemPreview: function(element, html){
			RokSprocket.modal.set({
				title: element.getElement('.title h1').get('text'),
				type: 'close',
				body: html
			}).show();
		},

		getItems: function(more){
			if (this.model.isRunning) this.model.cancel();
			this.model.options.data.model_action = 'getItems';
			//if (this.flag.getState()) return;

			var extras = RokSprocket.content.getProviderSubmit().object,
				params = {
					provider: RokSprocket.content.getProvider(),
					layout: RokSprocket.content.getLayout(),
					module_id: RokSprocket.content.getModuleId(),
					uuid: RokSprocket.content.getInstanceId(),
					filters: RokSprocket.content.getFilters('_filters').json,
					articles: RokSprocket.content.getFilters('_articles').json,
					sort: RokSprocket.content.getSort().json,
					display_limit: RokSprocket.displayLimit.getValue()
				};

			if (extras) params['extras'] = extras;
			params['load_all'] = (more && more.load_all) ? more.load_all : false;

			params['page'] = (more && more.page ? more.page : 1);

			this.loadmore = !!more;

			this.setParams(params).send();
		},

		getItemsWithNew: function(more){
			if (this.model.isRunning) this.model.cancel();
			this.model.options.data.model_action = 'getItemsWithNew';
			//if (this.flag.getState()) return;

			var extras = RokSprocket.content.getProviderSubmit().object,
				params = {
					provider: RokSprocket.content.getProvider(),
					layout: RokSprocket.content.getLayout(),
					module_id: RokSprocket.content.getModuleId(),
					uuid: RokSprocket.content.getInstanceId(),
					filters: RokSprocket.content.getFilters('_filters').json,
					articles: RokSprocket.content.getFilters('_articles').json,
					sort: RokSprocket.content.getSort().json,
					display_limit: RokSprocket.displayLimit.getValue()
				};

			if (extras) params['extras'] = extras;
			params['load_all'] = (more && more.load_all) ? more.load_all : false;

			params['page'] = (more && more.page ? more.page : 1);

			this.loadmore = !!more;

			this.setParams(params).send();
		},

		removeItem: function(item, more){
			if (this.model.isRunning) this.model.cancel();
			this.model.options.data.model_action = 'removeItem';
			//if (this.flag.getState()) return;

			var extras = RokSprocket.content.getProviderSubmit().object,
				params = {
					provider: RokSprocket.content.getProvider(),
					layout: RokSprocket.content.getLayout(),
					module_id: RokSprocket.content.getModuleId(),
					uuid: RokSprocket.content.getInstanceId(),
					filters: RokSprocket.content.getFilters('_filters').json,
					articles: RokSprocket.content.getFilters('_articles').json,
					sort: RokSprocket.content.getSort().json,
					display_limit: RokSprocket.displayLimit.getValue(),
					item_id: item
				};

			if (extras) params['extras'] = extras;
			params['load_all'] = (more && more.load_all) ? more.load_all : false;

			params['page'] = (more && more.page ? more.page : 1);

			this.loadmore = !!more;

			this.setParams(params).send();
		},

		showPopover: function(element, container){
			document.id(RokSprocket.popover).inject(element);
			RokSprocket.popover.set({
				title: container.getElement('.title h1').get('text'),
				body: '<div class="spinner"><i class="icon spinner spinner-64"></i></div>',
				kind: 'popover-right'
			}).show();
		},

		hidePopover: function(element, container){
			RokSprocket.popover.hide();
		},

		addArticle: function(article){
			var item = new Article(article).addEvents(this.bounds.article);
			this.articles.push(item);
		},

		onModelRequest: function(){
			this.fireEvent('modelRequest');
		},

		onModelSuccess: function(response){
			response = new Response(response, {onError: this.error.bind(this)});

			this.articles.empty();
			var html = response.getPath('payload.html');

			if (html !== null){
				this.container.set('class', 'clearfix provider-' + RokSprocket.content.getProvider() + ' articles no-articles');

				var dummy = new Element('div', {html: html});
				if (!this.loadmore) this.container.getElements('[data-article-id]').dispose();
				this.container.adopt(dummy.getChildren());
				this.container.getElements('[data-article-id]').each(this.addArticle.bind(this));
				if (this.articles.length) this.container.set('class', 'clearfix provider-' + RokSprocket.content.getProvider() + ' articles');
			} else {
				var payload = response.getPath('payload');
				if (payload){
					var removedItem = JSON.decode(payload).removed_item,
						removedItemElement = this.container.getElement('[data-article-id='+removedItem+']');

					moofx(removedItemElement).animate({opacity: 0, transform: 'scale(0)'}, {duration: 300, callback: function(){
						removedItemElement.dispose();
						this.container.getElements('[data-article-id]').each(this.addArticle.bind(this));
						this.container.set('class', 'clearfix provider-' + RokSprocket.content.getProvider() + ' articles' + (this.articles.length ? '' : ' no-articles'));
					}.bind(this)});
				}
			}

			RokSprocket.Paging.more = response.getPath('payload.more') || false;
			RokSprocket.Paging.page = response.getPath('payload.page') || 1;
			RokSprocket.Paging.next_page = response.getPath('payload.next_page') || 2;

			RokSprocket.more.button[(!RokSprocket.Paging.more) ? 'addClass' : 'removeClass']('hide-load-more');
			RokSprocket.more.button.removeClass('loader disabled');

			this.fireEvent('modelSuccess', response);
			this.loadmore = false;
		},

		setFlag: function(field, article){
			this.flag.setState(true);
			this.fireEvent('setFlag', [this.flag, field, article]);
		},

		resetFlag: function(){
			this.flag.setState(false);
			this.fireEvent('resetFlag', this.flag);
		},

		setParams: function(params){
			var data = Object.merge(this.model.options.data, {params: params || {}});

			data.params = JSON.encode(data.params);
			return this.model.setOptions(data);
		},

		updateLimit: function(limit){
			var articles = [], limited = [];

			if (!limit) limit = this.articles.length;

			articles = this.container.getElements('[data-article-id]:not(.i-am-a-clone)');
			limited = new Elements(articles.slice(0, limit));

			articles.removeClass('display-limit-flag').removeClass('last-child').removeClass('first-child');

			if(limited.length != articles.length || limit > articles.length) limited.addClass('display-limit-flag');
			if (limited[0]) limited[0].addClass('first-child');
			if (limit <= articles.length && limited.getLast()) limited.getLast().addClass('last-child');
		},

		error: function(message){
			RokSprocket.modal.set({
				kind: 'error',
				title: 'Error',
				type: ['close', {labels: {close: 'Close'}}],
				body: '<p>Error while retrieving the Articles with the applied filters:</p>' + '<p><pre>' + message + '</pre></p>'
			}).show();
		}

	});

	this.Article = new Class({

		Extends: Articles,

		Implements: [Options, Events],

		options:{
			/*
				onModelRequest: function(){},
				onModelSuccess: function(Response response){}
			*/
		},

		initialize: function(element, options){
			this.element = document.id(element) || null;
			this.model = new Request({
				url: RokSprocket.URL,
				data: {model: 'articles', model_action: '', params: {}},
				onRequest: this.onModelRequest.bind(this),
				onSuccess: this.onModelSuccess.bind(this)
			});

			this.bounds = {
				relay: {
					'click:relay(.preview-wrapper)': this.getPreview.bind(this),
					'change:relay(.item-params select, .item-params input)': this.setFlag.bind(this),
					'keyup:relay(.item-params input, [data-article-title-input])': this.setFlag.bind(this),
					'mouseenter:relay(.item-params input[type=text]:not([data-imagepicker-display],[data-peritempicker-display]))': this.showTipPreview.bind(this),
					'mouseleave:relay(.item-params input[type=text]:not([data-imagepicker-display],[data-peritempicker-display]))': this.hideTipPreview.bind(this),
					'blur:relay(.item-params input[type=text])': this.hideTipPreview.bind(this),
					'keyup:relay(.item-params input[type=text]:not([data-imagepicker-display],[data-peritempicker-display]))': this.updateTipPreview.bind(this)
				},
				info: {
					'mouseenter': this.getInfoOn.bind(this),
					'mouseleave': this.getInfoOff.bind(this)
				}
			};

			this.attach();
		},

		attach: function(){
			this.element.addEvents(this.bounds.relay);
			var info = this.element.getElement('.info-wrapper');
			if (info) info.addEvents(this.bounds.info);
		},

		detach: function(){
			this.element.removeEvents(this.bounds.relay);
			var info = this.element.getElement('.info-wrapper');
			if (info) info.removeEvents(this.bounds.info);
		},

		getID: function(){
			return this.element.get('data-article-id');
		},

		getAction: function(){
			return this.model.options.data.model_action;
		},

		getInfoOn: function(e){
			e.preventDefault();
			clearTimeout(this.timer);

			var extras = RokSprocket.content.getProviderSubmit().object,
				params = {id: this.getID()};

			if (extras) params['extras'] = extras;

			var timer = function(){
				this.model.options.data.model_action = 'getInfo';
				this.setParams(params);
				this.model.send();
				this.fireEvent('getInfoOn', [e.target, this.element]);
			}.bind(this);

			this.timer = timer.delay(400);
		},

		getInfoOff: function(e){
			e.preventDefault();
			clearTimeout(this.timer);

			this.fireEvent('getInfoOff', [e.target, this.element]);
		},

		getPreview: function(e){
			e.preventDefault();

			var extras = RokSprocket.content.getProviderSubmit().object,
				params = {id: this.getID()};

			if (extras) params['extras'] = extras;

			this.model.options.data.model_action = 'getPreview';
			this.setParams(params);
			this.model.send();
		},

		showTipPreview: function(event, input){
			input = document.id(input);

			var twipsy = input.retrieve('twipsy', input.twipsy({placement: 'above', offset: 5, html: true}));

			this.updateTipPreview(event, input);
		},

		hideTipPreview: function(event, input){
			input = document.id(input);

			var twipsy = input.retrieve('twipsy', input.twipsy({placement: 'above', offset: 5, html: true}));
			twipsy.hide();
		},

		updateTipPreview: function(event, input){
			input = document.id(input);

			var value = input.get('value'),
				twipsy = input.retrieve('twipsy', input.twipsy({placement: 'above', offset: 5, html: true}));

			if (!value.length) return this.hideTipPreview(event, input);

			input.set('data-original-title', value);
			twipsy.show().setContent(value);
		},

		setFlag: function(event, field){
			this.fireEvent('setFlag', [field, this.element]);
		},

		onModelSuccess: function(response){
			response = new Response(response, {onError: this.error.bind(this)});

			var html = response.getPath('payload.html');
			if (html !== null){
				this.fireEvent(this.getAction(), [this.element, html]);
			}

			this.fireEvent('modelSuccess', response);
		},

		toElement: function(){
			return this.element;
		}

	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Response = new Class({

		Implements: [Options, Events],

		options:{
			/*
				onParse: function(data){},
				onSuccess: function(data){},
				onError: function(data){}
			*/
		},

		initialize: function(data, options){
			this.setOptions(options);
			this.setData(data);

			return this;
		},

		setData: function(data){
			this.data = data;
		},

		getData: function(){
			return (typeOf(this.data) != 'object') ? this.parseData(this.data) : this.data;
		},

		parseData: function(){
			if (!JSON.validate(this.data)) return this.error('Invalid JSON data <hr /> ' + this.data);

			this.data = JSON.decode(this.data);

			if (this.data.status != 'success') return this.error(this.data.message);

			this.fireEvent('parse', this.data);

			return this.success(this.data);

		},

		getPath: function(path){
			var data = this.getData();

			if (typeOf(data) == 'object') return Object.getFromPath(data, path || '');
			else return null;
		},

		success: function(data){
			this.data = data;

			this.fireEvent('success', this.data);
			return this.data;
		},

		error: function(message){

			this.fireEvent('error', message);
			return message;
		}

	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
(function (doc, win, udef) {

	var
		firstEl = function (el) {
			return doc[el] || doc.getElementsByTagName(el)[0];
		},
		maybeCall = function(thing, ctx, args) {
			return typeof thing == 'function' ? thing.apply(ctx, args) : thing;
		},
		transitionEndEventName = null,

		stylesAreInjected = false,
		injectStyleSheet = function() {
			if (!stylesAreInjected) {

				stylesAreInjected = true;

				var stylesText =
					'.twipsy { display: block; position: absolute; visibility: visible; padding: 5px; font-size: 12px; z-index: 11000;}\
					.twipsy.above .twipsy-arrow { bottom: 0; left: 50%; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #000000;}\
					.twipsy.above-left .twipsy-arrow { bottom: 0; left: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #000000;}\
					.twipsy.above-right .twipsy-arrow { bottom: 0; right: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 5px solid #000000;}\
					.twipsy.left .twipsy-arrow { top: 50%; right: 0; margin-top: -5px; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-left: 5px solid #000000;}\
					.twipsy.below .twipsy-arrow { top: 0; left: 50%; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 5px solid #000000;}\
					.twipsy.below-left .twipsy-arrow { top: 0; left: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 5px solid #000000;}\
					.twipsy.below-right .twipsy-arrow { top: 0; right: 18px; margin-left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-bottom: 5px solid #000000;}\
					.twipsy.right .twipsy-arrow { top: 50%; left: 0; margin-top: -5px; border-top: 5px solid transparent; border-bottom: 5px solid transparent; border-right: 5px solid #000000;}\
					.twipsy-inner { padding: 3px 8px; background-color: #000000; color: white; text-align: center; max-width: 200px; text-decoration: none; -webkit-border-radius: 4px; -moz-border-radius: 4px; border-radius: 4px;}\
					.twipsy-arrow { position: absolute; width: 0; height: 0;}',
					stylesContainer = new Element("style", {"type":"text/css"}).inject(firstEl("head"), "bottom");

				stylesContainer.styleSheet
					? stylesContainer.styleSheet.cssText = stylesText
					: stylesContainer.innerHTML = stylesText;
			}
		};

	// Determine browser support for CSS transitions
	if (typeOf(Browser.Features.transition) != "boolean") {
		Browser.Features.transition = (function () {
			var styles = (doc.body || doc.documentElement).style;

			if (styles.transition !== udef || styles.MsTransition !== udef) {
				transitionEndEventName = "TransitionEnd";
			}
			else if (styles.WebkitTransition !== udef) {
				transitionEndEventName = "webkitTransitionEnd";
			}
			else if (styles.MozTransition !== udef) {
				transitionEndEventName = "transitionend";
			}
			else if (styles.OTransition !== udef) {
				transitionEndEventName = "oTransitionEnd";
			}

			return transitionEndEventName != null;
		})();
	}



	var Twipsy = new Class({

		/**
		* Construct the twipsy
		*
		* @param element Element
		* @param options object
		*/
		initialize:function (element, options) {
			this.options = Object.merge({}, Twipsy.defaults, options);
			this.element = doc.id(element);
			this.enabled = true;
			if (options.injectStyles) {
				injectStyleSheet();
			}
			this.fixTitle();
		},

		/**
		* Display the twipsy
		*
		* @return Twipsy
		*/
		show: function() {
			var pos, actualWidth, actualHeight, placement, twipsyElement, position,
				offset, size, twipsySize, leftPosition;
			if (this.hasContent() && this.enabled) {
				twipsyElement = this.setContent().getTip();

				if (this.options.animate) {
					moofx(twipsyElement).animate({'opacity': 0.8}, {
						duration: '150ms',
						equation: 'ease-in',
						callback: function(){
							this.isShown = true;
						}.bind(this)
					});//.addClass('twipsy-fade');
				}

				twipsyElement
					.setStyles({top: 0, left: 0, display: 'block'})
					.inject(document.body, 'top');

				offset = this.element.getPosition();
				size   = this.element.getSize();
				pos    = {
					left:   offset.x,
					top:    offset.y,
					width:  size.x,
					height: size.y
				};

				twipsySize = twipsyElement.getSize();
				actualWidth = twipsySize.x;
				actualHeight = twipsySize.y;

				placement = maybeCall(this.options.placement, this, [twipsyElement, this.element]);
				leftPosition = pos.left - actualWidth - this.options.offset;

				if (leftPosition < 0 && placement == 'left') placement = 'right';

				switch (placement) {
					case 'below':
						position = {top: pos.top + pos.height + this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
						break;

					case 'below-left':
						position = {top: pos.top + pos.height + this.options.offset, left: pos.left - this.options.offset};
						break;

					case 'below-right':
						position = {top: pos.top + pos.height + this.options.offset, left: pos.left + pos.width - actualWidth + this.options.offset};
						break;

					case 'above':
						position = {top: pos.top - actualHeight - this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
						break;

					case 'above-left':
						position = {top: pos.top - actualHeight - this.options.offset, left: pos.left - this.options.offset};
						break;

					case 'above-right':
						position = {top: pos.top - actualHeight - this.options.offset, left: pos.left + pos.width - actualWidth + this.options.offset};
						break;

					case 'left':
						position = {top: pos.top + pos.height / 2 - actualHeight / 2, left: leftPosition};
						break;

					case 'right':
						position = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width + this.options.offset};
						break;
				}

				twipsyElement
					.setStyles(position)
					.addClass(placement);
			}
			return this;
		},

		/**
		* Remove the twipsy from screen
		*
		* @return Twipsy
		*/
		hide: function() {
			var twipsyElement = this.getTip(),
				removeTwipsy = function(){
					this.isShown = false;
					twipsyElement.dispose();
				}.bind(this);

			if (!this.hasContent()){
				removeTwipsy();
				return this;
			}

			moofx(twipsyElement).animate({'opacity': 0}, {
				duration: '150ms',
				equation: 'ease-in',
				callback: removeTwipsy
			});

			return this;
		},

		/**
		* Set the readable content of the twipsy
		*
		* @return Twipsy
		*/
		setContent: function () {
			this.getTip().getElement('.twipsy-inner').set(this.options.html ? 'html' : 'text', this.getTitle());
			return this;
		},

		/**
		* Test if we have a content to put in the twipsy
		*
		@return boolean
		*/
		hasContent: function() {
			return this.getTitle().replace(/\s+/g, "") !== "";
		},

		/**
		* Get the title string
		*
		* @return String
		*/
		getTitle: function() {
			var title,
				e = this.element,
				o = this.options;

			this.fixTitle();

			if (typeof o.title == 'string') {
				title = e.getProperty(o.title == 'title' ? 'data-original-title' : o.title);
			}
			else if (typeof o.title == 'function') {
				title = o.title.call(e);
			}

			title = ('' + title).clean();
			return title || o.fallback;
		},

		/**
		* Get the twipsy HTML Element, construct it if not yet available
		*
		* @return Element
		*/
		getTip: function() {
			if (!this.tip) {
				this.tip = new Element("div.twipsy", {html: this.options.template});
			}
			return this.tip;
		},

		/**
		* Check if the given element is on screen
		*
		* @return boolean
		*/
		validate:function () {
			if (!this.element.parentNode) {
				this.hide();
				this.element = null;
				this.options = null;
				return false;
			}
			return true;
		},

		/**
		* Set enabled status to true
		*
		* @return Twipsy
		*/
		enable: function() {
			this.enabled = true;
			return this;
		},

		/**
		* Set enabled status to false
		*
		* @return twipsy
		*/
		disable: function() {
			this.enabled = false;
			return this;
		},

		/**
		* Toggle the enabled status
		*
		* @return Twipsy
		*/
		toggleEnabled: function() {
			this.enabled = !this.enabled;
			return this;
		},

		/**
		* Toggle the twipsy
		*
		* @return Twipsy
		*/
		toggle: function() {
			this[this.getTip().hasClass('in') ? 'hide' : 'show']();
			return this;
		},

		/**
		* Fix the title attribute of the trigger element, if not done yet
		*
		* @return Twipsy
		*/
		fixTitle:function () {
			var el = this.element;
			if (el.getProperty("title") || !el.getProperty("data-original-title")) {
				el.setProperty('data-original-title', el.getProperty("title") || '').removeProperty('title');
			}
			return this;
		}
	});

	Twipsy.defaults = {
		placement:    "above",
		animate:      true,
		delayIn:      0,
		delayOut:     0,
		html:         false,
		live:         false,
		offset:       0,
		title:        'title',
		trigger:      'hover',
		injectStyles: true,
		fallback:     "",
		template:     '<div class="twipsy-inner"></div><div class="twipsy-arrow"></div>'
	};

	Twipsy.rejectAttrOptions = ['title'];

	Twipsy.elementOptions = function (el, options) {
		var data = {},
			rejects = Twipsy.rejectAttrOptions,
			i = rejects.length;

		[
			"placement", "animate", "delay-in", "delay-out", "html",
			"offset", "title", "trigger", "template", "inject-styles"
		].each(function(item) {
			var res = null,lower;
			if (el.dataset) {
				res = el.dataset[item.camelCase()];
			}
			else {
				res = el.getProperty("data-" + item);
			}
			if (res) {
				lower = res.toLowerCase().clean();
				if (lower === "true") res = true;
				else if (lower === "false") res = false;
				else if (/^[0-9]+$/.test(lower)) lower = parseInt(lower, 10);
				data[item.camelCase()] = res;
			}
		});

		while (i--) {
			delete data[rejects[i]];
		}

		return Object.merge({}, options, data);
	};

	Element.implement({
		twipsy:function (options) {
			var twipsy, binder, eventIn, eventOut, name = 'twipsy';

			if (options === true) {
				return this.retrieve(name);
			}
			else if (typeof options == 'string') {
				twipsy = this.retrieve(name);
				if (twipsy) {
					twipsy[options]();
				}
				return this;
			}

			options = Object.merge({}, Twipsy.defaults, options);

			function get(ele) {
				var twipsy = ele.retrieve(name);

				if (!twipsy) {
					twipsy = new Twipsy(ele, Twipsy.elementOptions(ele, options));
					ele.store(name, twipsy);
				}

				return twipsy;
			}

			function enter() {
				var twipsy = get(this);
				twipsy.hoverState = 'in';

				if (options.delayIn == 0) {
					twipsy.show();
				} else {
					twipsy.fixTitle();
					setTimeout(function () {
						if (twipsy.hoverState == 'in') {
							twipsy.show();
						}
					}, options.delayIn);
				}
			}

			function leave() {
				var twipsy = get(this);
				twipsy.hoverState = 'out';
				if (options.delayOut == 0) {
					twipsy.hide();
				} else {
					setTimeout(function () {
						if (twipsy.hoverState == 'out') {
							twipsy.hide();
						}
					}, options.delayOut);
				}
			}

			if (options.trigger != 'manual') {
				eventIn = options.trigger == 'hover' ? 'mouseenter' : 'focus';
				eventOut = options.trigger == 'hover' ? 'mouseleave' : 'blur';
				get(this);

				document.id(this).addEvent(eventIn, enter).addEvent(eventOut, leave);
			}
			return this;
		}
	});

	Elements.implement({
		twipsy:function (options) {
			this.each(function(el) {
				el.twipsy(options);
			});
			return this;
		}
	});

	win.Twipsy = Twipsy;

})(document, self, undefined);
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){

	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Popover = new Class({

		Implements: [Options, Events],

		options: {
			/* */
		},

		initialize: function(options){
			this.built = false;
			this.build();
		},

		build: function(){
			if (this.built) return this.wrapper;

			if (document.getElement('.popover-wrapper')){
				['wrapper', 'arrow', 'inner', 'content'].each(function(type, i){
					this[type.camelCase()] = document.getElement('.popover-' + type);
				}, this);

			} else {
				this.wrapper = new Element('div.popover-wrapper', {styles: {display: 'none'}}).inject(document.body);
				this.wrapper.addEvent('click:relay([data-dismiss])', this.hide.bind(this));

				['arrow', 'inner', 'content'].each(function(type, i){
					this[type] = new Element('div.popover-' + type).inject(type == 'content' ? this.inner : this.wrapper);
				}, this);

				//this.close = new Element('a.close[data-dismiss=true]', {html: '&times;'}).inject(this.header);
				//this.title = new Element('h3').inject(this.header);
			}

			this.wrapper.styles({opacity: 0});

			this.built = true;
		},

		inject: function(element, where){
			this.wrapper.inject(element, where || 'inside');

			return this;
		},

		set: function(object){
			Object.each(object, function(args, action){
				var method = 'set' + action.capitalize();
				if (this[method]) this[method](args);
			}, this);

			return this;
		},

		setTitle: function(title){
			if (!this.title) this.title = new Element('h1.popover-title').inject(this.content, 'top');
			this.title.set('html', title);

			return this;
		},

		setBody: function(body){
			this.content.set('html', body);

			return this;
		},

		setKind: function(kind){
			this.kind = kind;
			this.wrapper.addClass(this.kind);

			return this;
		},

		show: function(){
			if (this.isShown) return;

			this.wrapper.setStyle('display', 'block');
			this.wrapper.fx({opacity: 1}, {
				duration: '300ms',
				equation: 'ease-out',
				callback: function(){
					this.isShown = true;
				}.bind(this)
			});

			return this;
		},

		hide: function(){
			if (!this.isShown) return;

			this.wrapper.fx({opacity: 0}, {
				duration: '300ms',
				equation: 'ease-out',
				callback: function(){
					if (this.kind) this.wrapper.removeClass(this.kind);
					this.wrapper.setStyle('display', 'none');
					this.isShown = false;
				}.bind(this)
			});

			return this;
		},

		toElement: function(){
			return this.wrapper;
		}

	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){

	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Modal = new Class({

		Implements: [Options, Events],

		options: {
			/*
				onBeforeShow: function(){},
				onBeforeHide: function(){}
			*/
		},

		initialize: function(options){
			this.built = false;
			this.callback = {};
			this.build();
		},

		build: function(){
			if (this.built) return this.wrapper;

			if (document.getElement('.modal-wrapper')){
				['wrapper', 'outer', 'inner', 'container', 'header', 'body', 'statusbar', 'close', 'title'].each(function(type, i){
					this[type.camelCase()] = document.getElement('.modal-' + type) || document.getElement('h3');
				}, this);

			} else {
				this.wrapper = new Element('div.modal-wrapper', {styles: {display: 'none'}}).inject(document.body);
				this.wrapper.addEvent('click:relay([data-dismiss])', this.hide.bind(this));

				['outer', 'inner', 'container',
				'header', 'body', 'statusbar'].each(function(type, i){
					var level = ' > div ',
						location = (!i) ? this.wrapper : this.wrapper.getElement(level.repeat(i));

					if (i > 2) location = this.container;

					this[type] = new Element('div.modal-' + type).inject(location);
				}, this);

				this.close = new Element('a.close[data-dismiss=true]', {html: '&times;'}).inject(this.header);
				this.title = new Element('h3').inject(this.header);

			}

			this.container.styles({top: -500, opacity: 0});

			this.built = true;
		},

		set: function(object){
			Object.each(object, function(args, action){
				var method = 'set' + action.capitalize();
				if (this[method]) this[method](args);
			}, this);

			return this;
		},

		setTitle: function(title){
			this.title.set('html', title);

			return this;
		},

		setBody: function(body){
			this.body.set('html', body);

			return this;
		},

		setKind: function(kind){
			this.kind = kind;
			this.wrapper.addClass(this.kind);

			return this;
		},

		setType: function(){
			var args = Array.from(arguments).flatten(),
				type = args[0] || 'close',
				options = args[1] || {labels: false},
				labels = {};

			type = type || 'close';

			switch(type){
				case 'yesno':
					labels = {no: (options.labels.no ? options.labels.no : 'No'), yes: (options.labels.yes ? options.labels.yes : 'Yes')};
					this.statusbar.empty().adopt(
						new Element('div.btn.no', {href: '#', text: labels.no, 'data-dismiss': 'true'}),
						new Element('div.btn.btn-primary.yes', {href: '#', text: labels.yes})
					);
					break;
				case 'close': default:
					labels = {close: (options.labels.close ? options.labels.close : 'Close')};
					this.statusbar.empty().adopt(
						new Element('div.btn.btn-primary.close', {href: '#', text: labels.close, 'data-dismiss': 'true'})
					);
			}
		},

		setBeforeShow: function(fn){
			this.callback.show = fn.bind(this);
			this.addEvent('beforeShow', this.callback.show);

			return this;
		},

		setBeforeHide: function(fn){
			this.callback.hide = fn.bind(this);
			this.addEvent('beforeHide', this.callback.hide);

			return this;
		},

		show: function(){
			if (this.isShown) return;

			this.fireEvent('beforeShow');
			this.removeEvents('beforeShow');
			document.body.addClass('modal-opened');
			document.getElement('body !> html').setStyle('overflow', 'hidden');
			this.wrapper.setStyles({'display': 'block', 'opacity': 1});
			this.container.fx({top: 0, opacity: 1}, {
				duration: '300ms',
				equation: 'ease-out',
				callback: function(){
					this.isShown = true;
				}.bind(this)
			});

			return this;
		},

		hide: function(){
			if (!this.isShown) return;

			this.fireEvent('beforeHide');
			this.removeEvents('beforeHide');
			var html = document.getElement('body.modal-opened !> html');
			document.body.removeClass('modal-opened');
			this.container.fx({top: -500, opacity: 0}, {
				duration: '300ms',
				equation: 'ease-out',
				callback: function(){
					html.setStyle('overflow', 'visible');
					this.wrapper.fx({'opacity': 0}, {
						callback: function(){
							if (this.kind) this.wrapper.removeClass(this.kind);
							this.setType('close');
							this.wrapper.setStyle('display', 'none');
							this.isShown = false;
						}.bind(this)
					});
				}.bind(this)
			});

			return this;
		},

		/*clearEvents: function(){
			this.removeEvents('beforeShow');
			this.removeEvents('beforeHide');
		},*/

		toElement: function(){
			return this.wrapper;
		}

	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Flag = new Class({

		Implements: [Options, Events],

		options:{
			/*
				onInitialize: function(){},
				onStateChange: function(flag){}
			*/
		},

		initialize: function(state, options){
			this.setOptions(options);
			this.flag = state || false;
			this.fireEvent('initialize');

			return this;
		},

		getState: function(){
			return this.flag;
		},

		setState: function(state){
			this.flag = state;

			this.fireEvent('stateChange', this.flag);
			return this;
		},

		reset: function(){
			this.flag = false;

			return this;
		}

	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};


((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	var OnInputEvent = (Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input';

	this.ImagePicker = new Class({

		Implements: [Options, Events],
		options: {},

		initialize: function(options){
			this.setOptions(options);

			this.attach();
		},

		getPickers: function(){
			this.pickers = document.getElements('[data-imagepicker]');

			return this.pickers;
		},

		attach: function(picker){
			var pickers = (picker ? new Elements([picker]).flatten() : this.getPickers());

			this.fireEvent('beforeAttach', pickers);

			pickers.each(function(picker){
				var select = picker.getElement('select'),
					display = picker.getElement('[data-imagepicker-display]'),
					selector = picker.getElement('a.modal'),
					input = picker.getElement('#' + picker.get('data-imagepicker-id'));

				var change = select.retrieve('roksprocket:pickers:change', function(event){
						this.change.call(this, event, select, selector);
					}.bind(this)),
					keypress = display.retrieve('roksprocket:pickers:input', function(event){
						this.keypress.call(this, event, display, input, select, selector);
					}.bind(this)),
					blur = display.retrieve('roksprocket:pickers:blur', function(event){
						this.blur.call(this, event, display, input, select, selector);
					}.bind(this));

				if (!input.get('value').test(/^-([a-z]{1,})-$/)){
					display.store('display_value', display.get('value') || '');
					display.store('display_datatitle', display.get('data-original-title') || '');
					input.store('json_value', input.get('value') || '');
				}

				select.addEvent('change', change);
				display.addEvent(OnInputEvent, keypress);
				//display.addEvent('blur', blur);
				display.twipsy({placement: 'above', offset: 5, html: true});

				if (typeof SqueezeBox != 'undefined'){
					picker.getElement('a.modal').removeEvents('click');
					SqueezeBox.assign(picker.getElement('a.modal'), {parse: 'rel'});
				}

			}, this);

			this.fireEvent('afterAttach', pickers);
		},

		detach: function(picker){
			var pickers = (picker ? new Elements([picker]).flatten() : this.pickers);

			this.fireEvent('beforeDetach', pickers);

			pickers.each(function(picker){
				var change = picker.retrieve('roksprocket:pickers:change'),
					keypress = picker.retrieve('roksprocket:pickers:input'),
					select = picker.getElement('select'),
					display = picker.getElement('[data-imagepicker-display]');

				select.removeEvent('change', change);
				display.removeEvent(OnInputEvent, keypress);

			}, this);

			if (!picker) document.store('roksprocket:pickers:document', false).removeEvent('click', this.bounds.document);

			this.fireEvent('afterDetach', pickers);
		},

		change: function(event, select, selector){
			var value = select.get('value'),
				parent = select.getParent('.imagepicker-wrapper'),
				hidden = parent.getElement('input[type=hidden]'),
				display = parent.getElement('[data-imagepicker-display]'),
				dropdown = parent.getElement('.sprocket-dropdown [data-toggle]'),
				icon = dropdown.getElement('i'),
				title = dropdown.getElement('span.name'),
				picker = parent.getElement('.modal');

			if (value.test(/^-([a-z]{1,})-$/)){
				parent.addClass('peritempicker-noncustom');
				title.set('text', select.getElement('[value='+value+']').get('text'));

				display.set('value', select.get('value'));
				hidden.set('value', value);
			} else {
				parent.removeClass('peritempicker-noncustom');
				title.set('text', '');
				selector.set('href', select.get('value'));

				if (display.get('value').test(/^-([a-z]{1,})-$/)){
					display.set('value', display.retrieve('display_value', '')).set('data-original-title', display.retrieve('display_datatitle', ''));
					hidden.set('value', hidden.retrieve('json_value', ''));
				}

				this.keypress(false, display, hidden, select);
			}

		},

		keypress: function(event, display, input, select, selector){
			var testValue = input.get('value').test(/^-([a-z]{1,})-$/),
				obj = JSON.decode(!testValue ? input.get('value') : '') || {type: 'mediamanager'},
				twipsy = display.retrieve('twipsy'),
				value = display.get('value'),
				data = {
					type: obj.type,
					path: value,
					preview: ''
				};

			if (!value.length) data = "";

			this.update(input, data);
			if (twipsy && event !== false){
				twipsy.setContent()[data ? 'show' : 'hide']();
			}
		},

		blur: function(event, display, input, select, selector){
			var twipsy = display.retrieve('twipsy');
			if (twipsy) twipsy.hide();
		},
		update: function(input, settings){
			input = document.id(input);

			// RokSprocket.SiteURL is always available

			var parent = input.getParent('[data-imagepicker]'),
				display = parent.getElement('[data-imagepicker-display]'),
				selector = parent.getElement('a.modal'),
				previewIMG = settings.path;

			settings.link = selector.get('href');

			if (previewIMG && (!previewIMG.test(/^https?:\/\//) && previewIMG.substr(0, 1) != '/')){
				previewIMG = RokSprocket.SiteURL + '/' + previewIMG;
			}


			var preview = (settings.preview && settings.preview.length) ? settings.preview : previewIMG;
				tip = "<div class='imagepicker-tip-preview'><img src='"+preview+"' /></div>";
				tip += (settings.width) ? "<div class='imagepicker-tip-size'>"+settings.width+" &times "+settings.height+"</div>": "";
				tip += "<div class='imagepicker-tip-path'>"+settings.path+"</div>";

			display
				.set('value', settings.path).store('display_value', settings.path)
				.set('data-original-title', (settings.path ? tip : '')).store('display_datatitle', (settings.path ? tip : ''))
				.twipsy({placement: 'above', offset: 5, html: true});

			var json = JSON.encode(settings).replace(/\"/g, "'");
			input.set('value', json).store('json_value', json);
		}

	});

	window.addEvent('domready', function(){
		this.RokSprocket.imagepicker = new ImagePicker();
	});

	if (typeof this.jInsertEditorText == 'undefined'){
		this.jInsertEditorText = function(value, input){
			var tag = value.match(/(src)=(\"[^\"]*\")/i),
				path = tag[2].replace(/\"/g, ''),
				data = {
					type: 'mediamanager',
					path: path,
					preview: ''
				};

			RokSprocket.imagepicker.update(input, data);
		};
	}

	if (typeof this.GalleryPickerInsertText == 'undefined'){
		this.GalleryPickerInsertText = function(input, value, size, minithumb){
			value = value.substr(RokSprocket.SiteURL.length + 1);

			var data = {
				type: 'rokgallery',
				path: value,
				width: size.width,
				height: size.height,
				preview: minithumb
			};

			RokSprocket.imagepicker.update(input, data);
		};
	}
})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
	var OnInputEvent = (Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input';

	this.PerItemPicker = new Class({

		Implements: [Options, Events],
		options: {},

		initialize: function(options){
			this.setOptions(options);

			this.attach();
		},

		getPickers: function(){
			this.pickers = document.getElements('[data-peritempicker]');

			return this.pickers;
		},

		attach: function(picker){
			var pickers = (picker ? new Elements([picker]).flatten() : this.getPickers());

			this.fireEvent('beforeAttach', pickers);

			pickers.each(function(picker){
				var select = picker.getElement('select'),
					display = picker.getElement('[data-peritempicker-display]'),
					input = picker.getElement('#' + picker.get('data-peritempicker-id'));

				var change = select.retrieve('roksprocket:pickers:change', function(event){
						this.change.call(this, event, select);
					}.bind(this)),
					keypress = display.retrieve('roksprocket:pickers:input', function(event){
						this.keypress.call(this, event, display, input, select);
					}.bind(this)),
					focus = display.retrieve('roksprocket:pickers:focus', function(event){
						this.focus.call(this, event, display, input);
					}.bind(this)),
					blur = display.retrieve('roksprocket:pickers:blur', function(event){
						this.blur.call(this, event, display, input, select);
					}.bind(this));

				if (!input.get('value').test(/^-([a-z]{1,})-$/)){
					display.store('display_value', display.get('value') || '');
					display.store('display_datatitle', display.get('data-original-title') || '');
					input.store('user_value', input.get('value') || '');
				}

				select.addEvent('change', change);
				display.addEvent(OnInputEvent, keypress);
				display.addEvent('focus', focus);
				display.addEvent('blur', blur);
				display.twipsy({placement: 'above', offset: 5, html: false});

			}, this);

			this.fireEvent('afterAttach', pickers);
		},

		detach: function(picker){
			var pickers = (picker ? new Elements([picker]).flatten() : this.pickers);

			this.fireEvent('beforeDetach', pickers);

			pickers.each(function(picker){
				var change = picker.retrieve('roksprocket:pickers:change'),
					keypress = picker.retrieve('roksprocket:pickers:input'),
					select = picker.getElement('select'),
					display = picker.getElement('[data-peritempicker-display]');

				select.removeEvent('change', change);
				display.removeEvent(OnInputEvent, keypress);

			}, this);

			if (!picker) document.store('roksprocket:pickers:document', false).removeEvent('click', this.bounds.document);

			this.fireEvent('afterDetach', pickers);
		},

		change: function(event, select){
			var value = select.get('value'),
				parent = select.getParent('.peritempicker-wrapper'),
				hidden = parent.getElement('input[type=hidden]'),
				display = parent.getElement('[data-peritempicker-display]'),
				dropdown = parent.getElement('.sprocket-dropdown [data-toggle]'),
				title = dropdown.getElement('span.name');

			if (value.test(/^-([a-z]{1,})-$/)){
				parent.addClass('peritempicker-noncustom');
				title.set('text', select.getElement('[value='+value+']').get('text'));

				display.set('value', select.get('value'));
				hidden.set('value', value);
			} else {
				parent.removeClass('peritempicker-noncustom');
				title.set('text', '');

				if (display.get('value').test(/^-([a-z]{1,})-$/)){
					display.set('value', display.retrieve('display_value', '')).set('data-original-title', display.retrieve('display_datatitle', ''));
					hidden.set('value', hidden.retrieve('user_value', ''));
				}

				this.keypress(false, display, hidden, select);
			}

		},

		keypress: function(event, display, input, select){
			var twipsy = display.retrieve('twipsy'),
				value = display.get('value');

			this.update(input, value);
			if (twipsy && event !== false){
				twipsy.setContent()[value.length ? 'show' : 'hide']();
			}
		},

		focus: function(event, display, input){
			new TextArea(input, display);
		},

		blur: function(event, display, input, select){
			var twipsy = display.retrieve('twipsy');

			if (twipsy) twipsy.hide();
			//if (ta && !ta.hasFocus) ta.dispose();
		},
		update: function(input, settings){
			input = document.id(input);

			// RokSprocket.SiteURL is always available

			var parent = input.getParent('[data-peritempicker]'),
				display = parent.getElement('[data-peritempicker-display]'),
				value = display.get('value');

			display
				.set('value', value).store('display_value', value)
				.set('data-original-title', value).store('display_datatitle', value)
				.twipsy({placement: 'above', offset: 5, html: false});

			input.set('value', value).store('juser_value', value);
		}

	});

	var TextArea = new Class({
		Implements: [Options, Events],
		options: {},
		initialize: function(input, display, options){
			this.setOptions(options);

			this.input = document.id(input);
			this.display = document.id(display);
			this.wrapper = null;
			this.textarea = null;

			this.build();
		},

		build: function(){
			this.wrapper = new Element('div.peritempicker-textarea-wrapper').adopt(
				new Element('span[data-peritempicker-close].close', {html: '&times;'}),
				new Element('textarea.peritempicker-textarea')
			).inject(document.body);

			this.wrapper.styles({position: 'absolute'});
			this.textarea = this.wrapper.getElement('textarea');

			this.attach();
			this.show();

			return this;
		},

		destroy: function(){
			this.detach();
			this.wrapper.dispose();

			return this;
		},

		attach: function(){
			var keypress = this.wrapper.retrieve('roksprocket:pickers:textarea', function(event){
					this.keypress.call(this, event);
				}.bind(this)),
				close = this.wrapper.retrieve('roksprocket:pickers:close', function(event){
					this.keypress.call(this, event);
					this.destroy.call(this, event);
				}.bind(this));

			document.body.addEvent('keyup:keys(esc)', close);
			this.textarea.addEvent('keydown', keypress);
			this.wrapper.addEvents({
				'blur:relay(textarea)': close,
				'click:relay(.close)': close
			});

			return this;
		},

		detach: function(){
			var keypress = this.wrapper.retrieve('roksprocket:pickers:textarea'),
				close = this.wrapper.retrieve('roksprocket:pickers:close');

			document.body.removeEvent('keyup:keys(esc)', close);
			this.textarea.removeEvent('keydown', keypress);
			this.wrapper.removeEvents({
				'blur:relay(textarea)': close,
				'click:relay(.close)': close
			});

			return this;
		},

		keypress: function(event){
			var value = this.textarea.get('value');

			this.input.set('value', value);
			this.display.set('value', value);

			if (event && event.type == 'keydown'){
				if (event.key == 'tab'){
					var next = this.input.getNext('[type!=hidden]');
					next.set('tabindex', 0).focus();
					next.set('tabindex', null);
				}
			}

			return this;
		},

		show: function(){
			this.wrapper.styles({display: 'block'}).position({relativeTo: this.display});
			this.textarea.set('value', this.display.get('value'));
			this.textarea.focus()

			return this;
		},

		hide: function(){
			this.wrapper.styles({display: 'none'});

			return this;
		},

		toElement: function(){
			return this.wrapper;
		}
	});

	window.addEvent('domready', function(){
		this.RokSprocket.peritempicker = new PerItemPicker();
	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
    if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};
    var OnInputEvent = (Browser.name == 'ie' && Browser.version <= 9) ? 'keypress' : 'input';

    this.PerItemPickerTags = new Class({

        Implements: [Options, Events],
        options: {},

        initialize: function(options){
            this.setOptions(options);

            this.attach();
        },

        getPickers: function(){
            this.pickers = document.getElements('[data-peritempickertags]');

            return this.pickers;
        },

        attach: function(picker){
            var pickers = (picker ? new Elements([picker]).flatten() : this.getPickers());

            this.fireEvent('beforeAttach', pickers);

            pickers.each(function(picker){
                var select = picker.getElement('select'),
                    display = picker.getElement('[data-peritempickertags-display]'),
                    input = picker.getElement('#' + picker.get('data-peritempickertags-id'));

                var change = select.retrieve('roksprocket:pickers:change', function(event){
                        this.change.call(this, event, select);
                    }.bind(this)),
                    keypress = display.retrieve('roksprocket:pickers:input', function(event){
                        this.keypress.call(this, event, display, input, select);
                    }.bind(this)),
                    focus = display.retrieve('roksprocket:pickers:focus', function(event){
                        this.focus.call(this, event, display, input);
                    }.bind(this)),
                    blur = display.retrieve('roksprocket:pickers:blur', function(event){
                        this.blur.call(this, event, display, input, select);
                    }.bind(this));

                if (!input.get('value').test(/^-([a-z]{1,})-$/)){
                    display.store('display_value', display.get('value') || '');
                    input.store('user_value', input.get('value') || '');
                }

                select.addEvent('change', change);
                display.addEvent(OnInputEvent, keypress);
                display.addEvent('focus', focus);
                //display.addEvent('blur', blur);
                //display.twipsy({placement: 'above', offset: 5, html: false});

            }, this);

            this.fireEvent('afterAttach', pickers);
        },

        detach: function(picker){
            var pickers = (picker ? new Elements([picker]).flatten() : this.pickers);

            this.fireEvent('beforeDetach', pickers);

            pickers.each(function(picker){
                var change = picker.retrieve('roksprocket:pickers:change'),
                    keypress = picker.retrieve('roksprocket:pickers:input'),
                    select = picker.getElement('select'),
                    display = picker.getElement('[data-peritempickertags-display]');

                select.removeEvent('change', change);
                display.removeEvent(OnInputEvent, keypress);

            }, this);

            if (!picker) document.store('roksprocket:pickers:document', false).removeEvent('click', this.bounds.document);

            this.fireEvent('afterDetach', pickers);
        },

        change: function(event, select){
            var value = select.get('value'),
                parent = select.getParent('.peritempickertags-wrapper'),
                hidden = parent.getElement('input[type=hidden]'),
                display = parent.getElement('[data-peritempickertags-display]'),
                dropdown = parent.getElement('.sprocket-dropdown [data-toggle]'),
                title = dropdown.getElement('span.name');

            RokSprocket.tags.reset(parent.getElement('[data-tags]'));

            if (value.test(/^-([a-z]{1,})-$/)){
                parent.addClass('peritempickertags-noncustom');
                title.set('text', select.getElement('[value='+value+']').get('text'));

                display.set('value', select.get('value'));
                hidden.set('value', value);
            } else {
                parent.removeClass('peritempickertags-noncustom');
                title.set('text', '');

                if (display.get('value').test(/^-([a-z]{1,})-$/)){
                    display.set('value', display.retrieve('display_value', ''));
                    hidden.set('value', hidden.retrieve('user_value', ''));
                }

                this.keypress(false, display, hidden, select);
            }

        },

        keypress: function(event, display, input, select){
            var value = display.get('value');

            this.update(input, value);
        },

        focus: function(event, display, input){
            new TextArea(input, display);
        },

        update: function(input, settings){
            input = document.id(input);

            // RokSprocket.SiteURL is always available

            var parent = input.getParent('[data-peritempickertags]'),
                display = parent.getElement('[data-peritempickertags-display]'),
                value = display.get('value');

            display
                .set('value', value).store('display_value', value);

            input.set('value', value).store('juser_value', value);
        }

    });

    var TextArea = new Class({
        Implements: [Options, Events],
        options: {},
        initialize: function(input, display, options){
            this.setOptions(options);

            this.input = document.id(input);
            this.display = document.id(display);
            this.wrapper = null;
            this.textarea = null;

            this.build();
        },

        build: function(){
            this.wrapper = new Element('div.peritempickertags-textarea-wrapper').adopt(
                new Element('span[data-peritempickertags-close].close', {html: '&times;'}),
                new Element('textarea.peritempickertags-textarea')
            ).inject(document.body);

            this.wrapper.styles({position: 'absolute'});
            this.textarea = this.wrapper.getElement('textarea');

            this.attach();
            this.show();

            return this;
        },

        destroy: function(){
            this.detach();
            this.wrapper.dispose();

            return this;
        },

        attach: function(){
            var keypress = this.wrapper.retrieve('roksprocket:pickers:textarea', function(event){
                    this.keypress.call(this, event);
                }.bind(this)),
                close = this.wrapper.retrieve('roksprocket:pickers:close', function(event){
                    this.keypress.call(this, event);
                    this.destroy.call(this, event);
                }.bind(this));

            document.body.addEvent('keyup:keys(esc)', close);
            this.textarea.addEvent('keydown', keypress);
            this.wrapper.addEvents({
                'blur:relay(textarea)': close,
                'click:relay(.close)': close
            });

            return this;
        },

        detach: function(){
            var keypress = this.wrapper.retrieve('roksprocket:pickers:textarea'),
                close = this.wrapper.retrieve('roksprocket:pickers:close');

            document.body.removeEvent('keyup:keys(esc)', close);
            this.textarea.removeEvent('keydown', keypress);
            this.wrapper.removeEvents({
                'blur:relay(textarea)': close,
                'click:relay(.close)': close
            });

            return this;
        },

        keypress: function(event){
            var value = this.textarea.get('value');

            this.input.set('value', value);
            this.display.set('value', value);

            if (event && event.type == 'keydown'){
                if (event.key == 'tab'){
                    var next = this.input.getNext('[type!=hidden]');
                    next.set('tabindex', 0).focus();
                    next.set('tabindex', null);
                }
            }

            return this;
        },

        show: function(){
            this.wrapper.styles({display: 'block'}).position({relativeTo: this.display});
            this.textarea.set('value', this.display.get('value'));
            this.textarea.focus()

            return this;
        },

        hide: function(){
            this.wrapper.styles({display: 'none'});

            return this;
        },

        toElement: function(){
            return this.wrapper;
        }
    });

    window.addEvent('domready', function(){
        this.RokSprocket.peritempickertags = new PerItemPickerTags();
    });

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
	this.ResizableTextbox = new Class({

		Implements: Options,

		options: {
			min: 1,
			max: 180,
			step: 8
		},

		initialize: function(element, options) {
			this.setOptions(options);
			this.element = document.id(element);
			this.width = this.element.offsetWidth;

			this.element.addEvents({
				'keydown': function(){
					var element = this.element,
						newsize = this.options.step * element.get('value').length;

					if (newsize < 25) newsize = 25;
					if (newsize >= this.options.max) newsize = this.options.max;
					element.setStyle('width', newsize);

				}.bind(this),
				'keyup': function() {
					var element = this.element,
						newsize = this.options.step * element.get('value').length;

					if (newsize <= this.options.min) newsize = this.width;
					if (newsize >= this.options.max) newsize = this.options.max;
					if (!(element.get('value').length == element.retrieve('rt-value') || newsize <= this.options.min || newsize >= this.options.max)){
						element.setStyle('width', newsize);

					}

				}.bind(this)
			});
		},

		toElement: function(){
			return this.element;
		}

	});
})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

 ((function(){

	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.Tags = new Class({

		Implements: [Options, Events],

		initialize: function(options){
			this.setOptions(options);

			this.elements = this.reload();
			this.attach();
		},

		reattach: function(){
			this.elements = this.reload();
			this.attach();
		},

		attach: function(){
			this.elements.each(function(container){
				if (!container.retrieve('tags:field:attached', false)){
					container.store('tags:field:attached', true);

					var relay = {
						tags: {
							click: container.retrieve('tags:field:click', function(event, element){
								if (event.target.get('data-tags-holder') === null) return true;

								container.getElement('[data-tags-maininput]').focus();
							}.bind(this)),

							select: container.retrieve('tags:feeds:select', function(event, element){
								this.select.call(this, container, element);
							}.bind(this)),

							unselect: container.retrieve('tags:field:remove', function(event, element){
								this.unselect.call(this, container, element);
							}.bind(this)),

							blur: container.retrieve('tags:feeds:blur', function(event, element){
								this.blur.call(this, container, element);
							}.bind(this)),

							keydown: container.retrieve('tags:feeds:keydown', function(event, element){
								this.keydown.call(this, event, container, element);
							}.bind(this))
						}
					};

					container.addEvents({
						'click:relay([data-tags-holder])': relay.tags.click,
						'click:relay([data-tags-value])': relay.tags.select,
						'click:relay([data-tags-remove])': relay.tags.unselect,
						'blur:relay([data-tags-maininput])': relay.tags.blur,
						'keydown:relay([data-tags-maininput])': relay.tags.keydown
					});

					this.maininput = new ResizableTextbox(container.getElement('[data-tags-maininput]'), {min: 1, max: 180, step: 9});
				}
			}, this);
		},

		keydown: function(event, container, element){
			if (event.key == 'enter'){
				event.preventDefault();
				this.blur(container, element);
			}
		},

		blur: function(container, element){
			var input = container.getElement('[data-tags-maininput]'),
				values = input.get('value') ? input.get('value').replace(/,\s/g, ',').split(',') : false;

			if (values !== false){
				values.each(function(value){
					this.select(container, value.replace(/('|"|\s)/g, ''));
					input.fireEvent('keyup');
				}, this);
			}
		},

		select: function(container, value){
			var maininput = container.getElement('[data-tags-maininput]'),
				realinput = container.getElement('[data-tags-input]'),
				current = realinput.get('value'),
				currentList = current.split(',');

			if (!currentList.contains(value)){
				var box = new Element('li.tags-box[data-tags-box='+value+']', {
						'html': '<span class="tags-title">'+value+'</span><span class="tags-remove" data-tags-remove>&times;</span>',
						'style': {opacity: 0, 'visibility': 'hidden'}
					});

				realinput.set('value', current ? current + ',' + value : value);
				box.inject(container.getElement('[data-tags-holder] .main-input'), 'before').set('tween', {duration: 200}).fade('in');
			}

			container.getElement('[data-tags-maininput]').set('value', '');
			maininput.focus();
		},

		unselect: function(container, element){
			var maininput = container.getElement('[data-tags-maininput]'),
				realinput = container.getElement('[data-tags-input]'),
				box = element.getParent('[data-tags-box]'),
				value = box.get('data-tags-box'),
				list = realinput.get('value').clean().replace(/,\s/g, ',').split(',');

			list.erase(value);
			realinput.set('value', list.join(','));
			box.set('tween', {duration: 200, onComplete: function(){ box.dispose(); }}).fade('out');
		},

		reset: function(container, values){
			var maininput = container.getElement('[data-tags-maininput]'),
				realinput = container.getElement('[data-tags-input]'),
				current = realinput.get('value'),
				currentList = current.split(',');

			var boxes = container.getElements('[data-tags-box]');
			realinput.set('value', '');
			if (boxes.length) boxes.dispose();
		},

		reload: function(assign){
			if (!assign) return document.getElements('[data-tags]');

			this.elements = document.getElements('[data-tags]');
			return this.elements;
		}

	});

	window.addEvent('domready', function(){
		this.RokSprocket.tags = new Tags();
	});

})());
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
 ((function(){

	if (typeof this.RokSprocket == 'undefined') this.RokSprocket = {};

	this.MultiSelect = new Class({

		Implements: [Options, Events],

		initialize: function(options){
			this.setOptions(options);

			this.elements = this.reload();
			this.attach();
		},

		reattach: function(){
			this.elements = this.reload();
			this.attach();
		},

		attach: function(){
			this.elements.each(function(container){
				if (!container.retrieve('tags:field:attached', false)){
					container.store('multiselect:field:attached', true);

					var relay = {
						tags: {
							click: container.retrieve('multiselect:field:click', function(event, element){
								if (event.target.get('data-multiselect-holder') === null) return true;

								container.getElement('[data-multiselect-maininput]').focus();
							}.bind(this)),

							unselect: container.retrieve('multiselect:field:remove', function(event, element){
								this.unselect.call(this, container, element);
							}.bind(this)),

							select: container.retrieve('multiselect:feeds:select', function(event, element){
								this.select.call(this, container, element);
							}.bind(this)),

							mouseenter: container.retrieve('multiselect:feeds:mouseenter', function(event, element){
								this.mouseenter.call(this, container, element);
							}.bind(this)),

							keydown: container.retrieve('multiselect:feeds:keydown', function(event, element){
								this.keydown.call(this, event, container, element);
							}.bind(this)),

							keyup: container.retrieve('multiselect:feeds:keyup', function(event, element){
								this.keyup.call(this, event, container, element);
							}.bind(this))
						},
						feeds: {
							mouseenter: container.retrieve('multiselect:feeds:mouseenter', function(event, element){
								this.refresh.call(this, container);
							}.bind(this)),

							focus: container.retrieve('multiselect:feeds:focus', function(event, element){
								this.focus.call(this, container, element);
							}.bind(this)),

							blur: container.retrieve('multiselect:feeds:blur', function(event, element){
								this.blur.delay(100, this, container, element);
							}.bind(this))
						}
					};

					container.addEvents({
						'click:relay([data-multiselect-holder])': relay.tags.click,
						'click:relay([data-multiselect-value])': relay.tags.select,
						'mouseenter:relay([data-multiselect-value])': relay.tags.mouseenter,
						'click:relay([data-multiselect-remove])': relay.tags.unselect,
						'mouseenter': relay.feeds.mouseenter,
						'keydown:relay([data-multiselect-maininput])': relay.tags.keydown,
						'keyup:relay([data-multiselect-maininput])': relay.tags.keyup,
						'focus:relay([data-multiselect-maininput])': relay.feeds.focus,
						'blur:relay([data-multiselect-maininput])': relay.feeds.blur
					});

					this.maininput = new ResizableTextbox(container.getElement('[data-multiselect-maininput]'), {min: 1, max: 500, step: 10});
				}
			}, this);
		},

		focus: function(container, element){
			var feeds = container.getElement('[data-multiselect-feeds]');

			this.refresh(container, element ? element.get('value') : null);
			container.addClass('multiselect-showing-feeds');
			feeds.setStyle('display', 'block');
		},

		blur: function(container, element){
			var feeds = container.getElement('[data-multiselect-feeds]');

			container.removeClass('multiselect-showing-feeds');
			feeds.setStyle('display', 'none');
		},

		keydown: function(event, container, element){
			var feeds = container.getElement('[data-multiselect-feed]'),
				focus = feeds.getElement('[data-multiselect-value].hover'),
				newActive;

			switch(event.key){
				case 'down':
					newActive = focus.getNext();
					if (newActive) this.mouseenter(container, newActive);
					break;
				case 'up':
					newActive = focus.getPrevious();
					if (newActive) this.mouseenter(container, focus.getPrevious());
					break;
				case 'enter':
					newActive = feeds.getElement('[data-multiselect-value].hover');
					if (newActive){
						this.select(container, feeds.getElement('[data-multiselect-value].hover'));
					}
					break;
				default:

			}
		},

		keyup: function(event, container, element){
			if (event.key != 'up' && event.key != 'down' && event.key != 'enter') this.refresh(container, element.get('value'));
			else if (event.key == 'enter') this.focus(container);
		},

		mouseenter: function(container, element){
			if (!element) return;

			element.getSiblings().removeClass('hover').removeClass('last-item');
			element.addClass('hover');
		},

		select: function(container, element){
			var select = container.getElement('[data-multiselect-select]'),
				value = element.get('data-multiselect-value'),
				text = element.get('text').clean(),
				box = new Element('li.multiselect-box[data-multiselect-box='+value+']', {
					'html': '<span class="multiselect-title">'+text+'</span><span class="multiselect-remove" data-multiselect-remove>&times;</span>',
					'style': {opacity: 0, 'visibility': 'hidden'}
				});

			select.getElement('option[value='+value+']').set('selected', 'selected');
			container.getElement('[data-multiselect-maininput]').set('value', '');
			box.inject(container.getElement('[data-multiselect-holder] .main-input'), 'before').set('tween', {duration: 200}).fade('in');
			this.focus(container);
		},

		unselect: function(container, element){
			var select = container.getElement('[data-multiselect-select]'),
			box = element.getParent('[data-multiselect-box]'),
			value = box.get('data-multiselect-box');

			select.getElement('option[value='+value+']').set('selected', null);
			box.set('tween', {duration: 200, onComplete: function(){ box.dispose(); }}).fade('out');
		},

		refresh: function(container, highlight){
			var options = container.getElements('[data-multiselect-select] option').filter(function(opt){ return !opt.get('selected'); }),
				feeds = container.getElement('[data-multiselect-feed]'),
				feedsList = [], text, elements;

			options.each(function(option, i){
				text = this.highlight(option.get('text'), highlight);
				feedsList.push(new Element('li[data-multiselect-value='+option.get('value')+']').set('html', text));
			}, this);

			feedsList = new Elements(feedsList);

			feeds.empty().adopt(feedsList.setStyle('display', 'block')).setStyle('width', container.getElement('[data-multiselect-holder]').offsetWidth - 2);

			elements = feedsList.filter(function(feed) { return !feed.get('text').test(highlight || '', 'i'); });
			if (elements.length) elements.setStyle('display', 'none');

			elements = feedsList.filter(function(feed) { return feed.getStyle('display') != 'none'; });
			if (elements.length){
				elements[0].addClass('hover');
				elements[elements.length - 1].addClass('last-item');
			}
		},

		highlight: function(html, highlight) {
			return html.replace(new RegExp(highlight, 'gi'), function(match) {
				return '<em>' + match + '</em>';
			});
		},

		reload: function(assign){
			if (!assign) return document.getElements('[data-multiselect]');

			this.elements = document.getElements('[data-multiselect]');
			return this.elements;
		}

	});

	window.addEvent('domready', function(){
		this.RokSprocket.multiselect = new MultiSelect();
	});

})());
/*!
---
name: Picker
description: Creates a Picker, which can be used for anything
authors: Arian Stolwijk
requires: [Core/Element.Dimensions, Core/Fx.Tween, Core/Fx.Transitions]
provides: Picker
...
*/


var Picker = new Class({

	Implements: [Options, Events],

	options: {/*
		onShow: function(){},
		onOpen: function(){},
		onHide: function(){},
		onClose: function(){},*/

		pickerClass: 'datepicker',
		inject: null,
		animationDuration: 400,
		useFadeInOut: true,
		positionOffset: {x: 0, y: 0},
		pickerPosition: 'bottom',
		draggable: true,
		showOnInit: true,
		columns: 1,
		footer: false
	},

	initialize: function(options){
		this.setOptions(options);
		this.constructPicker();
		if (this.options.showOnInit) this.show();
	},

	constructPicker: function(){
		var options = this.options;

		var picker = this.picker = new Element('div', {
			'class': options.pickerClass,
			styles: {
				left: 0,
				top: 0,
				display: 'none',
				opacity: 0
			}
		}).inject(options.inject || document.body);
		picker.addClass('column_' + options.columns);

		if (options.useFadeInOut){
			picker.set('tween', {
				duration: options.animationDuration,
				link: 'cancel'
			});
		}

		// Build the header
		var header = this.header = new Element('div.header').inject(picker);

		var title = this.title = new Element('div.title').inject(header);
		var titleID = this.titleID = 'pickertitle-' + String.uniqueID();
		this.titleText = new Element('div', {
			'role': 'heading',
			'class': 'titleText',
			'id': titleID,
			'aria-live': 'assertive',
			'aria-atomic': 'true'
		}).inject(title);

		this.closeButton = new Element('div.closeButton[text=x][role=button]')
			.addEvent('click', this.close.pass(false, this))
			.inject(header);

		// Build the body of the picker
		var body = this.body = new Element('div.body').inject(picker);

		if (options.footer){
			this.footer = new Element('div.footer').inject(picker);
			picker.addClass('footer');
		}

		// oldContents and newContents are used to slide from the old content to a new one.
		var slider = this.slider = new Element('div.slider', {
			styles: {
				position: 'absolute',
				top: 0,
				left: 0
			}
		}).set('tween', {
			duration: options.animationDuration,
			transition: Fx.Transitions.Quad.easeInOut
		}).inject(body);

		this.newContents = new Element('div', {
			styles: {
				position: 'absolute',
				top: 0,
				left: 0
			}
		}).inject(slider);

		this.oldContents = new Element('div', {
			styles: {
				position: 'absolute',
				top: 0
			}
		}).inject(slider);

		this.originalColumns = options.columns;
		this.setColumns(options.columns);

		// IFrameShim for select fields in IE
		var shim = this.shim = window['IframeShim'] ? new IframeShim(picker) : null;

		// Dragging
		if (options.draggable && typeOf(picker.makeDraggable) == 'function'){
			this.dragger = picker.makeDraggable(shim ? {
				onDrag: shim.position.bind(shim)
			} : null);
			picker.setStyle('cursor', 'move');
		}
	},

	open: function(noFx){
		if (this.opened == true) return this;
		this.opened = true;
		var picker = this.picker.setStyle('display', 'block').set('aria-hidden', 'false')
		if (this.shim) this.shim.show();
		this.fireEvent('open');
		if (this.options.useFadeInOut && !noFx){
			picker.fade('in').get('tween').chain(this.fireEvent.pass('show', this));
		} else {
			picker.setStyle('opacity', 1);
			this.fireEvent('show');
		}
		return this;
	},

	show: function(){
		return this.open(true);
	},

	close: function(noFx){
		if (this.opened == false) return this;
		this.opened = false;
		this.fireEvent('close');
		var self = this, picker = this.picker, hide = function(){
			picker.setStyle('display', 'none').set('aria-hidden', 'true');
			if (self.shim) self.shim.hide();
			self.fireEvent('hide');
		};
		if (this.options.useFadeInOut && !noFx){
			picker.fade('out').get('tween').chain(hide);
		} else {
			picker.setStyle('opacity', 0);
			hide();
		}
		return this;
	},

	hide: function(){
		return this.close(true);
	},

	toggle: function(){
		return this[this.opened == true ? 'close' : 'open']();
	},

	destroy: function(){
		this.picker.destroy();
		if (this.shim) this.shim.destroy();
	},

	position: function(x, y){
		var offset = this.options.positionOffset,
			scroll = document.getScroll(),
			size = document.getSize(),
			pickersize = this.picker.getSize();

		if (typeOf(x) == 'element'){
			var element = x,
				where = y || this.options.pickerPosition;

			var elementCoords = element.getCoordinates();

			x = (where == 'left') ? elementCoords.left - pickersize.x
				: (where == 'bottom' || where == 'top') ? elementCoords.left
				: elementCoords.right
			y = (where == 'bottom') ? elementCoords.bottom
				: (where == 'top') ? elementCoords.top - pickersize.y
				: elementCoords.top;
		}

		x += offset.x * ((where && where == 'left') ? -1 : 1);
		y += offset.y * ((where && where == 'top') ? -1: 1);

		if ((x + pickersize.x) > (size.x + scroll.x)) x = (size.x + scroll.x) - pickersize.x;
		if ((y + pickersize.y) > (size.y + scroll.y)) y = (size.y + scroll.y) - pickersize.y;
		if (x < 0) x = 0;
		if (y < 0) y = 0;

		this.picker.setStyles({
			left: x,
			top: y
		});
		if (this.shim) this.shim.position();
		return this;
	},

	setBodySize: function(){
		var bodysize = this.bodysize = this.body.getSize();

		this.slider.setStyles({
			width: 2 * bodysize.x,
			height: bodysize.y
		});
		this.oldContents.setStyles({
			left: bodysize.x,
			width: bodysize.x,
			height: bodysize.y
		});
		this.newContents.setStyles({
			width: bodysize.x,
			height: bodysize.y
		});
	},

	setColumnContent: function(column, content){
		var columnElement = this.columns[column];
		if (!columnElement) return this;

		var type = typeOf(content);
		if (['string', 'number'].contains(type)) columnElement.set('text', content);
		else columnElement.empty().adopt(content);

		return this;
	},

	setColumnsContent: function(content, fx){
		var old = this.columns;
		this.columns = this.newColumns;
		this.newColumns = old;

		content.forEach(function(_content, i){
			this.setColumnContent(i, _content);
		}, this);
		return this.setContent(null, fx);
	},

	setColumns: function(columns){
		var _columns = this.columns = new Elements, _newColumns = this.newColumns = new Elements;
		for (var i = columns; i--;){
			_columns.push(new Element('div.column').addClass('column_' + (columns - i)));
			_newColumns.push(new Element('div.column').addClass('column_' + (columns - i)));
		}

		var oldClass = 'column_' + this.options.columns, newClass = 'column_' + columns;
		this.picker.removeClass(oldClass).addClass(newClass);

		this.options.columns = columns;
		return this;
	},

	setContent: function(content, fx){
		if (content) return this.setColumnsContent([content], fx);

		// swap contents so we can fill the newContents again and animate
		var old = this.oldContents;
		this.oldContents = this.newContents;
		this.newContents = old;
		this.newContents.empty();

		this.newContents.adopt(this.columns);

		this.setBodySize();

		if (fx){
			this.fx(fx);
		} else {
			this.slider.setStyle('left', 0);
			this.oldContents.setStyles({left: 0, opacity: 0});
			this.newContents.setStyles({left: 0, opacity: 1});
		}
		return this;
	},

	fx: function(fx){
		var oldContents = this.oldContents,
			newContents = this.newContents,
			slider = this.slider,
			bodysize = this.bodysize;
		if (fx == 'right'){
			oldContents.setStyles({left: 0, opacity: 1});
			newContents.setStyles({left: bodysize.x, opacity: 1});
			slider.setStyle('left', 0).tween('left', 0, -bodysize.x);
		} else if (fx == 'left'){
			oldContents.setStyles({left: bodysize.x, opacity: 1});
			newContents.setStyles({left: 0, opacity: 1});
			slider.setStyle('left', -bodysize.x).tween('left', -bodysize.x, 0);
		} else if (fx == 'fade'){
			slider.setStyle('left', 0);
			oldContents.setStyle('left', 0).set('tween', {
				duration: this.options.animationDuration / 2
			}).tween('opacity', 1, 0).get('tween').chain(function(){
				oldContents.setStyle('left', bodysize.x);
			});
			newContents.setStyles({opacity: 0, left: 0}).set('tween', {
				duration: this.options.animationDuration
			}).tween('opacity', 0, 1);
		}
	},

	toElement: function(){
		return this.picker;
	},

	setTitle: function(content, fn){
		if (!fn) fn = Function.from;
		this.titleText.empty().adopt(
			Array.from(content).map(function(item, i){
				return typeOf(item) == 'element'
					? item
					: new Element('div.column', {text: fn(item, this.options)}).addClass('column_' + (i + 1));
			}, this)
		);
		return this;
	},

	setTitleEvent: function(fn){
		this.titleText.removeEvents('click');
		if (fn) this.titleText.addEvent('click', fn);
		this.titleText.setStyle('cursor', fn ? 'pointer' : '');
		return this;
	}

});
/*!
---
name: Picker.Attach
description: Adds attach and detach methods to the Picker, to attach it to element events
authors: Arian Stolwijk
requires: [Picker, Core/Element.Event]
provides: Picker.Attach
...
*/


Picker.Attach = new Class({

	Extends: Picker,

	options: {/*
		onAttached: function(event){},

		toggleElements: null, // deprecated
		toggle: null, // When set it deactivate toggling by clicking on the input */
		togglesOnly: true, // set to false to always make calendar popup on input element, if true, it depends on the toggles elements set.
		showOnInit: false, // overrides the Picker option
		blockKeydown: true
	},

	initialize: function(attachTo, options){
		this.parent(options);

		this.attachedEvents = [];
		this.attachedElements = [];
		this.toggles = [];
		this.inputs = [];

		var documentEvent = function(event){
			if (this.attachedElements.contains(event.target)) return;
			this.close();
		}.bind(this);
		var document = this.picker.getDocument().addEvent('click', documentEvent);

		var preventPickerClick = function(event){
			event.stopPropagation();
			return false;
		};
		this.picker.addEvent('click', preventPickerClick);

		// Support for deprecated toggleElements
		if (this.options.toggleElements) this.options.toggle = document.getElements(this.options.toggleElements);

		this.attach(attachTo, this.options.toggle);
	},

	attach: function(attachTo, toggle){
		if (typeOf(attachTo) == 'string') attachTo = document.id(attachTo);
		if (typeOf(toggle) == 'string') toggle = document.id(toggle);

		var elements = Array.from(attachTo),
			toggles = Array.from(toggle),
			allElements = [].append(elements).combine(toggles),
			self = this;

		var closeEvent = function(event){
			var stopInput = self.options.blockKeydown
					&& event.type == 'keydown'
					&& !(['tab', 'esc'].contains(event.key)),
				isCloseKey = event.type == 'keydown'
					&& (['tab', 'esc'].contains(event.key)),
				isA = event.target.get('tag') == 'a' || event.target.getParent().get('tag') == 'a';

			if (stopInput || isA) event.preventDefault();
			if (isCloseKey || isA) self.close();
		};

		var getOpenEvent = function(element){
			return function(event){
				var tag = event.target.get('tag');
				if (tag == 'input' && event.type == 'click' && !element.match(':focus') || (self.opened && self.input == element)) return;
				if (tag == 'a' || event.target.getParent().get('tag') == 'a') event.stop();

				self.position(element);
				self.open();
				self.fireEvent('attached', [event, element]);
			};
		};

		var getToggleEvent = function(open, close){
			return function(event){
				if (self.opened) close(event);
				else open(event);
			};
		};

		allElements.each(function(element){

			// The events are already attached!
			if (self.attachedElements.contains(element)) return;

			var events = {},
				tag = element.get('tag'),
				openEvent = getOpenEvent(element),
				// closeEvent does not have a depency on element
				toggleEvent = getToggleEvent(openEvent, closeEvent);

			if (tag == 'input'){
				// Fix in order to use togglers only
				if (!self.options.togglesOnly || !toggles.length){
					events = {
						focus: openEvent,
						click: openEvent,
						keydown: closeEvent
					};
				}
				self.inputs.push(element);
			} else {
				if (toggles.contains(element)){
					self.toggles.push(element);
					events.click = toggleEvent
				} else {
					events.click = openEvent;
				}
			}
			element.addEvents(events);
			self.attachedElements.push(element);
			self.attachedEvents.push(events);
		});
		return this;
	},

	detach: function(attachTo, toggle){
		if (typeOf(attachTo) == 'string') attachTo = document.id(attachTo);
		if (typeOf(toggle) == 'string') toggle = document.id(toggle);

		var elements = Array.from(attachTo),
			toggles = Array.from(toggle),
			allElements = [].append(elements).combine(toggles),
			self = this;

		if (!allElements.length) allElements = self.attachedElements;

		allElements.each(function(element){
			var i = self.attachedElements.indexOf(element);
			if (i < 0) return;

			var events = self.attachedEvents[i];
			element.removeEvents(events);
			delete self.attachedEvents[i];
			delete self.attachedElements[i];

			var toggleIndex = self.toggles.indexOf(element);
			if (toggleIndex != -1) delete self.toggles[toggleIndex];

			var inputIndex = self.inputs.indexOf(element);
			if (toggleIndex != -1) delete self.inputs[inputIndex];
		});
		return this;
	},

	destroy: function(){
		this.detach();
		return this.parent();
	}

});
/*!
---
name: Picker.Date
description: Creates a DatePicker, can be used for picking years/months/days and time, or all of them
authors: Arian Stolwijk
requires: [Picker, Picker.Attach, Locale.en-US.DatePicker, More/Locale, More/Date]
provides: Picker.Date
...
*/


(function(){

this.DatePicker = Picker.Date = new Class({

	Extends: Picker.Attach,

	options: {/*
		onSelect: function(date){},

		minDate: new Date('3/4/2010'), // Date object or a string
		maxDate: new Date('3/4/2011'), // same as minDate
		availableDates: {}, //
		invertAvailable: false,

		format: null,*/

		timePicker: false,
		timePickerOnly: false, // deprecated, use onlyView = 'time'
		timeWheelStep: 1, // 10,15,20,30

		yearPicker: true,
		yearsPerPage: 20,

		startDay: 1, // Sunday (0) through Saturday (6) - be aware that this may affect your layout, since the days on the right might have a different margin
		rtl: false,

		startView: 'days', // allowed values: {time, days, months, years}
		openLastView: false,
		pickOnly: false, // 'years', 'months', 'days', 'time'
		canAlwaysGoUp: ['months', 'days'],
		updateAll : false, //whether or not to update all inputs when selecting a date

		weeknumbers: false,

		// if you like to use your own translations
		months_abbr: null,
		days_abbr: null,
		years_title: function(date, options){
			var year = date.get('year');
			return year + '-' + (year + options.yearsPerPage - 1);
		},
		months_title: function(date, options){
			return date.get('year');
		},
		days_title: function(date, options){
			return date.format('%b %Y');
		},
		time_title: function(date, options){
			return (options.pickOnly == 'time') ? Locale.get('DatePicker.select_a_time') : date.format('%d %B, %Y');
		}
	},

	initialize: function(attachTo, options){
		this.parent(attachTo, options);

		this.setOptions(options);
		options = this.options;

		// If we only want to use one picker / backwards compatibility
		['year', 'month', 'day', 'time'].some(function(what){
			if (options[what + 'PickerOnly']){
				options.pickOnly = what;
				return true;
			}
			return false;
		});
		if (options.pickOnly){
			options[options.pickOnly + 'Picker'] = true;
			options.startView = options.pickOnly;
		}

		// backward compatibility for startView
		var newViews = ['days', 'months', 'years'];
		['month', 'year', 'decades'].some(function(what, i){
			return (options.startView == what) && (options.startView = newViews[i]);
		});

		options.canAlwaysGoUp = options.canAlwaysGoUp ? Array.from(options.canAlwaysGoUp) : [];

		// Set the min and max dates as Date objects
		if (options.minDate){
			if (!(options.minDate instanceof Date)) options.minDate = Date.parse(options.minDate);
			options.minDate.clearTime();
		}
		if (options.maxDate){
			if (!(options.maxDate instanceof Date)) options.maxDate = Date.parse(options.maxDate);
			options.maxDate.clearTime();
		}

		if (!options.format){
			options.format = (options.pickOnly != 'time') ? Locale.get('Date.shortDate') : '';
			if (options.timePicker) options.format = (options.format) + (options.format ? ' ' : '') + Locale.get('Date.shortTime');
		}

		// Some link or input has fired an event!
		this.addEvent('attached', function(event, element){

			// This is where we store the selected date
			if (!this.currentView || !options.openLastView) this.currentView = options.startView;

			this.date = limitDate(new Date(), options.minDate, options.maxDate);
			var tag = element.get('tag'), input;
			if (tag == 'input') input = element;
			else {
				var index = this.toggles.indexOf(element);
				if (this.inputs[index]) input = this.inputs[index];
			}
			this.getInputDate(input);
			this.input = input;
			this.setColumns(this.originalColumns);
		}.bind(this), true);

	},

	getInputDate: function(input){
		this.date = new Date();
		if (!input) return;
		var date = Date.parse(input.get('value'));
		if (date == null || !date.isValid()){
			var storeDate = input.retrieve('datepicker:value');
			if (storeDate) date = Date.parse(storeDate);
		}
		if (date != null && date.isValid()) this.date = date;
	},

	// Control the previous and next elements

	constructPicker: function(){
		this.parent();

		if (!this.options.rtl){
			this.previous = new Element('div.previous[html=&#171;]').inject(this.header);
			this.next = new Element('div.next[html=&#187;]').inject(this.header);
		} else {
			this.next = new Element('div.previous[html=&#171;]').inject(this.header);
			this.previous = new Element('div.next[html=&#187;]').inject(this.header);
		}
	},

	hidePrevious: function(_next, _show){
		this[_next ? 'next' : 'previous'].setStyle('display', _show ? 'block' : 'none');
		return this;
	},

	showPrevious: function(_next){
		return this.hidePrevious(_next, true);
	},

	setPreviousEvent: function(fn, _next){
		this[_next ? 'next' : 'previous'].removeEvents('click');
		if (fn) this[_next ? 'next' : 'previous'].addEvent('click', fn);
		return this;
	},

	hideNext: function(){
		return this.hidePrevious(true);
	},

	showNext: function(){
		return this.showPrevious(true);
	},

	setNextEvent: function(fn){
		return this.setPreviousEvent(fn, true);
	},

	setColumns: function(columns, view, date, viewFx){
		var ret = this.parent(columns), method;

		if ((view || this.currentView)
			&& (method = 'render' + (view || this.currentView).capitalize())
			&& this[method]
		) this[method](date || this.date.clone(), viewFx);

		return ret;
	},

	// Render the Pickers

	renderYears: function(date, fx){
		var options = this.options, pages = options.columns, perPage = options.yearsPerPage,
			_columns = [], _dates = [];
		this.dateElements = [];

		// start neatly at interval (eg. 1980 instead of 1987)
		date = date.clone().decrement('year', date.get('year') % perPage);

		var iterateDate = date.clone().decrement('year', Math.floor((pages - 1) / 2) * perPage);

		for (var i = pages; i--;){
			var _date = iterateDate.clone();
			_dates.push(_date);
			_columns.push(renderers.years(
				timesSelectors.years(options, _date.clone()),
				options,
				this.date.clone(),
				this.dateElements,
				function(date){
					if (options.pickOnly == 'years') this.select(date);
					else this.renderMonths(date, 'fade');
					this.date = date;
				}.bind(this)
			));
			iterateDate.increment('year', perPage);
		}

		this.setColumnsContent(_columns, fx);
		this.setTitle(_dates, options.years_title);

		// Set limits
		var limitLeft = (options.minDate && date.get('year') <= options.minDate.get('year')),
			limitRight = (options.maxDate && (date.get('year') + options.yearsPerPage) >= options.maxDate.get('year'));
		this[(limitLeft ? 'hide' : 'show') + 'Previous']();
		this[(limitRight ? 'hide' : 'show') + 'Next']();

		this.setPreviousEvent(function(){
			this.renderYears(date.decrement('year', perPage), 'left');
		}.bind(this));

		this.setNextEvent(function(){
			this.renderYears(date.increment('year', perPage), 'right');
		}.bind(this));

		// We can't go up!
		this.setTitleEvent(null);

		this.currentView = 'years';
	},

	renderMonths: function(date, fx){
		var options = this.options, years = options.columns, _columns = [], _dates = [],
			iterateDate = date.clone().decrement('year', Math.floor((years - 1) / 2));
		this.dateElements = [];

		for (var i = years; i--;){
			var _date = iterateDate.clone();
			_dates.push(_date);
			_columns.push(renderers.months(
				timesSelectors.months(options, _date.clone()),
				options,
				this.date.clone(),
				this.dateElements,
				function(date){
					if (options.pickOnly == 'months') this.select(date);
					else this.renderDays(date, 'fade');
					this.date = date;
				}.bind(this)
			));
			iterateDate.increment('year', 1);
		}

		this.setColumnsContent(_columns, fx);
		this.setTitle(_dates, options.months_title);

		// Set limits
		var year = date.get('year'),
			limitLeft = (options.minDate && year <= options.minDate.get('year')),
			limitRight = (options.maxDate && year >= options.maxDate.get('year'));
		this[(limitLeft ? 'hide' : 'show') + 'Previous']();
		this[(limitRight ? 'hide' : 'show') + 'Next']();

		this.setPreviousEvent(function(){
			this.renderMonths(date.decrement('year', years), 'left');
		}.bind(this));

		this.setNextEvent(function(){
			this.renderMonths(date.increment('year', years), 'right');
		}.bind(this));

		var canGoUp = options.yearPicker && (options.pickOnly != 'months' || options.canAlwaysGoUp.contains('months'));
		var titleEvent = (canGoUp) ? function(){
			this.renderYears(date, 'fade');
		}.bind(this) : null;
		this.setTitleEvent(titleEvent);

		this.currentView = 'months';
	},

	renderDays: function(date, fx){
		var options = this.options, months = options.columns, _columns = [], _dates = [],
			iterateDate = date.clone().decrement('month', Math.floor((months - 1) / 2));
		this.dateElements = [];

		for (var i = months; i--;){
			_date = iterateDate.clone();
			_dates.push(_date);
			_columns.push(renderers.days(
				timesSelectors.days(options, _date.clone()),
				options,
				this.date.clone(),
				this.dateElements,
				function(date){
					if (options.pickOnly == 'days' || !options.timePicker) this.select(date)
					else this.renderTime(date, 'fade');
					this.date = date;
				}.bind(this)
			));
			iterateDate.increment('month', 1);
		}

		this.setColumnsContent(_columns, fx);
		this.setTitle(_dates, options.days_title);

		var yearmonth = date.format('%Y%m').toInt(),
			limitLeft = (options.minDate && yearmonth <= options.minDate.format('%Y%m')),
			limitRight = (options.maxDate && yearmonth >= options.maxDate.format('%Y%m'));
		this[(limitLeft ? 'hide' : 'show') + 'Previous']();
		this[(limitRight ? 'hide' : 'show') + 'Next']();

		this.setPreviousEvent(function(){
			this.renderDays(date.decrement('month', months), 'left');
		}.bind(this));

		this.setNextEvent(function(){
			this.renderDays(date.increment('month', months), 'right');
		}.bind(this));

		var canGoUp = options.pickOnly != 'days' || options.canAlwaysGoUp.contains('days');
		var titleEvent = (canGoUp) ? function(){
			this.renderMonths(date, 'fade');
		}.bind(this) : null;
		this.setTitleEvent(titleEvent);

		this.currentView = 'days';
	},

	renderTime: function(date, fx){
		var options = this.options;
		this.setTitle(date, options.time_title);

		var originalColumns = this.originalColumns = options.columns;
		this.currentView = null; // otherwise you'd get crazy recursion
		if (originalColumns != 1) this.setColumns(1);

		this.setContent(renderers.time(
			options,
			date.clone(),
			function(date){
				this.select(date);
			}.bind(this)
		), fx);

		// Hide « and » buttons
		this.hidePrevious()
			.hideNext()
			.setPreviousEvent(null)
			.setNextEvent(null);

		var canGoUp = options.pickOnly != 'time' || options.canAlwaysGoUp.contains('time');
		var titleEvent = (canGoUp) ? function(){
			this.setColumns(originalColumns, 'days', date, 'fade');
		}.bind(this) : null;
		this.setTitleEvent(titleEvent);

		this.currentView = 'time';
	},

	select: function(date, all){
		this.date = date;
		var formatted = date.format(this.options.format),
			time = date.strftime(),
			inputs = (!this.options.updateAll && !all && this.input) ? [this.input] : this.inputs;

		inputs.each(function(input){
			input.set('value', formatted).store('datepicker:value', time).fireEvent('change');
		}, this);

		this.fireEvent('select', [date].concat(inputs));
		this.close();
		return this;
	}

});


// Renderers only output elements and calculate the limits!

var timesSelectors = {

	years: function(options, date){
		var times = [];
		for (var i = 0; i < options.yearsPerPage; i++){
			times.push(+date);
			date.increment('year', 1);
		}
		return times;
	},

	months: function(options, date){
		var times = [];
		date.set('month', 0);
		for (var i = 0; i <= 11; i++){
			times.push(+date);
			date.increment('month', 1);
		}
		return times;
	},

	days: function(options, date){
		var times = [];
		date.set('date', 1);
		while (date.get('day') != options.startDay) date.set('date', date.get('date') - 1);
		for (var i = 0; i < 42; i++){
			times.push(+date);
			date.increment('day',  1);
		}
		return times;
	}

};

var renderers = {

	years: function(years, options, currentDate, dateElements, fn){
		var container = new Element('div.years'),
			today = new Date(), element, classes;

		years.each(function(_year, i){
			var date = new Date(_year), year = date.get('year');

			classes = '.year.year' + i;
			if (year == today.get('year')) classes += '.today';
			if (year == currentDate.get('year')) classes += '.selected';
			element = new Element('div' + classes, {text: year}).inject(container);

			dateElements.push({element: element, time: _year});

			if (isUnavailable('year', date, options)) element.addClass('unavailable');
			else element.addEvent('click', fn.pass(date));
		});

		return container;
	},

	months: function(months, options, currentDate, dateElements, fn){
		var today = new Date(),
			month = today.get('month'),
			thisyear = today.get('year'),
			selectedyear = currentDate.get('year'),
			container = new Element('div.months'),
			monthsAbbr = options.months_abbr || Locale.get('Date.months_abbr'),
			element, classes;

		months.each(function(_month, i){
			var date = new Date(_month), year = date.get('year');

			classes = '.month.month' + (i + 1);
			if (i == month && year == thisyear) classes += '.today';
			if (i == currentDate.get('month') && year == selectedyear) classes += '.selected';
			element = new Element('div' + classes, {text: monthsAbbr[i]}).inject(container);

			dateElements.push({element: element, time: _month});

			if (isUnavailable('month', date, options)) element.addClass('unavailable');
			else element.addEvent('click', fn.pass(date));
		});

		return container;
	},

	days: function(days, options, currentDate, dateElements, fn){
		var month = new Date(days[14]).get('month'),
			todayString = new Date().toDateString(),
			currentString = currentDate.toDateString(),
			weeknumbers = options.weeknumbers,
			container = new Element('table.days' + (weeknumbers ? '.weeknumbers' : ''), {
				role: 'grid', 'aria-labelledby': this.titleID
			}),
			header = new Element('thead').inject(container),
			body = new Element('tbody').inject(container),
			titles = new Element('tr.titles').inject(header),
			localeDaysShort = options.days_abbr || Locale.get('Date.days_abbr'),
			day, classes, element, weekcontainer, dateString,
			where = options.rtl ? 'top' : 'bottom';

		if (weeknumbers) new Element('th.title.day.weeknumber', {
			text: Locale.get('DatePicker.week')
		}).inject(titles);

		for (day = options.startDay; day < (options.startDay + 7); day++){
			new Element('th.title.day.day' + (day % 7), {
				text: localeDaysShort[(day % 7)],
				role: 'columnheader'
			}).inject(titles, where);
		}

		days.each(function(_date, i){
			var date = new Date(_date);

			if (i % 7 == 0){
				weekcontainer = new Element('tr.week.week' + (Math.floor(i / 7))).set('role', 'row').inject(body);
				if (weeknumbers) new Element('th.day.weeknumber', {text: date.get('week'), scope: 'row', role: 'rowheader'}).inject(weekcontainer);
			}

			dateString = date.toDateString();
			classes = '.day.day' + date.get('day');
			if (dateString == todayString) classes += '.today';
			if (date.get('month') != month) classes += '.otherMonth';
			element = new Element('td' + classes, {text: date.getDate(), role: 'gridcell'}).inject(weekcontainer, where);

			if (dateString == currentString) element.addClass('selected').set('aria-selected', 'true');
			else element.set('aria-selected', 'false');

			dateElements.push({element: element, time: _date});

			if (isUnavailable('date', date, options)) element.addClass('unavailable');
			else element.addEvent('click', fn.pass(date.clone()));
		});

		return container;
	},

	time: function(options, date, fn){
		var container = new Element('div.time'),
			// make sure that the minutes are timeWheelStep * k
			initMinutes = (date.get('minutes') / options.timeWheelStep).round() * options.timeWheelStep

		if (initMinutes >= 60) initMinutes = 0;
		date.set('minutes', initMinutes);

		var hoursInput = new Element('input.hour[type=text]', {
			title: Locale.get('DatePicker.use_mouse_wheel'),
			value: date.format('%H'),
			events: {
				click: function(event){
					event.target.focus();
					event.stop();
				},
				mousewheel: function(event){
					event.stop();
					hoursInput.focus();
					var value = hoursInput.get('value').toInt();
					value = (event.wheel > 0) ? ((value < 23) ? value + 1 : 0)
						: ((value > 0) ? value - 1 : 23)
					date.set('hours', value);
					hoursInput.set('value', date.format('%H'));
				}.bind(this)
			},
			maxlength: 2
		}).inject(container);

		var minutesInput = new Element('input.minutes[type=text]', {
			title: Locale.get('DatePicker.use_mouse_wheel'),
			value: date.format('%M'),
			events: {
				click: function(event){
					event.target.focus();
					event.stop();
				},
				mousewheel: function(event){
					event.stop();
					minutesInput.focus();
					var value = minutesInput.get('value').toInt();
					value = (event.wheel > 0) ? ((value < 59) ? (value + options.timeWheelStep) : 0)
						: ((value > 0) ? (value - options.timeWheelStep) : (60 - options.timeWheelStep));
					if (value >= 60) value = 0;
					date.set('minutes', value);
					minutesInput.set('value', date.format('%M'));
				}.bind(this)
			},
			maxlength: 2
		}).inject(container);

		new Element('div.separator[text=:]').inject(container);

		new Element('input.ok[type=submit]', {
			value: Locale.get('DatePicker.time_confirm_button'),
			events: {click: function(event){
				event.stop();
				date.set({
					hours: hoursInput.get('value').toInt(),
					minutes: minutesInput.get('value').toInt()
				});
				fn(date.clone());
			}}
		}).inject(container);

		return container;
	}

};


Picker.Date.defineRenderer = function(name, fn){
	renderers[name] = fn;
	return this;
};

var limitDate = function(date, min, max){
	if (min && date < min) return min;
	if (max && date > max) return max;
	return date;
};

var isUnavailable = function(type, date, options){
	var minDate = options.minDate,
		maxDate = options.maxDate,
		availableDates = options.availableDates,
		year, month, day, ms;

	if (!minDate && !maxDate && !availableDates) return false;
	date.clearTime();

	if (type == 'year'){
		year = date.get('year');
		return (
			(minDate && year < minDate.get('year')) ||
			(maxDate && year > maxDate.get('year')) ||
			(
				(availableDates != null &&  !options.invertAvailable) && (
					availableDates[year] == null ||
					Object.getLength(availableDates[year]) == 0 ||
					Object.getLength(
						Object.filter(availableDates[year], function(days){
							return (days.length > 0);
						})
					) == 0
				)
			)
		);
	}

	if (type == 'month'){
		year = date.get('year');
		month = date.get('month') + 1;
		ms = date.format('%Y%m').toInt();
		return (
			(minDate && ms < minDate.format('%Y%m').toInt()) ||
			(maxDate && ms > maxDate.format('%Y%m').toInt()) ||
			(
				(availableDates != null && !options.invertAvailable) && (
					availableDates[year] == null ||
					availableDates[year][month] == null ||
					availableDates[year][month].length == 0
				)
			)
		);
	}

	// type == 'date'
	year = date.get('year');
	month = date.get('month') + 1;
	day = date.get('date');

	var dateAllow = (minDate && date < minDate) || (minDate && date > maxDate);
	if (availableDates != null){
		dateAllow = dateAllow
			|| availableDates[year] == null
			|| availableDates[year][month] == null
			|| !availableDates[year][month].contains(day);
		if (options.invertAvailable) dateAllow = !dateAllow;
	}

	return dateAllow;
};

})();
/*!
 * @version   $Id: roksprocket.js 30474 2016-10-27 20:49:18Z djamil $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){

	window.addEvent('domready', function(){
		if (typeof Calendar != 'undefined' && typeof Calendar.prototype.showAtElement != 'undefined'){
			Calendar.prototype.showAtElement = function (el, opts) {
				var self = this;

				var p = Calendar.getAbsolutePos(el),
					IF = document.id(this.params.inputField);

				if (IF){
					p = IF.getPosition();
					this.showAt(p.x, p.y + IF.offsetHeight + 2);
					return true;
				}

				if (!opts || typeof opts != "string") {
					this.showAt(p.x, p.y + el.offsetHeight);
					return true;
				}
				function fixPosition(box) {
					if (box.x < 0)
					box.x = 0;
					if (box.y < 0)
					box.y = 0;
					var cp = document.createElement("div");
					var s = cp.style;
					s.position = "absolute";
					s.right = s.bottom = s.width = s.height = "0px";
					document.body.appendChild(cp);
					var br = Calendar.getAbsolutePos(cp);
					document.body.removeChild(cp);
					if (Calendar.is_ie) {
						br.y += document.body.scrollTop;
						br.x += document.body.scrollLeft;
					} else {
						br.y += window.scrollY;
						br.x += window.scrollX;
					}
					var tmp = box.x + box.width - br.x;
					if (tmp > 0) box.x -= tmp;
					tmp = box.y + box.height - br.y;
					if (tmp > 0) box.y -= tmp;
				}
				this.element.style.display = "block";
				Calendar.continuation_for_the_khtml_browser = function() {
					var w = self.element.offsetWidth;
					var h = self.element.offsetHeight;
					self.element.style.display = "none";
					var valign = opts.substr(0, 1);
					var halign = "l";
					if (opts.length > 1) {
						halign = opts.substr(1, 1);
					}
					// vertical alignment
					switch (valign) {
						case "T": p.y -= h; break;
						case "B": p.y += el.offsetHeight; break;
						case "C": p.y += (el.offsetHeight - h) / 2; break;
						case "t": p.y += el.offsetHeight - h; break;
						case "b": break; // already there
					}
					// horizontal alignment
					switch (halign) {
						case "L": p.x -= w; break;
						case "R": p.x += el.offsetWidth; break;
						case "C": p.x += (el.offsetWidth - w) / 2; break;
						case "l": p.x += el.offsetWidth - w; break;
						case "r": break; // already there
					}
					p.width = w;
					p.height = h + 40;
					self.monthsCombo.style.display = "none";
					fixPosition(p);
					self.showAt(p.x, p.y);
				};
				if (Calendar.is_khtml)
					setTimeout("Calendar.continuation_for_the_khtml_browser()", 10);
				else
					Calendar.continuation_for_the_khtml_browser();
			};
		}
	});
})());
/*!
// Simple Set Clipboard System
// Author: Joseph Huckaby
*/
((function(){

	var SWFFile = 'ZeroClipboard' + (Browser.Plugins.Flash && Browser.Plugins.Flash.version >= 10 ? '10' : '') + '.swf';

	this.ZeroClipboard = {

		version: "1.0.7",
		clients: {}, // registered upload clients on page, indexed by id
		moviePath: '/components/com_roksprocket/assets/js/' + SWFFile, // URL to movie
		nextId: 1, // ID of next movie

		$: function(thingy) {

			return document.id(thingy) || document.getElement(thingy) || null;

			// simple DOM lookup utility function
			/*if (typeof(thingy) == 'string') thingy = document.getElementById(thingy);
			if (!thingy.addClass) {
				// extend element with a few useful methods
				thingy.hide = function() { this.style.display = 'none'; };
				thingy.show = function() { this.style.display = ''; };
				thingy.addClass = function(name) { this.removeClass(name); this.className += ' ' + name; };
				thingy.removeClass = function(name) {
					var classes = this.className.split(/\s+/);
					var idx = -1;
					for (var k = 0; k < classes.length; k++) {
						if (classes[k] == name) { idx = k; k = classes.length; }
					}
					if (idx > -1) {
						classes.splice( idx, 1 );
						this.className = classes.join(' ');
					}
					return this;
				};
				thingy.hasClass = function(name) {
					return !!this.className.match( new RegExp("\\s*" + name + "\\s*") );
				};
			}
			return thingy;*/
		},

		setMoviePath: function(path) {
			// set path to ZeroClipboard.swf
			this.moviePath = path;
		},

		dispatch: function(id, eventName, args) {
			// receive event from flash movie, send to client
			var client = this.clients[id];
			if (client) {
				client.receiveEvent(eventName, args);
			}
		},

		register: function(id, client) {
			// register new client to receive events
			this.clients[id] = client;
		},

		getDOMObjectPosition: function(obj, stopObj) {
			// get absolute coordinates for dom element
			var info = {
				left: 0,
				top: 0,
				width: obj.width ? obj.width : obj.offsetWidth,
				height: obj.height ? obj.height : obj.offsetHeight
			};

			while (obj && (obj != stopObj)) {
				info.left += obj.offsetLeft;
				info.top += obj.offsetTop;
				obj = obj.offsetParent;
			}

			return info;
		},

		Client: function(elem) {
			// constructor for new simple upload client
			this.handlers = {};

			// unique ID
			this.id = ZeroClipboard.nextId++;
			this.movieId = 'ZeroClipboardMovie_' + this.id;

			// register client with singleton to receive flash events
			ZeroClipboard.register(this.id, this);

			// create movie
			if (elem) this.glue(elem);
		}
	};

	ZeroClipboard.Client.prototype = {

		id: 0, // unique ID for us
		ready: false, // whether movie is ready to receive events or not
		movie: null, // reference to movie object
		clipText: '', // text to copy to clipboard
		handCursorEnabled: true, // whether to show hand cursor, or default pointer cursor
		cssEffects: true, // enable CSS mouse effects on dom container
		handlers: null, // user event handlers

		glue: function(elem, appendElem, stylesToAdd) {
			// glue to DOM element
			// elem can be ID or actual DOM element object
			this.domElement = ZeroClipboard.$(elem);

			// float just above object, or zIndex 99 if dom element isn't set
			var zIndex = 99;
			if (this.domElement.style.zIndex) {
				zIndex = parseInt(this.domElement.style.zIndex, 10) + 1;
			}

			if (typeof(appendElem) == 'string') {
				appendElem = ZeroClipboard.$(appendElem);
			}
			else if (typeof(appendElem) == 'undefined') {
				appendElem = document.getElementsByTagName('body')[0];
			}

			// find X/Y position of domElement
			var box = ZeroClipboard.getDOMObjectPosition(this.domElement, appendElem);

			// create floating DIV above element
			this.div = document.createElement('div');
			var style = this.div.style;
			style.position = 'absolute';
			style.left = '' + box.left + 'px';
			style.top = '' + box.top + 'px';
			style.width = '' + box.width + 'px';
			style.height = '' + box.height + 'px';
			style.zIndex = zIndex;

			if (typeof(stylesToAdd) == 'object') {
				for (var addedStyle in stylesToAdd) {
					style[addedStyle] = stylesToAdd[addedStyle];
				}
			}

			// style.backgroundColor = '#f00'; // debug

			appendElem.appendChild(this.div);

			this.div.innerHTML = this.getHTML( box.width, box.height );
		},

		getHTML: function(width, height) {
			// return HTML for movie
			var html = '';
			var flashvars = 'id=' + this.id +
				'&width=' + width +
				'&height=' + height;

			if (navigator.userAgent.match(/MSIE/)) {
				// IE gets an OBJECT tag
				var protocol = location.href.match(/^https/i) ? 'https://' : 'http://';
				html += '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="'+protocol+'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'+width+'" height="'+height+'" id="'+this.movieId+'" align="middle"><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="false" /><param name="movie" value="'+ZeroClipboard.moviePath+'" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="best" /><param name="bgcolor" value="#ffffff" /><param name="flashvars" value="'+flashvars+'"/><param name="wmode" value="transparent"/></object>';
			}
			else {
				// all other browsers get an EMBED tag
				html += '<embed id="'+this.movieId+'" src="'+ZeroClipboard.moviePath+'" loop="false" menu="false" quality="best" bgcolor="#ffffff" width="'+width+'" height="'+height+'" name="'+this.movieId+'" align="middle" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+flashvars+'" wmode="transparent" />';
			}
			return html;
		},

		hide: function() {
			// temporarily hide floater offscreen
			if (this.div) {
				this.div.style.left = '-2000px';
			}
		},

		show: function() {
			// show ourselves after a call to hide()
			this.reposition();
		},

		destroy: function() {
			// destroy control and floater
			if (this.domElement && this.div) {
				this.hide();
				this.div.innerHTML = '';

				var body = document.getElementsByTagName('body')[0];
				try { body.removeChild( this.div ); } catch(e) {}

				this.domElement = null;
				this.div = null;
			}
		},

		reposition: function(elem) {
			// reposition our floating div, optionally to new container
			// warning: container CANNOT change size, only position
			if (elem) {
				this.domElement = ZeroClipboard.$(elem);
				if (!this.domElement) this.hide();
			}

			if (this.domElement && this.div) {
				var box = ZeroClipboard.getDOMObjectPosition(this.domElement);
				var style = this.div.style;
				style.left = '' + box.left + 'px';
				style.top = '' + box.top + 'px';
			}
		},

		setText: function(newText) {
			// set text to be copied to clipboard
			this.clipText = newText;
			if (this.ready) this.movie.setText(newText);
		},

		addEventListener: function(eventName, func) {
			// add user event listener for event
			// event types: load, queueStart, fileStart, fileComplete, queueComplete, progress, error, cancel
			eventName = eventName.toString().toLowerCase().replace(/^on/, '');
			if (!this.handlers[eventName]) this.handlers[eventName] = [];
			this.handlers[eventName].push(func);
		},

		setHandCursor: function(enabled) {
			// enable hand cursor (true), or default arrow cursor (false)
			this.handCursorEnabled = enabled;
			if (this.ready) this.movie.setHandCursor(enabled);
		},

		setCSSEffects: function(enabled) {
			// enable or disable CSS effects on DOM container
			this.cssEffects = !!enabled;
		},

		receiveEvent: function(eventName, args) {
			// receive event from flash
			eventName = eventName.toString().toLowerCase().replace(/^on/, '');

			var self = this;

			// special behavior for certain events
			switch (eventName) {
				case 'load':
					// movie claims it is ready, but in IE this isn't always the case...
					// bug fix: Cannot extend EMBED DOM elements in Firefox, must use traditional function
					this.movie = document.getElementById(this.movieId);
					if (!this.movie) {
						self = this;
						setTimeout( function() { self.receiveEvent('load', null); }, 1 );
						return;
					}

					// firefox on pc needs a "kick" in order to set these in certain cases
					if (!this.ready && navigator.userAgent.match(/Firefox/) && navigator.userAgent.match(/Windows/)) {
						self = this;
						setTimeout( function() { self.receiveEvent('load', null); }, 100 );
						this.ready = true;
						return;
					}

					this.ready = true;
					this.movie.setText( this.clipText );
					this.movie.setHandCursor( this.handCursorEnabled );
					break;

				case 'mouseover':
					if (this.domElement && this.cssEffects) {
						this.domElement.addClass('hover');
						if (this.recoverActive) this.domElement.addClass('active');
					}
					break;

				case 'mouseout':
					if (this.domElement && this.cssEffects) {
						this.recoverActive = false;
						if (this.domElement.hasClass('active')) {
							this.domElement.removeClass('active');
							this.recoverActive = true;
						}
						this.domElement.removeClass('hover');
					}
					break;

				case 'mousedown':
					if (this.domElement && this.cssEffects) {
						this.domElement.addClass('active');
					}
					break;

				case 'mouseup':
					if (this.domElement && this.cssEffects) {
						this.domElement.removeClass('active');
						this.recoverActive = false;
					}
					break;
			} // switch eventName

			if (this.handlers[eventName]) {
				for (var idx = 0, len = this.handlers[eventName].length; idx < len; idx++) {
					var func = this.handlers[eventName][idx];

					if (typeof(func) == 'function') {
						// actual function reference
						func(this, args);
					}
					else if ((typeof(func) == 'object') && (func.length == 2)) {
						// PHP style object + method, i.e. [myObject, 'myMethod']
						func[0][ func[1] ](this, args);
					}
					else if (typeof(func) == 'string') {
						// name of function
						window[func](this, args);
					}
				} // foreach event handler defined
			} // user defined handler for event
		}

	};

})());
