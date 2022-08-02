<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;
class PatientContr extends Controller
{
    public function __construct()
    {
        BaseCode::$data['table_name'] = 'patient';
    }
    public function index(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_patient';
        BaseCode::$data['short_order_data'] = 'short_order_patient';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Patient';
        BaseCode::$data['class_name'] = 'patient';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('patient');
        
        if($status =='save')
        {
            return $this->patient_save($req);
        }
        else if($status =='view' && $id !='')
        {
            return $this->view_patient_detail($id);
        }

        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            $data['country_arr'] = DB::table('country_master')->orderBy('country_name','asc')->get()->pluck('country_name','id');
            if(isset($data['current_row']['country']) && $data['current_row']['country'] !='')
            {
                $data['state_arr'] = DB::table('state_master')->where('country_id',$data['current_row']['country'])->get()->pluck('state_name','id');                 
            }
            if(isset($data['current_row']['state']) && $data['current_row']['state'] !='')
            {
                $data['city_arr'] = DB::table('city_master')->where('state_id',$data['current_row']['state'])->get()->pluck('city_name','id');
            }
            BaseCode::addDatepicker();
            return view("back_end.patient.patient_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('name','contact','email','address','last_followup_date','next_followup_date','medical_history','other_comment');
            BaseCode::$data['column_arr'] = array(
                'name'=>'Name',
                'contact'=>'Contact',
                'email'=>'Email',
                'gender'=>'Gender',
                'age'=>'Age',
                'last_followup_date'=>'Last Followup',
                'next_followup_date'=>'Next Followup',
                'created_at'=>'Created On',
            );
            // BaseCode::$data['select_col'] = array('patient.id','patient.name','patient.contact','patient.email','patient.address','patient.created_at','patient.status','patient.image','department.department_name');
            $data['sort_column'] = 'patient.id';
            $data['sort_order'] = 'DESC';
            $data['view_btn'] = 'Yes';
            // $data['add_history_btn'] = 'Yes';
            return view("back_end.common_ajax_datatable",$data);
        }
    }

    public function patient_save(Request $req)
    {
        $validation = $req->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:patient,email,'.$req->id,
            'contact' => 'required|numeric|min:10',
            'gender' => 'required',
            'age' => 'required',
        ]);
        $data_arr = [
            'status'=>$req->status,
            'name'=>$req->name,
            'contact'=>$req->contact,
            'email'=>$req->email,
            'gender'=>$req->gender,
            'age'=>$req->age,
            'country'=>$req->country,
            'state'=>$req->state,
            'city'=>$req->city,
            'address'=>$req->address,
            'medical_history'=>$req->medical_history,
            'other_comment'=>$req->other_comment,
            'last_followup_date'=>$req->last_followup_date,
            'next_followup_date'=>$req->next_followup_date,
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
        return redirect(route('patient','All'));
    }

    public function view_patient_detail($id ='')
    {
        BaseCode::addDatepicker();
        $current_row = array();
        if($id !='')
        {
            $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
        }
        $data['id'] = $id;
        $data['current_row'] = $current_row;
        if(isset($current_row) && BaseCode::checkArray($current_row))
        {
            $data['patient_history_arr'] = DB::table('patient_history')->where('patient_id',$id)->latest()->get();
            return view("back_end.patient.patient_view",$data);
        }
        else
        {
            return redirect(route('patient','All'));
        }
    }

    public function patient_history_save(Request $req)
    {
        
        $pass_validation = true;
        if(isset($_FILES['report_file']['name']) && $_FILES['report_file']['name'] !='')
        {
            $validator = Validator::make($req->all(), [
                'report_file' => 'sometimes|mimes:jpeg,png,gif,webp,pdf|max:5048',
            ]);
            if (!$validator->passes())
            {
                $pass_validation = false;
            }
        }
        if ($pass_validation)
        {
            $report_path = BaseCode::$data['report_path'];
            $report_file = '';
            if($req->hasFile('report_file'))
            {
                $report_file = md5(time()).'.'.$req->report_file->extension();
                $req->report_file->move(public_path($report_path), $report_file);
            }

            $message = 'Please try again';
            $status = 'error';
            $errors = array();
            BaseCode::$data['table_name'] = 'patient_history';
            $data_arr = [
                'patient_id'=>$req->patient_id,
                'doctor_id'=>$req->doctor_id,
                'blood_pressure'=>$req->blood_pressure,
                'blood_sugar'=>$req->blood_sugar,
                'weight'=>$req->weight,
                'body_temperature'=>$req->body_temperature,
                'prescription'=>$req->prescription,
                'other_comment'=>$req->other_comment,
                'followup_date'=>BaseCode::getCurrentDate(),
                'next_followup_date'=>$req->next_followup_date,
                'report_file'=>$report_file,
            ];
            if($req->id =='')
            {
                $admin_data = session('adminData');
                if(isset($admin_data['user_type']) && $admin_data['user_type'] =='D' && isset($admin_data['id']) && $admin_data['id'] !='')
                {
                    $data_arr['doctor_id'] = $admin_data['id'];
                }
                $data_arr['created_at'] = BaseCode::getCurrentDate();
            }
            else
            {
                $data_arr['updated_at'] = BaseCode::getCurrentDate();
            }
            $resp = DB::table(BaseCode::$data['table_name'])->insert(
                $data_arr
            );
            if($resp)
            {
                $data_update_pat = array(
                    'last_followup_date'=>BaseCode::getCurrentDate(),
                    'next_followup_date'=>$req->next_followup_date,
                );
                $resp = DB::table('patient')->updateOrInsert(
                    ['id'=>$req->patient_id],
                    $data_update_pat
                );

                $status = 'success';
                if($req->id !='')
                {
                    $message = 'Data updated successfully';
                }
                else
                {
                    $message = 'Followup History added successfully';
                }
            }
            return response(array('message'=>$message,'status'=>$status,'errors'=>$errors));
        }
        else
        {
            if(isset($_FILES['report_file']['name']) && $_FILES['report_file']['name'] !='')
            {
                return response()->json(['status'=>'error','error'=>$validator->errors()->all()]);
            }
        }
    }
}