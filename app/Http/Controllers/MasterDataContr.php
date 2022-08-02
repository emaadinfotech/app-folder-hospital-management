<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;
class MasterDataContr extends Controller
{
    public function banner(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_banner';
        BaseCode::$data['short_order_data'] = 'short_order_banner';
        BaseCode::$data['table_name'] = 'banner_management';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Home Page Banner';
        BaseCode::$data['class_name'] = 'master_data';
        BaseCode::$data['method_name'] = 'home_page_banner';
        $data['main_url_append'] =  route('banner');
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            return view("back_end.banner_create",$data);
        }
        else
        {
            if($req->status_update !='')
            {
                $validation = $req->validate([
                    'status_update' => 'required',
                    'checkbox_val' => 'required',
                ]);
                BaseCode::update_status_delete($req);
            }
            if($req->submit_search =='Yes')
            {
                BaseCode::set_search_limit($req);
            }
            BaseCode::$data['search_col'] = array('banner_title','link','banner');
            BaseCode::$data['column_arr'] = array(
                'banner_title'=>'Banner Title',
                'link'=>'Link',
                'banner'=>'Banner',
            );
            BaseCode::$data['image_arr'] = [
                'banner'=> BaseCode::$data['banner_path']
            ];
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function banner_save(Request $req)
    {
        BaseCode::$data['search_session_name'] = 'search_session_banner';
        BaseCode::$data['table_name'] = 'banner_management';
        $validation = $req->validate([
            'banner_title' => 'required',
            'link' => 'required|url',
            'banner' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'status' => 'required',
        ]);

        $banner_val = $banner_val_old = '';
        if($req->banner_val !='')
        {
            $banner_val = $banner_val_old = $req->banner_val;
        }
        $file_upload_flag = 0;
        $logo_path = BaseCode::$data['banner_path'];
        if($req->hasFile('banner'))
        {
            $banner_val = md5(time()).'.'.$req->banner->extension();
            $req->banner->move(public_path($logo_path), $banner_val);
            $file_upload_flag = 1;
        }

        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'banner_title'=>$req->banner_title,
                'link'=>$req->link,
                'banner'=>$banner_val,
            ]
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
            if($file_upload_flag == 1 && $banner_val_old !='')
            {
                BaseCode::delete_file($banner_val_old, $logo_path);
            }
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
            if($file_upload_flag == 1 && $banner_val !='')
            {
                BaseCode::delete_file($banner_val, $logo_path);
            }
        }
        return redirect(route('banner','All'));
    }

    public function department(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_department';
        BaseCode::$data['short_order_data'] = 'short_order_department';
        BaseCode::$data['table_name'] = 'department';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Department';
        BaseCode::$data['class_name'] = 'master_data';
        BaseCode::$data['method_name'] = 'department_li';
        $data['main_url_append'] = route('department');
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            return view("back_end.master_data.department_create",$data);
        }
        else
        {
            if($req->status_update !='')
            {
                $validation = $req->validate([
                    'status_update' => 'required',
                    'checkbox_val' => 'required',
                ]);
                BaseCode::update_status_delete($req);
            }
            if($req->submit_search =='Yes')
            {
                BaseCode::set_search_limit($req);
            }
            BaseCode::$data['search_col'] = array('department_name','short_description');
            BaseCode::$data['column_arr'] = array(
                'department_name'=>'Department',
                'short_description'=>'Description',
                'display_home'=>'Display Home',
                'image'=>'Image',
            );
            BaseCode::$data['image_arr'] = [
                'image'=> BaseCode::$data['department_path']
            ];
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function department_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'department';
        $validation = $req->validate([
            'department_name' => 'required|unique:department,department_name,'.$req->id,
            'short_description' => 'required',
            'image' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'status' => 'required',
        ]);
        $image_val = $image_val_old = '';
        if($req->image_val !='')
        {
            $image_val = $image_val_old = $req->image_val;
        }
        $file_upload_flag = 0;
        $logo_path = BaseCode::$data['department_path'];
        if($req->hasFile('image'))
        {
            $image_val = md5(time()).'.'.$req->image->extension();
            $req->image->move(public_path($logo_path), $image_val);
            $file_upload_flag = 1;
        }

        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'display_home'=>$req->display_home,
                'department_name'=>$req->department_name,
                'short_description'=>$req->short_description,
                'image'=>$image_val,
            ]
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
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
            if($file_upload_flag == 1 && $image_val !='')
            {
                BaseCode::delete_file($image_val, $logo_path);
            }
        }
        return redirect(route('department','All'));
    }

    public function faq_list(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->faq_list_save($req);
        }
        else
        {
            BaseCode::$data['search_session_name'] = 'search_session_faq';
            BaseCode::$data['short_order_data'] = 'short_order_faq';
            BaseCode::$data['table_name'] = 'faq_master';
            $data['status'] = $status;
            BaseCode::$data['label_page'] = 'Manage FAQ';
            BaseCode::$data['class_name'] = 'master_data';
            BaseCode::$data['method_name'] = 'faq_list';
            $data['main_url_append'] = route('faq_list');
            if($status =='create' || $status =='edit')
            {
                $current_row = array();
                if($id !='')
                {
                    $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
                }
                $data['id'] = $id;
                $data['current_row'] = $current_row;
                return view("back_end.master_data.faq_create",$data);
            }
            else
            {
                if($req->status_update !='')
                {
                    $validation = $req->validate([
                        'status_update' => 'required',
                        'checkbox_val' => 'required',
                    ]);
                    BaseCode::update_status_delete($req);
                }
                if($req->submit_search =='Yes')
                {
                    BaseCode::set_search_limit($req);
                }
                BaseCode::$data['search_col'] = array('faq_title','faq_answer');
                BaseCode::$data['column_arr'] = array(
                    'faq_title'=>'title',
                    'faq_answer'=>'Answer',
                );
                $data['sort_column'] = 'id';
                $data['sort_order'] = 'DESC';
                return view("back_end.common_ajax_datatable",$data);
            }
        }
    }
    public function faq_list_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'faq_master';
        $validation = $req->validate([
            'faq_title' => 'required|unique:faq_master,faq_title,'.$req->id,
            'faq_answer' => 'required',
            'status' => 'required',
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'faq_title'=>$req->faq_title,
                'faq_answer'=>$req->faq_answer
            ]
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
        return redirect(route('faq_list','All'));
    }

    public function gallery_list(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->gallery_list_save($req);
        }
        BaseCode::$data['search_session_name'] = 'search_session_gallery';
        BaseCode::$data['short_order_data'] = 'short_order_gallery';
        BaseCode::$data['table_name'] = 'gallery_master';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Gallery';
        BaseCode::$data['class_name'] = 'master_data';
        BaseCode::$data['method_name'] = 'gallery_list';
        $data['main_url_append'] = route('gallery_list');
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            $data['department_arr'] = DB::table('department')->orderBy('department_name','asc')->get()->pluck('department_name','id');

            return view("back_end.master_data.gallery_create",$data);
        }
        else
        {
            if($req->status_update !='')
            {
                $validation = $req->validate([
                    'status_update' => 'required',
                    'checkbox_val' => 'required',
                ]);
                BaseCode::update_status_delete($req);
            }
            if($req->submit_search =='Yes')
            {
                BaseCode::set_search_limit($req);
            }

            BaseCode::$data['column_arr'] = array(
                'department_name'=>'Department',
                'type'=>'Type',
                'image_name'=>'Gallery',
            );
            BaseCode::$data['image_arr'] = [
                'image_name'=> BaseCode::$data['gallery_path']
            ];

            BaseCode::$data['search_col'] = array('type','image_name','department_name');
            BaseCode::$data['join_table'][] = array(
                'table'=>'department',
                'type'=>'left',
                'col1'=>'department_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['select_col'] = array('gallery_master.id','gallery_master.type','gallery_master.department_id','gallery_master.image_name','gallery_master.status','department.department_name as department_name');
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function gallery_list_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'gallery_master';
        if($req->type =='image')
        {
            $validation = $req->validate([
                'type' => 'required',
                'department' => 'required',
                'image_name' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
                'status' => 'required',
            ]);
        }
        else
        {
            $validation = $req->validate([
                'type' => 'required',
                'department' => 'required',
                'video_url' => 'required',
                'status' => 'required',
            ]);
        }
        $image_name_val = '';
        if($req->type =='image')
        {
            $image_name_val = $image_name_val_old = '';
            if($req->image_name_val !='')
            {
                $image_name_val = $image_name_val_old = $req->image_name_val;
            }
            $file_upload_flag = 0;
            $logo_path = BaseCode::$data['gallery_path'];
            if($req->hasFile('image_name'))
            {
                $image_name_val = md5(time()).'.'.$req->image_name->extension();
                $req->image_name->move(public_path($logo_path), $image_name_val);
                $file_upload_flag = 1;
            }
        }
        else
        {
            $image_name_val = $req->video_url;
        }
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'type'=>$req->type,
                'department_id'=>$req->department,
                'image_name'=>$image_name_val,
            ]
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
            if($req->type =='image' && $file_upload_flag == 1 && $image_name_val_old !='')
            {
                BaseCode::delete_file($image_name_val_old, $logo_path);
            }
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
            if($req->type =='image' && $file_upload_flag == 1 && $image_name_val !='')
            {
                BaseCode::delete_file($image_name_val, $logo_path);
            }
        }
        return redirect(route('gallery_list','All'));
    }

    public function health_tips(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->health_tips_save($req);
        }
        BaseCode::$data['search_session_name'] = 'search_session_health_tips';
        BaseCode::$data['short_order_data'] = 'short_order_health_tips';
        BaseCode::$data['table_name'] = 'health_tips';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Health Tips';
        BaseCode::$data['class_name'] = 'master_data';
        BaseCode::$data['method_name'] = 'health_tips_list';
        $data['main_url_append'] = route('health_tips');
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            return view("back_end.master_data.health_tips_create",$data);
        }
        else
        {
            if($req->status_update !='')
            {
                $validation = $req->validate([
                    'status_update' => 'required',
                    'checkbox_val' => 'required',
                ]);
                BaseCode::update_status_delete($req);
            }
            if($req->submit_search =='Yes')
            {
                BaseCode::set_search_limit($req);
            }
            BaseCode::$data['search_col'] = array('title','description');
            BaseCode::$data['column_arr'] = array(
                'title'=>'Title',
                'description'=>'Description',
                'image'=>'Image',
            );
            BaseCode::$data['image_arr'] = [
                'image'=> BaseCode::$data['helth_tips_path']
            ];
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function health_tips_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'health_tips';
        $validation = $req->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'status' => 'required',
        ]);
        $image_val = $image_val_old = '';
        if($req->image_val !='')
        {
            $image_val = $image_val_old = $req->image_val;
        }
        $file_upload_flag = 0;
        $logo_path = BaseCode::$data['health_tips_path'];
        if($req->hasFile('image'))
        {
            $image_val = md5(time()).'.'.$req->image->extension();
            $req->image->move(public_path($logo_path), $image_val);
            $file_upload_flag = 1;
        }

        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'title'=>$req->title,
                'description'=>$req->description,
                'image'=>$image_val,
            ]
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
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
            if($file_upload_flag == 1 && $image_val !='')
            {
                BaseCode::delete_file($image_val, $logo_path);
            }
        }
        return redirect(route('health_tips','All'));
    }

    public function lab_test(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->lab_test_save($req);
        }
        else
        {
            BaseCode::$data['search_session_name'] = 'search_session_lab_test';
            BaseCode::$data['short_order_data'] = 'short_order_lab_test';
            BaseCode::$data['table_name'] = 'lab_test_list';
            $data['status'] = $status;
            BaseCode::$data['label_page'] = 'Manage Lab Test';
            BaseCode::$data['class_name'] = 'master_data';
            BaseCode::$data['method_name'] = 'lab_test_list';
            $data['main_url_append'] = route('lab_test');
            if($status =='create' || $status =='edit')
            {
                $current_row = array();
                if($id !='')
                {
                    $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
                }
                $data['id'] = $id;
                $data['current_row'] = $current_row;
                return view("back_end.master_data.lab_test_create",$data);
            }
            else
            {
                if($req->status_update !='')
                {
                    $validation = $req->validate([
                        'status_update' => 'required',
                        'checkbox_val' => 'required',
                    ]);
                    BaseCode::update_status_delete($req);
                }
                if($req->submit_search =='Yes')
                {
                    BaseCode::set_search_limit($req);
                }
                BaseCode::$data['search_col'] = array('test_name','test_detail','fees');
                BaseCode::$data['column_arr'] = array(
                    'test_name'=>'Test Name',
                    'fees'=>'Fees',
                    'test_detail'=>'Test Detail',
                );
                $data['sort_column'] = 'id';
                $data['sort_order'] = 'DESC';
                return view("back_end.common_ajax_datatable",$data);
            }
        }
    }
    public function lab_test_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'lab_test_list';
        $validation = $req->validate([
            'test_name' => 'required|unique:lab_test_list,test_name,'.$req->id,
            'test_detail' => 'required',
            'fees' => 'required|numeric',
            'status' => 'required',
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'test_name'=>$req->test_name,
                'test_detail'=>$req->test_detail,
                'fees'=>$req->fees
            ]
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
        return redirect(route('lab_test','All'));
    }

    public function staff_role(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->staff_role_save($req);
        }
        else
        {
            BaseCode::$data['search_session_name'] = 'search_session_staff_role';
            BaseCode::$data['short_order_data'] = 'short_order_staff_role';
            BaseCode::$data['table_name'] = 'staff_role';
            $data['status'] = $status;
            BaseCode::$data['label_page'] = 'Manage Staff Role';
            BaseCode::$data['class_name'] = 'master_data';
            BaseCode::$data['method_name'] = 'staff_role';
            $data['main_url_append'] = route('staff_role');
            if($status =='create' || $status =='edit')
            {
                $current_row = array();
                if($id !='')
                {
                    $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
                }
                $data['id'] = $id;
                $data['current_row'] = $current_row;
                return view("back_end.master_data.staff_role_create",$data);
            }
            else
            {
                if($req->status_update !='')
                {
                    $validation = $req->validate([
                        'status_update' => 'required',
                        'checkbox_val' => 'required',
                    ]);
                    BaseCode::update_status_delete($req);
                }
                if($req->submit_search =='Yes')
                {
                    BaseCode::set_search_limit($req);
                }
                BaseCode::$data['search_col'] = array('role_name');
                BaseCode::$data['column_arr'] = array(
                    'role_name'=>'Role Name',
                );
                $data['sort_column'] = 'id';
                $data['sort_order'] = 'DESC';
                return view("back_end.common_ajax_datatable",$data);
            }
        }
    }
    public function staff_role_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'staff_role';
        $validation = $req->validate([
            'role_name' => 'required|unique:staff_role,role_name,'.$req->id,
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'role_name'=>$req->role_name,
            ]
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
        return redirect(route('staff_role','All'));
    }

    public function type_formulation(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->type_formulation_save($req);
        }
        else
        {
            BaseCode::$data['search_session_name'] = 'search_session_type_for';
            BaseCode::$data['short_order_data'] = 'short_order_type_for';
            BaseCode::$data['table_name'] = 'type_formulation';
            $data['status'] = $status;
            BaseCode::$data['label_page'] = 'Manage Medicinal Formulation';
            BaseCode::$data['class_name'] = 'master_data';
            BaseCode::$data['method_name'] = 'type_formulation';
            $data['main_url_append'] = route('type_formulation');
            if($status =='create' || $status =='edit')
            {
                $current_row = array();
                if($id !='')
                {
                    $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
                }
                $data['id'] = $id;
                $data['current_row'] = $current_row;
                return view("back_end.master_data.type_forum_create",$data);
            }
            else
            {
                if($req->status_update !='')
                {
                    $validation = $req->validate([
                        'status_update' => 'required',
                        'checkbox_val' => 'required',
                    ]);
                    BaseCode::update_status_delete($req);
                }
                if($req->submit_search =='Yes')
                {
                    BaseCode::set_search_limit($req);
                }
                BaseCode::$data['search_col'] = array('name');
                BaseCode::$data['column_arr'] = array(
                    'name'=>'Medicinal Formulation',
                );
                $data['sort_column'] = 'id';
                $data['sort_order'] = 'DESC';
                return view("back_end.common_ajax_datatable",$data);
            }
        }
    }
    public function type_formulation_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'type_formulation';
        $validation = $req->validate([
            'name' => 'required|unique:type_formulation,name,'.$req->id,
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'name'=>$req->name,
            ]
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
        return redirect(route('type_formulation','All'));
    }

    public function state(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_state';
        BaseCode::$data['short_order_data'] = 'short_order_state';
        BaseCode::$data['table_name'] = 'state_master';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage State';
        BaseCode::$data['class_name'] = 'master_data';
        BaseCode::$data['method_name'] = 'state_list';
        $data['main_url_append'] = route('state');
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
            return view("back_end.master_data.state_create",$data);
        }
        else
        {
            if($req->status_update !='')
            {
                $validation = $req->validate([
                    'status_update' => 'required',
                    'checkbox_val' => 'required',
                ]);
                BaseCode::update_status_delete($req);
            }
            if($req->submit_search =='Yes')
            {
                BaseCode::set_search_limit($req);
            }
            BaseCode::$data['search_col'] = array('state_name','country_name');
            BaseCode::$data['column_arr'] = array(
                'state_name'=>'State Name',
                'country_name'=>'Country Name',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'country_master',
                'type'=>'left',
                'col1'=>'country_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['select_col'] = array('state_master.id','state_master.state_name','state_master.country_id','state_master.status','country_master.country_name as country_name');
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function state_save(Request $req)
    {
        BaseCode::$data['search_session_name'] = 'search_session_state';
        BaseCode::$data['table_name'] = 'state_master';
        $validation = $req->validate([
            'country_id' => 'required',
            'state_name' => 'required',
            // 'state_name' => 'required|unique:state_master,state_name,'.$req->id,
            'status' => 'required',
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'country_id'=>$req->country_id,
                'state_name'=>$req->state_name,
            ]
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
        return redirect(route('state','All'));
    }

    public function city(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_city';
        BaseCode::$data['short_order_data'] = 'short_order_city';
        BaseCode::$data['table_name'] = 'city_master';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage City';
        BaseCode::$data['class_name'] = 'master_data';
        BaseCode::$data['method_name'] = 'city_list';
        $data['main_url_append'] = route('city');
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
                if(isset($current_row['country_id']) && $current_row['country_id'] !='')
                {
                    $data['state_arr'] = DB::table('state_master')
                    ->where('country_id',$current_row['country_id'])
                    ->orderBy('state_name','asc')->get()->pluck('state_name','id');
                }
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            $data['country_arr'] = DB::table('country_master')->orderBy('country_name','asc')->get()->pluck('country_name','id');

            return view("back_end.city_create",$data);
        }
        else
        {
            if($req->status_update !='')
            {
                $validation = $req->validate([
                    'status_update' => 'required',
                    'checkbox_val' => 'required',
                ]);
                BaseCode::update_status_delete($req);
            }
            if($req->submit_search =='Yes')
            {
                BaseCode::set_search_limit($req);
            }
            BaseCode::$data['search_col'] = array('city_name','state_name','country_name');
            BaseCode::$data['column_arr'] = array(
                'city_name'=>'City Name',
                'state_name'=>'State Name',
                'country_name'=>'Country Name',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'country_master',
                'type'=>'left',
                'col1'=>'country_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['join_table'][] = array(
                'table'=>'state_master',
                'type'=>'left',
                'col1'=>'state_id',
                'col2'=>'id',
                'operate'=>'=',
            );
            BaseCode::$data['select_col'] = array('city_master.id','city_master.city_name','city_master.state_id','city_master.country_id','city_master.status','country_master.country_name as country_name','state_master.state_name as state_name');
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function city_save(Request $req)
    {
        BaseCode::$data['search_session_name'] = 'search_session_city';
        BaseCode::$data['table_name'] = 'city_master';
        $validation = $req->validate([
            'country_id' => 'required',
            'state_id' => 'required',
            'city_name' => 'required',
            // 'state_name' => 'required|unique:state_master,state_name,'.$req->id,
            'status' => 'required',
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'country_id'=>$req->country_id,
                'state_id'=>$req->state_id,
                'city_name'=>$req->city_name,
            ]
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
        return redirect(route('city','All'));
    }

    public function statelist(Request $req,$id ='')
    {
        $return_arr = array();
        if($id !='')
        {
            if($req->type =='City')
            {
                $return_arr = DB::table('city_master')->where('state_id',$id)->get()->pluck('city_name','id');
            }
            else
            {
                $return_arr = DB::table('state_master')->where('country_id',$id)->get()->pluck('state_name','id');
            }
        }
        return response($return_arr);
    }

    public function doctor_list(Request $req,$id ='')
    {
        $return_arr = array();
        if($id !='')
        {
            $return_arr = DB::table('doctor_master')->where('department_id',$id)->get()->pluck('name','id');
        }
        return response($return_arr);
    }

    // not in used ajax demo
    public function tag(Request $req, $status = 'All',$id ='')
    {
        $data['status'] = $status;
        $data['id'] = $id;

        BaseCode::$data['table_name'] = 'tag_master';
        if($req->status_update !='')
        {
            $validation = $req->validate([
                'status_update' => 'required',
                'checkbox_val' => 'required',
            ]);
            BaseCode::update_status_delete($req);
        }
        if($req->is_ajax == 'Yes')
        {
            return view("back_end.tag.tag_list_sub",$data);
        }
        else
        {
            return view("back_end.tag.tag_list",$data);
        }
    }
    public function get_tag($id='')
    {
        return response((array) DB::table('tag_master')->find($id));
    }
    public function tag_save(Request $req)
    {
        $message = 'Please try again';
        $status = 'error';
        $errors = array();
        $validation = $req->validate([
            'tag_name' => 'required|unique:tag_master,tag_name,'.$req->id,
            'display_home_page' => 'required',
            'status' => 'required',
        ]);
        $resp = DB::table('tag_master')->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'tag_name'=>$req->tag_name,
                'display_footer'=>$req->display_home_page,
            ]
        );
        if($resp)
        {
            $status = 'success';
            if($req->id !='')
            {
                $message = 'Data updated successfully';
            }
            else
            {
                $message = 'Data inserted successfully';
            }
        }
        else
        {
            $message = 'Pleases update data atleast one data to update';
            $errors[] = $message;
        }
        return response(array('message'=>$message,'status'=>$status,'errors'=>$errors));
    }
    // not in used ajax demo


    public function currency(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->currency_save($req);
        }
        else
        {
            BaseCode::$data['search_session_name'] = 'search_session_currency';
            BaseCode::$data['short_order_data'] = 'short_order_currency';
            BaseCode::$data['table_name'] = 'currency_master';
            $data['status'] = $status;
            BaseCode::$data['label_page'] = 'Manage Currency';
            BaseCode::$data['class_name'] = 'master_data';
            BaseCode::$data['method_name'] = 'currency_list';
            $data['main_url_append'] = route('currency');
            if($status =='create' || $status =='edit')
            {
                $current_row = array();
                if($id !='')
                {
                    $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
                }
                $data['id'] = $id;
                $data['current_row'] = $current_row;
                return view("back_end.master_data.currency_create",$data);
            }
            else
            {
                if($req->status_update !='')
                {
                    $validation = $req->validate([
                        'status_update' => 'required',
                        'checkbox_val' => 'required',
                    ]);
                    BaseCode::update_status_delete($req);
                }
                if($req->submit_search =='Yes')
                {
                    BaseCode::set_search_limit($req);
                }
                BaseCode::$data['search_col'] = array('currency_name','currency_code');
                BaseCode::$data['column_arr'] = array(
                    'currency_name'=>'Currency Name',
                    'currency_code'=>'Currency Code',
                );
                $data['sort_column'] = 'id';
                $data['sort_order'] = 'DESC';
                return view("back_end.common_ajax_datatable",$data);
            }
        }
    }
    public function currency_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'currency_master';
        $validation = $req->validate([
            'currency_name' => 'required|unique:currency_master,currency_name,'.$req->id,
            'currency_code' => 'required|unique:currency_master,currency_code,'.$req->id,
            'status' => 'required',
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'currency_name'=>$req->currency_name,
                'currency_code'=>$req->currency_code,
            ]
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
        return redirect(route('currency','All'));
    }
}
