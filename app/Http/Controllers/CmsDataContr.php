<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CmsDataContr extends Controller
{
    public function view_cms_detail($id ='')
    {
        $current_row = array();
        if($id !='')
        {
            $current_row = (array) DB::table('cms_pages')->find($id);
        }
        $data['id'] = $id;
        $data['current_row'] = $current_row;
        if(isset($current_row) && BaseCode::checkArray($current_row))
        {
            return view("back_end.master_data.cms_view",$data);
        }
        else
        {
            return redirect(route('cms_list','All'));
        }
    }
    
    public function cms_list(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_cms';
        BaseCode::$data['short_order_data'] = 'short_order_cms';
        BaseCode::$data['table_name'] = 'cms_pages';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage CMS';
        BaseCode::$data['class_name'] = 'cms_list';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('cms_list');

        if($status =='save')
        {
            return $this->cms_list_save($req);
        }
        else if($status =='view' && $id !='')
        {
            return $this->view_cms_detail($id);
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
            BaseCode::$data['extra_js'][] = 'vendor/ckeditor/ckeditor.js';

            return view("back_end.master_data.cms_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);

            BaseCode::$data['search_col'] = array('page_title','alias','page_content','meta_title','meta_description');
            BaseCode::$data['column_arr'] = array(
                'page_title'=>'Page Title',
                'alias'=>'Alias',
                'meta_title'=>'Meta Title',
            );
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            $data['view_btn'] = 'Yes';
            return view("back_end.common_ajax_datatable",$data);
        }
    }

    public function cms_list_save(Request $req)
    {       
        BaseCode::$data['table_name'] = 'cms_pages';
        $validation = $req->validate([
            'page_title' => 'required|unique:cms_pages,page_title,'.$req->id,
            'page_content' => 'required',
            'status' => 'required',
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'page_title'=>$req->page_title,
                'alias'=>Str::slug($req->page_title),
                'page_content'=>$req->page_content,
                'meta_title'=>$req->meta_title,
                'display_footer'=>$req->display_footer,                
                'meta_description'=>$req->meta_description,
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
        return redirect(route('cms_list','All'));
    }

    public function view_blog_detail($id ='')
    {
        $current_row = array();
        if($id !='')
        {
            $current_row = (array) DB::table('blog_master')->find($id);
        }
        $data['id'] = $id;
        $data['current_row'] = $current_row;
        if(isset($current_row) && BaseCode::checkArray($current_row))
        {
            return view("back_end.master_data.blog_view",$data);
        }
        else
        {
            return redirect(route('blog_list','All'));
        }
    }

    public function blog_list(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_blog';
        BaseCode::$data['short_order_data'] = 'short_order_blog';
        BaseCode::$data['table_name'] = 'blog_master';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Blog';
        BaseCode::$data['class_name'] = 'blog_list';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('blog_list');
        if($status =='save')
        {
            return $this->blog_list_save($req);
        }
        else if($status =='view' && $id !='')
        {
            return $this->view_blog_detail($id);
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
            BaseCode::$data['extra_js'][] = 'vendor/ckeditor/ckeditor.js';
            return view("back_end.master_data.blog_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('title','alias','content','meta_title','meta_title','meta_description');
            BaseCode::$data['column_arr'] = array(
                'title'=>'Title',
                'alias'=>'Alias',
                'meta_title'=>'Meta Title',
                'main_image'=>'Image',
            );
            BaseCode::$data['image_arr'] = [
                'main_image'=> BaseCode::$data['blog_path']
            ];
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            $data['view_btn'] = 'Yes';
            return view("back_end.common_ajax_datatable",$data);
        }
    }

    public function blog_list_save(Request $req)
    {       
        BaseCode::$data['table_name'] = 'blog_master';
        $validation = $req->validate([
            'title' => 'required|unique:blog_master,title,'.$req->id,
            'content' => 'required',
            'main_image' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'status' => 'required',
        ]);
        $main_image_val = $main_image_val_old = '';
        if($req->main_image_val !='')
        {
            $main_image_val = $main_image_val_old = $req->main_image_val;
        }
        $file_upload_flag = 0;
        $logo_path = BaseCode::$data['blog_path'];
        if($req->hasFile('main_image'))
        {
            $main_image_val = md5(time()).'.'.$req->main_image->extension();
            $req->main_image->move(public_path($logo_path), $main_image_val);
            $file_upload_flag = 1;
        }

        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'title'=>$req->title,
                'alias'=>Str::slug($req->title),
                'content'=>$req->content,
                'meta_title'=>$req->meta_title,
                'main_image'=>$main_image_val,                
                'meta_description'=>$req->meta_description,
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
            if($file_upload_flag == 1 && $main_image_val_old !='')
            {
                BaseCode::delete_file($main_image_val_old, $logo_path);
            }
        }
        else
        {
            session()->flash('alert-error','Some error occurred');  
            if($file_upload_flag == 1 && $main_image_val !='')
            {
                BaseCode::delete_file($main_image_val, $logo_path);
            }         
        }
        return redirect(route('blog_list','All'));
    }

    public function view_email_temp_detail($id ='')
    {
        $current_row = array();
        if($id !='')
        {
            $current_row = (array) DB::table('email_templates')->find($id);
        }
        $data['id'] = $id;
        $data['current_row'] = $current_row;
        if(isset($current_row) && BaseCode::checkArray($current_row))
        {
            return view("back_end.master_data.email_templates_view",$data);
        }
        else
        {
            return redirect(route('email_templates','All'));
        }
    }

    public function email_templates(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_email_temp';
        BaseCode::$data['short_order_data'] = 'short_order_email_temp';
        BaseCode::$data['table_name'] = 'email_templates';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Email Templates';
        BaseCode::$data['class_name'] = 'email_templates';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('email_templates');

        if($status =='save')
        {
            return $this->email_templates_save($req);
        }
        else if($status =='view' && $id !='')
        {
            return $this->view_email_temp_detail($id);
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
            BaseCode::$data['extra_js'][] = 'vendor/ckeditor/ckeditor.js';
            return view("back_end.master_data.email_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('template_name','email_subject','email_content');
            BaseCode::$data['column_arr'] = array(
                'template_name'=>'Template Name',
                'email_subject'=>'Subject',
            );
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            $data['view_btn'] = 'Yes';
            return view("back_end.common_ajax_datatable",$data);
        }
    }

    public function email_templates_save(Request $req)
    {       
        BaseCode::$data['table_name'] = 'email_templates';
        $validation = $req->validate([
            'template_name' => 'required|unique:email_templates,template_name,'.$req->id,
            'email_content' => 'required',
            'email_subject' => 'required'
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'template_name'=>$req->template_name,
                'email_content'=>$req->email_content,
                'email_subject'=>$req->email_subject,
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
        return redirect(route('email_templates','All'));
    }

    public function sms_templates(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->sms_templates_save($req);
        }
        BaseCode::$data['search_session_name'] = 'search_session_sms_temp';
        BaseCode::$data['short_order_data'] = 'short_order_sms_temp';
        BaseCode::$data['table_name'] = 'sms_templates';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage SMS Templates';
        BaseCode::$data['class_name'] = 'sms_templates';
        BaseCode::$data['method_name'] = 'index';
        $data['main_url_append'] = route('sms_templates');
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            return view("back_end.master_data.sms_create",$data);
        }
        else
        {
            BaseCode::common_update_status($req);
            BaseCode::$data['search_col'] = array('template_name','sms_content');
            BaseCode::$data['column_arr'] = array(
                'template_name'=>'Template Name',
                'sms_content'=>'SMS Content',
            );
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }

    public function sms_templates_save(Request $req)
    {       
        BaseCode::$data['table_name'] = 'sms_templates';
        $validation = $req->validate([
            'template_name' => 'required|unique:sms_templates,template_name,'.$req->id,
            'sms_content' => 'required',
        ]);
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'template_name'=>$req->template_name,
                'sms_content'=>$req->sms_content,
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
        return redirect(route('sms_templates','All'));
    }

    public function sms_configuration(Request $req, $status = 'All',$id ='')
    {
        if($status =='save')
        {
            return $this->sms_configuration_save($req);
        }        
        BaseCode::$data['table_name'] = 'site_config';
        BaseCode::$data['label_page'] = 'Update Sms Configuration';
        BaseCode::$data['class_name'] = 'sms_templates';
        BaseCode::$data['method_name'] = 'sms_configuration';
        $data['main_url_append'] = route('sms_configuration');
        $current_row = BaseCode::get_config_data();
        $data['id'] = $current_row['id'];
        $data['current_row'] = $current_row;
        return view("back_end.site_setting.sms_configuration",$data);
    }

    public function sms_configuration_save(Request $req)
    {
        BaseCode::$data['table_name'] = 'site_config';
        $resp = DB::table(BaseCode::$data['table_name'])->updateOrInsert(
            ['id'=>$req->id],
            [
                'sms_api'=>$req->sms_api,
                'sms_api_status'=>$req->sms_api_status,
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
        return redirect(route('sms_configuration','All'));
    }
}
