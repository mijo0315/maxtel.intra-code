<?php 
namespace App\Http\Controllers\Auth; 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request; 
use DB; 
use App\Models\User; 
use Mail; 
use Hash;
use Illuminate\Support\Str;
  
class ForgotPasswordController extends Controller
{
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function showForgetPasswordForm()
      {
         return view('auth.password.forget-password');
      }
  
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function submitForgetPasswordForm(Request $request)
      {
        DB::beginTransaction();
		try {
            $email = $request->email;
            $user = DB::table('users')
                ->where('users.email', $email)
                ->first();
            
            if(!empty($user)){
                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => Str::random(60),
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ]);
                
                $tokenData = DB::table('password_resets')
                    ->where('email', $user->email)
                    ->where('status', 1)
                    ->first();
                
                $email_data=[];
                $email_data = ['email' =>  $user->email,'full_name' =>  $user->name, 'reset_code' => $tokenData->token];
                Mail::send('email.send-reset-code',$email_data, function($message) use ($email_data){
                    $message->to($email_data['email']);
                    $message->subject("HRIS | Password Reset Code");         
                });
                    
                DB::commit();
                return response()->json([
                    'message' => 'sucess'
                ]);
            } else {
                return response()->json([
                    'message' => 'Account not Found'
                ],400);
            }
            
        }catch(\Throwable $e){
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ],400);
        }
      }
      /**
       * Write code on Method
       *
       * @return response()
       */
      public function showResetPasswordForm($token) { 
        $email = request()->query('email');
        return view('auth.password.update-password', ['token' => $token, 'email' => $email]);
      }
  
      /**
       * Write code on Method
       *
       * @return response()
       */
    public function submitResetPasswordForm(Request $request)
    {
        $email = $request->email;
        $password = $request->password;
        $password_confirm = $request->password_confirm;
        $users = User::where('email', $email)->first();
        DB::beginTransaction();
		try {

            
            $tokenData = DB::table('password_resets')->where('token', $request->token)->where('email',$email)->first();
            if (!$tokenData){
                return view('auth.passwords.forget-password');
            }
            $user = User::where('email', $tokenData->email)->first();
            if (!$user){
                return response()->json([
                    'message' => 'Account not found',
                ],400);
            } else {
                $user->password = \Hash::make($password);
                $user->update(); 
                
                DB::table('password_resets')->where('email', $user->email)->delete();
                
                $email_data=[];
                $email_data = ['email' =>  $user->email,'full_name' =>  $user->name];
                Mail::send('email.password-updated-confirmation',$email_data, function($message) use ($email_data){
                    $message->to($email_data['email']);
                    $message->subject("HRIS | " . $email_data['full_name'] . "'s password changed");         
                });
                
                DB::commit();
                return response()->json([
                    'message' => 'sucess'
                ],200);
            }
            
        }catch(\Throwable $e){
            DB::rollback();
            return response()->json([
                'message' => $e->getMessage(),
            ],400);
        }
    }
}