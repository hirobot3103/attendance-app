<header class="page-header">
  <div class="page-logo">
    <img src="{{ asset('assets/img/logo.svg') }}" alt="ロゴ COACHTECH" />
  </div>
  <div class="page-logo"></div>
  <nav class="page-menu">
    <ul>
      <li><a href="{{ route('admin.dashboard') }}">勤怠一覧</a></li>
      <li><a href="{{ route('admin.stafflist') }}">スタッフ一覧</a></li>
      <li><a href="{{ route('admin.attendant-req') }}">申請一覧</a></li>
      @if (Auth::guard('admin')->check())
        <li>
          <form action="{{ route('admin.logout') }}" method="post">
              @csrf
              <button type="submit" class="page-menu-btn">ログアウト</button>
          </form>
        </li>
      @else
        <li><a href="{{ route('admin.login') }}">ログイン</a></li>
      @endif        
    </ul>
  </nav>
</header>