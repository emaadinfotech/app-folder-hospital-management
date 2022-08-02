<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;

class PublicPagesContr extends Controller
{
    public $table_name = 'public_pages_seo';
    public function __construct()
    {
        $this->path_image = BaseCode::$data['public_page_path'];
        BaseCode::$data['search_session_name'] = 'search_session_public_page';
        BaseCode::$data['table_name'] = $this->table_name;
        BaseCode::$data['short_order_data'] = 'short_order_public_pages';
    }
    public function index(Request $req, $status = 'All',$page = 1)
    {
        if($req->submit_search =='Yes')
        {
            BaseCode::set_search_limit($req);
        }
        if($req->status_update !='')
        {
            $validation = $req->validate([
                'status_update' => 'required',
                'checkbox_val' => 'required',
            ]);
            BaseCode::update_status_delete($req);
        }
        $data = array();        
        BaseCode::$data['column_arr'] = [
            // 'status'=>'Status',
            'page_name'=>'Page Name',
            'meta_title'=>'Meta Title',
            'meta_image'=>'Meta Image',
        ];
        BaseCode::$data['image_arr'] = [
            'meta_image'=> $this->path_image
        ];
        $db_obk = DB::table($this->table_name);
        $data['sort_column'] = 'id';
        $data['sort_order'] = 'DESC';
        if(session()->has(BaseCode::$data['short_order_data']))
        {
            $short_order_data = session()->get(BaseCode::$data['short_order_data']);
            if(isset($short_order_data['column']) && $short_order_data['column'] !='')
            {
                $data['sort_column'] = $short_order_data['column'];
            }
            if(isset($short_order_data['order']) && $short_order_data['order'] !='')
            {
                $data['sort_order'] = $short_order_data['order'];
            }
        }
        $db_obk->orderBy($data['sort_column'], $data['sort_order']);
        if(session()->has(BaseCode::$data['search_session_name']))
        {
            $search_str = session()->get(BaseCode::$data['search_session_name']);
            if($search_str !='')
            {
                $db_obk->where('page_name','like','%'.$search_str.'%');
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
        $data['status'] = $status;
        $data['page_number'] = $page;        
        return view("back_end.public_pages_list",$data);
    }
    public function edit($id = '')
    {
        $current_row = array();
        if($id !='')
        {
            $current_row = (array) DB::table($this->table_name)->find($id);
        }
        $data['id'] = $id;
        $data['current_row'] = $current_row;
        return view("back_end.public_page_create",$data);
    }
    public function create()
    {
        return $this->edit();
    }
    public function update(Request $req)
    {
        $validation = $req->validate([
            'page_name' => 'required',
            'meta_title' => 'required',
            'meta_image' => 'file|image|mimes:jpeg,png,gif,webp|max:5048',
            'meta_description' => 'required',
            'status' => 'required',
        ]);
        
        $meta_image_val = $meta_image_val_old = '';
        if($req->meta_image_val !='')
        {
            $meta_image_val = $meta_image_val_old = $req->meta_image_val;
        }
        $file_upload_flag = 0;
        $logo_path = $this->path_image;
        if($req->hasFile('meta_image'))
        {
            $meta_image_val = md5(time()).'.'.$req->meta_image->extension();
            $req->meta_image->move(public_path($logo_path), $meta_image_val);
            $file_upload_flag = 1;
        }

        $resp = DB::table($this->table_name)->updateOrInsert(
            ['id'=>$req->id],
            [
                'status'=>$req->status,
                'page_name'=>$req->page_name,
                'meta_title'=>$req->meta_title,
                'meta_description'=>$req->meta_description,
                'meta_image'=>$meta_image_val,
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
            if($file_upload_flag == 1 && $meta_image_val_old !='')
            {
                BaseCode::delete_file($meta_image_val_old, $logo_path);
            }
        }
        else
        {
            session()->flash('alert-error','Some error occurred');
        }
        return redirect(BaseCode::$data['admin_path'].'site-config/public-pages-seo');
    }
}
