<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetOtp;
use App\Models\Staff;
use App\Models\StaffPasswordOtp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class StaffPasswordOtpController extends Controller
{
    // STEP 1: SEND OTP
    public function sendOtp(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $user = Staff::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        // create 6 digit otp
        $otp = rand(1000, 9999);

        // delete previous OTP
        StaffPasswordOtp::where('email', $request->input('email'))->delete();

        StaffPasswordOtp::create([
            'email'      => $request->input('email'),
            'otp'        => Hash::make($otp),
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);

        // sending email/SMS here
        Mail::to($request->input('email'))->send(new PasswordResetOtp($otp));

        // For testing:
        return response()->json([
            'status'  => 'success',
            'message' => '4 digits OTP sent successfully!',
        ]);
    }


    // STEP 2: VERIFY OTP
    public function verifyOtp(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required'
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $record = StaffPasswordOtp::where('email', $request->input('email'))->first();

        if (!$record) {
            return response()->json(['message' => 'OTP not found'], 404);
        }

        if ($record->expires_at < now()) {
            return response()->json(['message' => 'OTP expired'], 400);
        }

        // if (!Hash::check($request->input('otp'), $record->input('otp'))) {
        //     return response()->json(['message' => 'Invalid OTP'], 400);
        // }
        if (!Hash::check($request->input('otp'), $record->otp)) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $record->verified = true;
        $record->save();

        return response()->json(['message' => 'OTP verified successfully']);
    }

    // STEP 3: RESET PASSWORD
    public function resetPassword(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email'       => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validated->fails()) {
            return response()->json($validated->errors(), 422);
        }

        $record = StaffPasswordOtp::where('email', $request->input('email'))->first();

        if (!$record || !$record->verified) {
            return response()->json(['message' => 'OTP not verified'], 400);
        }

        // Update user password
        Staff::where('email', $request->input('email'))->update([
            'password' => Hash::make($request->input('password'))
        ]);

        // remove otp after use
        $record->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Password reset successfully'
        ]);
    }
}
