<template>
    <div class="content component all-vendors-component">
        <div class="all-vendors__list" v-for="vendor in vendorsData" v-if="vendor && vendor.id && vendor.projects && Object.keys(vendor.projects).length">
            <h4 class="page-title"><span class="page-title__value">{{vendor.name}}</span></h4>

            <work-progress
                v-if="vendor.projects_count && vendor.projecs_in_works_count"
                :all="vendor.projects_count" 
                :complete="(vendor.projects_count - vendor.projecs_in_works_count)"
                :work="vendor.projecs_in_works_count"
                :price="vendor.full_price"
            ></work-progress>

            <section v-if="vendor.projects && Object.keys(vendor.projects).length" class="all-vendors__projects-list">
                <project v-for="project in vendor.projects" v-bind:key="project.id" v-bind:project="project"></project>
            </section>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import moment from 'moment';
    import paginate from 'vuejs-paginate';
    import Project from './Project';
    import WorkProgress from './WorkProgress';
    export default {
        name: 'all-projects',
        components: {paginate, Project, WorkProgress},
        props: {
            showAllBtn: {
                type: Boolean,
                default: false
            },
            showTitle: {
                type: Boolean,
                default: false
            },
            vendors: {
                type: [Array, Object],
                default: () => {
                    return [];
                }
            }
        },
        data: function () {
            return {
                count: 9,
                current_page: 1,
                page: 1,
                total: 0,
                lastPage: 0,
                
                vendorsData: []
            }
        },
        created() {
            moment.locale("ru");
            this.moment = moment;

            this.$nextTick(() => {
                if (this.$props.vendors && Object.keys(this.$props.vendors).length) this.vendorsData = Object.assign({}, this.$props.vendors);
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
        }
    }
</script>
<style lang="css">
    .all-vendors__projects-list{
        display: flex;
        margin: 0 -10px 20px -10px;
    }
</style>