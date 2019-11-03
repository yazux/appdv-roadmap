<template>
    <div class="content component project-media">
        <docs-gallery v-if="$props.docs && Object.keys($props.docs).length" v-bind:docs="$props.docs"></docs-gallery>
        <videos-gallery v-if="$props.videos && Object.keys($props.videos).length" v-bind:videos="$props.videos"></videos-gallery>
        <photo-gallery v-if="$props.photos && Object.keys($props.photos).length" v-bind:photos="$props.photos"></photo-gallery>
    </div>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    import PhotoGallery from './PhotoGallery';
    import VideosGallery from './VideosGallery';
    import DocsGallery from './DocsGallery';
    export default {
        name: 'project-media',
        components: {PhotoGallery, DocsGallery, VideosGallery},
        props: {
            photos: {
                type: [Array, Object, String],
                default: () => {
                    return [];
                }
            },
            docs: {
                type: [Array, Object, String],
                default: () => {
                    return [];
                }
            },
            videos: {
                type: [Array, Object, String],
                default: () => {
                    return [];
                }
            }
        },
        data: function () {
            return {
            }
        },
        created() {
            moment.locale("ru");
            this.moment = moment;
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
        methods: {}
    }
</script>
<style lang="css"></style>