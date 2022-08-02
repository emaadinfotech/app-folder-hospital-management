<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;
class CountryContr extends Controller
{
    public $table_name = 'country_master';
    public function __construct()
    {
        BaseCode::$data['search_session_name'] = 'search_session_public_page';
        BaseCode::$data['table_name'] = $this->table_name;
        BaseCode::$data['short_order_data'] = 'short_order_country';
    }
    public function index(Request $req, $status = 'All',$id ='')
    {
        $data['status'] = $status;
        BaseCode::$data['label_page'] = 'Manage Country';
        BaseCode::$data['class_name'] = 'master_data';
        BaseCode::$data['method_name'] = 'country_list';
        $data['main_url_append'] = route('Country');
        if($status =='create' || $status =='edit')
        {
            $current_row = array();
            if($id !='')
            {
                $current_row = (array) DB::table($this->table_name)->find($id);
            }
            $data['id'] = $id;
            $data['current_row'] = $current_row;
            return view("back_end.country_create",$data);
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
            BaseCode::$data['search_col'] = array('country_code','dialing_code','country_name');            
            BaseCode::$data['column_arr'] = array(
                'country_code'=>'Country Code',
                'dialing_code'=>'Dialing Code',
                'country_name'=>'Country Code',
            );
            $data['sort_column'] = 'status';
            $data['sort_order'] = 'DESC';
            return view("back_end.common_ajax_datatable",$data);
        }
    }
    public function save(Request $req)
    {
        $validation = $req->validate([
            'country_code' => 'required',
            'dialing_code' => 'required',
            'country_name' => 'required',
            'country_name' => 'required|unique:country_master,country_name,'.$req->id,
            'status' => 'required',
        ]);
        $resp = DB::table($this->table_name)->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'country_code'=>$req->country_code,
                'dialing_code'=>$req->dialing_code,
                'country_name'=>$req->country_name,
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
        return redirect(route('Country','All'));
    }
}