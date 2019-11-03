<template>
    <div class="content component photo-gallery" v-if="gallery && Object.keys(gallery).length">
        <h4 class="photo-gallery__title" v-on:click="toggleGallery">
            Изображения <span>{{((show) ? ' (нажмите чтобы свернуть)' : ' (нажмите чтобы развернуть)')}}</span>
        </h4>
        <div class="photo-gallery__container" v-show="show">
            <a 
                v-for="item in gallery"
                v-bind:href="('/storage/' + item)" 
                data-fancybox="gallery"
                class="photo-gallery__item"
                >
                <img v-bind:src="('/storage/' + item)" />
            </a>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    import fancybox from '@fancyapps/fancybox';
    import '@fancyapps/fancybox/dist/jquery.fancybox.min.css';
    export default {
        name: 'photo-gallery',
        components: {},
        props: {
            photos: {
                type: [Array, Object, String],
                default: () => {
                    return [];
                }
            }
        },
        data: function () {
            return {
                gallery: [],
                show: true,
            }
        },
        created() {
            moment.locale("ru");
            this.moment = moment;
            this.$nextTick(() => this.getPhotos());
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
            '$props.photos'() {
                this.$nextTick(() => this.getPhotos());
            }
        },
        methods: {
            getPhotos() {
                if (typeof this.$props.photos === 'string') this.gallery = JSON.parse(this.$props.photos)
                else this.gallery = this.$props.photos;
            },
            toggleGallery() {
                this.show = !this.show;
            }
        }
    }
</script>
<style lang="css">
    .component.photo-gallery{
        margin-bottom: 0px;
    }
    .photo-gallery__title{
        display: flex;
        align-items: center;
        justify-content: flex-start;
        color: var(--color-one);
        margin: 0 0 20px 0;
        font-size: 18px;
        line-height: 18px;
        border-bottom: 2px solid var(--color-one);
        padding-bottom: 5px;
        cursor: pointer;
    }
    .photo-gallery__title span{
        font-size: 14px;
        line-height: 14px;
        margin-left: 10px;
    }
    .photo-gallery__title:after{

    }
    .photo-gallery__container{
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    .photo-gallery__item{
        display: block;
        height: 200px;
        max-width: 300px;
        overflow: hidden;
        margin: 0 4px 4px 4px;
        border-radius: 4px;
        border: 2px solid var(--color-one);
        width: 100%;
        flex-grow: 1;
        cursor: pointer;
        box-shadow: 0 0 14px rgba(0,0,0,.1);
        transition: 0.25s ease-in-out;
    }
    .photo-gallery__item:hover{
        box-shadow: 0px 5px 14px rgba(0,0,0,0.4);
        transform: translate(0, -15px) scale(1.2);
    }
    .photo-gallery__item img{
        display: block;
        width: 100%;
        min-height: 100%;
    }
</style>