<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Providers\BaseProvider as BaseCode;
use Illuminate\Support\Facades\DB;

use Razorpay\Api\Api;
use Session;
use Redirect;

class HomeContr extends Controller
{
    public function index()
    {
        $data = array();
        $data['banner_arr'] = BaseCode::get_common_count_data('banner_management',3,array(array('status','A'),array('is_deleted','N')));

        $data['home_department_arr'] = BaseCode::get_common_count_data('department',3,array(array('status','A'),array('is_deleted','N'),['display_home','Yes']));

        $data['doctor_arr'] = BaseCode::get_common_count_data('doctor_master',3,array(['status','A'],['is_deleted','N']),['select_col'=>['id','image', 'name', 'designation', 'facebook_link', 'insta_link', 'youtube_link'],'limit'=>4]);

        $data['blog_arr'] = BaseCode::get_common_count_data('blog_master',3,array(['status','A'],['is_deleted','N']),['select_col'=>['id','title', 'alias', 'content', 'main_image', 'created_at'],'limit'=>3,'orderby'=>['column'=>'id','order'=>'desc']]);
        return view("front_end.home",$data);
    }

    public function department()
    {
        $data = array();
        $data['department_arr'] = BaseCode::get_common_count_data('department',3,array(array('status','A'),array('is_deleted','N')));

        return view("front_end.department_view",$data);
    }

    public function doctors()
    {
        $data = array();
        $data['doctors_arr'] = BaseCode::get_common_count_data('doctor_master',2,array(array('status','A'),array('is_deleted','N')));
        return view("front_end.doctors_view",$data);
    }
   
    public function blog()
    {
        $data = array();
        $data['blog_arr'] = BaseCode::get_common_count_data('blog_master',2,array(array('status','A'),array('is_deleted','N')));
        return view("front_end.blog_view",$data);
    }

    public function blog_detail($alias ='')
    {
        $data = array();
        $data['blog_detail'] = BaseCode::get_common_count_data('blog_master',1,array(array('status','A'),array('is_deleted','N'),array('alias',$alias)));

        $data['blog_arr'] = BaseCode::get_common_count_data('blog_master',3,array(array('status','A'),array('is_deleted','N')),['limit'=>6]);

        return view("front_end.blog_detail_view",$data);
    }

    public function contact()
    {
        return view("front_end.contact_us");
    }

    public function contact_save(Request $req)
    {
        $config_data = BaseCode::get_config_data();
        $mail_rec = $config_data['contact_email'];
        $webfriendlyname = $config_data['web_name'];
        $web_name = $config_data['web_friendly_name'];

        $message = 'Please try again';
        $status = 'error';
        $errors = array();
        $validation = $req->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'captcha_code' => 'required|captcha'
        ]);
        $name = $req->name;
        $email = $req->email;
        $description = $req->message;
        $subject = $req->subject;
        $message_content = "<html>
					<head>
					</head>
					<body>
						<p>Dear admin,</p>
						<p>This mail is to inform you that someone has tried to contact you from your website $webfriendlyname.</p>

						<p>Following are the details that has been provided by him/her.</p>

						<p><strong>
						Name : $name<br />
						Email : $email<br />
						Subject : $subject<br />
						Message : ".nl2br($description)."<br />
						</p>
						<br /><br />
						<p>Regards ,<br />
						   $webfriendlyname,<br />
						   $web_name
					    </p>
					</body>
					</html>";
		$subject  = "$name has submitted contact form on - ($web_name $webfriendlyname)";

        BaseCode::send_email($mail_rec,$subject,$message_content);
        $status = 'success';
        $message = 'Contact us form submitted successfully.';

        return response(array('message'=>$message,'status'=>$status,'errors'=>$errors));
    }
    public function about()
    {
       return $this->common_cms('about-us');
    }
    public function common_cms($alias = '')
    {
        $data['cms_data'] = BaseCode::get_common_count_data('cms_pages',1,array(array('is_deleted','N'),array('status','A'),array('alias',$alias)));
        if($data['cms_data'])
        {
            return view("front_end.cms_page_view",$data);
        }
        else
        {
            return redirect(route('home_f'));
        }
    }

    public function gallery()
    {
        $data = array();
        $data['gallery_arr'] = BaseCode::get_common_count_data('gallery_master',3,array(array('status','A'),array('is_deleted','N')));
        return view("front_end.gallery_view",$data);
    }

    public function appointment()
    {
        $data = array();
        $data['department_arr'] = BaseCode::get_common_count_data('department',3,array(array('status','A'),array('is_deleted','N')));
        return view("front_end.appointment_view",$data);
    }
    public function appointment_save(Request $req)
    {
        $message = 'Please try again';
        $status = 'error';
        $errors = array();
        $validation = $req->validate([
            'name' => 'required',
            'contact' => 'required',
            'email' => 'required|email',
            'appointment_date' => 'required',
            'department' => 'required',
            'captcha_code' => 'required|captcha'
        ]);
        $appointment_date = date('Y-m-d',strtotime($req->appointment_date));
        $dat_array = [
            'name'=>$req->name,
            'contact'=>$req->contact,
            'email'=>$req->email,
            'address'=>$req->message,
            'department_id'=>$req->department,
            'appointment_date'=>$appointment_date,
            'type'=>$req->type,
            'created_at'=>BaseCode::getCurrentDate(),
        ];
        if($req->type =='On Call' && $req->payment_amount !='' )
        {
            $dat_array['payment_amount'] = $req->payment_amount;
            $dat_array['payment_status'] = 'Pending';
        }
        $insert_id = DB::table('appointment_master')->insertGetId(
            $dat_array
        );

        if($insert_id !='')
        {
            Session::put('last_appointment_id',$insert_id);
            $status = 'success';
            $message = 'Your appointment detail submitted successfully';
            if($req->type =='On Call')
            {
                $message = 'Your appointment detail submitted successfully, Please make payment to confirm your Video Consulting';
            }
        }
        else
        {
            $message = 'Pleases update data atleast one data to update';
            $errors[] = $message;
        }
        return response(array('message'=>$message,'status'=>$status,'errors'=>$errors));
    }
    public function payment(Request $request)
    {
        $input = $request->all();
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $payment = $api->payment->fetch($input['razorpay_payment_id']);
        $last_appointment_id = $request->session()->get('last_appointment_id');
        $status = 'Cancelled';
        if(count($input)  && !empty($input['razorpay_payment_id']))
        {
            try
            {
                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount'=>$payment['amount']));                
                $transaction_id = $response['id'];
                $status = $response['status'];
                if($status =='captured' && $last_appointment_id !='')
                {
                    $resp = DB::table('appointment_master')->updateOrInsert(
                        ['id'=>$last_appointment_id],
                        [
                            'transaction_id'=>$transaction_id,
                            'payment_status'=>'Success'
                        ]
                    );
                    \Session::put('success_appointment', 'Success! Your payment successful, Your meeting detail will be delivered to your email soon');
                }
                else
                {
                    \Session::put('error_appointment',"Your payment cancelled, please try again");
                }
            }
            catch (\Exception $e)
            {
                \Session::put('error_appointment',$e->getMessage());
            }
        }
        if($status == 'Cancelled' && $last_appointment_id !='')
        {
            $resp = DB::table('appointment_master')->updateOrInsert(
                ['id'=>$last_appointment_id],
                [
                    'payment_status'=>'Cancelled'
                ]
            );
        }
        return redirect()->back();
    }
}
