<?php

namespace App\Http\Controllers\Zoom;

use App\Http\Controllers\Controller;
use App\Traits\ZoomJWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Providers\BaseProvider as BaseCode;

class MeetingController extends Controller
{
    use ZoomJWT;

    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    public function list(Request $request)
    {
        $path = 'users/me/meetings';
        $response = $this->zoomGet($path);

        $data = json_decode($response->body(), true);
        $data['meetings'] = array_map(function (&$m) {
            $m['start_at'] = $this->toUnixTimeStamp($m['start_time'], $m['timezone']);
            return $m;
        }, $data['meetings']);

        return [
            'success' => $response->ok(),
            'data' => $data,
        ];
    }
    public function send_email_meeting($appointment_id ='',$json_repsonse = [],$mode = 'create')
    {
        if($appointment_id !='' && isset($json_repsonse) && BaseCode::checkArray($json_repsonse))
        {
            $appointment_arr = (array) DB::table('appointment_master')->find($appointment_id);
            if(isset($appointment_arr) && BaseCode::checkArray($appointment_arr))
            {
                $dr_arr = array();
                if(isset($appointment_arr['dr_id']) && $appointment_arr['dr_id'] !='')
                {
                    $dr_arr = (array) DB::table('doctor_master')->find($appointment_arr['dr_id']);
                }
                $dr_detail = '';
                $appointment_detail = '';
                $first_line = "Your meeting fixed, Find below details for the meeting:";
                if($mode != 'create')
                {
                    $first_line = "Your meeting details updated, Find below updated details for the meeting:";
                }
                $zoom_meeting = "
                    <p>".$first_line."</p>
                    <p>Topic: ".$json_repsonse['topic']."</p>
                    <p>Start Time: ".date('F j, Y h:i A',strtotime($json_repsonse['start_time']))."</p>
                    <p>Join Url: <a target='_blank' href='".$json_repsonse['join_url']."'>".$json_repsonse['join_url']."</a></p>
                    <p>Meeting ID: ".$json_repsonse['id']."</p>
                    <p>Password: ".$json_repsonse['password']."</p>
                    <p><hr/></p>
                ";
                if(isset($dr_arr) && BaseCode::checkArray($dr_arr))
                {
                    $dr_detail = "
                        <p>Dr Name: ".$dr_arr['name']."</p>
                        <p>Email: ".$dr_arr['email']."</p>
                        <p>Contact No: ".$dr_arr['contact']."</p>
                    ";
                }
                $meeting_data = array(
                    '#USERNAME#'=>$appointment_arr['name'],
                    '#ZOOM_MEETING#'=>$zoom_meeting.$dr_detail,
                );
                BaseCode::send_email_template('Meeting Detail',$meeting_data,$appointment_arr['email']);

                // for the send email to doctor
                if(isset($dr_arr) && BaseCode::checkArray($dr_arr))
                {
                    $zoom_meeting = "
                        <p>".$first_line."</p>
                        <p>Topic: ".$json_repsonse['topic']."</p>
                        <p>Start Time: ".date('F j, Y h:i A',strtotime($json_repsonse['start_time']))."</p>
                        <p>Start Meeting Url: <a target='_blank' href='".$json_repsonse['start_url']."'>Click to start meeting </a></p>
                        <p>Join Url: <a target='_blank' href='".$json_repsonse['join_url']."'>".$json_repsonse['join_url']."</a></p>
                        <p>Meeting ID: ".$json_repsonse['id']."</p>
                        <p>Password: ".$json_repsonse['password']."</p>
                        <p><hr/></p>
                        <p>Meeting with: ".$appointment_arr['name']."</p>
                        <p>Email: ".$appointment_arr['email']."</p>
                        <p>Contact No: ".$appointment_arr['contact']."</p>
                    ";
                    $meeting_data = array(
                        '#USERNAME#'=>$dr_arr['name'],
                        '#ZOOM_MEETING#'=>$zoom_meeting,
                    );
                    BaseCode::send_email_template('Meeting Detail',$meeting_data,$dr_arr['email']);
                }
            }
        }
    }
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|integer',
            'topic' => 'required|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'agenda' => 'string|nullable',
        ]);
        if ($validator->fails()) {
            $message = "";
            $err_message = json_decode($validator->errors());
            foreach ($err_message as $key=>$mess) {
                $message.=$mess[0].' ';
            }
            return [
                'success' => false,
                'message'=>nl2br($message),
                'data' => $validator->errors(),
            ];
        }
        $data = $validator->validated();

        $path = 'users/me/meetings';
        $response = $this->zoomPost($path, [
            'topic' => $data['topic'],
            'type' => self::MEETING_TYPE_SCHEDULE,
            'start_time' => $this->toZoomTimeFormat($data['start_date'].' '.$data['start_time']),
            'duration' => 30,
            'agenda' => $data['agenda'],
            'settings' => [
                'host_video' => true,
                'participant_video' => true,
                'waiting_room' => true,
            ]
        ]);

        if($response->status() === 201)
        {
            $appointment_id = $request->appointment_id;
            $json_repsonse = json_decode($response->body(), true);
            $json_repsonse['start_time'] = $this->toZoomTimeFormat($data['start_date'].' '.$data['start_time']);
            $data_arr = [
                'zoom_response'=>json_encode($json_repsonse)
            ];
            $resp = DB::table('appointment_master')->updateOrInsert(
                ['id'=>$appointment_id],
                $data_arr
            );
            if($resp)
            {
                $this->send_email_meeting($appointment_id,$json_repsonse,'create');
                return [
                    'success' => true,
                    'message'=>'Meeting successfully created.',
                    'data' => json_decode($response->body(), true),
                ];
            }
            else
            {
                return [
                    'success' => false,
                    'message'=>'Some error occurred, please try again',
                    'data' => '',
                ];
            }
        }
        else
        {
            return [
                'success' => $response->status() === 201,
                'message'=>'Some error occurred, please try again',
                'data' => json_decode($response->body(), true),
            ];
        }
    }
    public function get(Request $request, string $id)
    {
        $path = 'meetings/' . $id;
        $response = $this->zoomGet($path);

        $data = json_decode($response->body(), true);
        if ($response->ok()) {
            $data['start_at'] = $this->toUnixTimeStamp($data['start_time'], $data['timezone']);
        }

        return [
            'success' => $response->ok(),
            'data' => $data,
        ];
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'agenda' => 'string|nullable',
            'appointment_id' => 'required|integer',
            'meeting_id' => 'required',
        ]);

        if ($validator->fails()) {
            $message = "";
            $err_message = json_decode($validator->errors());
            foreach ($err_message as $key=>$mess) {
                $message.=$mess[0].' ';
            }
            return [
                'success' => false,
                'message'=>nl2br($message),
            ];
        }
        $data = $validator->validated();
        $id = $data['meeting_id'];
        $appointment_id = $data['appointment_id'];
        $path = 'meetings/' . $id;
        $response = $this->zoomPatch($path, [
            'topic' => $data['topic'],
            'type' => self::MEETING_TYPE_SCHEDULE,
            'start_time' => $this->toZoomTimeFormat($data['start_date'].' '.$data['start_time']),
            'duration' => 30,
            'agenda' => $data['agenda'],
            'settings' => [
                'host_video' => false,
                'participant_video' => false,
                'waiting_room' => true,
            ]
        ]);
        if($response->status() === 204)
        {
            $data_arr = [
                'zoom_response->topic'=>$data['topic'],
                'zoom_response->agenda'=>$data['agenda'],
                'zoom_response->start_time'=> $this->toZoomTimeFormat($data['start_date'].' '.$data['start_time']),
            ];
            $resp = DB::table('appointment_master')->updateOrInsert(
                ['id'=>$appointment_id],
                $data_arr
            );
            $appointment_row = (array) DB::table('appointment_master')->find($appointment_id);
            if(isset($appointment_row['zoom_response']) && $appointment_row['zoom_response'] !='')
            {
                $json_repsonse = json_decode($appointment_row['zoom_response'], true);
                $this->send_email_meeting($appointment_id,$json_repsonse,'update');
            }
            return [
                'success' => true,
                'message'=>'Meeting successfully updated.',
            ];
        }
        else
        {
            return [
                'success' => false,
                'message'=>'Some error occurred, please try again',
            ];
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|integer',
            'meeting_id' => 'required',
        ]);
        if ($validator->fails()) {
            $message = "";
            $err_message = json_decode($validator->errors());
            foreach ($err_message as $key=>$mess) {
                $message.=$mess[0].' ';
            }
            return [
                'success' => false,
                'message'=>nl2br($message),
            ];
        }
        $appointment_id = $request->appointment_id;
        $meeting_id = $request->meeting_id;
        if($meeting_id !='')
        {
            $path = 'meetings/' . $meeting_id;
            $response = $this->zoomDelete($path);
            if($response->status() === 204)
            {
                $appointment_arr = (array) DB::table('appointment_master')->find($appointment_id);
                $data_arr = [
                    'zoom_response'=>NULL
                ];
                $resp = DB::table('appointment_master')->updateOrInsert(
                    ['id'=>$appointment_id],
                    $data_arr
                );
                if(isset($appointment_arr['zoom_response']) && $appointment_arr['zoom_response'] !='')
                {
                    $json_repsonse = json_decode($appointment_arr['zoom_response'], true);
                    $dr_arr = array();
                    if(isset($appointment_arr['dr_id']) && $appointment_arr['dr_id'] !='')
                    {
                        $dr_arr = (array) DB::table('doctor_master')->find($appointment_arr['dr_id']);
                    }
                    $dr_detail = '';
                    $appointment_detail = '';
                    $first_line = "Your meeting cancelled,we let you know, Once any new meeting schedule";
                    $zoom_meeting = "
                        <p>".$first_line."</p>
                        <p>Topic: ".$json_repsonse['topic']."</p>
                        <p>Meeting ID: ".$json_repsonse['id']."</p>
                        <p><hr/></p>
                    ";
                    if(isset($dr_arr) && BaseCode::checkArray($dr_arr))
                    {
                        $dr_detail = "
                            <p>Dr Name: ".$dr_arr['name']."</p>
                            <p>Email: ".$dr_arr['email']."</p>
                            <p>Contact No: ".$dr_arr['contact']."</p>
                        ";
                    }
                    $meeting_data = array(
                        '#USERNAME#'=>$appointment_arr['name'],
                        '#ZOOM_MEETING#'=>$zoom_meeting.$dr_detail,
                    );
                    BaseCode::send_email_template('Meeting Detail',$meeting_data,$appointment_arr['email']);

                    // for the send email to doctor
                    if(isset($dr_arr) && BaseCode::checkArray($dr_arr))
                    {
                        $zoom_meeting = "
                            <p>".$first_line."</p>
                            <p>Topic: ".$json_repsonse['topic']."</p>
                            <p>Meeting ID: ".$json_repsonse['id']."</p>
                            <p><hr/></p>
                            <p>Meeting with: ".$appointment_arr['name']."</p>
                            <p>Email: ".$appointment_arr['email']."</p>
                            <p>Contact No: ".$appointment_arr['contact']."</p>
                        ";
                        $meeting_data = array(
                            '#USERNAME#'=>$dr_arr['name'],
                            '#ZOOM_MEETING#'=>$zoom_meeting,
                        );
                        BaseCode::send_email_template('Meeting Detail',$meeting_data,$dr_arr['email']);
                    }
                }
                return [
                    'success' => true,
                    'message'=>'Meeting successfully deleted',
                ];
            }
            else
            {
                return [
                    'success' => false,
                    'message'=>'Some error occurred, please try again',
                ];
            }
        }
        else
        {
            return [
                'success' => false,
                'message' => "Please provide Meeting ID"
            ];
        }
    }
}
