define(['exports'], (function (exports) { 'use strict';

    /**
     * FormValidation (https://formvalidation.io)
     * The best validation library for JavaScript
     * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
     */
    function color() {
        var SUPPORTED_TYPES = ['hex', 'rgb', 'rgba', 'hsl', 'hsla', 'keyword'];
        var KEYWORD_COLORS = [
            // Colors start with A
            'aliceblue',
            'antiquewhite',
            'aqua',
            'aquamarine',
            'azure',
            // B
            'beige',
            'bisque',
            'black',
            'blanchedalmond',
            'blue',
            'blueviolet',
            'brown',
            'burlywood',
            // C
            'cadetblue',
            'chartreuse',
            'chocolate',
            'coral',
            'cornflowerblue',
            'cornsilk',
            'crimson',
            'cyan',
            // D
            'darkblue',
            'darkcyan',
            'darkgoldenrod',
            'darkgray',
            'darkgreen',
            'darkgrey',
            'darkkhaki',
            'darkmagenta',
            'darkolivegreen',
            'darkorange',
            'darkorchid',
            'darkred',
            'darksalmon',
            'darkseagreen',
            'darkslateblue',
            'darkslategray',
            'darkslategrey',
            'darkturquoise',
            'darkviolet',
            'deeppink',
            'deepskyblue',
            'dimgray',
            'dimgrey',
            'dodgerblue',
            // F
            'firebrick',
            'floralwhite',
            'forestgreen',
            'fuchsia',
            // G
            'gainsboro',
            'ghostwhite',
            'gold',
            'goldenrod',
            'gray',
            'green',
            'greenyellow',
            'grey',
            // H
            'honeydew',
            'hotpink',
            // I
            'indianred',
            'indigo',
            'ivory',
            // K
            'khaki',
            // L
            'lavender',
            'lavenderblush',
            'lawngreen',
            'lemonchiffon',
            'lightblue',
            'lightcoral',
            'lightcyan',
            'lightgoldenrodyellow',
            'lightgray',
            'lightgreen',
            'lightgrey',
            'lightpink',
            'lightsalmon',
            'lightseagreen',
            'lightskyblue',
            'lightslategray',
            'lightslategrey',
            'lightsteelblue',
            'lightyellow',
            'lime',
            'limegreen',
            'linen',
            // M
            'magenta',
            'maroon',
            'mediumaquamarine',
            'mediumblue',
            'mediumorchid',
            'mediumpurple',
            'mediumseagreen',
            'mediumslateblue',
            'mediumspringgreen',
            'mediumturquoise',
            'mediumvioletred',
            'midnightblue',
            'mintcream',
            'mistyrose',
            'moccasin',
            // N
            'navajowhite',
            'navy',
            // O
            'oldlace',
            'olive',
            'olivedrab',
            'orange',
            'orangered',
            'orchid',
            // P
            'palegoldenrod',
            'palegreen',
            'paleturquoise',
            'palevioletred',
            'papayawhip',
            'peachpuff',
            'peru',
            'pink',
            'plum',
            'powderblue',
            'purple',
            // R
            'red',
            'rosybrown',
            'royalblue',
            // S
            'saddlebrown',
            'salmon',
            'sandybrown',
            'seagreen',
            'seashell',
            'sienna',
            'silver',
            'skyblue',
            'slateblue',
            'slategray',
            'slategrey',
            'snow',
            'springgreen',
            'steelblue',
            // T
            'tan',
            'teal',
            'thistle',
            'tomato',
            'transparent',
            'turquoise',
            // V
            'violet',
            // W
            'wheat',
            'white',
            'whitesmoke',
            // Y
            'yellow',
            'yellowgreen',
        ];
        var hex = function (value) {
            return /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(value);
        };
        var hsl = function (value) {
            return /^hsl\((\s*(-?\d+)\s*,)(\s*(\b(0?\d{1,2}|100)\b%)\s*,)(\s*(\b(0?\d{1,2}|100)\b%)\s*)\)$/.test(value);
        };
        var hsla = function (value) {
            return /^hsla\((\s*(-?\d+)\s*,)(\s*(\b(0?\d{1,2}|100)\b%)\s*,){2}(\s*(0?(\.\d+)?|1(\.0+)?)\s*)\)$/.test(value);
        };
        var keyword = function (value) {
            return KEYWORD_COLORS.indexOf(value) >= 0;
        };
        var rgb = function (value) {
            return (/^rgb\((\s*(\b([01]?\d{1,2}|2[0-4]\d|25[0-5])\b)\s*,){2}(\s*(\b([01]?\d{1,2}|2[0-4]\d|25[0-5])\b)\s*)\)$/.test(value) || /^rgb\((\s*(\b(0?\d{1,2}|100)\b%)\s*,){2}(\s*(\b(0?\d{1,2}|100)\b%)\s*)\)$/.test(value));
        };
        var rgba = function (value) {
            return (/^rgba\((\s*(\b([01]?\d{1,2}|2[0-4]\d|25[0-5])\b)\s*,){3}(\s*(0?(\.\d+)?|1(\.0+)?)\s*)\)$/.test(value) ||
                /^rgba\((\s*(\b(0?\d{1,2}|100)\b%)\s*,){3}(\s*(0?(\.\d+)?|1(\.0+)?)\s*)\)$/.test(value));
        };
        return {
            /**
             * Return true if the input value is a valid color
             * @returns {boolean}
             */
            validate: function (input) {
                if (input.value === '') {
                    return { valid: true };
                }
                var types = typeof input.options.type === 'string'
                    ? input.options.type.toString().replace(/s/g, '').split(',')
                    : input.options.type || SUPPORTED_TYPES;
                for (var _i = 0, types_1 = types; _i < types_1.length; _i++) {
                    var type = types_1[_i];
                    var tpe = type.toLowerCase();
                    if (SUPPORTED_TYPES.indexOf(tpe) === -1) {
                        continue;
                    }
                    var result = true;
                    switch (tpe) {
                        case 'hex':
                            result = hex(input.value);
                            break;
                        case 'hsl':
                            result = hsl(input.value);
                            break;
                        case 'hsla':
                            result = hsla(input.value);
                            break;
                        case 'keyword':
                            result = keyword(input.value);
                            break;
                        case 'rgb':
                            result = rgb(input.value);
                            break;
                        case 'rgba':
                            result = rgba(input.value);
                            break;
                    }
                    if (result) {
                        return { valid: true };
                    }
                }
                return { valid: false };
            },
        };
    }

    exports.color = color;

}));
