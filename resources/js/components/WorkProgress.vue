<template>
    <div class="content component progress-component">
        <div class="progress-component__number">
            <circle-progress
                v-if="symbol == '%'" 
                id="circle-progress"
                title="Заключено"
                width="100px"
                :text-size="22"
                :title-size="16"
                :value="parseInt($props.all)"
                :symbol="$props.symbol"
            ></circle-progress>
            <div v-else class="progress-component__number-item">
                <span class="progress-component__number-item-value">{{parseInt($props.all)}}</span>
                <span class="progress-component__number-item-text">Заключено</span>
            </div>

            <circle-progress
                v-if="symbol == '%'" 
                id="circle-progress"
                title="Выполненно"
                width="100px"
                :text-size="22"
                :title-size="16"
                :value="parseInt($props.complete)"
                :symbol="$props.symbol"
            ></circle-progress>
            <div v-else class="progress-component__number-item">
                <span class="progress-component__number-item-value">{{parseInt($props.complete)}}</span>
                <span class="progress-component__number-item-text">Выполненно</span>
            </div>

            <circle-progress
                v-if="symbol == '%'" 
                id="circle-progress"
                title="В работе"
                width="100px"
                :text-size="22"
                :title-size="16"
                :value="parseInt($props.work)"
                :symbol="$props.symbol"
            ></circle-progress>
            <div v-else class="progress-component__number-item">
                <span class="progress-component__number-item-value">{{parseInt($props.work)}}</span>
                <span class="progress-component__number-item-text">В работе</span>
            </div>
        </div>
        <div class="progress-component__progress">
            <div v-if="$props.price" class="progress-component__price">
                <span class="progress-component__price-title">Суммарная стоимость контролируемых СМР: </span>
                <span class="progress-component__price-value">{{ $props.price + ' руб.' }}</span>
            </div>
            <div class="progress-component__progress-container" v-if="$props.all">
                <p class="progress-component__progress-title">Прогресс выполнения работ</p>
                <div class="progress-component__progress-line">
                    <span class="progress-component__progress-line__progress" v-bind:style="'width: ' + (Math.round(100*parseInt($props.complete)/parseInt($props.all))) + '%;'"></span>
                    <span class="progress-component__progress-line__value">{{ (Math.round(100*parseInt($props.complete)/parseInt($props.all))) + '% (' + $props.complete + ' из ' + $props.all + ')'}}</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import CircleProgress from './CircleProgress';
    export default {
        name: 'work-progress',
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
            },
            price: {
                type: [String, Number],
                default: () => {
                    return 0;
                },
            },
            symbol: {
                type: String,
                default: ''
            }
        },
        components: {CircleProgress},
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
<style lang="css">
    .progress-component{
        margin: 20px 0;
        color: var(--text-color-black);
        display: flex;
        align-items: center;
        justify-content: flex-start;
        box-shadow: 0 0 14px rgba(0,0,0,.1);
        padding: 20px;
        width: calc(100% - 40px);
        border-radius: 4px;
        background-color: #fff;
    }
    .progress-component__number{
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        width: 40%;
        margin: 0 60px 0 0;
    }
    .progress-component__number-item{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .progress-component__number-item-value{
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        border: 7px solid var(--color-two);
        width: 66px;
        height: 66px;
        border-radius: 100%;
        margin: 0 auto;
    }
    .progress-component__number-item-text{
        font-size: 16px;
        line-height: 18px;
        font-weight: normal;
        margin: 10px 0 0 0;
    }

    .progress-component__price{
        margin-bottom: 15px;
    }
    .progress-component__price-title{
        font-size: 18px;
    }
    .progress-component__price-value{
        color: var(--color-two);
        font-size: 19px;
        line-height: 19px;
        font-weight: bold;
    }

    .progress-component__progress{
        width: 60%;
    }
    .progress-component__progress-title{
        font-size: 18px;
        margin: 0 0 10px 0;
    }
    .progress-component__progress-line{
        width: 100%;
        background-color: #3f444d;
        height: 20px;
        border-radius: 60px;
        position: relative;
    }
    .progress-component__progress-line__progress{
        background: #de6d59;
        height: 100%;
        display: block;
        border-radius: 60px;
    }
    .progress-component__progress-line__value{
        position: absolute;
        top: 0;
        left: calc(50% - 50px);
        width: 100px;
        color: #fff;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        line-height: 15px;
    }
</style>