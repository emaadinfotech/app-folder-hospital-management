<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use App\Models\SiteSettingModal;
use App\Models\home_page_content;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
class SitesettingContr extends Controller
{
    public function logo_favicon(Request $req)
    {
        $validation = $req->validate([
            'upload_logo' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'upload_favicon' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
        ]);
        $site_data_arr = SiteSettingModal::find(1);
        if($site_data_arr)
        {
            $logo_path = BaseCode::$data['logo_path'];
            $upload_logo_val = $req->upload_logo_val;
            $upload_favicon_val = $req->upload_favicon_val;
            $newLogo_name = '';
            $newfav_name = '';
            $qr_code_image_name = '';
            $file_upload_flag = 0;
            if($req->hasFile('upload_logo'))
            {
                $newLogo_name = md5(time()).'.'.$req->upload_logo->extension();
                $req->upload_logo->move(public_path($logo_path), $newLogo_name);
                $file_upload_flag = 1;
            }
            if($req->hasFile('upload_favicon'))
            {
                $newfav_name = md5(time()).'upload_favicon.'.$req->upload_favicon->extension();
                $req->upload_favicon->move(public_path($logo_path), $newfav_name);
                $file_upload_flag = 1;
            }
           
            if($file_upload_flag == 1)
            {
                if($newLogo_name !='')
                {
                    $site_data_arr->upload_logo = $newLogo_name;
                }
                if($newfav_name !='')
                {
                    $site_data_arr->upload_favicon = $newfav_name;
                }
    
                if($site_data_arr->save()) {
                    session()->flash('alert-success', 'Upload was successful!');
                    // need to remove old file
                    if($newLogo_name !='' && isset($upload_logo_val) && $upload_logo_val !='')
                    {
                        BaseCode::delete_file($upload_logo_val,$logo_path);
                    }
                    if($newfav_name !='' && isset($upload_favicon_val) && $upload_favicon_val !='')
                    {
                        BaseCode::delete_file($upload_favicon_val,$logo_path);
                    }
                    if($qr_code_image_name !='' && isset($qr_code_image_val) && $qr_code_image_val !='')
                    {
                        BaseCode::delete_file($qr_code_image_val,$logo_path);
                    }
                }
                else
                {
                   session()->flash('alert-danger', 'Sorry!, update was not successful!');
                }
            }
        }
        else
        {
            $req->session()->put('error', "Please try again" );
        }
        return redirect(route('v_logo_favicon'));
    }

    public function update_email(Request $req)
    {
        $validation = $req->validate([
            'from_email' => 'required|email',
            'contact_email' => 'required|email',
            ]
        );
        $site_data_arr = SiteSettingModal::find(1);
        if($site_data_arr)
        {
            $site_data_arr->from_email = $req->from_email;
            $site_data_arr->contact_email = $req->contact_email;
            if($site_data_arr->save())
            {
                session()->flash('alert-success', 'Update was successful!');
            }
            else
            {
                session()->flash('alert-danger', 'Sorry!, update was not successful!');
            }
        }
        else
        {
            $req->session()->put('error', "Please try again" );
        }
        return redirect(route('v_update_email'));
    }

    public function basic_setting(Request $req)
    {
        $validation = $req->validate([
            'web_name' => 'required',
            'web_friendly_name' => 'required',
            'website_title' => 'required',
            'website_description' => 'required',
            'footer_text' => 'required',
            'about_footer' => 'required',
            'contact_no' => 'required',
            'full_address' => 'required',
        ]);
        $site_data_arr = SiteSettingModal::find(1);
        if($site_data_arr)
        {
            $site_data_arr->web_name = $req->web_name;
            $site_data_arr->web_friendly_name = $req->web_friendly_name;
            $site_data_arr->website_title = $req->website_title;
            $site_data_arr->website_keywords = $req->website_keywords;
            $site_data_arr->website_description = $req->website_description;
            $site_data_arr->footer_text = $req->footer_text;
            $site_data_arr->about_footer = $req->about_footer;
            $site_data_arr->contact_no = $req->contact_no;
            $site_data_arr->emergency_number = $req->emergency_number;
            $site_data_arr->ambulance_number = $req->ambulance_number;
            $site_data_arr->full_address = $req->full_address;$site_data_arr->map_address = $req->map_address;
            $site_data_arr->google_analytics_code = $req->google_analytics_code;
            $site_data_arr->timezone = $req->timezone;
            if($site_data_arr->save())
            {
                session()->flash('alert-success', 'Update was successful!');
            }
            else
            {
                session()->flash('alert-danger', 'Sorry!, update was not successful!');
            }
        }
        else
        {
            $req->session()->put('error', "Please try again" );
        }
        return redirect(route('v_basic_setting'));
    }

    public function home_page_content(Request $req)
    {
         $validation = $req->validate([
            'image1_about' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'image3_about' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'image5_about' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
        ]);

        $site_data_arr = home_page_content::find(1);
        if($site_data_arr)
        {
            $logo_path = BaseCode::$data['logo_path'];
            $image1_about_val = $req->image1_about_val;
            $image3_about_val = $req->image3_about_val;
            $image5_about_val = $req->image5_about_val;

            $image1_about = '';
            $image3_about = '';
            $image5_about_name = '';

            $file_upload_flag = 0;
            if($req->hasFile('image1_about'))
            {
                $image1_about = md5(time()).'.'.$req->image1_about->extension();
                $req->image1_about->move(public_path($logo_path), $image1_about);
                $file_upload_flag = 1;
            }
            if($req->hasFile('image3_about'))
            {
                $image3_about = md5(time()).'.'.$req->image3_about->extension();
                $req->image3_about->move(public_path($logo_path), $image3_about);
                $file_upload_flag = 1;
            }
            if($req->hasFile('image5_about'))
            {
                $image5_about_name = md5(time()).'.'.$req->image5_about->extension();
                $req->image5_about->move(public_path($logo_path), $image5_about_name);
                $file_upload_flag = 1;
            }
            if($file_upload_flag == 1)
            {
                if($image1_about !='')
                {
                    $site_data_arr->image1_about = $image1_about;
                }
                if($image3_about !='')
                {
                    $site_data_arr->image3_about = $image3_about;
                }
                if($image5_about_name !='')
                {
                    $site_data_arr->image5_about = $image5_about_name;
                }
            }

            $site_data_arr->title1_text = $req->title1_text;
            $site_data_arr->title1_about = $req->title1_about;

            $site_data_arr->content1_about = $req->content1_about;
            $site_data_arr->title2_text = $req->title2_text;
            $site_data_arr->title2_about = $req->title2_about;
            $site_data_arr->title3_text = $req->title3_text;
            $site_data_arr->title3_about = $req->title3_about;

            $site_data_arr->title4_text = $req->title4_text;
            $site_data_arr->title4_about = $req->title4_about;
            $site_data_arr->title5_text = $req->title5_text;
            $site_data_arr->title5_about = $req->title5_about;

            $site_data_arr->content5_about = $req->content5_about;
            $site_data_arr->title6_text = $req->title6_text;
            $site_data_arr->title6_about = $req->title6_about;
            $site_data_arr->count_1 = $req->count_1;
            $site_data_arr->count_1_title = $req->count_1_title;
            $site_data_arr->count_2 = $req->count_2;
            $site_data_arr->count_2_title = $req->count_2_title;

            $site_data_arr->count_3 = $req->count_3;
            $site_data_arr->count_3_title = $req->count_3_title;
            $site_data_arr->count_4 = $req->count_4;
            $site_data_arr->count_4_title = $req->count_4_title;

            if($site_data_arr->save())
            {
                session()->flash('alert-success', 'Update was successful!');
                 // need to remove old file

                 if($image1_about !='' && isset($image1_about_val) && $image1_about_val !='')
                 {
                     BaseCode::delete_file($image1_about_val,$logo_path);
                 }
                 if($image3_about !='' && isset($image3_about_val) && $image3_about_val !='')
                 {
                     BaseCode::delete_file($image3_about_val,$logo_path);
                 }
                 if($image5_about_name !='' && isset($image5_about_val) && $image5_about_val !='')
                 {
                     BaseCode::delete_file($image5_about_val,$logo_path);
                 }
            }
            else
            {
                session()->flash('alert-danger', 'Sorry!, update was not successful!');
            }
        }
        else
        {
            $req->session()->put('error', "Please try again" );
        }
        return redirect(route('v_home_page_content'));
    }

    public function change_password(Request $req)
    {
        $validation = $req->validate([
            'password' => 'required|min:6',
            'new_password' => 'required|min:6',
            'confirm_password' => 'required|same:new_password|min:6',
        ]);
        $admin_data = session('adminData');
        if($admin_data)
        {
            $table_name = "admin_user";
            if(isset($admin_data['user_type']) && $admin_data['user_type'] =='D')
            {
                $table_name = "doctor_master";
            }
            $old_password_hash = (array) DB::table($table_name)->find($admin_data['id']);
            if($old_password_hash)
            {
                if(Hash::check($req->password, $old_password_hash['password']))
                {
                    $response = DB::table($table_name)
                        ->where('id',$admin_data)
                        ->update([
                            'password'=>Hash::make($req->new_password)
                        ]);
                    if($response)
                    {
                        session()->flash('alert-success', 'Password changed successful!');
                    }
                    else
                    {
                        session()->flash('alert-danger', 'Sorry!, update was not successful!');
                    }
                }
                else
                {
                    session()->flash('alert-danger', 'Please enter valid old password');
                }
            }
            else
            {
                session()->flash('alert-danger', 'Please enter valid old password');
            }
        }
        else
        {
            $req->session()->put('alert-danger', "Please try again" );
        }
        return redirect(route('v_change_password'));
    }
    public function app_link(Request $req)
    {
        $validation = $req->validate([
            'android_app_link' => 'nullable|url',
            'ios_app_link' => 'nullable|url',
        ]);
        $site_data_arr = SiteSettingModal::find(1);
        if($site_data_arr)
        {
            $site_data_arr->android_app_link = $req->android_app_link;
            $site_data_arr->ios_app_link = $req->ios_app_link;
            if($site_data_arr->save())
            {
                session()->flash('alert-success', 'Update was successful!');
            }
            else
            {
                session()->flash('alert-danger', 'Sorry!, update was not successful!');
            }
        }
        else
        {
            $req->session()->put('error', "Please try again" );
        }
        return redirect(route('v_app_link'));
    }

    public function oncall_fee(Request $req)
    {
        $validation = $req->validate([
            'currency_code' => 'required',
            'oncall_fee' => 'required',
        ]);
        $site_data_arr = SiteSettingModal::find(1);
        if($site_data_arr)
        {
            $site_data_arr->currency_code = $req->currency_code;
            $site_data_arr->oncall_fee = $req->oncall_fee;
            if($site_data_arr->save())
            {
                session()->flash('alert-success', 'Update was successful!');
            }
            else
            {
                session()->flash('alert-danger', 'Sorry!, update was not successful!');
            }
        }
        else
        {
            $req->session()->put('error', "Please try again" );
        }
        return redirect(route('v_oncall_fee'));
    }

    // old not in used
    public function old_social_media($status = 'All',$page = 1)
    {
        $data = array();
        BaseCode::$data['extra_css'][] = 'vendor/checkbox/src/0.1.4/css/checkBo.min.css';
        BaseCode::$data['extra_js'][] = 'vendor/checkbox/src/0.1.4/js/checkBo.min.js';
        $db_obk = DB::table('social_networking_link');
        if(session()->has('search_session'))
        {
            $search_str = session()->get('search_session');
            if($search_str !='')
            {
                $db_obk->where('social_name','like','%'.$search_str.'%');
                // $search_str
            }
        }
        if($status != 'All')
        {
            $data['list_arr'] = $db_obk
                ->where('status',$status)
                ->paginate(BaseCode::get_per_page());
        }
        else
        {
            $data['list_arr'] = $db_obk->paginate(BaseCode::get_per_page());
        }
        return view("back_end.social_media_list",$data);
    }

    public function social_media_add($id = '')
    {
        BaseCode::$data['extra_css'][] = 'css/fontawesome-iconpicker.min.css';
        BaseCode::$data['extra_js'][] = 'js/fontawesome-iconpicker.js';
        $data = array();
        if($id !='')
        {
            $data = (array) DB::table('social_networking_link')->find($id);
        }
        return view("back_end.social_media_add",['current_row'=>$data]);
    }

    public function save_social_media(Request $req)
    {
        $validation = $req->validate([
            'social_name' => 'required',
            'social_link' => 'required|url',
            'social_logo' => 'required',
            'status' => 'required',
        ]);
        $id = $req->id;
        $return_message = '';
        if($id !='')
        {
            $response = DB::table('social_networking_link')->where('id',$id)->update([
                'social_name'=>$req->social_name,
                'social_link'=>$req->social_link,
                'social_logo'=>$req->social_logo,
                'status'=>$req->status,
            ]);
            $return_message = "Data updated successfully";
        }
        else
        {
            $response = DB::table('social_networking_link')->insert([
                'social_name'=>$req->social_name,
                'social_link'=>$req->social_link,
                'social_logo'=>$req->social_logo,
                'status'=>$req->status,
            ]);
            $return_message = "Data inserted successfully";
        }
        if($return_message)
        {
            session()->flash('alert-success',$return_message);
        }
        else
        {
            session()->flash('alert-danger',"Some error ocurred, please try again");
        }
        return redirect(route('v_social_media'));
    }

    public function update_social_media(Request $req)
    {
        $validation = $req->validate([
            'status_update' => 'required',
            'checkbox_val' => 'required',
        ]);
        if(in_array($req->status_update,array('DELETE','A','I')))
        {
            $tab_op = DB::table('social_networking_link')
                        ->whereIn('id',$req->checkbox_val);
            $success_message = '';
            if($req->status_update == 'DELETE')
            {
               $result = $tab_op->delete();
               $success_message = "Data deleted successfully";
            }
            else
            {
                $result =  $tab_op->update([
                    'status' =>$req->status_update
                ]);
                $success_message = "Data updated successfully";
            }
            if($result)
            {
                session()->flash('alert-success',$success_message);
            }
            else
            {
                session()->flash('alert-danger',"Some error ocurred, please try again");
            }
        }
        else
        {
            session()->flash('alert-danger',"Some error ocurred, please try again");
        }
        return redirect(route('v_social_media'));
    }
    // old not in used

    public function social_media(Request $req, $status = 'All',$id ='')
    {
        BaseCode::$data['search_session_name'] = 'search_session_soc';
        BaseCode::$data['short_order_data'] = 'short_order__soc';
        BaseCode::$data['table_name'] = 'social_networking_link';
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Social Media';
        BaseCode::$data['class_name'] = 'site_config';
        BaseCode::$data['method_name'] = 'social_media';
        $data['main_url_append'] = route('social_media');//'site-config/social-media/';
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table(BaseCode::$data['table_name'])->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            BaseCode::$data['extra_css'][] = 'css/fontawesome-iconpicker.min.css';
        BaseCode::$data['extra_js'][] = 'js/fontawesome-iconpicker.js';

            return view("back_end.social_media_add",$data);
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
            BaseCode::$data['search_col'] = array('social_name','social_link','social_logo');
            BaseCode::$data['column_arr'] = array(
                'social_name'=>'Name',
                'social_link'=>'Link',
                'social_logo'=>'Logo',
            );
            $data['sort_column'] = 'id';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    
    public function social_media_save(Request $req)
    {
        $validation = $req->validate([
            'social_name' => 'required',
            'social_link' => 'required|url',
            'social_logo' => 'required',
            'status' => 'required',
        ]);
        $id = $req->id;
        $return_message = '';
        if($id !='')
        {
            $response = DB::table('social_networking_link')->where('id',$id)->update([
                'social_name'=>$req->social_name,
                'social_link'=>$req->social_link,
                'social_logo'=>$req->social_logo,
                'status'=>$req->status,
            ]);
            $return_message = "Data updated successfully";
        }
        else
        {
            $response = DB::table('social_networking_link')->insert([
                'social_name'=>$req->social_name,
                'social_link'=>$req->social_link,
                'social_logo'=>$req->social_logo,
                'status'=>$req->status,
            ]);
            $return_message = "Data inserted successfully";
        }
        if($return_message)
        {
            session()->flash('alert-success',$return_message);
        }
        else
        {
            session()->flash('alert-danger',"Some error ocurred, please try again");
        }
        return redirect(route('social_media'));
    }
}
