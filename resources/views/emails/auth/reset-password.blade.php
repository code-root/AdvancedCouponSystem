@extends('emails.layout')

@section('content')
    <h2>Hello {{ $user->name }}!</h2>
    
    <p>You are receiving this email because we received a password reset request for your account.</p>
    
    <p>Click the button below to reset your password:</p>
    
    <a href="{{ $resetUrl }}" class="btn">Reset Password</a>
    
    <div class="info-box">
        <p><strong>Security Notice:</strong></p>
        <p>This password reset link will expire in {{ $count }} minutes. If you didn't request a password reset, no further action is required.</p>
    </div>
    
    <div class="divider"></div>
    
    <p style="font-size: 13px; color: #718096;">
        If you didn't request a password reset, please ignore this email or contact support if you have concerns about your account security.
    </p>
    
    <p style="font-size: 13px; color: #718096;">
        If you're having trouble clicking the button, copy and paste the URL below into your web browser:<br>
        <a href="{{ $resetUrl }}" style="color: #667eea; word-break: break-all;">{{ $resetUrl }}</a>
    </p>
@endsection

