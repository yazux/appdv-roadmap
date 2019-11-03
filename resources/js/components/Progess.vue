<template>
    <div class="content component progress-component">

    </div>
</template>
<script>
    import axios from 'axios';
    export default {
        name: 'progress',
        props: {
            all: {
                type: Number,
                default: 0,
            },
            complete: {
                type: Number,
                default: 0,
            },
            work: {
                type: Number,
                default: 0,
            }
        },
        components: {},
        data: function () {
            return {}
        },
        created() {
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
            }
        },
        watch: {},
        methods: {}
    }
</script>
<style lang="css"></style>