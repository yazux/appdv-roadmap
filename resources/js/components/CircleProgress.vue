<template>
    <div class="component circle-progress-component" v-if="$props.value">
        <div class="flex-wrapper">
            <div class="single-chart" v-bind:style="('width: ' + $props.width + ';')">
                <div class="single-chart__container">
                    <svg viewBox="0 0 36 36" class="circular-chart green">
                        <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="circle" v-bind:stroke-dasharray="(parseInt($props.value) + ', 100')" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <span class="percentage" v-bind:style="('font-size: ' + $props.textSize + 'px; line-height: ' + (textSize+2) + 'px;')">{{ (parseInt($props.value) + $props.symbol) }}</span>
                </div>
                <p class="single-chart__title" v-if="$props.title" v-bind:style="('font-size: ' + $props.titleSize + 'px; line-height: ' + (titleSize+2) + 'px;')" >{{$props.title}}</p>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        name: 'circle-progress',
        props: {
            title: {
                type: String,
                default: ''
            },
            value: {
                type: Number,
                default: 0
            },
            width: {
                type: String,
                default: '100px'
            },
            textSize: {
                type: Number,
                default: '22'
            },
            titleSize: {
                type: Number,
                default: '20'
            },
            symbol: {
                type: String,
                default: ''
            }
        },
        components: {},
        data: function () {
            return {}
        },
        created() {},
        computed: {},
        watch: {},
        methods: {}
    }
</script>
<style lang="css">
    .circle-progress-component{}
    .flex-wrapper {
        display: flex;
        flex-flow: row nowrap;
    }

    .single-chart {
        width: 33%;
        justify-content: space-around ;
    }

    .circular-chart {
        display: block;
        margin: 0 auto;
        max-width: 80%;
        max-height: 250px;
    }

    .circle-bg {
        fill: none;
        stroke: #eee;
        stroke-width: 3.8;
    }

    .circle {
        fill: none;
        stroke-width: 2.8;
        stroke-linecap: round;
        animation: progress 1s ease-out forwards;
    }

    @keyframes progress {
        0% {
            stroke-dasharray: 0 100;
        }
    }

    .circular-chart.orange .circle {
        stroke: #ff9f00;
    }

    .circular-chart.green .circle {
        stroke: var(--color-two);
    }

    .circular-chart.blue .circle {
        stroke: #3c9ee5;
    }

    .percentage {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #000;
        font-size: 22px;
        line-height: 22px;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 100%;
    }

    .single-chart__container{
        position: relative;
    }
    .single-chart__title{
        font-size: 14px;
        line-height: 16px;
        text-align: center;
        margin: 10px 0 0 0;
    }
</style>