<template>
    <div class="content component docs-gallery">
        <h4 class="docs-gallery__title" v-on:click="toggleGallery">{{ 'Документы ' + ((show) ? '(нажмите чтобы свернуть)' : '(нажмите чтобы развернуть)') }}</h4>
        <div class="docs-gallery__container" v-show="show">
            <pre>{{gallery}}</pre>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    export default {
        name: 'docs-gallery',
        components: {},
        props: {
            docs: {
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
            this.$nextTick(() => this.getDocs());
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
            '$props.docs'() {
                this.$nextTick(() => this.getDocs());
            }
        },
        methods: {
            getDocs() {
                if (typeof this.$props.docs === 'string') this.gallery = JSON.parse(this.$props.docs)
            },
        }
    }
</script>
<style lang="css">
    .docs-gallery{
        margin-bottom: 20px;
    }
    .docs-gallery__title{
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
</style>