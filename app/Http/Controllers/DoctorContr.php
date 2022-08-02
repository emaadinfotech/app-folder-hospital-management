<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
class DoctorContr extends Controller
{
    public function __construct()
    {
        BaseCode::$data['table_name'] = 'doctor_master';
    }

    public function doctor_edit_profile(Request $req, $status = 'All')
    {        
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Edit Profile';
        BaseCode::$data['class_name'] = 'site_config';
        BaseCode::$data['method_name'] = 'profile_edit';
        $data['main_url_append'] = route('doctor');
        if($status =='save')
        {
            return $this->doctor_save($req);
        }
        else
        {
            $admin_data = session('adminData');
            $id = '';
            if(isset($admin_data['id']) && $admin_data['id'] !='')
            {
                $id = $admin_data['id'];
            }
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['is_doctor'] = 'Yes';
            $data['current_row'] = $current_row;
            BaseCode::$data['extra_css'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
            BaseCode::$data['extra_js'][] = 'vendor/bootstrap-datepicker/js/bootstrap-datepicker.js';

            $data['department_arr'] = DB::table('department')->where('status','A')->orderBy('department_name','asc')->get()->pluck('department_name','id');
            return view("back_end.doctor.doctor_create",$data);
        }
    }
    
    public function index(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_doctor';
        BaseCode::$data['short_order_data'] = 'short_order_doctor';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Doctor';
        BaseCode::$data['class_name'] = 'doctor';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('doctor');

        if($status =='save')
        {
            return $this->doctor_save($req);
        }
        else if($status =='view' && $id !='')
        {
            return $this->view_doctor_detail($id);
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
            BaseCode::$data['extra_css'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
            BaseCode::$data['extra_js'][] = 'vendor/bootstrap-datepicker/js/bootstrap-datepicker.js';

            $data['department_arr'] = DB::table('department')->where('status','A')->orderBy('department_name','asc')->get()->pluck('department_name','id');
            return view("back_end.doctor.doctor_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('name','contact','email','address','department_name','expertise','education');
            BaseCode::$data['column_arr'] = array(
                'department_name'=>'Department',
                'name'=>'Name',
                'contact'=>'Contact',
                'email'=>'Email',
                'image'=>'image',
                'created_at'=>'Created On',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'department',
                'type'=>'left',
                'col1'=>'department_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['image_arr'] = [
                'image'=> BaseCode::$data['doctor_image_path']
            ];
            BaseCode::$data['select_col'] = array('doctor_master.id','doctor_master.name','doctor_master.contact','doctor_master.email','doctor_master.address','doctor_master.created_at','doctor_master.status','doctor_master.image','department.department_name');

            $data['sort_column'] = 'doctor_master.id';
            $data['sort_order'] = 'DESC';
            $data['view_btn'] = 'Yes';
            return view("back_end.common_ajax_datatable",$data);
        }
    }

    public function doctor_save(Request $req)
    {
        $validation = $req->validate([
            'department_id' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:doctor_master,email,'.$req->id,
            'contact' => 'required|numeric|min:10',

            //'degree' => 'required',
            'gender' => 'required',
            'designation' => 'required',
            'facebook_link' => 'nullable|url',
            'insta_link' => 'nullable|url',
            'youtube_link' => 'nullable|url',
            'image' => 'nullable|file|image|mimes:jpeg,png,gif,webp|max:5048',
            'resume' => 'nullable|file|mimes:pdf,doc,docx,rtf|max:5048',
        ]);

        if($req->id =='')
        {
            Validator::make($req->all(), [
                'password' => 'required|min:6',
            ])->validate();
        }

        $image_val = $image_val_old = '';
        if($req->image_val !='')
        {
            $image_val = $image_val_old = $req->image_val;
        }
        $file_upload_flag = 0;
        $logo_path = BaseCode::$data['doctor_image_path'];
        if($req->hasFile('image'))
        {
            $image_val = md5(time()).'.'.$req->image->extension();
            $req->image->move(public_path($logo_path), $image_val);
            $file_upload_flag = 1;
        }

        $resume_val = $resume_val_old = '';
        if($req->resume_val !='')
        {
            $resume_val = $resume_val_old = $req->resume_val;
        }
        $file_upload_flag_res = 0;
        $logo_path_res = BaseCode::$data['resume_path'];
        if($req->hasFile('resume'))
        {
            $resume_val = md5(time()).'.'.$req->resume->extension();
            $req->resume->move(public_path($logo_path_res), $resume_val);
            $file_upload_flag_res = 1;
        }
        $data_arr = [
            
            'department_id'=>$req->department_id,
            'name'=>$req->name,
            'contact'=>$req->contact,
            'email'=>$req->email,
            'address'=>$req->address,
            'gender'=>$req->gender,
            'birthdate'=>$req->birthdate,
            'image'=>$image_val,
            'resume'=>$resume_val,
            //'degree'=>$req->degree,
            'designation'=>$req->designation,
            'timing'=>$req->timing,
            'expertise'=>$req->expertise,
           // 'years_of_practice'=>$req->years_of_practice,
            'education'=>$req->education,
            //'achievement'=>$req->achievement,
            //'experience'=>$req->experience,
            'facebook_link'=>$req->facebook_link,
            'insta_link'=>$req->insta_link,
            'youtube_link'=>$req->youtube_link,
        ];
        $admin_data = session('adminData');
        if(isset($admin_data['user_type']) && $admin_data['user_type'] !='D')
        {
            $data_arr['status'] = $req->status;
        }
        if($req->password !='')
        {
            $data_arr['password'] = Hash::make($req->password);
        }
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
            if($file_upload_flag == 1 && $image_val_old !='')
            {
                BaseCode::delete_file($image_val_old, $logo_path);
            }
            if($file_upload_flag_res == 1 && $resume_val_old !='')
            {
                BaseCode::delete_file($resume_val_old, $logo_path_res);
            }
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
            if($file_upload_flag == 1 && $image_val !='')
            {
                BaseCode::delete_file($image_val, $logo_path);
            }
            if($file_upload_flag_res == 1 && $resume_val !='')
            {
                BaseCode::delete_file($resume_val, $logo_path_res);
            }
        }
        if(isset($admin_data['user_type']) && $admin_data['user_type'] =='D')
        {
            return redirect(route('v_profile_edit'));
        }
        else
        {
            return redirect(route('doctor','All'));
        }
    }

    public function view_doctor_detail($id ='')
    {
        $current_row = array();
        if($id !='')
        {
            $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
        }
        $data['id'] = $id;
        $data['current_row'] = $current_row;
        if(isset($current_row) && BaseCode::checkArray($current_row))
        {
            return view("back_end.doctor.doctor_view",$data);
        }
        else
        {
            return redirect(route('doctor','All'));
        }
    }

    // for the staff
    public function staff(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['table_name'] = 'user_master';
        BaseCode::$data['search_session_name'] = 'search_session_staff';
        BaseCode::$data['short_order_data'] = 'short_order_staff';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Staff';
        BaseCode::$data['class_name'] = 'staff';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('staff');

        if($status =='save')
        {
            return $this->staff_save($req);
        }
        else if($status =='view' && $id !='')
        {
            return $this->view_staff_detail($id);
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
            BaseCode::$data['extra_css'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
            BaseCode::$data['extra_js'][] = 'vendor/bootstrap-datepicker/js/bootstrap-datepicker.js';

            $data['staff_role_arr'] = DB::table('staff_role')->where('status','A')->orderBy('role_name','asc')->get()->pluck('role_name','id');
            return view("back_end.staff.staff_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('name','contact','email','address','role_name','degree','designation','education');
            BaseCode::$data['column_arr'] = array(
                'role_name'=>'Role',
                'designation'=>'Designation',
                'name'=>'Name',
                'contact'=>'Contact',
                'email'=>'Email',
                'image'=>'image',
                'created_at'=>'Created On',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'staff_role',
                'type'=>'left',
                'col1'=>'staff_role',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['image_arr'] = [
                'image'=> BaseCode::$data['staff_image_path']
            ];
            BaseCode::$data['select_col'] = array('user_master.id','user_master.name','user_master.contact','user_master.email','user_master.address','user_master.created_at','user_master.status','user_master.image','user_master.designation','staff_role.role_name');

            $data['sort_column'] = 'user_master.id';
            $data['sort_order'] = 'DESC';
            $data['view_btn'] = 'Yes';
            return view("back_end.common_ajax_datatable",$data);
        }
    }

    public function staff_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'user_master';
        $validation = $req->validate([
            'staff_role' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:user_master,email,'.$req->id,
            'contact' => 'required|numeric|digits:10',
            //'degree' => 'required',
            'gender' => 'required',
            'designation' => 'required',
            'image' => 'nullable|file|image|mimes:jpeg,png,gif,webp|max:5048',
            'resume' => 'nullable|file|mimes:pdf,doc,docx,rtf|max:5048',
        ]);
        $image_val = $image_val_old = '';
        if($req->image_val !='')
        {
            $image_val = $image_val_old = $req->image_val;
        }
        $file_upload_flag = 0;
        $logo_path = BaseCode::$data['staff_image_path'];
        if($req->hasFile('image'))
        {
            $image_val = md5(time()).'.'.$req->image->extension();
            $req->image->move(public_path($logo_path), $image_val);
            $file_upload_flag = 1;
        }

        $resume_val = $resume_val_old = '';
        if($req->resume_val !='')
        {
            $resume_val = $resume_val_old = $req->resume_val;
        }
        $file_upload_flag_res = 0;
        $logo_path_res = BaseCode::$data['resume_path'];
        if($req->hasFile('resume'))
        {
            $resume_val = md5(time()).'.'.$req->resume->extension();
            $req->resume->move(public_path($logo_path_res), $resume_val);
            $file_upload_flag_res = 1;
        }
        $data_arr = [
            'status'=>$req->status,
            'staff_role'=>$req->staff_role,
            'name'=>$req->name,
            'contact'=>$req->contact,
            'email'=>$req->email,
            'address'=>$req->address,
            'gender'=>$req->gender,
            'birthdate'=>$req->birthdate,
            'image'=>$image_val,
            'resume'=>$resume_val,
            //'degree'=>$req->degree,
            'designation'=>$req->designation,
            'education'=>$req->education,
        ];
        if($req->password !='')
        {
            $data_arr['password'] = Hash::make($req->password);
        }
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
            if($file_upload_flag == 1 && $image_val_old !='')
            {
                BaseCode::delete_file($image_val_old, $logo_path);
            }
            if($file_upload_flag_res == 1 && $resume_val_old !='')
            {
                BaseCode::delete_file($resume_val_old, $logo_path_res);
            }
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
            if($file_upload_flag == 1 && $image_val !='')
            {
                BaseCode::delete_file($image_val, $logo_path);
            }
            if($file_upload_flag_res == 1 && $resume_val !='')
            {
                BaseCode::delete_file($resume_val, $logo_path_res);
            }
        }
        return redirect(route('staff','All'));
    }

    public function view_staff_detail($id ='')
    {
        BaseCode::$data['table_name'] = 'user_master';
        $current_row = array();
        if($id !='')
        {
            $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
        }
        $data['id'] = $id;
        $data['current_row'] = $current_row;
        if(isset($current_row) && BaseCode::checkArray($current_row))
        {
            return view("back_end.staff.staff_view",$data);
        }
        else
        {
            return redirect(route('staff','All'));
        }
    }
}
