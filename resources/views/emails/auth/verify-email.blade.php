@extends('emails.layout')

@section('content')
    <h2 style="color: #2d3748; margin-bottom: 20px;">Hello {{ $user->name }}!</h2>
    
    <p style="font-size: 16px; line-height: 1.6; color: #4a5568;">
        Thank you for registering with Trakifi. We're excited to have you join our affiliate marketing platform.
    </p>
    
    <p style="font-size: 16px; line-height: 1.6; color: #4a5568;">
        Please confirm your email address to activate your account and start tracking your campaigns.
    </p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $verificationUrl }}" class="btn" style="display: inline-block; padding: 14px 28px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
            Verify Email Address
        </a>
    </div>
    
    <div class="info-box" style="background-color: #f7fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; margin: 25px 0;">
        <p style="margin: 0 0 10px 0; color: #2d3748; font-weight: 600;">
            Why do we need email verification?
        </p>
        <p style="margin: 0; font-size: 14px; color: #4a5568; line-height: 1.5;">
            Email verification helps protect your account and ensures you receive important notifications about your affiliate campaigns, commissions, and earnings.
        </p>
    </div>
    
    <div class="divider" style="height: 1px; background-color: #e2e8f0; margin: 30px 0;"></div>
    
    <p style="font-size: 13px; color: #718096; line-height: 1.5;">
        <strong>Note:</strong> This verification link will expire in 60 minutes for security purposes.
    </p>
    
    <p style="font-size: 13px; color: #718096; line-height: 1.5;">
        If you did not create an account with Trakifi, please disregard this email. No further action is required.
    </p>
    
    <p style="font-size: 12px; color: #a0aec0; line-height: 1.5; margin-top: 20px;">
        Having trouble clicking the button? Copy and paste this link into your browser:<br>
        <span style="color: #667eea; word-break: break-all; font-family: monospace; font-size: 11px;">{{ $verificationUrl }}</span>
    </p>
@endsection

