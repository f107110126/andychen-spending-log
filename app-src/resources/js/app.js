/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

var Turbolinks = require('turbolinks');
Turbolinks.start();

window.Vue = require('vue').default;

var TurbolinksAdapter = require('vue-turbolinks');
Vue.use(TurbolinksAdapter); // <-- this will not execute `$destroy` method.

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

// Vue.component('example-component', require('./components/ExampleComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

/**
 * Since `Turbolinks` might been enable, it will change the `life-cycle` of the javascript.
 * The `dependencies` should only require once, so when browser loading full-page it will been re-loaded.
 * But, when `Turbolinks` been enabled, it will not re-load the javascript file. And, also not trigger
 * `jQuery(document).ready()`, but fire `turbolinks:load` event. So, here for smoothly we wrap the codes
 * should been execute with `jQuery(document).ready()` in to `per_page_init_func`. Calling on normal ready
 * and `turbolinks:load`, but `per-page-once`.
 *
 * `document.ready` event
 * Also, normally, when the browser loading a new-page, the old-page will no more effect. But, the Vue
 * instance once been created with Turbolinks enabled, the instance will been exist forever, until calling
 * the instance `$destroy` method.
 *
 * @author Andy.Chen <f107110126@gmail.com>
 */

let app; // point to `Vue` instance, but per-page independent.
const per_page_init_func = (e) => {
    console.log(e.type);
    // make sure at least notify previous `Vue` instance 'destroy yourself'.
    if (app instanceof Vue) app.$destroy();
    app = new Vue({
        el: '#vue-app'
    });

    // disable `Turbolinks` on all hyperlink start with hash-mark
    // even the dom created by vue.
    if (typeof Turbolinks !== 'undefined') {
        document.querySelectorAll('a[href^="#"]')
            .forEach(element => {
                element.setAttribute('data-turbolinks', false);
            });
    }
};

if (typeof Turbolinks === 'undefined') {
    document.addEventListener('DOMContentLoaded', per_page_init_func);
} else {
    document.addEventListener('turbolinks:load', per_page_init_func);

    document.addEventListener('turbolinks:before-render', () => {
        // since `per_page_func` doing this, here doesn't need any more.
        // if (app instanceof Vue) {
        //     app.$destroy();
        // }
    });
}
