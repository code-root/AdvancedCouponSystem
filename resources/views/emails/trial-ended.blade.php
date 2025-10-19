<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Ended</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Your Trial Has Ended</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $user->name }},</p>
        
        <p>Your free trial for the <strong>{{ $plan->name }}</strong> plan has ended on <strong>{{ $trialEndDate }}</strong>.</p>
        
        <p>To continue using our service and access all features, please subscribe to a plan.</p>
        
        <div style="text-align: center;">
            <a href="{{ route('subscriptions.plans') }}" class="button">Subscribe Now</a>
        </div>
        
        <p>We hope you enjoyed your trial and look forward to serving you!</p>
        
        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
    </div>
    
    <div class="footer">
        <p>Best regards,<br>The Team</p>
        <p><small>This is an automated message. Please do not reply to this email.</small></p>
    </div>
</body>
</html>



