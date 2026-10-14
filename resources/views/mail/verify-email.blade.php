@extends('mail.layouts.app')

@section('content')
<div style="background-color: #ffffff; border-radius: 8px; padding: 25px;">
    <h2 style="color: #333; margin-top: 0;">Verify Your Email Address</h2>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Thank you for registering at <strong>{{ config('app.name') }}</strong>! Please click the button below to verify your email address.</p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $verificationUrl }}" 
           style="background-color: #d4a373; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
            Verify Email Address
        </a>
    </div>
    
    <p>If you did not create an account, no further action is required.</p>
    
    <p style="margin-top: 25px; font-size: 13px; color: #666;">
        If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:
    </p>
    <p style="font-size: 12px; color: #999; word-break: break-all;">
        {{ $verificationUrl }}
    </p>
</div>
@endsection