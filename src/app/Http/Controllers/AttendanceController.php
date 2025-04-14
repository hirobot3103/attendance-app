<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $todayDate = new Carbon();
        $loginUserId = Auth::user()->id;
        $userAttendanceData = Attendance::where('user_id', $loginUserId)
            ->whereDate('clock_in', $todayDate->format('y-m-d'))
            ->first();

        if (empty($userAttendanceData)) {

            $params = [
                'status' => 0,
            ];
        } else {
            $params = [
                'status' => $userAttendanceData->status,
            ];
        }
        $firstTime = $todayDate->format('Y-m-d h:i');
        return view('top', compact('params', 'firstTime'));
    }

    public function action(Request $request)
    {
        $todayDate = new Carbon();
        $loginUserId = Auth::user()->id;

        // 出勤ボタン押下時
        if ($request->has('clock_in')) {
            $params = [
                'user_id'  => $loginUserId,
                'clock_in' => $todayDate::now()->format('y-m-d H:i:00'),
                'status'   => 1,
            ];

            Attendance::create($params);
            return redirect('/attendance');
        }

        // 退勤ボタン押下時
        if ($request->has('clock_out')) {

            $userAttendanceData = Attendance::where('user_id', $loginUserId)
                ->whereDate('clock_in', $todayDate->format('y-m-d'))
                ->first();

            $params = [
                'clock_out' => $todayDate::now()->format('y-m-d H:i:00'),
                'status'    => 2,
            ];
            $userAttendanceData->update($params);
            return redirect('/attendance');
        }

        // 休憩入ボタン押下時
        if ($request->has('rest_in')) {

            $userAttendanceData = Attendance::where('user_id', $loginUserId)
                ->whereDate('clock_in', $todayDate->format('y-m-d'))
                ->first();

            $params = [
                'status'    => 3,
            ];
            $userAttendanceData->update($params);

            $params = [
                'attendance_id' => $userAttendanceData->id,
                'rest_in'       => $todayDate::now()->format('y-m-d H:i:00'),
            ];
            Rest::create($params);
            return redirect('/attendance');
        }

        // 休憩戻ボタン押下時
        if ($request->has('rest_out')) {

            $userAttendanceData = Attendance::where('user_id', $loginUserId)
                ->whereDate('clock_in', $todayDate->format('y-m-d'))
                ->first();

            $params = [
                'status'    => 1,
            ];
            $userAttendanceData->update($params);

            $restDates = Rest::where('attendance_id', $userAttendanceData->id)
                ->whereNull('rest_out')
                ->first();

            $params = [
                'attendance_id' => $userAttendanceData->id,
                'rest_out'       => $todayDate::now()->format('y-m-d H:i:00'),
            ];
            $restDates->update($params);
            return redirect('/attendance');
        }
    }
}
