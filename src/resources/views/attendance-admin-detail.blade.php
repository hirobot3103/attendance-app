{{-- 管理者用ページ：承認直前の勤怠詳細 --}}
@extends('layouts.app')

@section('subtitle','勤怠詳細画面(管理者ページ)')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/attendance-admin-detail.css') }}" />
@endsection

@section('header-contents')
  <x-header-auth></x-header-auth>
@endsection  

@section('main-contents')
  @php
    $dateStrings =preg_split('/[-]/', $dispDetailDates[0]['dateline'] );
    $startTime = $dispDetailDates[0]['clock_in'] <> "" ? date('H:i', strtotime($dispDetailDates[0]['clock_in'])) : "";
    $endTime = $dispDetailDates[0]['clock_out']  <> "" ? date('H:i', strtotime($dispDetailDates[0]['clock_out'])) : "";
    $sectionNumber = 0;
  @endphp
  <main class="contents">
    <section class="contents__lists-area">
      <div class="attendance-title">勤怠詳細</div>
      <div class="attendance-list">
        <div class="name-section">
          <div class="section__index">名前</div>
          <div class="name-section__content">{{ $dispDetailDates[0]['name'] }}</div>
        </div>
        <div class="date-section">
          <div class="section__index">日付</div>
          <div class="date-section__year">{{ $dateStrings[0] }}年</div>
          <div class="date-section__space">&nbsp;</div>
          <div class="date-section__month">{{ $dateStrings[1] }}月{{ $dateStrings[2] }}日</div>
        </div>
        <div class="att-section">
          <div class="section__index">出勤・退勤</div>
          <div class="att-section__clockin">
            <input
              type="text"
              class="clock-section"
              name="attendance_clockin"
              id="attendance_clockin"
              value="{{ $startTime }}"
              form="detail-form"
              readonly
            />
          </div>
          <div class="att-section__space"><span>～</span></div>
          <div class="att-section__clockout">
            <input
              type="text"
              class="clock-section"
              name="attendance_clockout"
              id="attendance_clockout"
              value="{{ $endTime }}"
              form="detail-form"
              readonly
            />
          </div>
        </div>

        @php
          $flg = 0;
        @endphp
        {{-- {{ dd($attendanceRestDates); }} --}}
        @foreach ( $attendanceRestDates as $restdate )
          @php
            $flg = 1;

            $restStartTime = $restdate['rest_in']  <> "" ? date('H:i', strtotime($restdate['rest_in']))  : "";
            $restEndTime   = $restdate['rest_out'] <> "" ? date('H:i', strtotime($restdate['rest_out'])) : "";

            if ($sectionNumber == 0) {
              $sectNo = "";
            } else {
              $sectNo = $sectionNumber;
            }

          @endphp
          <div class="rest-section">
            <div class="section__index">休憩{{ $sectNo }}</div>
            <div class="rest-section__clockin">
              <input type="hidden" value="{{ $restdate['id'] }}" name="rest_id{{ $sectNo }}" form="detail-form">
              <input
                type="text"
                class="clock-section"
                name="rest_clockin{{ $sectNo }}"
                id="rest_clockin{{ $sectNo }}"
                value="{{ $restStartTime }}"
                form="detail-form"
                readonly
              />
            </div>
            <div class="rest-section__space"><span>～</span></div>
            <div class="rest-section__clockout">
              <input
                type="text"
                class="clock-section"
                name="rest_clockout{{ $sectNo }}"
                id="rest_clockout{{ $sectNo }}"
                value="{{ $restEndTime }}"
                form="detail-form"
                readonly
              />
            </div>
          </div>
          @php
            $sectionNumber++;
          @endphp
        @endforeach
        @if (($sectionNumber <> 0) or ($flg == 0))
          @php
            if ($flg == 0){
              $sectionNumber = "";
            }
          @endphp
          <div class="rest-section">
            <div class="section__index">休憩{{ $sectionNumber }}</div>
            <div class="rest-section__clockin">
              <input type="hidden" value="" name="rest_id{{ $sectionNumber }}" form="detail-form">
              <input
                type="text"
                class="clock-section"
                name="rest_clockin{{ $sectionNumber }}"
                id="rest_clockin{{ $sectionNumber }}"
                value=""
                form="detail-form"
                readonly
              />
            </div>
            <div class="rest-section__space"><span>～</span></div>
            <div class="rest-section__clockout">
              <input
                type="text"
                class="clock-section"
                name="rest_clockout{{ $sectionNumber }}"
                id="rest_clockout{{ $sectionNumber }}"
                value=""
                form="detail-form"
                readonly
              />
            </div>
          </div>
        @endif

        <div class="descript-section">
          <div class="section__index">備考</div>
          <div class="descript-section__content">
            <textarea name="descript" id="descript" form="detail-form" readonly>{{ $dispDetailDates[0]['descript'] }}</textarea>
          </div>
        </div>
      </div>
      <section class="approve_btn">
        @if ( $dispDetailDates[0]['status'] == 15 )
          <p class="request-stat">承認済み</p>
        @else
          <form action="/stamp_correction_request/approve/{{ $dispDetailDates[0]['id'] }}" class="detail-form" id="detail-form" method="POST">
            @csrf
            <button type="submit" class="form-btn">承　認</button>
            <input type="hidden" value="{{ $dispDetailDates[0]['name'] }}" name="name">
            <input type="hidden" value="{{ $dispDetailDates[0]['dateline'] }}" name="dateline">
            <input type="hidden" value="{{ $dispDetailDates[0]['status'] }}" name="status">
            <input type="hidden" value="{{ $sectionNumber }}" name="restSectMax">
          </form>
        @endif
      </section>
    </section>
    <p>$attendanceRestDates</p>
    <p>{{$attendanceRestDates}}</p>
    <p>attendance-admin-detail.php</p>
  </main>
@endsection