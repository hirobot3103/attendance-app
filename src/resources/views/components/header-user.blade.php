<header class="page-header">
  <div class="page-logo">
    <img src="{{ asset('assets/img/logo.svg') }}" alt="ロゴ COACHTECH" />
  </div>
  <div class="page-logo"></div>
  <nav class="page-menu">
    <ul>
      <li><a href="{{ route('user.dashboard') }}">勤怠</a></li>
      <li><a href="{{ route('user.attendant-list') }}">勤怠一覧</a></li>
      <li><a href="{{ route('attendant-req') }}">申請</a></li>
      @if (Auth::guard('web')->check())
        <li>
          <form action="{{ route('logout') }}" method="post">
              @csrf
              <button type="submit" class="page-menu-btn">ログアウト</button>
          </form>
        </li>
      @else
        <li><a href="{{ route('login') }}">ログイン</a></li>
      @endif
    </ul>
  </nav>
</header>