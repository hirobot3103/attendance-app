<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Request_Attendance;
use App\Models\Request_Rest;
use App\Models\User;

class RequestStampAdminController extends Controller
{

    private function actionMain($pageId)
    {
        if ($pageId == 15) {
            $requestDates = Request_Attendance::where('status', '<>', $pageId)->orderBy('clock_in')->get();
        } else {
            $requestDates = Request_Attendance::where('status', 15)->orderBy('clock_in')->get();
        }
        $requestName  = User::all();


        return view('request-admin-list', compact('requestDates', 'requestName'));
    }

    public function index()
    {
        return $this->actionMain(15);
    }

    public function reqindex(int $pageId)
    {
        return $this->actionMain($pageId);
    }

    public function detail(int $id)
    {
        $attendanceUserName  = User::where('id', Auth::user()->id)->first();

        $attendanceDetailDates = Request_Attendance::where('id', $id)->first();
        $attendanceRestDates = Request_Rest::where('attendance_id', $attendanceDetailDates['attendance_id'])->get();

        $reqId = $id;
        $reqDate = date('Y-m-d', strtotime($attendanceDetailDates->clock_in));
        $reqName = $attendanceUserName->name;
        $reqClockIn = $attendanceDetailDates->clock_in;
        $reqClockOut = $attendanceDetailDates->clock_out;
        $reqDescript = $attendanceDetailDates->descript;
        $reqStat = $attendanceDetailDates->status;

        $dispDetailDates[] = [
            'id' => $reqId,
            'dateline' => $reqDate,
            'name' => $reqName,
            'clock_in' => $reqClockIn,
            'clock_out' => $reqClockOut,
            'descript'  => $reqDescript,
            'status'    => $reqStat,
        ];
        return view('attendance-detail', compact('dispDetailDates', 'attendanceRestDates'));
    }
}
