<nav class="header-nav">
    <ul class="header-nav__list">
        <li class="header-nav__list__item">
            <a href="{{route('home')}}" class="header-nav__link {{(\Request::route() && \Request::route()->getName() == 'home') ? 'active' : ''}}">Главная</a>
        </li>
        <li class="header-nav__list__item">
            <a href="{{route('projects')}}" class="header-nav__link {{(\Request::route() && \Request::route()->getName() == 'projects') ? 'active' : ''}}">Объекты</a>
        </li>
        <li class="header-nav__list__item">
            <a href="{{route('static', ['slug' => 'info'])}}" class="header-nav__link {{(\Request::route() && \Request::route('slug') == 'info') ? 'active' : ''}}">Информация</a>
        </li>

        @if(setting('site.instagram_link'))
            <li class="header-nav__list__item-social">
                <a href="{{setting('site.instagram_link')}}" class="header-nav__link-social" target="_blank">
                    <img src="/public/img/instagram.svg" alt="instagram">
                </a>
            </li>
        @endif
        @if(setting('site.whatsapp'))
            <li class="header-nav__list__item-social">
                <a href="https://api.whatsapp.com/send?phone={{setting('site.whatsapp')}}" class="header-nav__link-social" target="_blank">
                    <img src="/public/img/whatsapp.svg" alt="whatsapp">
                </a>
            </li>
        @endif
    </ul>
</nav>