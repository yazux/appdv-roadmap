<template>
    <div class="content component docs-gallery" v-if="gallery && Object.keys(gallery).length">
        <h4 class="docs-gallery__title" v-on:click="toggleGallery">
            Документы <span>{{((show) ? ' (нажмите чтобы свернуть)' : ' (нажмите чтобы развернуть)')}}</span>
        </h4>
        <div class="docs-gallery__container" v-show="show">
            <a
                v-for="item in gallery" 
                v-bind:href="('/storage/' + item.download_link)"
                target="_blank"
                class="docs-gallery__item"
                title="Нажмите чтобы просмотреть документ"
            >{{item.original_name}}</a>
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
                else this.gallery = this.$props.docs;
            },
            toggleGallery() {
                this.show = !this.show;
            }
        }
    }
</script>
<style lang="css">
    .component.docs-gallery{
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
    .docs-gallery__title span{
        font-size: 14px;
        line-height: 14px;
        margin-left: 10px;
    }
    .docs-gallery__container{
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
    }
    .docs-gallery__item{
        font-size: 18px;
        text-decoration: none;
        outline: none;
        color: var(--color-two);
        transition: 0.2s ease-in-out;
    }
    .docs-gallery__item:hover{
        color: var(--color-one);
    }
</style>