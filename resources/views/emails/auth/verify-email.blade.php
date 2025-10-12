@extends('emails.layout')

@section('content')
    <h2>Hello {{ $user->name }}!</h2>
    
    <p>Welcome to AdvancedCouponSystem! We're excited to have you on board.</p>
    
    <p>To get started, please verify your email address by clicking the button below:</p>
    
    <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
    
    <div class="info-box">
        <p><strong>Why verify your email?</strong></p>
        <p>Email verification helps us ensure the security of your account and allows you to receive important updates about your campaigns and earnings.</p>
    </div>
    
    <div class="divider"></div>
    
    <p style="font-size: 13px; color: #718096;">
        If you didn't create an account with AdvancedCouponSystem, you can safely ignore this email.
    </p>
    
    <p style="font-size: 13px; color: #718096;">
        If you're having trouble clicking the button, copy and paste the URL below into your web browser:<br>
        <a href="{{ $verificationUrl }}" style="color: #667eea; word-break: break-all;">{{ $verificationUrl }}</a>
    </p>
@endsection

