{{-- 管理者用詳細・修正ページ --}}
@php
  if (Auth::guard('admin')->check()){
    $dispDetailDates[0]['gardFlg'] = 1;
  } else {
    $dispDetailDates[0]['gardFlg'] = 0;  
  }

  if ($errors->any()) {
    $dispDetailDates[0]['id'] = old('id');
    $dispDetailDates[0]['user_id'] = old('user_id');   
    $dispDetailDates[0]['target_id'] = old('user_id');   
    $dispDetailDates[0]['dateline'] = old('dateline');   
    $dispDetailDates[0]['name'] = old('name');
    $dispDetailDates[0]['clock_in'] = old('attendance_clockin');
    $dispDetailDates[0]['clock_out'] = old('attendance_clockout');
    $dispDetailDates[0]['descript'] = old('descript');
    $dispDetailDates[0]['status'] = old('status');
    $dispDetailDates[0]['gardFlg'] = old('gardFlg');

    $attendanceRestDates[] = [
      'rest_id'      => old('rest_id')?old('rest_id'):"",
      'rest_in'      => old('rest_clockin')?old('rest_clockin'):"",
      'rest_out'     => old('rest_clockout')?old('rest_clockout'):"",
    ];
    if (old('restSectMax')  > 0 ) {
      for ($index = 1; $index <= old('restSectMax'); $index++) {
        $attendanceRestDates[] = [
          "rest_id" => old("rest_id{$index}"),
          "rest_in" => old("rest_clockin{$index}"),
          "rest_out" => old("rest_clockout{$index}"),
        ];      
      }
    }
  }
  $subTitle = "";
  if ($dispDetailDates[0]['gardFlg'] == 1) {
    $subTitle = "(管理者用ページ)";
  }
@endphp

@extends('layouts.app')

@section('subtitle',"勤怠詳細画面{$subTitle}")

@section('css')
  <link rel="stylesheet" href="{{ asset('assets/css/attendance-admin-detail.css') }}" />
@endsection

@section('header-contents')
  @if($dispDetailDates[0]['gardFlg'] == 1)
    <x-header-auth></x-header-auth>
  @else
    <x-header-user></x-header-user>
  @endif
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
            />
          </div>
        </div>
        @error("clock_in")
          <div class="validatin-error__area">&#x274C;&emsp;{{$message}}</div> 
        @enderror
        @error("clock_out")
          <div class="validatin-error__area">&#x274C;&emsp;{{$message}}</div> 
        @enderror
        @php
          $flg = 0;
        @endphp
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
            if(empty($restdate['id'])){
              $restdate['id'] = "";
            }

          @endphp
          <div class="rest-section">
            <div class="section__index">休憩{{ $sectNo }}</div>
            <div class="rest-section__clockin">
              @if (old("rest_id" . $sectNo))
                <input type="hidden" value="{{ old('rest_id' . $sectNo) }}" name="rest_id{{ $sectNo }}" form="detail-form">
              @else
                <input type="hidden" value="{{ $restdate['id'] }}" name="rest_id{{ $sectNo }}" form="detail-form">
              @endif
              <input
                type="text"
                class="clock-section"
                name="rest_clockin{{ $sectNo }}"
                id="rest_clockin{{ $sectNo }}"
                value="{{ $restStartTime }}"
                form="detail-form"
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
              />
            </div>
          </div>
          @error("rest_in" . $sectNo)
            <div class="validatin-error__area">&#x274C;&emsp;{{$message}}</div> 
          @enderror
          @error("rest_out" . $sectNo)
            <div class="validatin-error__area">&#x274C;&emsp;{{$message}}</div> 
          @enderror
          @php
            $sectionNumber++;
          @endphp
        @endforeach
        @if (( old("restSectMax") <> "" ))

        @else
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
              />
            </div>
            @if ($errors->has("rest_in{{$sectionNumber}}"))
              @foreach($errors->get("rest_in{{$sectionNumber}}") as $errorMassage )
                  <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
              @endforeach
            @endif
            @if ($errors->has("rest_out{{$sectionNumber}}"))
              @foreach($errors->get("rest_out{{$sectionNumber}}") as $errorMassage )
                  <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
              @endforeach
            @endif
          </div>
        @endif
        <div class="descript-section">
          <div class="section__index">備考</div>
          <div class="descript-section__content">
            <textarea name="descript" id="descript" form="detail-form">{{ $dispDetailDates[0]['descript'] }}</textarea>
          </div>
          @if ($errors->has('descript'))
            @foreach($errors->get('descript') as $errorMassage )
                <div class="validatin-error__area">&#x274C;&emsp;{{$errorMassage}}</div> 
            @endforeach
          @endif
        </div>
      </div>
      @if ( $dispDetailDates[0]['status'] >=11 && $dispDetailDates[0]['status'] <=13 )
        <p class="request-stat">承認待ち</p>
        <input type="hidden" value="{{ $dispDetailDates[0]['id'] }}" name="id">
        <input type="hidden" value="{{ $dispDetailDates[0]['target_id'] }}" name="user_id">
        <input type="hidden" value="{{ $dispDetailDates[0]['name'] }}" name="name">
        <input type="hidden" value="{{ $dispDetailDates[0]['dateline'] }}" name="dateline">
        <input type="hidden" value="{{ $dispDetailDates[0]['status'] }}" name="status">
        @if (old("restSectMax") <> "")
          <input type="hidden" value="{{ old("restSectMax") }}" name="restSectMax">
        @else
          <input type="hidden" value="{{ $sectionNumber }}" name="restSectMax">
        @endif
        @if (old("gardFlg") <> "")
          <input type="hidden" value="{{ old("gardFlg") }}" name="gardFlg">
        @else
          <input type="hidden" value="{{ $dispDetailDates[0]['gardFlg'] }}" name="gardFlg">
        @endif
      @elseif($dispDetailDates[0]['status'] == 15)
      <p class="request-stat">承認済み</p>      
      @else
        @if(Auth::guard('admin')->check())
          <form action="/stamp_correction_request/approve/{{ $dispDetailDates[0]['id'] }}" class="detail-form" id="detail-form" method="POST">
        @else
          <form action="/attendance/{{ $dispDetailDates[0]['id'] }}" class="detail-form" id="detail-form" method="POST">
        @endif
          @csrf
          <button type="submit" class="form-btn" name="admin_btn_mod">修  正</button>
          <input type="hidden" value="{{ $dispDetailDates[0]['id'] }}" name="id">
          <input type="hidden" value="{{ $dispDetailDates[0]['target_id'] }}" name="user_id">
          <input type="hidden" value="{{ $dispDetailDates[0]['name'] }}" name="name">
          <input type="hidden" value="{{ $dispDetailDates[0]['dateline'] }}" name="dateline">
          <input type="hidden" value="{{ $dispDetailDates[0]['status'] }}" name="status">
          @if (old("restSectMax") <> "")
            <input type="hidden" value="{{ old("restSectMax") }}" name="restSectMax">
          @else
            <input type="hidden" value="{{ $sectionNumber }}" name="restSectMax">
          @endif
          @if (old("gardFlg") <> "")
            <input type="hidden" value="{{ old("gardFlg") }}" name="gardFlg">
          @else
            <input type="hidden" value="{{ $dispDetailDates[0]['gardFlg'] }}" name="gardFlg">
          @endif
        </form>
      @endif
    </section>
    <p>attendance-staff-detail.blade.php</p>
  </main>
@endsection