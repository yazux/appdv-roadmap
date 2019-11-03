<template>
    <div class="content component projects-component">
        <section v-if="projectsData && Object.keys(projectsData).length" class="projects-component__list">
            <project v-for="project in projectsData" v-bind:key="project.id" v-bind:project="project"></project>
        </section>
        <div v-else class="projects-component__no-items no-items page-description">
            Проекты ещё не созданны, пожалуйста зайдите позже
        </div>

        <paginate
            v-if="(projectsData && projectsData.length) && total > count"
            :page-count="lastPage"
            :initial-page="1"
            :click-handler="paginatorAction"
            :prev-text="'Назад'"
            :next-text="'Вперёд'"
            :container-class="'pagination'"
            :page-class="'page-item'"
        ></paginate>
    </div>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    import paginate from 'vuejs-paginate';
    import Project from './Project';
    export default {
        name: 'vendor-projects',
        components: {paginate, Project},
        props: {
            showAllBtn: {
                type: Boolean,
                default: false
            },
            showTitle: {
                type: Boolean,
                default: false
            },
            projects: {
                type: [Array, Object],
                default: () => {
                    return [];
                }
            },
            vendorId: {
                type: Number,
                default: 0
            }
        },
        data: function () {
            return {
                count: 9,
                current_page: 1,
                page: 1,
                total: 0,
                lastPage: 0,
                
                projectsData: []
            }
        },
        created() {
            moment.locale("ru");
            this.moment = moment;

            this.$nextTick(() => {
                if (this.$props.projects && this.$props.projects.length) this.projectsData = Object.assign({}, this.$props.projects);
                else this.getProjects(this.count);
                /*
                "docs": null,
                "photo": "projects/July2019/JurXUxredAvhsCIkiFUl.jpg",
                "photos": null,
                "percent": "50.00",
                */
            });
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
            paginatorAction(page) {
                this.page = page;
                this.getProjects(this.count, this.page);  
            },
            getProjects(count, page, sortBy, sort_type) {
                let url = this.ajaxEndPont + '/projects?' +
                    'count='      + ((count)     ? count     : 9    ) +
                    '&page='      + ((page)      ? page      : 1    ) +
                    '&sort_by='   + ((sortBy)    ? sortBy    : 'id' ) +
                    '&sort_type=' + ((sort_type) ? sort_type : 'asc');

                if (this.$props.vendorId) url += ('&vendor_id=' + this.$props.vendorId);

                return axios.get(url).then(r => {
                    if (r && r.data && r.data.data) {
                        this.projectsData = Object.assign([], r.data.data);
                        this.total = r.data.total;
                        this.lastPage = r.data.last_page;
                    }
                });
            }
        }
    }
</script>
<style lang="css">
    .projects-component{}
    .projects-component__list{
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        flex-wrap: wrap;
        margin: 0 -10px;
    }
</style>