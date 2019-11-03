<template>
    <article class="vendors-component__item" v-if="vendor && vendor.id">
        <a v-bind:href="(endPoint + '/vendors/' + vendor.id)" class="vendors-component__link">
            <div class="vendors-component__item-progress" v-if="vendor.projecs_in_works_count && vendor.projects_count">
                <span class="vendors-component__item-progress-line" v-bind:style="'width: ' + (parseInt(vendor.projecs_in_works_count) * 100 / parseInt(vendor.projects_count)) + '%;'"></span>
                <span class="vendors-component__item-progress-value">{{('Процесс работы: '  + Math.round(parseInt(vendor.projecs_in_works_count) * 100 / parseInt(vendor.projects_count)) + '%')}}</span>
                <span class="vendors-component__item-progress-numeric">{{ vendor.projecs_in_works_count  + ' из ' + vendor.projects_count }}</span>
            </div>

            <div class="vendors-component__hero-image">
                <img v-if="vendor.photos" v-for="(image, i) in vendor.photos" v-bind:src="('/storage/' + image)" v-bind:alt="vendor.name" v-bind:class="{'vendors-component__hero-image__slider': true, 'active': ((i == activeNumber) ? 'active' : '')}" />
                <img v-else v-bind:src="('/storage/' + vendor.hero_image)" v-bind:alt="vendor.name" />
            </div>
            <div class="vendors-component__content">
                <p class="vendors-component__name">{{vendor.name}}</p>
                <p class="vendors-component__description">{{vendor.description}}</p>
            </div>
        </a>
    </article>
</template>
<script>
    import axios from 'axios';
    import paginate from 'vuejs-paginate';
    export default {
        name: 'vendors',
        props: {
            vendor: {
                type: Object,
                default: () =>  {
                    return {};
                }
            }
        },
        components: {},
        data: function () {
            return {
                slider: [],
                active: '',
                activeNumber: 0
            }
        },
        created() {
            this.$nextTick(() => this.startSliding());
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
        watch: {},
        methods: {
            startSliding() {
                this.slider = Object.assign([], this.$props.vendor.photos);
                this.active = this.slider[0];
                this.activeNumber = 0;
                let interval = (this.getRandomInt(10, 15) * 1000);
                setInterval(() => {
                    this.activeNumber++;
                    if ((this.activeNumber+1) > this.slider.length) this.activeNumber = 0;
                    this.active = this.slider[this.activeNumber];
                }, interval);
            },
            getRandomInt(min, max) {
                min = Math.ceil(min);
                max = Math.floor(max);
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
        }
    }
</script>
<style lang="css">
    .vendors-component__link{}
    .vendors-component__item{
        width: calc(33% - 15.8px);
        height: 260px;
        overflow: hidden;
        margin: 0 10px 20px 10px;
        position: relative;
        border-radius: 4px;
        box-shadow: 0 0 14px rgba(0,0,0,.1);
        transition: 0.25s ease-in-out;
    }
    .vendors-component__item:hover{
        transform: translate(0, -10px);
        box-shadow: 0px 10px 25px 0px rgba(0,0,0,0.4);
    }
    .vendors-component__hero-image{
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 10;
    }
    .vendors-component__hero-image img{
        display: block;
        width: 100%;
        min-height: 100%;
        height: auto;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    .vendors-component__hero-image__slider{
        transition: 0.5s ease-in-out;
        opacity: 0;
    }
    .vendors-component__hero-image__slider.active{
        opacity: 1;
    }

    .vendors-component__content{
        position: absolute;
        z-index: 20;
        bottom: 0;
        left: 0;
        right: 0;
        background-color: rgba(0, 0, 0, 0.6);
        color: var(--text-color-white);
        padding: 10px;
    }
    .vendors-component__name{
        text-transform: uppercase;
        white-space: nowrap;
        overflow: hidden;
        font-size: 18px;
        line-height: 20px;
        margin: 0;
        color: var(--text-color-white);
        border-bottom: 2px solid var(--color-two);
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .vendors-component__description{
        margin: 0;
        font-size: 14px;
    }

    .vendors-component__item-progress{
        position: absolute;
        z-index: 20;
        background-color: var(--color-one);
        width: 100%;
        height: 25px;
    }
    .vendors-component__item-progress-line{
        background-color: var(--color-two);
        height: 100%;
        display: block;
    }
    .vendors-component__item-progress-value{
        display: flex;
        align-items: center;
        justify-content: center;
        width: 200px;
        height: 100%;
        position: absolute;
        top: 0;
        left: calc(50% - 100px);
        color: var(--text-color-white);
        z-index: 30;
        font-size: 13px;
        line-height: 13px;
    }
    .vendors-component__item-progress-numeric{
        position: absolute;
        color: var(--text-color-white);
        background-color: rgba(0,0,0,0.6);
        font-size: 15px;
        line-height: 15px;
        width: 80px;
        height: 30px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        left: calc(50% - 40px);
        top: -10px;
        opacity: 0;
        transition: 0.25s ease-in-out;
    }
    .vendors-component__item-progress-numeric:before{
        content: '';
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 6px 6px 7px;
        border-color: transparent transparent rgba(0,0,0,0.6) transparent;
        position: absolute;
        top: -6px;
        left: calc(50% - 6px);
    }

    .vendors-component__item-progress:hover .vendors-component__item-progress-numeric{
        top: 30px;
        opacity: 1;
    }
    .vendors-component__item-progress:hover{}
</style>