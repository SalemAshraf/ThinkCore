<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Request Status</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="90" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }

        .header h1 {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin: 0;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
        }

        .header .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 8px 0 0 0;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
            line-height: 1.7;
            color: #2d3748;
        }

        .greeting {
            font-size: 18px;
            font-weight: 500;
            color: #1a202c;
            margin-bottom: 24px;
        }

        .content p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #4a5568;
        }

        .status-box {
            background: #f7fafc;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 24px 0;
            border-radius: 0 8px 8px 0;
        }

        .status-approved {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border-left: 4px solid #2f855a;
        }

        .status-rejected {
            background: linear-gradient(135deg, #f56565, #e53e3e);
            color: white;
            border-left: 4px solid #c53030;
        }

        .status-pending {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            color: white;
            border-left: 4px solid #c05621;
        }

        .status-box h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
        }

        .status-box p {
            margin: 0;
            opacity: 0.95;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 16px;
            margin: 20px 0;
            transition: transform 0.2s ease;
        }

        .cta-button:hover {
            transform: translateY(-1px);
        }

        .signature {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }

        .signature p {
            margin: 4px 0;
        }

        .team-name {
            font-weight: 600;
            color: #667eea;
        }

        .footer {
            background: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            font-size: 14px;
            color: #718096;
            margin: 0;
        }

        .social-links {
            margin-top: 16px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #a0aec0;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .social-links a:hover {
            color: #667eea;
        }

        @media (max-width: 600px) {
            body {
                padding: 20px 10px;
            }

            .email-container {
                border-radius: 12px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 30px 20px;
            }

            .footer {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Instructor Application</h1>
            <p class="subtitle">Status Update</p>
        </div>

        <div class="content">
            <p class="greeting">Dear {{ $instructor->name }},</p>

            <p>Thank you for your interest in becoming an instructor on our platform. We appreciate the time you've taken to submit your application and share your expertise with our learning community.</p>

            {{-- Status-specific content --}}
            @if(isset($instructor->approved_status) && $instructor->approved_status === 'approved')
                <div class="status-box status-approved">
                    <h3>🎉 Application Approved!</h3>
                    <p>Congratulations! Your instructor application has been approved. You can now access your instructor dashboard and start creating courses.</p>
                </div>
                <p>
                    <a href="{{ config('app.url') }}/instructor/dashboard" class="cta-button">Access Your Dashboard</a>
                </p>
                <p>Our team will be in touch shortly with onboarding materials and next steps to help you get started.</p>

            @elseif(isset($instructor->approved_status) && $instructor->approved_status === 'rejected')
                <div class="status-box status-rejected">
                    <h3>Application Update</h3>
                    <p>After careful review, we're unable to approve your instructor application at this time. This decision is based on our current curriculum needs and platform requirements.</p>
                </div>
                <p>We encourage you to continue developing your expertise and consider reapplying in the future. If you have questions about this decision, please don't hesitate to contact our support team.</p>

            @else
                <div class="status-box status-pending">
                    <h3>⏳ Application Under Review</h3>
                    <p>Your application is currently being reviewed by our academic team. We carefully evaluate each submission to ensure the highest quality learning experience for our students.</p>
                </div>
                <p>We typically complete our review process within 2-3 business days. You'll receive another email as soon as we have an update on your application status.</p>
            @endif

            <div class="signature">
                <p>Best regards,</p>
                <p class="team-name">The {{ config('app.name') }} Team</p>
                <p style="font-size: 14px; color: #718096; margin-top: 8px;">Building the future of online education</p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <div class="social-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Contact Support</a>
            </div>
        </div>
    </div>
</body>
</html>
