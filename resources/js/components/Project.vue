<template>
    <article v-if="project" class="projects-component__item">
        <a v-bind:href="(endPoint + '/projects/' + project.id)" class="projects-component__link">
            <img v-bind:src="('/storage/' + project.photo)" v-bind:alt="project.name" class="projects-component__item-photo">
            <div class="projects-component__item-content">
                <p class="projects-component__item-name">{{project.name}}</p>

                <p class="projects-component__item-field" v-if="project.start_date">{{'Дата начала работ: ' + moment(project.start_date).format('DD.MM.YYYY')}}</p>
                <p class="projects-component__item-field" v-if="project.end_date">{{'Дата окончания работ: ' + moment(project.end_date).format('DD.MM.YYYY')}}</p>
                <p class="projects-component__item-field" v-if="project.status && statuses[project.status]">{{'Статус: ' + statuses[project.status]}}</p>
                <p class="projects-component__item-field" v-if="project.price">{{'Стоимость контролируемых СМР: ' + project.price}}</p>

                <p class="projects-component__item-description" v-if="project.description">{{project.description}}</p>

                <div class="projects-component__item-progress" v-if="project.percent && parseInt(project.percent) > 0">
                    <span class="projects-component__item-progress-line" v-bind:style="'width: ' + parseInt(project.percent) + '%;'"></span>
                    <span class="projects-component__item-progress-value">{{('Процесс работы: ' + parseInt(project.percent) + '%')}}</span>
                </div>
            </div>
        </a>
    </article>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    import paginate from 'vuejs-paginate';
    export default {
        name: 'vendor-project',
        components: {paginate},
        props: {
            project: {
                type: Object,
                default: () => {
                    return {}
                }
            }
        },
        data: function () {
            return {
                statuses: {
                    planned: 'Проектировка/В плане',
                    work: 'В работе',
                    done: 'Закончен'
                },
            }
        },
        created() {
            moment.locale("ru");
            this.moment = moment;
            this.$nextTick(() => {});
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
<style lang="css">
    .projects-component{}
    .projects-component__item{
        width: calc(33% - 15.9px);
        margin: 0 10px 20px 10px;
        height: 300px;
        overflow: hidden;
        border-radius: 4px;
        box-shadow: 0 0 14px rgba(0,0,0,.1);
        position: relative;
        transition: 0.25s ease-in-out;
    }
    .projects-component__item:hover{
        transform: translate(0, -10px);
        box-shadow: 0px 10px 25px 0px rgba(0,0,0,0.4);
    }
    .projects-component__item-photo{
        display: block;
        width: 100%;
        min-height: 100%;
        height: auto;
    }
    .projects-component__item-content{
        position: absolute;
        z-index: 10;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.6);
        color: var(--text-color-white);
        padding: 20px;
    }
    .projects-component__item-name{
        font-size: 18px;
        line-height: 20px;
        margin: 0;
        color: #fff;
        border-bottom: 2px solid #de6d59;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }
    .projects-component__item-description{
        bottom: 30px;
        position: absolute;
        left: 20px;
        right: 20px;
        font-size: 14px;
        line-height: 16px;
    }
    
    .projects-component__item-field{
        display: block;
        margin: 0 0 10px 0;
        font-size: 16px;
        line-height: 18px;
    }
    

    .projects-component__item-progress{
        width: 100%;
        height: 20px;
        background: var(--color-one);
        bottom: 0;
        left: 0;
        right: 0;
        position: absolute;
    }
    .projects-component__item-progress-line{
        display: block;
        background: var(--color-two);
        height: 100%;
    }
    .projects-component__item-progress-value{
        position: absolute;
        top: 0;
        left: calc(50% - 100px);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 200px;
        font-size: 14px;
        line-height: 14px;
    }
</style>