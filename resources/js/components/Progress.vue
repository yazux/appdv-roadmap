<template>
    <div class="content component progress-component">
        <div class="progress-component__number">
            <div class="progress-component__number-item">
                <span class="progress-component__number-item-value">{{parseInt($props.all)}}</span>
                <span class="progress-component__number-item-text">Заключено</span>
            </div>
            <div class="progress-component__number-item">
                <span class="progress-component__number-item-value">{{parseInt($props.complete)}}</span>
                <span class="progress-component__number-item-text">Выполненно</span>
            </div>
            <div class="progress-component__number-item">
                <span class="progress-component__number-item-value">{{parseInt($props.work)}}</span>
                <span class="progress-component__number-item-text">В работе</span>
            </div>
        </div>
        <div class="progress-component__progress">
            <div class="progress-component__progress-line">
                <span v-bind:style="'width: ' + (Math.round(100*parseInt($props.complete)/parseInt($props.all))) + 'px;'"></span>
            </div>
        </div>
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