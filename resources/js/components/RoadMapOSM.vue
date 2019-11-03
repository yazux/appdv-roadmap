<template>
    <div v-bind:class="{'component component-roads-map-osm': true, 'loading': loading}">
        <p v-if="loading" id="loading" class="component-road-loading"><span>Подождите, идёт загрузка данных...</span></p>

        <div id="map" ref="map" style="width: 100%; height: 100%;"></div>

        <div class="contoll">
            <label for="view-type">Тип отображения</label>
            <div class="select-container">
                <select v-model="viewType" class="select" id="view-type">
                    <option class="select-option" v-for="(opt, i) in viewTypes" v-bind:value="opt.name" v-bind:key="i">{{opt.label}}</option>
                </select>
            </div>

            <hr v-if="viewType == 'categories' || viewType == 'covers'">

            <div class="categories-type" v-if="categoriesType && categoriesType.length && viewType == 'categories'">
                <div class="categories-item">
                    <span class="color" v-bind:style="'background-color: rgb(255, 202, 134);'"></span>
                    <span>Без категории</span>
                </div>
                <div class="categories-item" v-for="category in categoriesType" v-bind:key="category.id" v-if="category.show">
                    <span class="color" v-bind:style="'background-color: ' + category.color_rgb + ';'"></span>
                    <span>{{category.name}}</span>
                </div>
            </div>

            <div class="categories-type" v-if="coversType && coversType.length && viewType == 'covers'">
                <div class="categories-item">
                    <span class="color" v-bind:style="'background-color: rgb(255, 202, 134);'"></span>
                    <span>Покрытие не указано</span>
                </div>
                <div class="categories-item" v-for="cover in coversType" v-bind:key="cover.id" v-if="cover.show">
                    <span class="color" v-bind:style="'background-color: ' + cover.color_rgb + ';'"></span>
                    <span>{{cover.name}}</span>
                </div>
            </div>

            <div class="diagnostic-type" v-if="viewType == 'diagnostic'">
                <div class="categories-item">
                    <span class="color" v-bind:style="'background-color: rgb(255, 202, 134);'"></span>
                    <span>Не указано</span>
                </div>
                <div class="categories-item">
                    <span class="color" v-bind:style="'background-color: rgb(51, 204, 0);'"></span>
                    <span>Соответствует нормам</span>
                </div>
                <div class="categories-item">
                    <span class="color" v-bind:style="'background-color: rgb(255, 0, 0);'"></span>
                    <span>Не соответствует нормам</span>
                </div>
            </div>
            
        </div>
        <a class="copyright" target="_blank" href="http://appdv.ru" style="background-image: url('/public/img/map-logo.jpg');"> </a>
    </div>
</template>
<script>
    import axios from 'axios';
    import { setTimeout } from 'timers';
    import * as leaflet from "leaflet";
    //import { GestureHandling } from "leaflet-gesture-handling";
    import "leaflet/dist/leaflet.css";
    //import "leaflet-gesture-handling/dist/leaflet-gesture-handling.css";

    export default {
        name: 'roads-map',
        props: {},
        components: {},
        data: function () {
            return {
                map: null,
                messages: [],
                roads: [],

                diagnostic: null,

                categories: null,
                categoriesType: [],
                
                covers: null,
                coversType: [],
                
                icons: [],

                layers: {
                    tracks: null,
                    covers: null,
                    categories: null,
                    diagnostic: null,
                    messages: null
                },

                viewType: 'covers',
                viewTypes: [
                   {name: 'covers', label: 'Покрытия'},
                   {name: 'categories', label: 'Категории'},
                   {name: 'diagnostic', label: 'Данные диагностики'}
                ],

                
                customIcon: null,
                
                loading: false,
                currentZoom: 10,
                geoObjectsCountAdded: 0,
                objectManager: null
            }
        },
        created() {
            this.init();
        },
        computed: {
            endPoint() {
                return location.protocol + '//'
                    + location.hostname
                    + (location.port ? (':' + location.port) : '');
            },
            ajaxEndPont() {
                return this.endPoint + '/ajax';
            },
        },
        watch: {
            'viewType'(val) {
                this.$nextTick(() => {
                    //this.hideLayers();
                    this.clearLayers();
                    switch (val) {
                        case 'categories': 
                            if (this.categories) {
                                //this.layers.categories.bringToFront();
                                //this.layers.categories.setStyle({opacity: 1});
                                this.buildCategories();
                            } else this.getCategoriesTrack().then(r => {if (r) this.buildCategories();});
                            this.hideIcons();
                        break;
                        case 'covers':
                            if (this.covers) {
                                //this.layers.covers.bringToFront();
                                //this.layers.covers.setStyle({opacity: 1});

                                this.buildCovers();
                                this.buildMessages();
                                this.layers.messages.setStyle({opacity: 1});
                                this.layers.messages.bringToFront();

                            } else this.getCoversTrack().then(r => {if (r) this.buildCovers();});
                        break;
                        case 'diagnostic':
                            if (this.diagnostic) {
                                //this.layers.diagnostic.bringToFront();
                                //this.layers.diagnostic.setStyle({opacity: 1});
                                this.buildDiagnostic();
                            } else this.getDiagnosticTracks().then(r => {if (r) this.buildDiagnostic();});
                            this.hideIcons();
                        break;
                    }
                });
            }
        },
        methods: {
            init() {
                this.$nextTick(() => {
                    //leaflet.Map.addInitHook("addHandler", "gestureHandling", GestureHandling);
                    this.map = new leaflet.Map('body', {
                        center: [50.34031567, 127.53951720], 
                        zoom: 10,
                        minZoom: 6,
                        maxZoom: 15,
                        gestureHandling: true,
                        gestureHandlingOptions: {
                            duration: 2000,
                            text: {
                                touch: "Для перемещения и зума карты используйте 2 пальца",
                                scroll: "Для перемещения карты используйте ЛКМ, для зума Ctrl + scroll",
                                scrollMac: "Для перемещения карты используйте ЛКМ, для зума Cmd + scroll"
                            }
                        }
                    });

                    leaflet.tileLayer(
                        'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', 
                        {attribution: '&copy; <a rel="nofollow" href="http://osm.org/copyright">OpenStreetMap</a> contributors'}
                    ).addTo(this.map);

     
                    
                    this.customIcon = leaflet.Icon.extend({
                        options: {
                            iconSize:     [30, 26],
                            iconAnchor:   [15, 13],
                            popupAnchor:  [0, 0]
                        }
                    });

                    
                    this.getCategoriesType();
                    this.getCoversType();
                    setTimeout(() => {
                        this.getCoversTrack().then(r => {
                            if (r) this.buildCovers();
                            this.getRoadMessages().then(r => {
                                if (r) this.buildMessages();
                            });
                        });
                    }, 500);
                    
                });
            },

            
            //---------------------------------
            getRoadMessages(zoom) {
                if (!zoom) zoom = 10;
                return axios.get(this.ajaxEndPont + '/messages').then(r => {
                    if (r && r.data) {
                        this.messages = Object.assign([], r.data);
                        return this.messages;
                    } else return null;
                }, () => null);
            },
            buildMessages() {
                if (!this.messages.length) return;
                let GeoJSON = {type: "FeatureCollection", features: []};
                let Features = [], Icons = [];

                this.messages.map((message, i) => {
                    if (message.tracks && message.tracks.length > 1) {
                        Features.push(this.getMessageFeature(message));
                        if (!this.icons.length) Icons.push(this.getMessageIcon(message));
                    }
                });

                if (Features.length) {
                    GeoJSON.features = Object.assign([], Features);
                    this.layers.messages = new leaflet.geoJSON(GeoJSON, {
                        style:(feature) => {return {stroke: true, color: "red", weight: 5};},
                        onEachFeature: (feature, layer) => {
                            layer.setStyle({stroke: true, color: feature.properties.color, weight: 5, opacity: 1});
                        }
                    });
                    this.map.addLayer(this.layers.messages);
                    this.layers.messages.setStyle({opacity: 1});
                    this.layers.messages.bringToFront();
                    
                    if (this.icons.length) this.showIcons();
                    else if (Icons.length) Icons.map(icon => {
                        icon.addTo(this.map);
                        this.icons.push(icon);
                    });
                }
            },
            getMessageFeature(message) {
                let name = message.road.name, color = 'rgb(255, 0, 0)', coordinates = [];

                message.tracks.map(track => {
                    if (track && track.longitude && track.latitude) coordinates.push([parseFloat(track.longitude), parseFloat(track.latitude)]);
                });

                return {
                    type: 'Feature', 
                    id: 'message-' + message.id, 
                    properties: {
                        name: name, 
                        color: color,
                        weight: 3,
                        opacity: 1,
                        smoothFactor: 1
                    }, 
                    geometry: {
                        type: "LineString",
                        coordinates: coordinates
                    }
                };
            },
            getMessageIcon(message) {
                let name = '',
                    iconUrl = (this.endPoint + message.icon),
                    text = message.message,
                    center = Math.ceil(message.tracks.length/2);

                if (!center || !message.tracks[center]) return null;
                center = message.tracks[center];

                if (message.begin_location && message.end_location)
                    name = (message.road.name + ' (с ' + Math.ceil(message.begin_location/1000) + ' км. по ' + Math.ceil(message.end_location/1000) + ' км.)');
                else name = message.road.name;
                let Icon = new this.customIcon({iconUrl: iconUrl});

                return leaflet.marker([center.latitude, center.longitude], {icon: Icon}).bindPopup('<span>' + text + '</span>');
            },
            hideIcons() {
                if (this.icons && this.icons.length) this.icons.map(icon => icon.setOpacity(0));
            },
            showIcons() {
                if (this.icons && this.icons.length) this.icons.map(icon => icon.setOpacity(1));
            },
            //---------------------------------

            //---------------------------------
            convertColor(color) {
                color = color.split(',');
                color = color.map(item => {
                    item = parseInt(item.substr(2, item.length), 16).toString(10);
                    return item;
                });
                return 'rgb(' + color.join(', ') + ')';
            },

            clearLayers() {
                Object.keys(this.layers).map(key => {
                    let layer = this.layers[key];
                    if (layer) layer.remove();
                    this.layers[key] = null;
                });
            },

            hideLayers() {
                Object.keys(this.layers).map(key => {
                    let layer = this.layers[key];
                    if (layer) {
                        layer.bringToBack();
                        layer.setStyle({opacity: 0});
                    }
                });
            },
            //---------------------------------


            //---------------------------------
            getCoversType() {
                return axios.get(this.ajaxEndPont + '/covers_type').then(r => {
                    if (r && r.data) {
                        this.coversType = Object.assign([], r.data);
                        this.coversType.map(cover => {
                            cover.color_rgb = this.convertColor(cover.color_rgb);
                            cover.show = false; //по дефолту скрываем типы, потом они будут отображены, если есть треки с этим типом
                        });
                    }
                });
            },

            getCoversTrack() {
                this.loading = true;
                return axios.get(this.endPoint + '/public/storage/JSON/covers_osm.json').then(r => {
                    if (r && r.data) {
                        this.covers = Object.assign({}, r.data);
                        return this.covers;
                    } else return null;
                }, () => null).finally(() => this.loading = false);
            },
            
            buildCovers() {
                if (!this.covers) return;
                //this.clearLayers();
                this.layers.covers = new leaflet.geoJSON(this.covers, {
                    style:(feature) => {return {stroke: true, color: "red", weight: 5};},
                    onEachFeature: (feature, layer) => {
                        let color = feature.properties.color;
                        if (!color) color = "rgb(0, 0, 0)";
                        else if (!(color.indexOf('rgb(') + 1)) color = this.convertColor(color); 
                        layer.setStyle({stroke: true, color: color, weight: 5, opacity: 1});
                        if (feature.properties.cover_type_id) this.showCoverType(feature.properties.cover_type_id);
                        if (feature.properties.name) layer.bindPopup('<span>' + feature.properties.name + '</span>');
                    }
                });
                this.layers.covers.bringToFront();
                this.layers.covers.setStyle({opacity: 1});
                this.map.addLayer(this.layers.covers);
            },

            showCoverType(id) {
                if (!id || !this.coversType || !this.coversType.length) return;
                this.coversType.map((type, i) => {
                    if (parseInt(type.id) === parseInt(id)) {
                        type.show = true;
                        this.$set(this.coversType, i, type);
                    }
                });
            },
            //---------------------------------


            //---------------------------------
            getCategoriesType() {
                return axios.get(this.ajaxEndPont + '/categories_type').then(r => {
                    if (r && r.data) {
                        this.categoriesType = Object.assign([], r.data);
                        this.categoriesType.map(category => {
                            category.color_rgb = this.convertColor(category.color_rgb);
                            category.show = false; //по дефолту скрываем типы, потом они будут отображены, если есть треки с этим типом
                        });
                    }
                });
            },

            getCategoriesTrack() {
                this.loading = true;
                return axios.get(this.endPoint + '/public/storage/JSON/categories_osm.json').then(r => {
                    if (r && r.data) {
                        this.categories = Object.assign({}, r.data);
                        return this.categories;
                    } else return null;
                }, () => null).finally(() => this.loading = false);
            },

            buildCategories() {
                if (!this.categories) return;
                //this.clearLayers();
                this.layers.categories = new leaflet.geoJSON(this.categories, {
                    style:(feature) => {return {stroke: true, color: "red", weight: 5};},
                    onEachFeature: (feature, layer) => {
                        let color = feature.properties.color;
                        if (!color) color = "rgb(0, 0, 0)";
                        else if (!(color.indexOf('rgb(') + 1)) color = this.convertColor(color);
                        if (feature.properties.category_type_id) this.showCategoryType(feature.properties.category_type_id);
                        layer.setStyle({stroke: true, color: color, weight: 5, opacity: 1});
                        if (feature.properties.name) layer.bindPopup(feature.properties.name);
                    }
                });
                this.layers.categories.bringToFront();
                this.layers.categories.setStyle({opacity: 1});
                this.map.addLayer(this.layers.categories);
            },

            showCategoryType(id) {
                if (!id || !this.categoriesType || !this.categoriesType.length) return;
                this.categoriesType.map((type, i) => {
                    if (parseInt(type.id) === parseInt(id)) {
                        type.show = true;
                        this.$set(this.categoriesType, i, type);
                    }
                });
            },
            //---------------------------------



            //---------------------------------
            getDiagnosticTracks() {
                this.loading = true;
                return axios.get(this.endPoint + '/public/storage/JSON/diagnostic_osm.json').then(r => {
                    if (r && r.data) {
                        this.diagnostic = Object.assign({}, r.data);
                        return this.diagnostic;
                    } else return null;
                }, () => null).finally(() => this.loading = false);
            },

            buildDiagnostic() {
                if (!this.diagnostic) return;
                //this.clearLayers();
                this.layers.diagnostic = new leaflet.geoJSON(this.diagnostic, {
                    style:(feature) => {return {stroke: true, color: "red", weight: 5};},
                    onEachFeature: (feature, layer) => {
                        let color = feature.properties.color;
                        if (!color) color = "rgb(0, 0, 0)";
                        else if (!(color.indexOf('rgb(') + 1)) color = this.convertColor(color);
                        layer.setStyle({stroke: true, color: color, weight: 5, opacity: 1});

                        if (feature.properties.name) layer.bindPopup(feature.properties.name);
                    }
                });
                this.layers.diagnostic.bringToFront();
                this.layers.diagnostic.setStyle({opacity: 1});
                this.map.addLayer(this.layers.diagnostic);
            },
            //---------------------------------
        }
    }
</script>
<style lang="css">
    html, body, #app {
        height: 100%;
        width: 100%;
    }
    .component-roads-map-osm{
        transition: 0.25s ease-in-out;
        opacity: 1;
        position: relative;
        height: 100%;
        width: 100%;
        overflow: hidden;
        margin: 0px;
        padding: 5px;
        box-sizing: border-box;
        display: block;
    }
    
    .component-roads-map-osm .component-road-loading{
       position: absolute;
        z-index: 999;
        background-color: rgba(255, 255, 255, 0.8);
        width: 100%;
        height: 100%;
        padding: 0;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: 0.25s ease-in-out;
    }
    .component-roads-map-osm .component-road-loading span{
        font-size: 20px;
        font-weight: normal;
        text-shadow: 0px 0px 25px rgba(0,0,0,1);
    }
    

    .select-container{
        position: relative;
        display: table;
        margin: 0 0 10px 0;
        background: #fff;
    }
    .select-container label {
        font-size: 14px;
        line-height: 14px;
        color: #000;
        margin-right: 5px;
    }
    .select{
        box-shadow: 0 1px 2px 1px rgba(0,0,0,.15), 0 2px 5px -3px rgba(0,0,0,.15);
        -webkit-appearance: none !important;
        box-sizing: border-box!important;
        border-width: 1px;
        border-style: solid;
        border-radius: 3px;
        background-clip: border-box;
        color: #000;
        vertical-align: middle;
        text-decoration: none;
        font-family: Arial,Helvetica,sans-serif;
        height: 28px;
        padding: 0 33px 0 4px;
        cursor: pointer;
        outline: none !important;
        background: transparent;
        position: relative;
        z-index: 900;
        font-size: 14px;
    }
    .select-container::after{
        content: '';
        background: url('/public/img/arrow.svg');
        height: 28px;
        width: 29px;
        display: block;
        position: absolute;
        z-index: 910;
        right: 0px;
        top: 0px;
        background-repeat: no-repeat;
        background-position: center;
    }
    .select .select-option{
        -webkit-appearance: none !important;
    }

    .component-roads-map-osm .copyright{
        z-index: 900;
        position: absolute;
        left: 10px;
        bottom: 10px;
        top: auto;
        width: 40px;
        height: 40px;
        max-width: 10%;
        max-height: 10%;
        overflow: hidden;
        display: block;
        border-radius: 3px;
        box-shadow: 0 1px 2px 1px rgba(0,0,0,.15), 0 2px 5px -3px rgba(0,0,0,.15);
        background-color: #fff;
        background-size: contain;
        background-position: center;
        background-repeat: no-repeat;
    }

    .component-roads-map-osm .contoll{
        position: absolute;
        right: 10px;
        left: auto;
        top: 10px;
        bottom: auto;
        z-index: 900;
        background-color: rgba(255,255,255,0.9);
        max-width: 50%;
        max-height: calc(100% - 65px);
        overflow: auto;
        padding: 10px;
        border-radius: 3px;
        box-shadow: 0 1px 2px 1px rgba(0,0,0,.15), 0 2px 5px -3px rgba(0,0,0,.15);
    }
    .component-roads-map-osm .categories-type{}
    .component-roads-map-osm .categories-item{
        font-size: 12px;
        line-height: 12px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-bottom: 4px;
    }
    .component-roads-map-osm .categories-item .color{
        width: 15px;
        height: 15px;
        border-radius: 100%;
        margin-right: 5px;
        flex-shrink: 0;
    }
</style>