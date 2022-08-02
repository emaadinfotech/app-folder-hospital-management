<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Providers\BaseProvider as BaseCode;
use PDF;

class AdminLoginContr extends Controller
{
    public function checklogin(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'username' => 'required|email',
            'password' => 'required',
            'captcha_code' => 'required|captcha'
        ]);
        if ($validator->passes()) {
            $admin_data = (array) DB::table('admin_user')
                ->where('email',$req->username)
                ->where('is_deleted','N')
                ->where('status','A')
                ->first();
            if($admin_data &&
            Hash::check($req->password, $admin_data['password']))
            {
                $req->session()->put('adminData',['id'=>$admin_data['id'],'username'=>$admin_data['username'], 'email'=>$admin_data['email'],'user_type'=>$admin_data['user_type']]);
                return response()->json(['success'=>'Successfully Logged in']);
            }
            else
            {
                return response()->json(['error'=>['Username and password is wrong']]);
            }
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
    public function check_email_forgot(Request $req)
    {
        $validated = $req->validate([
            'username' => 'required|email',
            'captcha_code' => 'required|captcha'
        ]);

        $admin_data = (array) DB::table('admin_user')
            ->where('email',$req->username)
            ->where('is_deleted','N')
            ->where('status','A')
            ->first();
        if($admin_data)
        {
            $new_password = Str::random(8);
            $resp = DB::table('admin_user')
                ->where('id', $admin_data['id'])
                ->update([
                'c_password'=>$new_password,
                'password'=>Hash::make($new_password)
                ]);
            if($resp)
            {

                BaseCode::send_email($admin_data['email'],"Password Changed Successfully","Your new password set successfully:".$new_password);
                session()->put('user_log_out','Password changed successfully');
            }
            else
            {
                session()->put('user_log_err','Some error occurred');
            }
        }
        else
        {
            session()->put('user_log_err','Please enter valid email address');
        }
        return redirect(route('forget-password'));
    }

  
    public function checklogin_doctor(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'username' => 'required|email',
            'password' => 'required',
            'captcha_code' => 'required|captcha'
        ]);
        if ($validator->passes()) {
            $admin_data = (array) DB::table('doctor_master')
                ->where('email',$req->username)
                ->where('is_deleted','N')
                ->where('status','A')
                ->first();
            if($admin_data &&
            Hash::check($req->password, $admin_data['password']))
            {
                $req->session()->put('adminData',['id'=>$admin_data['id'],'username'=>$admin_data['name'], 'email'=>$admin_data['email'],'user_type'=>'D']);
                return response()->json(['success'=>'Successfully Logged in']);
            }
            else
            {
                return response()->json(['error'=>['Username and password is wrong']]);
            }
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }

    // for the doctor check email forgot doctor
    public function check_email_forgot_doctor(Request $req)
    {
        $validated = $req->validate([
            'username' => 'required|email',
            'captcha_code' => 'required|captcha'
        ]);

        $admin_data = (array) DB::table('doctor_master')
            ->where('email',$req->username)
            ->where('is_deleted','N')
            ->where('status','A')
            ->first();
        if($admin_data)
        {
            $new_password = Str::random(8);
            $resp = DB::table('doctor_master')
                ->where('id', $admin_data['id'])
                ->update([
                'password'=>Hash::make($new_password)
                ]);
            if($resp)
            {
                BaseCode::send_email($admin_data['email'],"Password Changed Successfully","Your new password set successfully:".$new_password);
                session()->put('user_log_out','Password changed successfully');
            }
            else
            {
                session()->put('user_log_err','Some error occurred');
            }
        }
        else
        {
            session()->put('user_log_err','Please enter valid email address');
        }
        return redirect(route('forget-password'));
    }

    public function logout()
    {
        if(session()->has('adminData'))
        {
            session()->pull('adminData','');
            session()->put('user_log_out',"You have successfully logged out");
        }
        return redirect(route('admin_login'));
    }
   
   
    public function generatePDF()
    {
        $data = [
            'title' => 'Welcome to EmaadInfotech.com',
            'date' => date('m/d/Y'),
            'email'=>'developer.emaad@gmail.com',
        ];
        $data["client_name"]='Test email with pdf';
        $data["subject"]= "Test email with pdf";

        $pdf = PDF::loadView('myPDFcontent', $data);

        \Mail::send('myPDFcontent', $data, function($message)use($data,$pdf) {
            $message->to($data["email"], $data["client_name"])
            ->subject($data["subject"])
            ->attachData($pdf->output(), "invoice.pdf");
        });

        if ( \Mail::failures()) {
             $this->statusdesc  =   "Error sending mail";
             $this->statuscode  =   "0";
        }else{
           $this->statusdesc  =   "Message sent Successfully";
           $this->statuscode  =   "1";
        }
        return response()->json(compact('this'));
    }
}
