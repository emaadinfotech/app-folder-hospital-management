<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;

class AppointmentContr extends Controller
{
    public function appointment(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->appointment_save($req);
        }
        BaseCode::$data['search_session_name'] = 'search_session_appoint';
        BaseCode::$data['short_order_data'] = 'short_order_appoint';
        BaseCode::$data['table_name'] = 'appointment_master';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Appointment';
        BaseCode::$data['class_name'] = 'appointment';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('appointment');

        BaseCode::$data['show_status_arr'] = array("A"=>'Approved', "I"=>'Pending','C'=>'Cancelled');
        BaseCode::$data['change_status_arr'] = array("A"=>'Approve', "I"=>'Pending','C'=>'Cancelled');
        BaseCode::$data['status_btn_color_arr'] = array("A"=>'btn-success', "I"=>'btn-info','C'=>'btn-warning');
        BaseCode::$data['status_fa_arr'] = array("A"=>'fa fa-thumbs-up', "C"=>'fa fa-thumbs-down','I'=>'fa fa-bars');
        BaseCode::$data['status_arr_color_dm'] = array("A"=>'text-success', "I"=>' text-info','C'=>'text-warning');

        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            $department_id = '';
            $department_id = BaseCode::getValue('department_id',$current_row);
            if(isset($department_id) && $department_id !='')
            {
                $data['doctor_arr'] = DB::table('doctor_master')->where('department_id',$department_id)->get()->pluck('name','id');
            }

            BaseCode::$data['extra_css'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
            //BaseCode::$data['extra_css'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';


            BaseCode::$data['extra_js'][] = 'vendor/bootstrap-datepicker/js/bootstrap-datepicker.js';
            //BaseCode::$data['extra_js'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';

            $data['department_arr'] = DB::table('department')->where('status','A')->orderBy('department_name','asc')->get()->pluck('department_name','id');
            return view("back_end.appointment.appointment_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('name','contact','email','address','department_name','appointment_date','message');
            BaseCode::$data['column_arr'] = array(
                'name'=>'Name',
                'contact'=>'Contact',
                'email'=>'Email',
                'address'=>'Address',
                'department_name'=>'Department',
                'doctor_name'=>'Doctor',
                'appointment_date'=>'Appointment Date',
                'created_at'=>'Created On',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'department',
                'type'=>'left',
                'col1'=>'department_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'doctor_master',
                'type'=>'left',
                'col1'=>'dr_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            $admin_data = session('adminData');
            BaseCode::$data['common_where'][] = array("colname"=>"appointment_master.type",'colval'=>'Appointment');
            if(isset($admin_data['user_type']) && $admin_data['user_type'] =='D' && isset($admin_data['id']) && $admin_data['id'] !='')
            {
                $doc_id = $admin_data['id'];
                BaseCode::$data['common_where'][] = array("colname"=>"appointment_master.dr_id",'colval'=>$doc_id);
                $data['allowDelete'] = 'No';
                $data['allowStatusChange'] = 'No';
                $data['allowAddNew'] = 'No';
                $data['allowEdit'] = 'No';
                if(isset(BaseCode::$data['column_arr']['doctor_name']) && BaseCode::$data['column_arr']['doctor_name'] !='')
                {
                    unset(BaseCode::$data['column_arr']['doctor_name']);
                }
            }

            BaseCode::$data['select_col'] = array('appointment_master.id','appointment_master.name','appointment_master.contact','appointment_master.email','appointment_master.address','appointment_master.created_at','appointment_master.appointment_date','appointment_master.status','department.department_name','doctor_master.name as doctor_name');

            $data['sort_column'] = 'appointment_master.id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function appointment_save(Request $req,$type="Appointment")
    {
        BaseCode::$data['table_name'] = 'appointment_master';
        $validation = $req->validate([
            'department_id'=> 'required',
            'email'=> 'required|email',
            'dr_id'=> 'required',
            'name' => 'required',
            'contact' => 'required',
            'appointment_date' => 'required',
        ]);
        $data_arr = [
            'status'=>$req->status,
            'name'=>$req->name,
            'dr_id'=>$req->dr_id,
            'contact'=>$req->contact,
            'email'=>$req->email,
            'address'=>$req->address,
            'department_id'=>$req->department_id,
            'appointment_date'=>$req->appointment_date,
            'type'=>$type,
        ];
        if($req->id =='')
        {
            $data_arr['created_at'] = BaseCode::getCurrentDate();
        }
        else
        {
            $data_arr['updated_at'] = BaseCode::getCurrentDate();
        }
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            $data_arr
        );
        if($resp)
        {
            if($req->id !='')
            {
                session()->flash('alert-success','Data updated successfully');
            }
            else
            {
                session()->flash('alert-success','Data inserted successfully');
            }
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
        }
        if($type =="Appointment")
        {
            return redirect(route('appointment','All'));
        }
        else
        {
            return redirect(route('oncall_appointment','All'));
        }
    }
    public function oncall_appointment(Request $req, $status = 'All',$id ='')
    {
        BaseCode::addDatepicker();
        if($status =='save')
        {
            return $this->appointment_save($req,"On Call");
        }
        BaseCode::$data['search_session_name'] = 'search_session_appointment_oncall';
        BaseCode::$data['short_order_data'] = 'short_order_appointment_oncall';
        BaseCode::$data['table_name'] = 'appointment_master';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Oncall Appointment';
        BaseCode::$data['class_name'] = 'oncall_appointment';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('oncall_appointment');

        BaseCode::$data['show_status_arr'] = array("A"=>'Approved', "I"=>'Pending','C'=>'Cancelled');
        BaseCode::$data['change_status_arr'] = array("A"=>'Approve', "I"=>'Pending','C'=>'Cancelled');
        BaseCode::$data['status_btn_color_arr'] = array("A"=>'btn-success', "I"=>'btn-info','C'=>'btn-warning');
        BaseCode::$data['status_fa_arr'] = array("A"=>'fa fa-thumbs-up', "C"=>'fa fa-thumbs-down','I'=>'fa fa-bars');
        BaseCode::$data['status_arr_color_dm'] = array("A"=>'text-success', "I"=>' text-info','C'=>'text-warning');

        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            $department_id = '';
            $department_id = BaseCode::getValue('department_id',$current_row);
            if(isset($department_id) && $department_id !='')
            {
                $data['doctor_arr'] = DB::table('doctor_master')->where('department_id',$department_id)->get()->pluck('name','id');
            }

            BaseCode::$data['extra_css'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
            BaseCode::$data['extra_js'][] = 'vendor/bootstrap-datepicker/js/bootstrap-datepicker.js';
            $data['department_arr'] = DB::table('department')->where('status','A')->orderBy('department_name','asc')->get()->pluck('department_name','id');
            return view("back_end.appointment.appointment_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('appointment_master.name','appointment_master.payment_status','appointment_master.payment_amount','appointment_master.contact','appointment_master.email','appointment_master.address','department.department_name','appointment_master.appointment_date','appointment_master.message','doctor_master.name');
            BaseCode::$data['column_arr'] = array(
                'payment_status'=>'Payment Status',
                'payment_amount'=>'Payment Amount',
                'name'=>'Name',
                'contact'=>'Contact',
                'email'=>'Email',
                'address'=>'Address',
                'department_name'=>'Department',
                'doctor_name'=>'Doctor',
                'appointment_date'=>'Appointment Date',
                'created_at'=>'Created On',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'department',
                'type'=>'left',
                'col1'=>'department_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'doctor_master',
                'type'=>'left',
                'col1'=>'dr_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            $admin_data = session('adminData');
            BaseCode::$data['common_where'][] = array("colname"=>"appointment_master.type",'colval'=>'On Call');
            if(isset($admin_data['user_type']) && $admin_data['user_type'] =='D' && isset($admin_data['id']) && $admin_data['id'] !='')
            {
                $doc_id = $admin_data['id'];
                BaseCode::$data['common_where'][] = array("colname"=>"appointment_master.dr_id",'colval'=>$doc_id);
                $data['allowDelete'] = 'No';
                $data['allowStatusChange'] = 'No';
                $data['allowAddNew'] = 'No';
                $data['allowEdit'] = 'No';
                if(isset(BaseCode::$data['column_arr']['doctor_name']) && BaseCode::$data['column_arr']['doctor_name'] !='')
                {
                    unset(BaseCode::$data['column_arr']['doctor_name']);
                }
            }
            $data['extraButtonArr'] = array(
                array('label'=>'Meeting','btn_text'=>'Make Meeting','onclick'=>"generate_meeting('#id#')", 'extra_data'=>" data-toggle='modal' data-target='#make_meeting'", 'column_name'=>'id','class'=>'btn-success','href'=>"javascript:;")
            );
            BaseCode::$data['select_col'] = array('appointment_master.id','appointment_master.name','appointment_master.contact','appointment_master.email','appointment_master.address','appointment_master.created_at','appointment_master.appointment_date','appointment_master.status','department.department_name','doctor_master.name as doctor_name','appointment_master.payment_amount','appointment_master.payment_status');

            $data['sort_column'] = 'appointment_master.id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function make_meeting_view(Request $req, $id ='')
    {
        if($id !='')
        {
            $data['id'] = $id;
            $data['appointment_arr'] = $current_row = (array) DB::table('appointment_master')->find($id);
        }
        return view("back_end.appointment.meeting_generate",$data);
    }
}
