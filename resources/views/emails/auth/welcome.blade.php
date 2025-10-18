@extends('emails.layout')

@section('content')
    <h2>Welcome to AdvancedCouponSystem, {{ $user->name }}!</h2>
    
    <p>Congratulations! Your email has been verified and your account is now fully activated.</p>
    
    <p>You can now access all features of our affiliate marketing platform:</p>
    
    <div class="info-box">
        <p><strong>✓</strong> Manage campaigns and coupons</p>
        <p><strong>✓</strong> Track Orders and revenues</p>
        <p><strong>✓</strong> Connect with multiple affiliate networks</p>
        <p><strong>✓</strong> Generate detailed reports</p>
        <p><strong>✓</strong> Monitor your performance</p>
    </div>
    
    <a href="{{ config('app.url') }}/dashboard" class="btn">Go to Dashboard</a>
    
    <div class="divider"></div>
    
    <p>If you have any questions or need assistance, feel free to reach out to our support team.</p>
    
    <p>Happy marketing!</p>
@endsection

