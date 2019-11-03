<template>
    <div class="component news-component" v-if="news && news.length">
        <p class="news-component__title">Последние новости</p>
        <section class="news-component__list">
            <article v-for="(item, i) in news" v-if="i <= 8" class="news-component__item">
                <span class="news-component__item-date">
                    <span>{{moment(item.created_at).format('DD MMMM')}}</span>
                    <span>{{moment(item.created_at).format('YYYY')}}</span>
                </span>
                <p class="news-component__item-text">{{item.text}}</p>
            </article>
        </section>
    </div>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    export default {
        name: 'news-list',
        props: {},
        components: {},
        data: function () {
            return {
                news: []
            }
        },
        created() {
            moment.locale("ru");
            this.moment = moment;
            this.$nextTick(() => this.getNews());
        },
        computed: {
            endPoint() {
                return location.protocol + '//'
                    + location.hostname
                    + (location.port ? (':' + location.port) : '');
            },
            ajaxEndPont() {
                return this.endPoint + '/ajax';
            }
        },
        watch: {},
        methods: {
            getNews(count, page, sortBy, sort_type) {
                return axios.get((this.ajaxEndPont + '/news?count=8')).then(r => {
                    if (r && r.data && r.data.data) this.news = Object.assign([], r.data.data);
                });
            }
        }
    }
</script>
<style lang="css">
    .news-component{}
    .news-component__title{
        color: var(--text-color-white);
        margin: 0 0 20px 0;
        font-size: 20px;
        border-bottom: 2px solid var(--text-color-white);;
        padding-bottom: 5px;
    }
    .news-component__list{
        color: var(--text-color-white);
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .news-component__item{
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        margin-bottom: 8px;
        padding-bottom: 8px;
        width: 48.5%;
    }
    .news-component__item-date{
        font-size: 14px;
        line-height: 14px;
        color: #aaaaaa;
        margin-right: 10px;
        text-align: right;
        width: 70px;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: flex-start;
    }
    .news-component__item-text{
        font-size: 14px;
        line-height: 16px;
        margin: 0;
        width: calc(100% - 70px);
    }
</style>