@extends('emails.layout')

@section('content')
    <h2 style="color: #2d3748; margin-bottom: 20px;">Hello {{ $user->name }}!</h2>
    
    <p style="font-size: 16px; line-height: 1.6; color: #4a5568;">
        We received a request to reset your password for your Trakifi account.
    </p>
    
    <p style="font-size: 16px; line-height: 1.6; color: #4a5568;">
        Click the button below to set a new password for your account:
    </p>
    
    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $resetUrl }}" class="btn" style="display: inline-block; padding: 14px 28px; background-color: #667eea; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
            Reset Password
        </a>
    </div>
    
    <div class="info-box" style="background-color: #fef5e7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b; margin: 25px 0;">
        <p style="margin: 0 0 10px 0; color: #2d3748; font-weight: 600;">
            🔒 Security Notice
        </p>
        <p style="margin: 0; font-size: 14px; color: #4a5568; line-height: 1.5;">
            This password reset link will expire in {{ $count }} minutes. If you didn't request this change, your password remains secure and you can ignore this email.
        </p>
    </div>
    
    <div class="divider" style="height: 1px; background-color: #e2e8f0; margin: 30px 0;"></div>
    
    <p style="font-size: 13px; color: #718096; line-height: 1.5;">
        <strong>Important:</strong> If you didn't request a password reset, please contact our support team immediately to ensure your account security.
    </p>
    
    <p style="font-size: 12px; color: #a0aec0; line-height: 1.5; margin-top: 20px;">
        Having trouble? Copy and paste this link into your browser:<br>
        <span style="color: #667eea; word-break: break-all; font-family: monospace; font-size: 11px;">{{ $resetUrl }}</span>
    </p>
@endsection

