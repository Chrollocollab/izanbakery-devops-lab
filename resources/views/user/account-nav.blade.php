<ul class="account-nav">
    <li><a href="{{route('user.profile')}}" class="menu-link menu-link_us-s {{ request()->routeIs('user.profile') ? 'active' : '' }}">Profile</a></li>
    <li><a href="{{route('user.orders')}}" class="menu-link menu-link_us-s {{ request()->routeIs('user.orders*') ? 'active' : '' }}">My Orders</a></li>
    <li><a href="{{route('user.password')}}" class="menu-link menu-link_us-s {{ request()->routeIs('user.password') ? 'active' : '' }}">Change Password</a></li>
    <li><a href="{{route('user.feedback')}}" class="menu-link menu-link_us-s {{ request()->routeIs('user.feedback*') ? 'active' : '' }}">Customer Feedback</a></li>
    <li>
        <form method="POST" action="{{route('logout')}}" id="logout-form">
            @csrf
            <a href="{{route('logout')}}" class="menu-link menu-link_us-s" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
        </form>
    </li>
</ul>  