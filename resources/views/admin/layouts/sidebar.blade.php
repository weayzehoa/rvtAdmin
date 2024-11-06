<aside class="main-sidebar sidebar-dark-primary bg-navy elevation-4">
    <a href=" {{ route('admin.dashboard') }} " class="brand-link bg-navy text-center">
        <img src="{{ asset('img/icarry-logo-white.png') }}" alt="iCarry Logo"
            class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-light text-yellow float-left">iCarry 後台管理系統</span>
    </a>
    <div class="sidebar">
        <nav id="sidebar" class="mt-2 nav-compact">
            {{-- SEARCH FORM --}}
            <form action="{{ url('users') }}" method="GET" class="form-inline">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" name="keyword" placeholder="輸入關鍵字查找使用者" aria-label="Search" title="輸入使用者id、姓名、電話、地址或推薦人id快速查找使用者">
                    <div class="input-group-append">
                        <button class="btn btn-navbar bg-white" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <hr class="bg-gray mt-1 mb-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ url('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p class="text-sm">首頁</p>
                    </a>
                </li>
                @foreach($mainmenus as $mainmenu)
                @if(in_array($mainmenu->code,explode(',',Auth::user()->power ?? '' )))
                @if($mainmenu->type == 1)
                @if($mainmenu->url_type == 1)
                <li class="nav-item">
                    <a href="{{ url($mainmenu->url) }}" class="nav-link" {{ $mainmenu->open_window ? 'target="_blank"' : '' }}>
                        {!! $mainmenu->fa5icon !!}
                        <p class="text-sm">
                            {{ $mainmenu->name }}
                        </p>
                    </a>
                </li>
                @elseif($mainmenu->url_type == 2)
                <li class="nav-item">
                    <a href="{{ $mainmenu->url }}" class="nav-link" {{ $mainmenu->open_window ? 'target="_blank"' : '' }}>
                        {!! $mainmenu->fa5icon !!}
                        <p class="text-sm">
                            {{ $mainmenu->name }}
                        </p>
                    </a>
                </li>
                @else
                <li class="nav-item has-treeview">
                    <a href="{{ $mainmenu->url ? $mainmenu->url : 'javascript:' }}" class="nav-link" {{ $mainmenu->open_window ? 'target="_blank"' : '' }}>
                        {!! $mainmenu->fa5icon !!}
                        <p class="text-sm">
                            {{ $mainmenu->name }}
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                    @foreach($mainmenu->submenu as $submenu)
                    @if(in_array($submenu->code,explode(',',Auth::user()->power ?? '' )))
                    @if($submenu->code == 'M1S2' && in_array(Auth::user()->id,[1,2,40]))
                    <li class="nav-item">
                        <a href="{{ $submenu->url ? url($submenu->url) : 'javascript:' }}" class="nav-link" {{ $submenu->open_window ? 'target="_blank"' : '' }}>
                            {!! $submenu->fa5icon !!}
                            <p class="text-sm">{{ $submenu->name }}</p>
                        </a>
                    </li>
                    @elseif($submenu->code != 'M1S2')
                    <li class="nav-item">
                        <a href="{{ $submenu->url ? url($submenu->url) : 'javascript:' }}" class="nav-link" {{ $submenu->open_window ? 'target="_blank"' : '' }}>
                            {!! $submenu->fa5icon !!}
                            <p class="text-sm">{{ $submenu->name }}</p>
                        </a>
                    </li>
                    @endif
                    @endif
                    @endforeach
                    </ul>
                </li>
                @endif
                @endif
                @endif
                @endforeach
                {{-- 登出 --}}
                <li class="nav-item">
                    <a href="{{ route('admin.logout') }}" class="nav-link">
                        <i class="nav-icon fas fa-door-open text-danger"></i>
                        <p class="text-sm">登出 (Logout)</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>


<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
