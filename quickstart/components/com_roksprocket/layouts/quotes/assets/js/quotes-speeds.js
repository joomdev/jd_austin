/*!
 * @version   $Id: quotes-speeds.js 10889 2013-05-30 07:48:35Z btowles $
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2018 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){
    var AnimationsSpeed = {
        fade: {
            duration: '250ms'
        },

        fadeDelay: {
            duration: '250ms',
            delay: 75
        },

        slide: {
            duration: '250ms'
        },

        flyIn: {
            duration: '500ms',
            delay: 90
        },

        fallDown: {
            duration: '250ms',
            delay: 50
        },

        floatUp: {
            duration: '250ms',
            delay: 50
        },

        scaleOut: {
            duration: '250ms',
            delay: 100
        },

        scaleIn: {
            duration: '250ms',
            delay: 100
        }
    };

    this.RokSprocket.Quotes.prototype.AnimationsSpeed = Object.merge({}, this.RokSprocket.Quotes.prototype.AnimationsSpeed, AnimationsSpeed);

})());
