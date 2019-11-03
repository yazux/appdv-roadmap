<template>
    <div v-bind:class="{'component component-roads-map': true, 'loading': loading}">
        <p v-if="loading" id="loading" class="component-road-loading">
            <span>Подождите, идёт загрузка данных...</span>
        </p>
        <div id="map" style="width: 1250px; height: 800px"></div>
        <div class="contoll">
            <div class="select-container">
                <label for="view-type">Тип отображения</label>
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
                clusterer: null,
                objectManager: null,

                roads: {
                    0: [],
                    1: [],
                    2: [],
                    3: [],
                    4: [],
                    5: [],
                    6: [],
                    7: [],
                    8: [],
                    9: [],
                    10: [],
                    11: [],
                    12: [],
                    13: [],
                    14: [],
                    15: [],
                    16: [],
                    17: [],
                    18: [],
                    19: []
                },

                diagnostic: {
                    0: [],
                    1: [],
                    2: [],
                    3: [],
                    4: [],
                    5: [],
                    6: [],
                    7: [],
                    8: [],
                    9: [],
                    10: [],
                    11: [],
                    12: [],
                    13: [],
                    14: [],
                    15: [],
                    16: [],
                    17: [],
                    18: [],
                    19: []
                },

                categories: {
                    0: [],
                    1: [],
                    2: [],
                    3: [],
                    4: [],
                    5: [],
                    6: [],
                    7: [],
                    8: [],
                    9: [],
                    10: [],
                    11: [],
                    12: [],
                    13: [],
                    14: [],
                    15: [],
                    16: [],
                    17: [],
                    18: [],
                    19: []
                },
                categoriesType: [],
                
                covers: {
                    0: [],
                    1: [],
                    2: [],
                    3: [],
                    4: [],
                    5: [],
                    6: [],
                    7: [],
                    8: [],
                    9: [],
                    10: [],
                    11: [],
                    12: [],
                    13: [],
                    14: [],
                    15: [],
                    16: [],
                    17: [],
                    18: [],
                    19: []
                },
                coversType: [],

                viewType: 'track',
                viewTypes: [
                   {name: 'track', label: 'Треки'},
                   {name: 'categories', label: 'Категории'},
                   {name: 'covers', label: 'Покрытия'},
                   {name: 'diagnostic', label: 'Данные диагностики'}
                ],
                
                loading: false,
                currentZoom: 10,
                geoObjectsCountAdded: 0
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
                        
                        this.getTracks(this.map.getBounds(), this.currentZoom).then(r => {
                            console.log(r);
                            if (r) this.buildRoads();
                        });

                        /*
                        this.map.events.add('boundschange', (e) => {
                            this.currentZoom = e.get('newZoom');
                            if (e.get('newZoom') <= e.get('oldZoom')) this.$nextTick(() => {
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
                                            if (r) this.buildCovers();
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
                        */
                        
                        this.map.geoObjects.events.add('add', (e) => {
                            this.geoObjectsCountAdded++;
                            if (parseInt(this.geoObjectsCountAdded) > 0) setTimeout(() => this.loading = false, 1000);
                        });
                        
                    });
                });
            },
            
            getTracks(coords, zoom) {
                console.log('getTracks');
                /*
                if (this.roads[zoom] && this.roads[zoom].length) return new Promise(() => {
                    resolve(this.roads[zoom]);
                });
                */
                this.loading = true;
                return axios.get(this.endPoint + '/storage/JSON/tracks_' + zoom + '.json').then(r => {
                    console.log(r);
                    if (r && r.data) {
                        this.roads[zoom] = Object.assign({}, r.data);
                        return this.roads[zoom];
                    } else return null;
                }, () => null).finally(() => this.loading = false);

            },

            buildRoads() {

                /*
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
                 */

                console.log( this.roads );

                //if (!this.roads[this.currentZoom]) return;
                this.objectManager.add(this.roads[this.currentZoom]);
                this.map.geoObjects.removeAll();
                this.map.geoObjects.add(this.objectManager);
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
                if (this.categories[zoom] && this.categories[zoom].length) return new Promise(() => {
                    resolve(this.categories[zoom]);
                });
                this.loading = true;
                return axios.get(this.endPoint + '/storage/JSON/categories_' + zoom + '.json').then(r => {
                    if (r && r.data) {
                        this.categories[zoom] = Object.assign([], r.data);
                        return this.categories[zoom];
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
                if (!this.categories[this.currentZoom] || !this.categories[this.currentZoom].length) return;
                this.loading = true;
                //this.categories[this.currentZoom].map(item => this.buildTracks(item));
                this.buildTracks(this.categories[this.currentZoom]);
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
                if (this.covers[zoom] && this.covers[zoom].length) return new Promise(() => {
                    resolve(this.covers[zoom]);
                });
                this.loading = true;
                return axios.get(this.endPoint + '/storage/JSON/covers_' + zoom + '.json').then(r => {
                    if (r && r.data) {
                        this.covers[zoom] = Object.assign([], r.data);
                        return this.covers[zoom];
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
                if (!this.covers[this.currentZoom] || !this.covers[this.currentZoom].length) return;
                this.loading = true;
                //this.covers[this.currentZoom].map(item => this.buildTracks(item));
                this.buildTracks(this.covers[this.currentZoom]);
            },

            getDiagnosticTracks(coords, zoom) {
                if (this.diagnostic[zoom] && this.diagnostic[zoom].length) return new Promise(() => {
                    resolve(this.diagnostic[zoom]);
                });
                this.loading = true;
                return axios.get(this.endPoint + '/storage/JSON/diagnostic_' + zoom + '.json').then(r => {
                    if (r && r.data) {
                        this.diagnostic[zoom] = Object.assign([], r.data);
                        return this.diagnostic[zoom];
                    } else return null;
                }, () => null).finally(() => this.loading = false);
            },

            buildDiagnostic() {
                if (!this.diagnostic[this.currentZoom] || !this.diagnostic[this.currentZoom].length) return;
                this.loading = true;
                //this.diagnostic[this.currentZoom].map(item => this.buildTracks(item));
                this.buildTracks(this.diagnostic[this.currentZoom]);
            },
            buildTracks(data) {
                /*
                if (!data || !data.length) return;                
                data.map(item => {
                    this.objectManager.add(new ymaps.GeoObject(item[0], item[1]));
                });
                */
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
        margin: 0;
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

    .component-roads-map .contoll{
        position: absolute;
        right: 10px;
        left: auto;
        top: 50px;
        bottom: auto;
        z-index: 10;
        background-color: rgba(255,255,255,0.9);
        max-width: 30%;
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