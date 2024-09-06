<div class="navigation">
    <ul>
        <li>
            <a href="{{ route('admin.dashboard') }}">
                <span class="icon">
                    <ion-icon name="logo-apple"></ion-icon>
                </span>
                <span class="title">{{ get_settings_value('website_name') ?? 'Alata' }}</span>
            </a>
        </li>
        @can('read users')
        <li class="{{ Route::is('admin.dashboard') ? 'hovered-menu' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <span class="icon">
                    <ion-icon name="home-outline"></ion-icon>
                </span>
                <span class="title">Dashboard</span>
            </a>
        </li>
        @endcan

        @can('read users')
        <li class="{{ Route::is('admin.users*') ? 'hovered-menu' : '' }}">
            <a href="{{ route('admin.users.index') }}">
                <span class="icon">
                    <ion-icon name="people-outline"></ion-icon>
                </span>
                <span class="title">Customers</span>
            </a>
        </li>
        @endcan

        <li class="{{ request()->is('cp/manage-admin/roles*') ? 'hovered-menu' : '' }}">
            <a href="{{ route('admin.roles.index') }}">
                <span class="icon">
                    <ion-icon name="chatbubble-outline"></ion-icon>
                </span>
                <span class="title">Roles & Permissions</span>
            </a>
        </li>

        <li class="{{ request()->is('cp/products*') ? 'hovered-menu' : '' }}">
            <a href="{{ route('admin.products.index') }}">
                <span class="icon">
                    <ion-icon name="help-outline"></ion-icon>
                </span>
                <span class="title">Products</span>
            </a>
        </li>

        <li class="{{ request()->is('cp/properties*') ? 'hovered-menu' : '' }}">
            <a href="{{ route('admin.properties.index') }}">
                <span class="icon">
                    <ion-icon name="settings-outline"></ion-icon>
                </span>
                <span class="title">Properties</span>
            </a>
        </li>

        <li class="{{ request()->is('cp/categories*') ? 'hovered-menu' : '' }}">
            <a href="{{ route('admin.categories.index') }}">
                <span class="icon">
                    <ion-icon name="list-outline"></ion-icon>
                </span>
                <span class="title">Services/Categories</span>
            </a>
        </li>

        {{-- <li class="{{ Route::is('admin.transactions.index') ? 'hovered-menu' : '' }}">
            <a href="{{ route('admin.transactions.index') }}">
                <span class="icon">
                    <ion-icon name="list-outline"></ion-icon>
                </span>
                <span class="title">Transactions</span>
            </a>
        </li> --}}

        <li>
            <a href="#" onclick="document.getElementById('logoutForm').submit()"> 
                <span class="icon">
                    <ion-icon name="log-out-outline"></ion-icon>
                </span>
                <span class="title">Sign Out</span>
            </a>
        </li>
    </ul>
</div>


<form action="{{ route('admin.logout') }}" id="logoutForm" method="post">
    @csrf
</form>
