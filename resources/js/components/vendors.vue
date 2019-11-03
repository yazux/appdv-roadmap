<template>
    <div></div>
</template>
<script>
    import axios from 'axios';
    export default {
        name: 'vendors',
        props: {},
        components: {},
        data: function () {
            return {}
        },
        created() {
            this.$nextTick(() => {
               
            });
        },
        computed: {
            endpoint() {
                return location.protocol + '//'
                    + location.hostname
                    + (location.port ? (':' + location.port) : '');
            }
        },
        watch: {},
        methods: {}
    }
</script>
<style></style>