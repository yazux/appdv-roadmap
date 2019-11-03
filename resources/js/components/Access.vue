<template>
    <div class="component access-component">
        <div v-if="show" class="access-overlay">
            <div v-bind:class="{'access-form': true, 'loading': loading}">
                <h4 class="access-title">Введите ключ доступа</h4>
                <input class="access-password" type="text" autocomplete="off" name="password" v-model="password"/>
                <p class="access-message" v-if="message">{{message}}</p>
                <a href="javascript:void(0)" class="btn access-btn" v-on:click="send">Отправить</a>
            </div>
        </div>
    </div>
</template>
<script>
    import axios from 'axios';
    import paginate from 'vuejs-paginate';
    export default {
        name: 'access',
        props: {
            enable: {
                type: Number,
                default: 0
            }
        },
        components: {},
        data: function () {
            return {
                show: false,
                loading: false,
                message: '',
                password: ''
            }
        },
        created() {
            this.$nextTick(() => this.init());
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
            init() {
                if (parseInt(this.$props.enable) && !localStorage.getItem('access')) this.show = true;
            },
            send() {
                this.loading = true;
                return axios.post(this.ajaxEndPont + '/access', {password: this.password}).then(r => {
                    if (r && r.data && r.data.access) {
                        localStorage.setItem('access', true);
                        this.show = false;
                    } else this.message = 'В доступе отказанно, убедитесь в правильности ввода ключа.';
                }).finally(() => this.loading = false);
            }
        }
    }
</script>
<style lang="css">
    .access-overlay{
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        z-index: 9999;
        width: 100%;
        height: 100vh;
        background: rgba(0,0,0,0.95);
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        transition: 0.5s ease-in-out;
    }
    .access-form{
        color: var(--text-color-black);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        box-shadow: 0 0 14px rgba(0,0,0,.1);
        padding: 50px 40px;
        width: 300px;
        border-radius: 4px;
        background-color: #fff;
        transition: 0.5s ease-in-out;
    }
    .access-form.loading{
        opacity: 0.4;
    }
    .access-title{
        margin: 0 0 40px 0;
        font-size: 20px;
        line-height: 20px;
    }
    .access-password{
        width: 100%;
        background: #fff;
        border: 1px solid #d5d4d4;
        border-radius: 4px;
        height: 40px;
        padding: 0 15px;
        box-sizing: border-box;
        margin: 0 0 20px 0;
        outline: none !important;
        transition: 0.25s ease-in-out;
    }
    .access-password:focus,
    .access-password:active{
        border-color: var(--color-two);
    }
    .access-message{
        margin: 0 0 20px 0;
        text-align: center;
        font-size: 14px;
        color: red;
    }
    .access-btn{
        padding: 15px 30px;
    }
</style>