<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\PseudoTypes\False_;

class BaseProvider extends ServiceProvider
{
    public static $data = array();
    public function __construct()
	{
        self::$data['config_data'] = [];
        self::$data['logo_path'] = 'dynamic/logo/';
        self::$data['report_path'] = 'dynamic/report/';
        self::$data['public_page_path'] = 'dynamic/pages_seo/';
        self::$data['banner_path'] =  'dynamic/banner/';
        self::$data['department_path'] =  'dynamic/department/';
        self::$data['gallery_path'] =  'dynamic/gallery/';
        self::$data['health_tips_path'] =  'dynamic/health_tips/';
        self::$data['blog_path'] =  'dynamic/blog/';

        self::$data['doctor_image_path'] =  'dynamic/doctor_image/';
        self::$data['resume_path'] =  'dynamic/resume/';
        self::$data['staff_image_path'] =  'dynamic/staff_image/';

        self::$data['data_not_available'] ='N/A';
        self::$data['display_date_arr'] = array('created_at', 'updated_at','appointment_date','last_followup_date','next_followup_date');
        self::$data['show_status_arr'] = array("A"=>'Approved', "I"=>'Unapproved');
        self::$data['change_status_arr'] = array("A"=>'Approve', "I"=>'Unapproved');
        self::$data['status_btn_color_arr'] = array("A"=>'btn-success', "I"=>'btn-warning');
        self::$data['status_fa_arr'] = array("A"=>'fa fa-thumbs-up', "I"=>' fa fa-thumbs-down');
        self::$data['status_arr_color_dm'] = array("A"=>'text-success', "I"=>' text-warning');

        self::$data['gender_arr'] = array("Male"=>'Male', "Female"=>'Female');
        self::$data['search_session_name'] = '';
        self::$data['short_order_data'] = '';

        self::$data['extra_css'] = array();
        self::$data['extra_js'] = array();
        self::$data['per_page_limit'] = 10;
        self::$data['per_page_opt'] = [
            1,2,3,5,10,20,50,100
        ];
        // self::$data['label_page'] = '';
        self::$data['admin_path'] = 'back-office/';
        self::$data['table_name'] = '';

        self::$data['front_end_asset'] = 'public/front_end/';
        self::$data['back_end_asset'] = 'public/back_end/';
        self::$data['public_asset'] = 'public/';

    }
    public static function is_local_url()
    {
        $host = $_SERVER['HTTP_HOST'];
		if($host =='localhost' || strpos($host,'localhost') !== false)
		{
			return true;
		}
		else
		{
			return false;
		}
    }
    public static function get_config_data()
    {
        if(isset(self::$data['config_data']) && self::checkArray(self::$data['config_data']))
        {

        }
        else
        {
            self::$data['config_data'] = (array) DB::table('site_config')->find(1);
        }
        return self::$data['config_data'];
    }
    public static function update_status_delete($req)
    {
        $success_message = '';
        $db_obk = DB::table(self::$data['table_name'])
                    ->whereIn('id',$req->checkbox_val);
        if($req->status_update =='DELETE')
        {
            $result = $db_obk->delete();
            $success_message = "Data deleted successfully";
        }
        else
        {
            $result =  $db_obk->update([
                'status' =>$req->status_update
            ]);
            $success_message = "Data updated successfully";
        }

        if($result !== false)
        {
            session()->flash('alert-success',$success_message);
        }
        else
        {
            session()->flash('alert-danger',"Some error ocurred, please try again");
        }
    }
    public static function set_search_limit(Request $req)
    {
        if($req->limit_per_page !='' && $req->limit_per_page != self::$data['per_page_limit'])
        {
            session()->put('search_session_limit',$req->limit_per_page);
        }
        else
        {
            session()->pull('search_session_limit');
        }
        if($req->search_filed !='')
        {
            session()->put(self::$data['search_session_name'],$req->search_filed);
        }
        else
        {
            session()->pull(self::$data['search_session_name']);
        }
        if($req->sort_column !='' && $req->sort_order !='')
        {
            session()->put(self::$data['short_order_data'],array(
                'column'=>$req->sort_column,
                'order'=>$req->sort_order,
            ));
        }
        else
        {
            session()->pull(self::$data['short_order_data']);
        }
    }
    public static function get_per_page()
    {
        $return_data = self::$data['per_page_limit'];
        $search_session_limit = session()->get('search_session_limit');
        if($search_session_limit !='')
        {
            $return_data = $search_session_limit;
        }
        return $return_data;
    }
    public static function check_file($file_name = '')
    {
        $return = false;
        if($file_name !='' && file_exists(public_path().'/'.$file_name))
        {
            $return = true;
        }
        return $return;
    }
    public static function common_file_url($file_name, $path = '')
    {
        $url = '';
        if(isset($file_name) && $file_name !='' && file_exists(public_path().'/'.$path.$file_name) && $path !='')
        {
            $url = url('/').'/public/'.$path.$file_name;
        }
        return $url;
    }
    public static function get_favicon_url()
    {
        $url = '';
        if(isset(self::$data['config_data']['upload_favicon']) && self::$data['config_data']['upload_favicon'] !='' && file_exists(public_path().'/'.self::$data['logo_path'].self::$data['config_data']['upload_favicon']))
        {
            $url = url('/').'/public/'.self::$data['logo_path'].self::$data['config_data']['upload_favicon'];
        }
        return $url;
    }
    public static function get_logo_url()
    {
        $url = '';
        if(isset(self::$data['config_data']['upload_logo']) && self::$data['config_data']['upload_logo'] !='' && file_exists(public_path().'/'.self::$data['logo_path'].self::$data['config_data']['upload_logo']))
        {
            $url = url('/').'/public/'.self::$data['logo_path'].self::$data['config_data']['upload_logo'];
        }
        return $url;
    }
    public static function send_email($to='',$subject='',$message ='')
    {
        if($to !='' && $message !='' && $subject !='')
        {
            if(!self::is_local_url())
            {
                self::$data['to'] = $to;
                self::$data['subject'] = $subject;
                \Mail::html($message, function($message) {
                    $message->subject(self::$data['subject'])
                    ->to(self::$data['to']);
                });
            }
            else
            {
                $fp = fopen("email_content.html","a+");
			  	fwrite($fp,$to);
                fwrite($fp,$subject);
                fwrite($fp,$message);
                fclose($fp);
            }
        }
    }
    public static function checkArray($arr_val)
    {
        $return = false;
        if(isset($arr_val) && is_array($arr_val) && count($arr_val) > 0)
        {
            $return = true;
        }
        return $return;
    }
    // for the delete the file, common function
    public static function delete_file($file = '',$path ='')
    {
        if(!self::checkArray($file))
        {
            $file = array($file);
        }

        foreach($file as $file_val)
        {
            if($file_val !='' && file_exists(public_path($path).$file_val))
            {
                unlink(public_path($path).$file_val);
            }
        }
    }
    public static function get_list_arr($type ='')
    {
        $data_arr = array();
        $time_zone = array( "Pacific/Midway" => "(UTC-11:00) Midway Island", "Pacific/Samoa"=>"(UTC-11:00) Samoa", "Pacific/Honolulu"=>"(UTC-10:00) Hawaii", "US/Alaska"=>"(UTC-09:00) Alaska", "America/Los_Angeles"=>"(UTC-08:00) Pacific Time (US &amp; Canada)", "America/Tijuana"=>"(UTC-08:00) Tijuana", "US/Arizona"=>"(UTC-07:00) Arizona", "America/Chihuahua"=>"(UTC-07:00) Chihuahua", "America/Chihuahua"=>"(UTC-07:00) La Paz", "America/Mazatlan"=>"(UTC-07:00) Mazatlan", "US/Mountain"=>"(UTC-07:00) Mountain Time (US &amp; Canada)", "America/Managua"=>"(UTC-06:00) Central America", "US/Central"=>"(UTC-06:00) Central Time (US &amp; Canada)", "America/Mexico_City"=>"(UTC-06:00) Guadalajara", "America/Mexico_City"=>"(UTC-06:00) Mexico City", "America/Monterrey"=>"(UTC-06:00) Monterrey", "Canada/Saskatchewan"=>"(UTC-06:00) Saskatchewan", "America/Bogota"=>"(UTC-05:00) Bogota", "US/Eastern"=>"(UTC-05:00) Eastern Time (US &amp; Canada)", "US/East-Indiana"=>"(UTC-05:00) Indiana (East)", "America/Lima"=>"(UTC-05:00) Lima", "America/Bogota"=>"(UTC-05:00) Quito", "Canada/Atlantic"=>"(UTC-04:00) Atlantic Time (Canada)", "America/Caracas"=>"(UTC-04:30) Caracas", "America/La_Paz"=>"(UTC-04:00) La Paz", "America/Santiago"=>"(UTC-04:00) Santiago", "Canada/Newfoundland"=>"(UTC-03:30) Newfoundland", "America/Sao_Paulo"=>"(UTC-03:00) Brasilia", "America/Argentina/Buenos_Aires"=>"(UTC-03:00) Buenos Aires", "America/Argentina/Buenos_Aires"=>"(UTC-03:00) Georgetown", "America/Godthab"=>"(UTC-03:00) Greenland", "America/Noronha"=>"(UTC-02:00) Mid-Atlantic", "Atlantic/Azores"=>"(UTC-01:00) Azores", "Atlantic/Cape_Verde"=>"(UTC-01:00) Cape Verde Is.", "Africa/Casablanca"=>"(UTC+00:00) Casablanca", "Europe/London"=>"(UTC+00:00) Edinburgh", "Etc/Greenwich"=>"(UTC+00:00) Greenwich Mean Time : Dublin", "Europe/Lisbon"=>"(UTC+00:00) Lisbon", "Europe/London"=>"(UTC+00:00) London", "Africa/Monrovia"=>"(UTC+00:00) Monrovia", "UTC"=>"(UTC+00:00) UTC", "Europe/Amsterdam"=>"(UTC+01:00) Amsterdam", "Europe/Belgrade"=>"(UTC+01:00) Belgrade", "Europe/Berlin"=>"(UTC+01:00) Berlin", "Europe/Berlin"=>"(UTC+01:00) Bern", "Europe/Bratislava"=>"(UTC+01:00) Bratislava", "Europe/Brussels"=>"(UTC+01:00) Brussels", "Europe/Budapest"=>"(UTC+01:00) Budapest", "Europe/Copenhagen"=>"(UTC+01:00) Copenhagen", "Europe/Ljubljana"=>"(UTC+01:00) Ljubljana", "Europe/Madrid"=>"(UTC+01:00) Madrid", "Europe/Paris"=>"(UTC+01:00) Paris", "Europe/Prague"=>"(UTC+01:00) Prague", "Europe/Rome"=>"(UTC+01:00) Rome", "Europe/Sarajevo"=>"(UTC+01:00) Sarajevo", "Europe/Skopje"=>"(UTC+01:00) Skopje", "Europe/Stockholm"=>"(UTC+01:00) Stockholm", "Europe/Vienna"=>"(UTC+01:00) Vienna", "Europe/Warsaw"=>"(UTC+01:00) Warsaw", "Africa/Lagos"=>"(UTC+01:00) West Central Africa", "Europe/Zagreb"=>"(UTC+01:00) Zagreb", "Europe/Athens"=>"(UTC+02:00) Athens", "Europe/Bucharest"=>"(UTC+02:00) Bucharest", "Africa/Cairo"=>"(UTC+02:00) Cairo", "Africa/Harare"=>"(UTC+02:00) Harare", "Europe/Helsinki"=>"(UTC+02:00) Helsinki", "Europe/Istanbul"=>"(UTC+02:00) Istanbul", "Asia/Jerusalem"=>"(UTC+02:00) Jerusalem", "Europe/Helsinki"=>"(UTC+02:00) Kyiv", "Africa/Johannesburg"=>"(UTC+02:00) Pretoria", "Europe/Riga"=>"(UTC+02:00) Riga", "Europe/Sofia"=>"(UTC+02:00) Sofia", "Europe/Tallinn"=>"(UTC+02:00) Tallinn", "Europe/Vilnius"=>"(UTC+02:00) Vilnius", "Asia/Baghdad"=>"(UTC+03:00) Baghdad", "Asia/Kuwait"=>"(UTC+03:00) Kuwait", "Europe/Minsk"=>"(UTC+03:00) Minsk", "Africa/Nairobi"=>"(UTC+03:00) Nairobi", "Asia/Riyadh"=>"(UTC+03:00) Riyadh", "Europe/Volgograd"=>"(UTC+03:00) Volgograd", "Asia/Tehran"=>"(UTC+03:30) Tehran", "Asia/Muscat"=>"(UTC+04:00) Abu Dhabi", "Asia/Baku"=>"(UTC+04:00) Baku", "Europe/Moscow"=>"(UTC+04:00) Moscow", "Asia/Muscat"=>"(UTC+04:00) Muscat", "Europe/Moscow"=>"(UTC+04:00) St. Petersburg", "Asia/Tbilisi"=>"(UTC+04:00) Tbilisi", "Asia/Yerevan"=>"(UTC+04:00) Yerevan", "Asia/Kabul"=>"(UTC+04:30) Kabul", "Asia/Karachi"=>"(UTC+05:00) Islamabad", "Asia/Karachi"=>"(UTC+05:00) Karachi", "Asia/Tashkent"=>"(UTC+05:00) Tashkent", "Asia/Calcutta"=>"(UTC+05:30) Chennai", "Asia/Kolkata"=>"(UTC+05:30) Kolkata", "Asia/Calcutta"=>"(UTC+05:30) Mumbai", "Asia/Calcutta"=>"(UTC+05:30) New Delhi", "Asia/Calcutta"=>"(UTC+05:30) Sri Jayawardenepura", "Asia/Katmandu"=>"(UTC+05:45) Kathmandu", "Asia/Almaty"=>"(UTC+06:00) Almaty", "Asia/Dhaka"=>"(UTC+06:00) Astana", "Asia/Dhaka"=>"(UTC+06:00) Dhaka", "Asia/Yekaterinburg"=>"(UTC+06:00) Ekaterinburg", "Asia/Rangoon"=>"(UTC+06:30) Rangoon", "Asia/Bangkok"=>"(UTC+07:00) Bangkok", "Asia/Bangkok"=>"(UTC+07:00) Hanoi", "Asia/Jakarta"=>"(UTC+07:00) Jakarta", "Asia/Novosibirsk"=>"(UTC+07:00) Novosibirsk", "Asia/Hong_Kong"=>"(UTC+08:00) Beijing", "Asia/Chongqing"=>"(UTC+08:00) Chongqing", "Asia/Hong_Kong"=>"(UTC+08:00) Hong Kong", "Asia/Krasnoyarsk"=>"(UTC+08:00) Krasnoyarsk", "Asia/Kuala_Lumpur"=>"(UTC+08:00) Kuala Lumpur", "Australia/Perth"=>"(UTC+08:00) Perth", "Asia/Singapore"=>"(UTC+08:00) Singapore", "Asia/Taipei"=>"(UTC+08:00) Taipei", "Asia/Ulan_Bator"=>"(UTC+08:00) Ulaan Bataar", "Asia/Urumqi"=>"(UTC+08:00) Urumqi", "Asia/Irkutsk"=>"(UTC+09:00) Irkutsk", "Asia/Tokyo"=>"(UTC+09:00) Osaka", "Asia/Tokyo"=>"(UTC+09:00) Sapporo", "Asia/Seoul"=>"(UTC+09:00) Seoul", "Asia/Tokyo"=>"(UTC+09:00) Tokyo", "Australia/Adelaide"=>"(UTC+09:30) Adelaide", "Australia/Darwin"=>"(UTC+09:30) Darwin", "Australia/Brisbane"=>"(UTC+10:00) Brisbane", "Australia/Canberra"=>"(UTC+10:00) Canberra", "Pacific/Guam"=>"(UTC+10:00) Guam", "Australia/Hobart"=>"(UTC+10:00) Hobart", "Australia/Melbourne"=>"(UTC+10:00) Melbourne", "Pacific/Port_Moresby"=>"(UTC+10:00) Port Moresby", "Australia/Sydney"=>"(UTC+10:00) Sydney", "Asia/Yakutsk"=>"(UTC+10:00) Yakutsk", "Asia/Vladivostok"=>"(UTC+11:00) Vladivostok", "Pacific/Auckland"=>"(UTC+12:00) Auckland", "Pacific/Fiji"=>"(UTC+12:00) Fiji", "Pacific/Kwajalein"=>"(UTC+12:00) International Date Line West", "Asia/Kamchatka"=>"(UTC+12:00) Kamchatka", "Asia/Magadan"=>"(UTC+12:00) Magadan", "Pacific/Fiji"=>"(UTC+12:00) Marshall Is.", "Asia/Magadan"=>"(UTC+12:00) New Caledonia", "Asia/Magadan"=>"(UTC+12:00) Solomon Is.", "Pacific/Auckland"=>"(UTC+12:00) Wellington", "Pacific/Tongatapu"=>"(UTC+13:00) Nuku'alofa");
		if($type !='' && isset($$type))
        {
            $data_arr = $$type;
        }
        return $data_arr;
    }
    public static function valueFromId($table_name='',$arry_id='',$clm_value='',$id_clm='id',$return_type = 'str',$delimiter=',')
    {
        $return_arr = array();
        $db_obk = DB::table($table_name);
        $db_obk->where($id_clm,$arry_id);
        $db_obk->select($clm_value);
        $return = $db_obk->get();
        if($return)
        {
            foreach($return as $return_val)
            {
                $return_arr[] = $return_val->$clm_value;
            }
        }
        if($return_type == 'array')
        {

        }
        else
        {
            return implode($delimiter.' ',$return_arr);
        }
        return $return_arr;
    }
    public static function get_common_count_data($table_name='',$flag = 0,$where = array(),$extra_option = array())
    {
       // DB::enableQueryLog();
        $return = '';
        if($table_name !='')
        {
            $db_obk = DB::table($table_name);
            if(isset($extra_option) && self::checkArray($extra_option))
            {
                if(isset($extra_option['orderby']) && self::checkArray($extra_option['orderby']))
                {
                    if($extra_option['orderby']['column'] !='' && $extra_option['orderby']['order'] !='' )
                    {
                        $db_obk->orderBy($extra_option['orderby']['column'], $extra_option['orderby']['order']);
                    }
                }
                if(isset($extra_option['join_arr']) && self::checkArray($extra_option['join_arr']))
                {
                    foreach($extra_option['join_arr'] as $join_val)
                    {
                        $operator = '=';
                        if(isset($join_val['operate']) && $join_val['operate'] !='')
                        {
                            $operator = $join_val['operate'];
                        }
                        $db_obk->leftJoin($join_val['table'],$join_val['col1'],$operator,$join_val['table'].'.'.$join_val['col2']);
                    }
                }
                if(isset($extra_option['select_col']) && $extra_option['select_col'] !='')
                {
                    $db_obk->select($extra_option['select_col']);
                }
                if(isset($extra_option['limit']) && $extra_option['limit'] !='')
                {
                    $db_obk->limit($extra_option['limit']);
                }
            }
            if(isset(self::$data['common_where']) && self::$data['common_where'] !='' && self::checkArray(self::$data['common_where']))
            {
                foreach(self::$data['common_where'] as $common_where)
                {
                    $db_obk->Where($common_where['colname'],$common_where['colval']);
                }
            }
            if(self::checkArray($where))
            {
                if((isset($where['where']) && self::checkArray($where['where'])) || (isset($where['orWhere']) && self::checkArray($where['orWhere'])))
                {

                }
                else if(isset($where) && self::checkArray($where))
                {
                    $where_new = array('where' => $where);
                    $where = $where_new;
                }
                if(isset($where['where']) && self::checkArray($where['where']))
                {
                    foreach($where['where'] as $where_val)
                    {
                        if(count($where_val)== 3)
                        {
                            $db_obk->Where($where_val[0],$where_val[1],$where_val[2]);
                        }
                        else{
                            $db_obk->where($where_val[0],$where_val[1]);
                        }
                    }
                }
                if(isset($where['orWhere']) && self::checkArray($where['orWhere']))
                {
                    $db_obk->Where(function($query) use ($where) {
                        foreach($where['orWhere'] as $where_val)
                        {
                            if(count($where_val)== 3)
                            {
                                $query->orWhere($where_val[0],$where_val[1],$where_val[2]);
                            }
                            else{
                                $query->orwhere($where_val[0],$where_val[1]);
                            }
                        }
                    });
                }
            }
            if($flag == 0)
            {
                $return = $db_obk->count();
            }
            else if($flag == 3)
            {
                $return = $db_obk->get();
            }
            else if($flag == 1)
            {
                $return = $db_obk->first();
            }
            else
            {
                $return = $db_obk->paginate(self::get_per_page());
            }
        }
        return $return;
    }

    public static function sort_icon($sort_column = '', $colname_val = '', $colname_checkact = '')
    {
        $s_title_asc = "Click to sort ASC";
        $s_title_desc = "Click to sort DESC";
        $fa_title_asc = '&nbsp;<i style="font-weight:bold" class="fa fa-caret-up" aria-hidden="true"></i>';
        $fa_title_desc = '&nbsp;<i style="font-weight:bold" class="fa fa-caret-down" aria-hidden="true"></i>';

        $display_title = $s_title_asc;
        $display_order = 'ASC';
        $display_fa = '';
        if($sort_column ==$colname_checkact)
        {
            if($colname_val =='ASC')
            {
                $display_title = $s_title_desc;
                $display_fa = $fa_title_asc;
                $display_order = 'DESC';
            }
            else
            {
                $display_title = $s_title_asc;
                $display_fa = $fa_title_desc;
                $display_order = 'ASC';
            }
        }
        return [$display_title, $display_fa, $display_order];
    }

    public static function getValue($key='',$current_row=array(),$return_val = '')
    {
        if(old($key) !='')
        {
            $return_val = old($key);
        }
        else if(isset($current_row[$key]) && $current_row[$key] !='')
        {
            $return_val = $current_row[$key];
        }
        return $return_val;
    }

    public static function common_update_status(Request $req)
    {
        if($req->status_update !='')
        {
            $validation = $req->validate([
                'status_update' => 'required',
                'checkbox_val' => 'required',
            ]);
            self::update_status_delete($req);
        }
        if($req->submit_search =='Yes')
        {
            self::set_search_limit($req);
        }
    }

    public static function getCurrentDate($dformat='Y-m-d H:i:s')
	{
		return date($dformat);
	}

    public static function displayDate($date = '',$dformat='F j, Y h:i A') // Y-m-d h:i:s
	{
		if($date =='' || $date =='-' || $date =='0000-00-00' ||  $date =='0000-00-00 00:00:00')
		{
			return self::$data['data_not_available'];
		}
        if(strlen($date) == 10)
        {
            $dformat = str_replace('h:i A','',$dformat);
        }
        $config_data = self::get_config_data();
        $strtime = strtotime($date);
        if(isset($config_data['timezone']) && $config_data['timezone'] !='')
        {
            date_default_timezone_set($config_data['timezone']);
        }
        $date_retuen = date($dformat,$strtime);
        date_default_timezone_set('UTC'); // already set in config
        return $date_retuen;
    }

    public static function display_data_na($data_disp ='')
	{
		if(isset($data_disp) && $data_disp !='')
		{
			return $data_disp;
		}
		return self::$data['data_not_available'];
	}

    public static function addDatepicker()
    {
        self::$data['extra_css'][] = 'vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
        self::$data['extra_js'][] = 'vendor/bootstrap-datepicker/js/bootstrap-datepicker.js';
    }

    public static function display_label($label = '')
    {
        if($label !='')
        {
            $label = str_replace('_',' ',$label);
            $label = ucwords($label);
        }
        return $label;
    }

    public static function send_email_template($template_name='', $data_arr=array(), $email)
    {
        if($template_name !='' && $email !='')
        {
            $template_array = (array) DB::table('email_templates')->where('template_name', $template_name)->first();
            if(self::checkArray($template_array))
            {
                $subject = $template_array['email_subject'];
                $email_content = $template_array['email_content'];
                if(isset($data_arr) && self::checkArray($data_arr))
                {
                    $email_content = strtr($email_content, $data_arr);
                }
                $config_data = self::get_config_data();
                $logo_url = self::get_logo_url();

                $app_link = '';
                $base_url = $config_data['web_name'];
                if(isset($config_data['android_app_link']) && $config_data['android_app_link'] !='')
                {
                    $app_link.= '<a href="'.$config_data['android_app_link'].'" target="_blank"><img src="'.$base_url.'assets/images/google-play.png" style="width:130px" alt="Android App" title="Android App" /></a>&nbsp;&nbsp;&nbsp;';
                }
                if(isset($config_data['ios_app_link']) && $config_data['ios_app_link'] !='')
                {
                    $app_link.= '<a href="'.$config_data['ios_app_link'].'" target="_blank"><img src="'.$base_url.'assets/images/apple-ios.png" alt="iOS App"  style="width:130px" title="iOS App" /></a>&nbsp;&nbsp;&nbsp;';
                }
                $footer_text = "Copyright © ".$config_data['web_friendly_name'].'. '.$config_data['footer_text'];

                $social_icon = '';
                $social_link = self::get_common_count_data('social_networking_link',3,array(array('status','A'),array('is_deleted','N')));
                if(isset($social_link) && self::checkArray($social_link))
                {
                    $social_image_arr = array(
                        'facebook'=>'facebook.png',
                        'fb'=>'facebook.png',
                        'google-plus'=>'google-plus.png',
                        'google plus'=>'google-plus.png',
                        'gp'=>'google-plus.png',
                        'linkedin'=>'linkedin.png',
                        'in'=>'linkedin.png',
                        'twitter'=>'twitter.png',
                        'instagram'=>'instagram.png',
                    );
                    foreach($social_link as $social_link_val)
                    {
                        $social_name = strtolower($social_link_val->social_name);
                        if(isset($social_image_arr[$social_name]) && $social_image_arr[$social_name] !='')
                        {
                            $social_icon.= '<a href="'.$social_link_val->social_link.'" target="_blank"><img src="'.$base_url.'assets/images/'.$social_image_arr[$social_name].'" alt="'.$social_link_val->social_name.'" title="'.$social_link_val->social_name.'" /></a>&nbsp;&nbsp;&nbsp;';
                        }
                    }
                }

                $temp_arra = array('#WEB_FRIENDLY_NAME#'=>$config_data['web_friendly_name'],'#TO_EMAIL#'=>$config_data['contact_email'], '#CONTACT_NO#'=>$config_data['contact_no'], '#WEB_URL#'=>$config_data['web_name'], '#FROM_EMAIL#'=>$config_data['from_email'],'#SOCIAL_ICON#'=>$social_icon, '#APP_LINK#'=>$app_link,'#LOGO_URL#'=>$logo_url,'#FOOTER_TEXT#'=>$footer_text);

			    $email_content = strtr($email_content, $temp_arra);

                self::send_email($email,$subject,$email_content);
            }
        }
    }
}
