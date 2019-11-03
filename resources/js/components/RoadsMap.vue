<template>
    <div v-bind:class="{'component component-roads-map': true, 'loading': loading}">
        <p v-if="loading" id="loading" class="component-road-loading">
            <span>Подождите, идёт загрузка данных...</span>
        </p>
        <div id="map" style="width: 100vw; height: 100vh"></div>
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
    export default {
        name: 'roads-map',
        props: {},
        components: {},
        data: function () {
            return {
                map: null,
                messages: [],
                roads: [],

                diagnostic: [],

                categories: [],
                categoriesType: [],
                
                covers: [],
                coversType: [],

                viewType: 'covers',
                viewTypes: [
                   //{name: 'track', label: 'Треки'},
                   {name: 'covers', label: 'Покрытия'},
                   {name: 'categories', label: 'Категории'},
                   {name: 'diagnostic', label: 'Данные диагностики'}
                ],
                
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
                    this.geoObjectsCountAdded = 0;
                    this.map.geoObjects.removeAll();
                    switch (val) {
                        case 'track':
                            this.getTracks(this.map.getBounds(), this.currentZoom).then(r => {
                                if (r) this.buildRoads();
                            });
                            break;
                        case 'categories': 
                            this.getCategoriesTrack(this.map.getBounds(), this.currentZoom).then(r => {
                                if (r) this.buildCategories();
                            });
                            break;
                        case 'covers':
                            this.getCoversTrack(this.map.getBounds(), this.currentZoom).then(r => {
                                if (r) this.buildCovers();
                            });
                            break;
                        case 'diagnostic':
                            this.getDiagnosticTracks(this.map.getBounds(), this.currentZoom).then(r => {
                                if (r) this.buildDiagnostic();
                            })
                            break;
                    }
                });
            }
        },
        methods: {
            init() {
                this.$nextTick(() => {
                    this.getCategoriesType();
                    this.getCoversType();
                    ymaps.ready(() => {
                        // Создание карты.    
                        this.map = new ymaps.Map("map", {
                            // Координаты центра карты.
                            // Порядок по умолчанию: «широта, долгота».
                            // Чтобы не определять координаты центра карты вручную,
                            // воспользуйтесь инструментом Определение координат.
                            center: [50.34031567, 127.53951720],
                            // Уровень масштабирования. Допустимые значения:
                            // от 0 (весь мир) до 19.
                            zoom: 10,
                            controls: ['typeSelector', 'fullscreenControl', 'zoomControl'],
                        }, {
                            maxZoom: 14,
                            minZoom: 7
                        });

                        // Отключаем некоторые включенные по умолчанию поведения:
                        this.map.behaviors.disable([
                            'rightMouseButtonMagnifier', //увеличение области, выделенной правой кнопкой мыши.
                            'leftMouseButtonMagnifier',  //увеличение области, выделенной левой кнопкой мыши либо одиночным касанием, 
                            'scrollZoom',                //масштабирование колесом мыши
                            'dblClickZoom',              //масштабирование карты двойным щелчком кнопки мыши 
                            'multiTouch',                //масштабирование карты двойным касанием (например, пальцами на сенсорном экране)
                            'ruler',                     //измерение расстояния
                            'routeEditor'                //редактор маршрутов
                        ]);

                        this.objectManager = new ymaps.ObjectManager({
                            // Чтобы метки начали кластеризоваться, выставляем опцию.
                            clusterize: false,
                            // ObjectManager принимает те же опции, что и кластеризатор.
                            gridSize: 32,
                            clusterDisableClickZoom: true
                        });

                        this.$nextTick(() => {
                            this.getCoversTrack(this.map.getBounds(), this.currentZoom).then(r => {
                                if (r) {
                                    this.buildCovers();
                                    this.getRoadMessages(this.currentZoom).then(r => this.buildMessages());
                                }
                            });
                            /*
                            this.getTracks(this.map.getBounds(), this.currentZoom).then(r => {
                                if (r) this.buildRoads();
                            });
                            */
                        });

                        this.map.events.add('boundschange', (e) => {
                            this.currentZoom = e.get('newZoom');
                            this.$nextTick(() => {
                                this.map.geoObjects.removeAll();
                                switch (this.viewType) {
                                    case 'track':
                                        this.getTracks(e.get('newBounds'), this.currentZoom).then(r => {
                                            if (r) this.buildRoads();
                                        });
                                    break;
                                    case 'categories':
                                        this.getCategoriesTrack(e.get('newBounds'), this.currentZoom).then(r => {
                                            if (r) this.buildCategories(); 
                                        });
                                    break;
                                    case 'covers':
                                        this.getCoversTrack(e.get('newBounds'), this.currentZoom).then(r => {
                                            if (r) {
                                                this.buildCovers();
                                                if (e.get('newZoom') != e.get('oldZoom'))  {
                                                    this.getRoadMessages(this.currentZoom).then(r => this.buildMessages());
                                                } else {
                                                    if (this.messages && this.messages.length) this.buildMessages();
                                                    else this.getRoadMessages(this.currentZoom).then(r => this.buildMessages());
                                                }
                                            }
                                        });
                                    break;
                                    case 'diagnostic':
                                        this.getDiagnosticTracks(e.get('newBounds'), this.currentZoom).then(r => {
                                            if (r) this.buildDiagnostic();
                                        })
                                    break;
                                }
                            });
                        });
                        
                        this.map.geoObjects.events.add('add', (e) => {
                            this.geoObjectsCountAdded++;
                            if (parseInt(this.geoObjectsCountAdded) > 0) setTimeout(() => this.loading = false, 1000);
                        });
                        
                    });
                });
            },
            getDataByCoords(coords) {
                return {
                    latitude: {
                        from: coords[0][0],
                        to: coords[1][0]
                    },
                    longitude: {
                        from: coords[0][1],
                        to: coords[1][1]
                    }
                };
            },

            getRoadMessages(zoom) {
                if (!zoom) zoom = 10;
                return axios.get(this.ajaxEndPont + '/messages?zoom=' + zoom).then(r => {
                    if (r && r.data) {
                        this.messages = Object.assign([], r.data);
                        return this.messages;
                    } else return null;
                }, () => null);
            },

            buildMessages() {
                if (!this.messages.length) return;
                this.messages.map((message, i) => {
                    if (message.tracks && message.tracks.length && message.tracks.length > 1) {
                        this.buildMessagesTracks(message, message.road.name, (this.endPoint + message.icon), message.message, message.tracks, 'rgb(255, 0, 0)');
                    }
                });
            },
            buildMessagesTracks(item, name, icon, message, tracks, color, id) {
                if (!color) color = "rgb(0, 0, 0)";
                else if (!(color.indexOf('rgb(') + 1)) color = this.convertColor(color);
                
                if (tracks && tracks.length) {
                    let coordinates = [];
                    tracks.map(track => {
                        if (track) coordinates.push([parseFloat(track.latitude), parseFloat(track.longitude)]);
                    });
                    
                    //Добавляем треки
                    let GeoObject = new ymaps.GeoObject({
                        geometry:  {type: "LineString", coordinates: coordinates},
                        properties:{hintContent: (name) ? name : '', balloonContent: ''}
                    }, {draggable: false, strokeColor: color, strokeWidth: 5});
                    this.map.geoObjects.add(GeoObject);

                    //Добавляем метку с иконкой и сообщением
                    let center = Math.ceil(tracks.length/2);
                    if (center && tracks[center]) {
                        center = tracks[center];
                        let MyIconContentLayout = ymaps.templateLayoutFactory.createClass('<div>$[properties.iconContent]</div>');
                        let myPlacemark = new ymaps.Placemark([center.latitude, center.longitude], {
                            hintContent: (name) ? name : '', 
                            balloonContentHeader: name ? ( item.begin_location && item.end_location ? (name + ' (с ' + Math.ceil(item.begin_location/1000) + ' км. по ' + Math.ceil(item.end_location/1000) + ' км.)')  : name) : '',
                            balloonContent: message
                            }, {
                            hideIconOnBalloonOpen: false,
                            iconLayout: 'default#image', iconImageHref: icon, iconImageSize: [30, 26], iconImageOffset: [-15, -13]
                        });
                        this.map.geoObjects.add(myPlacemark);
                    }
                }
            },

            getTracks(coords, zoom) {
                this.loading = true;
                return axios.get(this.ajaxEndPont + '/tracks?coords=' + JSON.stringify(this.getDataByCoords(coords)) + '&zoom=' + zoom).then(r => {
                    if (r && r.data && r.data.roads) {
                        this.roads = Object.assign([], r.data.roads);
                        return this.roads;
                    } else return null;
                }, () => null).finally(() => this.loading = false);
            },

            buildRoads() {
                if (!this.roads.length) return;
                this.loading = true;
                
                this.roads.map((road, i) => {
                    //if (road.tracks && road.tracks.length) this.objectManager.add(this.buildTracks(road.name, road.tracks, null, i));
                    if (road.tracks && road.tracks.length) this.buildTracks(road.name, road.tracks, null, i);
                });
                //this.objectManager.add(GeoObject);
                //this.map.geoObjects.add(this.objectManager);
            },

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

            getCategoriesTrack(coords, zoom) {
                this.loading = true;
                return axios.get(this.ajaxEndPont + '/tracks_categories?zoom=' + zoom + '&coords=' + JSON.stringify(this.getDataByCoords(coords))).then(r => {
                    if (r && r.data) {
                        this.categories = Object.assign([], r.data);
                        return this.categories;
                    } else return null;
                }, () => null).finally(() => this.loading = false);
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

            buildCategories() {
                if (!this.categories.length) return;
                this.loading = true;
                this.categories.map((category, i) => {
                    let name = (category.road.name + ' (' + category.category_type.name + ')');
                    if (category.tracks && category.tracks.length && category.tracks.length > 1) {
                        this.showCategoryType(category.category_type.id);
                        this.buildTracks(name, category.tracks, category.category_type.color_rgb);
                    }
                });
            },

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

            getCoversTrack(coords, zoom) {
                this.loading = true;
                return axios.get(this.ajaxEndPont + '/tracks_covers?zoom=' + zoom + '&coords=' + JSON.stringify(this.getDataByCoords(coords))).then(r => {
                    if (r && r.data) { 
                        this.covers = Object.assign([], r.data);
                        return this.covers;
                    } else return null;
                }, () => null).finally(() => this.loading = false);
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

            buildCovers() {
                if (!this.covers.length) return;
                this.loading = true;
                this.covers.map((cover, i) => {
                    let name = (cover.road.name + ' (' + cover.cover_type.name + ')');
                    if (cover.tracks && cover.tracks.length && cover.tracks.length > 1) {
                        this.showCoverType(cover.cover_type.id);
                        this.buildTracks(name, cover.tracks, cover.cover_type.color_rgb);
                    }
                });
            },

            getDiagnosticTracks(coords, zoom) {
                this.loading = true;
                return axios.get(this.ajaxEndPont + '/tracks_diagnostic?zoom=' + zoom + '&coords=' + JSON.stringify(this.getDataByCoords(coords))).then(r => {
                    if (r && r.data) { 
                        this.diagnostic = Object.assign([], r.data);
                        return this.diagnostic;
                    } else return null;
                }, () => null).finally(() => this.loading = false);
            },

            buildDiagnostic() {
                if (!this.diagnostic.length) return;
                this.loading = true;
                this.diagnostic.map((item, i) => {
                    if (item.tracks && item.tracks.length && item.tracks.length > 1)
                        this.buildTracks(item.road.name, item.tracks, item.color_rgb);
                });
            },

            buildTracks(name, tracks, color, id) {
                if (!color) color = "rgb(0, 0, 0)";
                else if (!(color.indexOf('rgb(') + 1)) color = this.convertColor(color);
                
                if (tracks && tracks.length) {
                    let coordinates = [];
                    tracks.map(track => {
                        if (track) coordinates.push([parseFloat(track.latitude), parseFloat(track.longitude)]);
                    });
                    /*
                    return {
                        type: 'Feature',
                        id: id,
                        geometry: {
                            type: 'LineString',
                            coordinates: coordinates
                        },
                        properties: {
                            hintContent: (name) ? name : '',
                            balloonContent: (name) ? name : ''
                        },
                        options: {
                            draggable: false,
                            strokeColor: color,
                            strokeWidth: 5
                        }
                    };
                    */

                    /* */
                    this.map.geoObjects.add(new ymaps.GeoObject({
                        geometry: {
                            type: "LineString",
                            coordinates: coordinates
                        },
                        properties:{
                            hintContent: (name) ? name : '',
                            balloonContent: ""
                        }
                    }, {
                        draggable: false,
                        strokeColor: color,
                        strokeWidth: 5
                    }));
                    /* */

                }
            },
            convertColor(color) {
                color = color.split(',');
                color = color.map(item => {
                    item = parseInt(item.substr(2, item.length), 16).toString(10);
                    return item;
                });
                return 'rgb(' + color.join(', ') + ')';
            }
        }
    }
</script>
<style lang="css">
    .component-roads-map{
        transition: 0.25s ease-in-out;
        opacity: 1;
        position: relative;
    }
    
    .component-roads-map .component-road-loading{
       position: absolute;
        z-index: 15;
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
    .component-roads-map .component-road-loading span{
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
        z-index: 10;
        font-size: 14px;
    }
    .select-container::after{
        content: '';
        background: url('/public/img/arrow.svg');
        height: 28px;
        width: 29px;
        display: block;
        position: absolute;
        z-index: 1;
        right: 0px;
        top: 0px;
        background-repeat: no-repeat;
        background-position: center;
    }
    .select .select-option{
        -webkit-appearance: none !important;
    }

    .component-roads-map .copyright{
        z-index: 10;
        position: absolute;
        left: 10px;
        top: 10px;
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

    .component-roads-map .contoll{
        position: absolute;
        right: 10px;
        left: auto;
        top: 50px;
        bottom: auto;
        z-index: 10;
        background-color: rgba(255,255,255,0.9);
        max-width: 50%;
        max-height: calc(100% - 65px);
        overflow: auto;
        padding: 10px;
        border-radius: 3px;
        box-shadow: 0 1px 2px 1px rgba(0,0,0,.15), 0 2px 5px -3px rgba(0,0,0,.15);
    }
    .component-roads-map .categories-type{}
    .component-roads-map .categories-item{
        font-size: 12px;
        line-height: 12px;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-bottom: 4px;
    }
    .component-roads-map .categories-item .color{
        width: 15px;
        height: 15px;
        border-radius: 100%;
        margin-right: 5px;
        flex-shrink: 0;
    }
</style>