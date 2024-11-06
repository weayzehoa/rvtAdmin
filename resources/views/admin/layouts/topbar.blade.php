<nav id="topbar" class="main-header navbar navbar-expand navbar-dark bg-navy">
    {{-- <ul class="navbar-nav">
        <li class="nav-item">
            <a href="{{ url('/') }}" class="nav-link text-primary" target="_blank">前台首頁</a>
        </li>
    </ul> --}}
    <span class="ml-3">iCarry - 台灣伴手禮，交給「我來寄」後台管理系統 {{ env('APP_VERSION') }}</span>
    <ul class="navbar-nav ml-auto">
        {{-- SEARCH FORM --}}
        @if(in_array('M5S1',explode(',',Auth::user()->power)))
        <form action="{{ url('users') }}" method="GET" class="form-inline">
            <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar" type="search" name="keyword" placeholder="輸入關鍵字查找使用者" aria-label="Search" title="輸入使用者id、姓名、電話、地址或推薦人id快速查找使用者">
                <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
        @endif
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <i class="fas fa-user mr-2"></i><span class="d-none d-md-inline">{{ Auth::user()->name ?? '' }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item dropdown-header">{{ Auth::user()->email ?? '' }}</span>
                <div class="dropdown-divider"></div>
                <a href="{{ url('admins/changePassWord') }}" class="dropdown-item">
                    <i class="fas fa-cog mr-2"></i><span>個人資料變更</span>
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('admin.logout') }}" class="dropdown-item">
                    <i class="fas fa-door-open text-danger mr-2"></i><span>登出 (Logout)</span>
                </a>
            </div>
        </li>
        <li class="nav-item">
            <a href="#" title="縮小側邊選單" class="nav-link" data-widget="pushmenu" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
            <a href="javascript:" id="fullscreen-button" title="擴展成全螢幕" class="nav-link" data-widget="fullscreen" data-slide="true" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
        {{-- <li class="nav-item">
            <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
                <i class="fas fa-th-large"></i>
            </a>
        </li> --}}
    </ul>
</nav>
