<template>
    <div class="content component video-gallery" v-if="gallery && Object.keys(gallery).length">
        <h4 class="video-gallery__title" v-on:click="toggleGallery">
            Видео <span>{{((show) ? ' (нажмите чтобы свернуть)' : ' (нажмите чтобы развернуть)')}}</span>
        </h4>
        <div class="video-gallery__container" v-show="show">
            <a
                v-for="item in gallery"
                v-bind:href="('https://www.youtube.com/watch?v=' + item)" 
                data-fancybox
                class="video-gallery__item"
            >
                <img v-bind:src="('https://img.youtube.com/vi/' + item + '/default.jpg')" />
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
        name: 'videos-gallery',
        components: {},
        props: {
            videos: {
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
            this.$nextTick(() => this.getVideos());
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
            '$props.videos'() {
                this.$nextTick(() => this.getVideos());
            }
        },
        methods: {
            getVideos() {
                if (typeof this.$props.videos === 'string') this.gallery = JSON.parse(this.$props.videos)
                else this.gallery = this.$props.videos;
            },
            toggleGallery() {
                this.show = !this.show;
            }
        }
    }
</script>
<style lang="css">
    .component.video-gallery{
        margin-bottom: 20px;
    }
    .video-gallery__title{
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
    .video-gallery__title span{
        font-size: 14px;
        line-height: 14px;
        margin-left: 10px;
    }
    .photo-gallery__title:after{

    }
    .video-gallery__container{
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        flex-wrap: wrap;
    }
    .video-gallery__item{
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
    .video-gallery__item:hover{
        box-shadow: 0px 5px 14px rgba(0,0,0,0.4);
        transform: translate(0, -15px) scale(1.2);
    }
    .video-gallery__item img{
        display: block;
        width: 100%;
        min-height: 100%;
    }
</style>