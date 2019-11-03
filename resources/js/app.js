/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

/*
Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('vendors', require('./components/Vendors.vue').default);
Vue.component('work-progress', require('./components/WorkProgress.vue').default);
Vue.component('news-list', require('./components/NewsList.vue').default);
Vue.component('vendor-projects', require('./components/Projects.vue').default);
Vue.component('project-media', require('./components/ProjectMedia.vue').default);
Vue.component('circle-progress', require('./components/CircleProgress.vue').default);
Vue.component('all-projects', require('./components/AllProjects.vue').default);
Vue.component('Access', require('./components/Access.vue').default);
Vue.component('RoadsMapNew', require('./components/RoadsMapNew.vue').default);
*/

Vue.component('RoadMapOsm', require('./components/RoadMapOSM.vue').default);
Vue.component('RoadsMap', require('./components/RoadsMap.vue').default);

/** 
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 
let RoadMapOsm = document.querySelector('#road-map-osm');
if (RoadMapOsm) {RoadMapOsm = new Vue({el: '#road-map-osm'})}

let RoadsMap = document.querySelector('#roads-map');
if (RoadsMap) {RoadsMap = new Vue({el: '#roads-map'})}

/*
let Vendors = document.querySelector('#vendors');
if (Vendors) {Vendors = new Vue({el: '#vendors'})}

let WorkProgress = document.querySelector('#work-progress');
if (WorkProgress) {WorkProgress = new Vue({el: '#work-progress'})}

let NewsList = document.querySelector('#news-list');
if (NewsList) {NewsList = new Vue({el: '#news-list'})}

let VendorProjects = document.querySelector('#vendor-projects');
if (VendorProjects) {VendorProjects = new Vue({el: '#vendor-projects'})}

let ProjectMedia = document.querySelector('#project-media');
if (ProjectMedia) {ProjectMedia = new Vue({el: '#project-media'})}

let CircleProgress = document.querySelector('#circle-progress');
if (CircleProgress) {CircleProgress = new Vue({el: '#circle-progress'})}

let AllProjects = document.querySelector('#all-projects');
if (AllProjects) {AllProjects = new Vue({el: '#all-projects'})}

let Access = document.querySelector('#access');
if (Access) {Access = new Vue({el: '#access'})}

let RoadsMapNew = document.querySelector('#roads-map-new');
if (RoadsMapNew) {RoadsMapNew = new Vue({el: '#roads-map-new'})}
*/