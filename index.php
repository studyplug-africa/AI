<?php
//check if the user was never online
session_start();

if (isset($_SESSION["admin_email"])) {
    echo "<script> window.location.href='company/';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpsInsyts</title>
    <link rel="icon" href="src/image/logos.png" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #ffffff;
            color: #1a1a2e;
            scroll-behavior: smooth;
        }


        :root {
            --OpsInsyts-primary: #2373BA;
            --OpsInsyts-primary-dark: #1b5a96;
            --OpsInsyts-primary-light: #3f8bc9;
            --OpsInsyts-secondary-1: #0c96D4;
            --OpsInsyts-secondary-2: #464646;
            --OpsInsyts-secondary-3: #989798;
            --OpsInsyts-accent-bg: #eef4fc;
            --OpsInsyts-border-light: #e2e8f0;
        }

        .OpsInsyts-global-loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            flex-direction: column;
            gap: 24px;
        }

        .OpsInsyts-global-loader.hide {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .OpsInsyts-loader-spinner {
            width: 64px;
            height: 64px;
            border: 5px solid var(--OpsInsyts-border-light);
            border-top: 5px solid var(--OpsInsyts-primary);
            border-radius: 50%;
            animation: OpsInsyts-loader-spin 0.9s cubic-bezier(0.4, 0, 0.2, 1) infinite;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        @keyframes OpsInsyts-loader-spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .OpsInsyts-loader-text {
            font-size: 18px;
            font-weight: 500;
            color: var(--OpsInsyts-secondary-2);
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .OpsInsyts-loader-text i {
            color: var(--OpsInsyts-primary);
            font-size: 20px;
        }

        /* Navigation */
        .OpsInsyts-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--OpsInsyts-border-light);
            padding: 16px 32px;
            z-index: 1000;
        }

        .OpsInsyts-nav-container {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .OpsInsyts-logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .OpsInsyts-logo img {
            height: 70px;
            width: auto;
        }

        /* Desktop Navigation */
        .OpsInsyts-nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .OpsInsyts-nav-links a {
            text-decoration: none;
            color: var(--OpsInsyts-secondary-2);
            font-weight: 500;
            transition: color 0.2s;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .OpsInsyts-nav-links a i {
            font-size: 16px;
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-nav-links a:hover {
            color: var(--OpsInsyts-primary);
        }

        /* Mobile Menu Button */
        .OpsInsyts-mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #1a1a2e;
            z-index: 1001;
        }

        /* Mobile Navigation */
        .OpsInsyts-mobile-nav {
            position: fixed;
            top: 0;
            right: -100%;
            width: 280px;
            height: 100vh;
            background: white;
            box-shadow: -5px 0 30px rgba(0, 0, 0, 0.1);
            z-index: 1002;
            padding: 80px 24px 40px;
            transition: right 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .OpsInsyts-mobile-nav.active {
            right: 0;
        }

        .OpsInsyts-mobile-nav a {
            text-decoration: none;
            color: var(--OpsInsyts-secondary-2);
            font-weight: 500;
            font-size: 18px;
            padding: 12px 0;
            border-bottom: 1px solid var(--OpsInsyts-border-light);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .OpsInsyts-mobile-nav a i {
            width: 24px;
            font-size: 18px;
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-mobile-nav a:hover {
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-mobile-nav .OpsInsyts-btn-outline,
        .OpsInsyts-mobile-nav .OpsInsyts-btn-primary {
            width: 100%;
            text-align: center;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        /* Overlay */
        .OpsInsyts-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1001;
            display: none;
        }

        .OpsInsyts-menu-overlay.active {
            display: block;
        }

        .OpsInsyts-btn-outline {
            padding: 8px 20px;
            border: 2px solid var(--OpsInsyts-primary);
            background: transparent;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--OpsInsyts-primary);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .OpsInsyts-btn-outline:hover {
            background: var(--OpsInsyts-primary);
            color: white;
        }

        .OpsInsyts-btn-outline:hover i {
            color: white;
        }

        .OpsInsyts-btn-primary {
            padding: 8px 24px;
            background: var(--OpsInsyts-primary);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .OpsInsyts-btn-primary i {
            color: white;
        }

        .OpsInsyts-btn-primary:hover {
            background: var(--OpsInsyts-primary-dark);
            transform: translateY(-1px);
        }

        .OpsInsyts-btn-large {
            padding: 14px 32px;
            font-size: 16px;
        }

        /* Hero Section  */
        .OpsInsyts-hero {
            min-height: 100vh;
            padding: 140px 32px 80px;
            position: relative;
            display: flex;
            align-items: center;
            background-image: url('https://wallpapers.com/blog/wp-content/uploads/2024/03/Upscaling-Vector-Images.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .OpsInsyts-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.75) 100%);
            z-index: 0;
        }

        .OpsInsyts-hero-content {
            position: relative;
            z-index: 1;
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            width: 100%;
        }

        .OpsInsyts-hero-text h1 {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 24px;
            color: white;
        }

        .OpsInsyts-hero-text p {
            font-size: 18px;
            color: #d1d5db;
            line-height: 1.5;
            margin-bottom: 32px;
        }

        .OpsInsyts-hero-badge {
            display: inline-block;
            background: rgba(35, 115, 186, 0.2);
            border: 1px solid rgba(35, 115, 186, 0.5);
            color: #87b9e6;
            padding: 6px 14px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .OpsInsyts-hero-badge i {
            margin-right: 8px;
            color: var(--OpsInsyts-secondary-1);
        }

        .OpsInsyts-hero-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
            flex-wrap: wrap;
        }

        .OpsInsyts-stats {
            display: flex;
            gap: 40px;
            padding-top: 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            flex-wrap: wrap;
        }

        .OpsInsyts-stat h3 {
            font-size: 28px;
            font-weight: 800;
            color: var(--OpsInsyts-secondary-1);
        }

        .OpsInsyts-stat p {
            font-size: 14px;
            color: #9ca3af;
            margin: 0;
        }

        .OpsInsyts-hero-image {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .OpsInsyts-hero-image i {
            font-size: 120px;
            color: var(--OpsInsyts-secondary-1);
        }

        /* Features Section  */
        .OpsInsyts-features {
            padding: 100px 32px;
            background: #f8fafc;
        }

        .OpsInsyts-section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 60px;
        }

        .OpsInsyts-section-header h2 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--OpsInsyts-secondary-2);
        }

        .OpsInsyts-section-header p {
            font-size: 18px;
            color: var(--OpsInsyts-secondary-3);
        }

        .OpsInsyts-features-grid {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }

        .OpsInsyts-feature-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--OpsInsyts-border-light);
            transition: all 0.2s;
        }

        .OpsInsyts-feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .OpsInsyts-feature-icon {
            width: 56px;
            height: 56px;
            background: #e4eff8;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .OpsInsyts-feature-icon i {
            font-size: 28px;
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-feature-card h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--OpsInsyts-secondary-2);
        }

        .OpsInsyts-feature-card p {
            color: var(--OpsInsyts-secondary-3);
            line-height: 1.5;
        }

        /* How It Works Section */
        .OpsInsyts-how-it-works {
            padding: 100px 32px;
            max-width: 1280px;
            margin: 0 auto;
        }

        .OpsInsyts-steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 48px;
            margin-top: 48px;
        }

        .OpsInsyts-step {
            text-align: center;
        }

        .OpsInsyts-step-number {
            width: 48px;
            height: 48px;
            background: var(--OpsInsyts-primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 700;
            margin: 0 auto 20px;
        }

        .OpsInsyts-step h3 {
            font-size: 20px;
            margin-bottom: 12px;
            color: var(--OpsInsyts-secondary-2);
        }

        .OpsInsyts-step p {
            color: var(--OpsInsyts-secondary-3);
            line-height: 1.5;
        }

        /* Testimonials Section  */
        .OpsInsyts-testimonials {
            padding: 100px 32px;
            background: #f8fafc;
        }

        .OpsInsyts-testimonials-grid {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 32px;
        }

        .OpsInsyts-testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid var(--OpsInsyts-border-light);
        }

        .OpsInsyts-testimonial-card i.fa-quote-left {
            color: #c7d2fe;
            font-size: 28px;
            margin-bottom: 16px;
        }

        .OpsInsyts-testimonial-text {
            font-size: 16px;
            line-height: 1.6;
            color: #2d3748;
            margin-bottom: 24px;
        }

        .OpsInsyts-testimonial-author {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .OpsInsyts-testimonial-avatar {
            width: 48px;
            height: 48px;
            background: #e4eff8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--OpsInsyts-primary);
            font-weight: 600;
        }

        .OpsInsyts-testimonial-name {
            font-weight: 700;
            color: var(--OpsInsyts-secondary-2);
        }

        .OpsInsyts-testimonial-role {
            font-size: 13px;
            color: var(--OpsInsyts-secondary-3);
        }

        .OpsInsyts-stars {
            color: #f59e0b;
            font-size: 13px;
            margin-top: 8px;
        }

        /* CTA Section */
        .OpsInsyts-cta {
            padding: 100px 32px;
            position: relative;
            background-image: url('https://images.unsplash.com/photo-1556761175-b413da4baf72?q=80&w=2074&auto=format');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .OpsInsyts-cta::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.85) 0%, rgba(35, 115, 186, 0.7) 100%);
            z-index: 0;
        }

        .OpsInsyts-cta-content {
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .OpsInsyts-cta h2 {
            font-size: 36px;
            font-weight: 700;
            color: white;
            margin-bottom: 16px;
        }

        .OpsInsyts-cta p {
            color: #d1d5db;
            margin-bottom: 32px;
        }

        .OpsInsyts-cta .OpsInsyts-btn-primary {
            background: white;
            color: var(--OpsInsyts-primary);
            border: none;
        }

        .OpsInsyts-cta .OpsInsyts-btn-primary i {
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-cta .OpsInsyts-btn-primary:hover {
            background: #f0f4f9;
            transform: translateY(-2px);
        }

        /* Footer */
        .OpsInsyts-footer {
            padding: 48px 32px 32px;
            border-top: 1px solid var(--OpsInsyts-border-light);
            max-width: 1280px;
            margin: 0 auto;
        }

        .OpsInsyts-footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .OpsInsyts-footer-logo i {
            font-size: 24px;
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-footer-logo .OpsInsyts-logo-text {
            font-size: 18px;
            margin-top: 10px;
            font-weight: 600;
            color: var(--OpsInsyts-secondary-2);
        }

        .OpsInsyts-footer-links h4 {
            margin-bottom: 16px;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--OpsInsyts-secondary-2);
        }

        .OpsInsyts-footer-links h4 i {
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-footer-links a {
            display: block;
            color: var(--OpsInsyts-secondary-3);
            text-decoration: none;
            margin-bottom: 10px;
            font-size: 14px;
            cursor: pointer;
            transition: color 0.2s;
        }

        .OpsInsyts-footer-links a:hover {
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-footer-bottom {
            text-align: center;
            padding-top: 32px;
            border-top: 1px solid var(--OpsInsyts-border-light);
            color: var(--OpsInsyts-secondary-3);
            font-size: 14px;
        }

        /* Modals */
        .OpsInsyts-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .OpsInsyts-modal.active {
            display: flex;
        }

        .OpsInsyts-modal-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .OpsInsyts-modal-content i {
            font-size: 48px;
            color: var(--OpsInsyts-primary);
            margin-bottom: 20px;
        }

        .OpsInsyts-modal-content h2 {
            margin-bottom: 12px;
            color: var(--OpsInsyts-secondary-2);
        }

        .OpsInsyts-modal-content p {
            color: var(--OpsInsyts-secondary-3);
            margin-bottom: 24px;
        }

        .OpsInsyts-modal-content input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid var(--OpsInsyts-border-light);
            border-radius: 8px;
            font-size: 16px;
        }

        .OpsInsyts-modal-content input:focus {
            outline: none;
            border-color: var(--OpsInsyts-primary);
            box-shadow: 0 0 0 2px rgba(35, 115, 186, 0.2);
        }

        .OpsInsyts-password-strength {
            font-size: 12px;
            margin-top: -8px;
            margin-bottom: 10px;
            text-align: left;
        }

        .OpsInsyts-strength-weak {
            color: #dc2626;
        }

        .OpsInsyts-strength-medium {
            color: #f59e0b;
        }

        .OpsInsyts-strength-strong {
            color: #10b981;
        }

        .OpsInsyts-modal-btn {
            margin-top: 16px;
            padding: 12px 24px;
            background: var(--OpsInsyts-primary);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .OpsInsyts-modal-btn:hover {
            background: var(--OpsInsyts-primary-dark);
        }

        .OpsInsyts-modal-btn:disabled {
            background: var(--OpsInsyts-secondary-3);
            cursor: not-allowed;
        }

        .OpsInsyts-modal-footer {
            margin-top: 20px;
            font-size: 14px;
            color: var(--OpsInsyts-secondary-3);
        }

        .OpsInsyts-modal-footer a {
            color: var(--OpsInsyts-primary);
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
        }

        /* Slide-in Notification */
        .OpsInsyts-notification {
            position: fixed;
            top: 90px;
            right: 20px;
            z-index: 3000;
            min-width: 320px;
            max-width: 400px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateX(400px);
            transition: transform 0.3s ease-in-out;
            overflow: hidden;
        }

        .OpsInsyts-notification.show {
            transform: translateX(0);
        }

        .OpsInsyts-notification-content {
            padding: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .OpsInsyts-notification-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .OpsInsyts-notification-icon i {
            font-size: 14px;
        }

        .OpsInsyts-notification-message {
            flex: 1;
        }

        .OpsInsyts-notification-message strong {
            display: block;
            margin-bottom: 4px;
        }

        .OpsInsyts-notification-message p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        .OpsInsyts-notification-close {
            cursor: pointer;
            color: #999;
            font-size: 12px;
        }

        .OpsInsyts-notification-close:hover {
            color: #666;
        }

        .OpsInsyts-notification.success {
            border-left: 4px solid #10b981;
        }

        .OpsInsyts-notification.success .OpsInsyts-notification-icon {
            background: #d1fae5;
            color: #10b981;
        }

        .OpsInsyts-notification.error {
            border-left: 4px solid #dc2626;
        }

        .OpsInsyts-notification.error .OpsInsyts-notification-icon {
            background: #fee2e2;
            color: #dc2626;
        }

        .OpsInsyts-notification.info {
            border-left: 4px solid var(--OpsInsyts-primary);
        }

        .OpsInsyts-notification.info .OpsInsyts-notification-icon {
            background: #e4eff8;
            color: var(--OpsInsyts-primary);
        }

        .OpsInsyts-notification.warning {
            border-left: 4px solid #f59e0b;
        }

        .OpsInsyts-notification.warning .OpsInsyts-notification-icon {
            background: #fed7aa;
            color: #f59e0b;
        }

        /* Loading spinner */
        .OpsInsyts-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: OpsInsyts-spin 0.6s linear infinite;
            margin-right: 8px;
        }

        @keyframes OpsInsyts-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .OpsInsyts-nav-links {
                display: none;
            }

            .OpsInsyts-mobile-menu-btn {
                display: block;
            }

            .OpsInsyts-hero-content {
                grid-template-columns: 1fr;
            }

            .OpsInsyts-hero-text h1 {
                font-size: 36px;
            }

            .OpsInsyts-hero-text p {
                font-size: 16px;
            }

            .OpsInsyts-hero-buttons {
                flex-direction: column;
            }

            .OpsInsyts-hero-buttons button {
                width: 100%;
            }

            .OpsInsyts-stats {
                gap: 20px;
            }

            .OpsInsyts-stat h3 {
                font-size: 22px;
            }

            .OpsInsyts-hero-image {
                display: none;
            }

            .OpsInsyts-steps {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .OpsInsyts-section-header h2 {
                font-size: 28px;
            }

            .OpsInsyts-section-header p {
                font-size: 16px;
            }

            .OpsInsyts-features-grid {
                grid-template-columns: 1fr;
            }

            .OpsInsyts-testimonials-grid {
                grid-template-columns: 1fr;
            }

            .OpsInsyts-footer-content {
                grid-template-columns: 1fr 1fr;
            }

            .OpsInsyts-footer-logo {
                grid-column: span 2;
            }

            .OpsInsyts-notification {
                left: 20px;
                right: 20px;
                min-width: auto;
                max-width: none;
            }

            .OpsInsyts-cta h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .OpsInsyts-navbar {
                padding: 12px 20px;
            }

            .OpsInsyts-hero {
                padding: 120px 20px 60px;
            }

            .OpsInsyts-features,
            .OpsInsyts-how-it-works,
            .OpsInsyts-testimonials,
            .OpsInsyts-cta {
                padding: 60px 20px;
            }

            .OpsInsyts-footer-content {
                grid-template-columns: 1fr;
            }

            .OpsInsyts-footer-logo {
                grid-column: span 1;
            }
        }
    </style>
</head>

<body>

    <!-- Network detection-->
    <div class="OpsInsyts-global-loader" id="OpsInsytsGlobalLoader">
        <div class="OpsInsyts-loader-spinner"></div>
        <div class="OpsInsyts-loader-text">
        </div>
    </div>

    <!-- Navigation -->
    <nav class="OpsInsyts-navbar">
        <div class="OpsInsyts-nav-container">
            <div class="OpsInsyts-logo">
                <img src="src/image/main.png" alt="OpsInsyts Logo" onerror="this.src='https://via.placeholder.com/40x40?text=OI'">
            </div>
            <div class="OpsInsyts-nav-links">
                <a onclick="scrollToSection('features')"><i class="fas fa-concierge-bell"></i> Services</a>
                <a onclick="scrollToSection('how-it-works')"><i class="fas fa-project-diagram"></i> Workflow</a>
                <a onclick="scrollToSection('testimonials')"><i class="fas fa-message"></i> Business Results</a>
                <button class="OpsInsyts-btn-outline" onclick="openLoginModal()"><i class="fas fa-sign-in-alt"></i> Sign In</button>
                <button class="OpsInsyts-btn-primary" onclick="openRegisterModal()"><i class="fas fa-user-plus"></i> Get Started</button>
            </div>
            <button class="OpsInsyts-mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="OpsInsyts-mobile-nav" id="mobileNav">
        <a onclick="scrollToSection('features')"><i class="fas fa-concierge-bell"></i> Services</a>
        <a onclick="scrollToSection('how-it-works')"><i class="fas fa-project-diagram"></i> Workflow</a>
        <a onclick="scrollToSection('testimonials')"><i class="fas fa-message"></i> Business Results</a>
        <button class="OpsInsyts-btn-outline" onclick="closeMobileMenu(); openLoginModal()"><i class="fas fa-sign-in-alt"></i> Sign In</button>
        <button class="OpsInsyts-btn-primary" onclick="closeMobileMenu(); openRegisterModal()"><i class="fas fa-user-plus"></i> Get Started</button>
    </div>
    <div class="OpsInsyts-menu-overlay" id="menuOverlay"></div>

    <!-- main_section: hero -->
    <section class="OpsInsyts-hero">
        <div class="OpsInsyts-hero-content">
            <div class="OpsInsyts-hero-text">
                <div class="OpsInsyts-hero-badge"><i class="fas fa-robot"></i> AI-Powered Operations</div>
                <h1>Your Business Intelligence Layer</h1>
                <p>OpsInsyts reads your accounting data and shows you what needs attention, overdue invoices, expense anomalies, and compliance deadlines, before they become crises.</p>
                <div class="OpsInsyts-hero-buttons">
                    <button class="OpsInsyts-btn-primary OpsInsyts-btn-large" onclick="openRegisterModal()"><i class="fas fa-unlock"></i> Start Free Trial</button>
                    <button class="OpsInsyts-btn-outline OpsInsyts-btn-large" onclick="scrollToSection('how-it-works')"><i class="fas fa-project-diagram"></i> See How It Works</button>
                </div>
                <div class="OpsInsyts-stats">
                    <div class="OpsInsyts-stat">
                        <h3>R62K+</h3>
                        <p>Recovered in first month</p>
                    </div>
                    <div class="OpsInsyts-stat">
                        <h3>90 sec</h3>
                        <p>Morning scan time</p>
                    </div>
                    <div class="OpsInsyts-stat">
                        <h3>100%</h3>
                        <p>Compliance on time</p>
                    </div>
                </div>
            </div>
            <div class="OpsInsyts-hero-image">
                <i class="fas fa-chart-line"></i>
                <i class="fas fa-chart-pie" style="font-size: 80px; margin: 0 10px;"></i>
                <i class="fas fa-bell"></i>
            </div>
        </div>
    </section>

    <!-- main_section: features -->
    <section id="features" class="OpsInsyts-features">
        <div class="OpsInsyts-section-header">
            <h2>Everything you need to stay in control</h2>
            <p>No new data entry. No training required. Just actionable insights from your existing data.</p>
        </div>
        <div class="OpsInsyts-features-grid">
            <div class="OpsInsyts-feature-card">
                <div class="OpsInsyts-feature-icon"><i class="fas fa-coins"></i></div>
                <h3>Cash Intelligence</h3>
                <p>Shows every outstanding invoice with age and Rand value at risk. Overdue items sorted by severity, not alphabetically.</p>
            </div>
            <div class="OpsInsyts-feature-card">
                <div class="OpsInsyts-feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3>Expense Watchdog</h3>
                <p>Flags anomalies, duplicate invoices, and unusual category deviations automatically. Never pay twice again.</p>
            </div>
            <div class="OpsInsyts-feature-card">
                <div class="OpsInsyts-feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Compliance Calendar</h3>
                <p>Prepopulated SARS deadlines with reminders at 30, 14, 7, and 1 day before each deadline. Never miss a filing.</p>
            </div>
            <div class="OpsInsyts-feature-card">
                <div class="OpsInsyts-feature-icon"><i class="fas fa-chart-simple"></i></div>
                <h3>Health Score</h3>
                <p>A single number from 0–100 calculated from cash ratio, overdue invoices, expense growth, and compliance items.</p>
            </div>
            <div class="OpsInsyts-feature-card">
                <div class="OpsInsyts-feature-icon"><i class="fas fa-bolt"></i></div>
                <h3>Priority Actions</h3>
                <p>The three things you must do today, ordered by financial risk. One-click to send reminders or resolve alerts.</p>
            </div>
            <div class="OpsInsyts-feature-card">
                <div class="OpsInsyts-feature-icon"><i class="fas fa-bell"></i></div>
                <h3>Alert Feed</h3>
                <p>Real-time notifications for overdue invoices, duplicate expenses, and approaching deadlines. Stay ahead of problems.</p>
            </div>
        </div>
    </section>

    <!-- main_section: how-it-works -->
    <section id="how-it-works" class="OpsInsyts-how-it-works">
        <div class="OpsInsyts-section-header">
            <h2>How It Works</h2>
            <p>Three simple steps to operational clarity</p>
        </div>
        <div class="OpsInsyts-steps">
            <div class="OpsInsyts-step">
                <div class="OpsInsyts-step-number">1</div>
                <h3>Register Your Company</h3>
                <p>Create your free account. Tell us about your business.</p>
            </div>
            <div class="OpsInsyts-step">
                <div class="OpsInsyts-step-number">2</div>
                <h3>Upload Your Data</h3>
                <p>Export CSV from your accounting system. OpsInsyts reads it instantly.</p>
            </div>
            <div class="OpsInsyts-step">
                <div class="OpsInsyts-step-number">3</div>
                <h3>Scan Your Dashboard</h3>
                <p>Open the app each morning. In 90 seconds, you'll know exactly what needs your attention.</p>
            </div>
        </div>
    </section>

    <!-- main_section: testimonials -->
    <section id="testimonials" class="OpsInsyts-testimonials">
        <div class="OpsInsyts-section-header">
            <h2>Trusted by South African SMEs</h2>
            <p>Join hundreds of business owners who sleep better with OpsInsyts</p>
        </div>
        <div class="OpsInsyts-testimonials-grid">
            <div class="OpsInsyts-testimonial-card"><i class="fas fa-quote-left"></i>
                <p class="OpsInsyts-testimonial-text">"Before OpsInsyts, I spent two hours every Monday chasing debtors. Now I spend 90 seconds on the dashboard and recovered R62,000 in the first month."</p>
                <div class="OpsInsyts-testimonial-author">
                    <div class="OpsInsyts-testimonial-avatar"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="OpsInsyts-testimonial-name">Michael van der Merwe</div>
                        <div class="OpsInsyts-testimonial-role">Retail, Johannesburg</div>
                        <div class="OpsInsyts-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                </div>
            </div>
            <div class="OpsInsyts-testimonial-card"><i class="fas fa-quote-left"></i>
                <p class="OpsInsyts-testimonial-text">"We haven't missed a single SARS deadline since implementing OpsInsyts. The Compliance Calendar alone is worth every cent."</p>
                <div class="OpsInsyts-testimonial-author">
                    <div class="OpsInsyts-testimonial-avatar"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="OpsInsyts-testimonial-name">Sarah Williams</div>
                        <div class="OpsInsyts-testimonial-role">Accounting Firm, Cape Town</div>
                        <div class="OpsInsyts-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                </div>
            </div>
            <div class="OpsInsyts-testimonial-card"><i class="fas fa-quote-left"></i>
                <p class="OpsInsyts-testimonial-text">"The Expense Watchdog caught a R12,400 duplicate billing. That single alert paid for the software for two years."</p>
                <div class="OpsInsyts-testimonial-author">
                    <div class="OpsInsyts-testimonial-avatar"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="OpsInsyts-testimonial-name">Thabo Nkosi</div>
                        <div class="OpsInsyts-testimonial-role">Construction, Durban</div>
                        <div class="OpsInsyts-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- main_section: cta -->
    <section class="OpsInsyts-cta">
        <div class="OpsInsyts-cta-content">
            <h2>Ready to take control?</h2>
            <p>Join 500+ South African businesses already using OpsInsyts to direct their operations.</p><button class="OpsInsyts-btn-primary OpsInsyts-btn-large" onclick="openRegisterModal()"><i class="fas fa-arrow-right"></i> Start Free Trial</button>
        </div>
    </section>

    <footer class="OpsInsyts-footer">
        <div class="OpsInsyts-footer-content">
            <div class="OpsInsyts-footer-logo">
                <div class="OpsInsyts-logo-text">
                    <div class="OpsInsyts-logo">
                        <img src="src/image/logos.png" alt="OpsInsyts Logo" onerror="this.src='https://via.placeholder.com/40x40?text=OI'">
                    </div>
                </div>
                <p style="margin-top: 10px; color: #989798; font-size: 14px;">AI Operations Assistant for SMEs</p>
            </div>
            <div class="OpsInsyts-footer-links">
                <h4><i class="fas fa-box"></i> Product</h4><a onclick="scrollToSection('features')"><i class="fas fa-star"></i> Features</a><a onclick="openRegisterModal()"><i class="fas fa-rocket"></i> Start Free Trial</a><a onclick="openLoginModal()"><i class="fas fa-sign-in-alt"></i> Sign In</a>
            </div>
            <div class="OpsInsyts-footer-links">
                <h4><i class="fas fa-building"></i> Company</h4><a href="#"><i class="fas fa-info-circle"></i> About</a><a href="#"><i class="fas fa-blog"></i> Blog</a><a href="#"><i class="fas fa-envelope"></i> Contact</a>
            </div>
            <div class="OpsInsyts-footer-links">
                <h4><i class="fas fa-gavel"></i> Legal</h4><a href="#"><i class="fas fa-lock"></i> Privacy Policy</a><a href="#"><i class="fas fa-file-contract"></i> Terms of Service</a><a href="#"><i class="fas fa-shield-alt"></i> Security</a>
            </div>
            <div class="OpsInsyts-footer-links">
                <h4><i class="fas fa-address-card"></i> Contact</h4><a href="#"><i class="fas fa-envelope"></i> hello@opsinsyts.com</a><a href="#"><i class="fas fa-phone"></i> +27 (0) 10 123 4567</a>
            </div>
        </div>
        <div class="OpsInsyts-footer-bottom">
            <p>&copy; 2025 OpsInsyts. All rights reserved. Made in South Africa</p>
        </div>
    </footer>

    <div id="OpsInsyts-notificationContainer"></div>

    <!-- MODALS -->
    <div class="OpsInsyts-modal" id="registerModal">
        <div class="OpsInsyts-modal-content"><i class="fas fa-user-plus"></i>
            <h2>Create Your Account</h2>
            <p>Start your 14-day free trial. No credit card required.</p><input type="text" id="regFullName" placeholder="Full name" required><input type="text" id="regCompanyName" placeholder="Company name" required><input type="email" id="regEmail" placeholder="Email address" required><input type="tel" id="regPhone" placeholder="Phone number (+27XXXXXXXXX)" required><input type="password" id="regPassword" placeholder="Password" onkeyup="checkPasswordStrength()" required>
            <div class="OpsInsyts-password-strength" id="passwordStrength"></div><button class="OpsInsyts-modal-btn" id="registerBtn" onclick="registerUser()">Create Account</button>
            <div class="OpsInsyts-modal-footer">Already have an account? <a onclick="switchToLogin()">Sign In</a></div>
        </div>
    </div>

    <div class="OpsInsyts-modal" id="loginModal">
        <div class="OpsInsyts-modal-content"><i class="fas fa-sign-in-alt"></i>
            <h2>Welcome Back</h2>
            <p>Sign in to your OpsInsyts account</p><input type="email" id="loginEmail" placeholder="Email address" required><input type="password" id="loginPassword" placeholder="Password" required><button class="OpsInsyts-modal-btn" id="loginBtn" onclick="loginUser()"> Sign In</button>
            <div class="OpsInsyts-modal-footer">Don't have an account? <a onclick="switchToRegister()">Create Account</a></div>
        </div>
    </div>

    <script>
        const API_BASE = 'api/login_register';
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileNav = document.getElementById('mobileNav');
        const menuOverlay = document.getElementById('menuOverlay');
        const globalLoader = document.getElementById('OpsInsytsGlobalLoader');

        // Hide loader function when network is fine
        function hideNetworkLoader() {
            if (globalLoader) {
                globalLoader.classList.add('hide');
                setTimeout(() => {
                    if (globalLoader.parentNode) globalLoader.style.display = 'none';
                }, 500);
            }
        }

        // Check network connectivity and hide loader when connection is stable
        async function checkNetworkAndProceed() {
            try {
                // Try to fetch a lightweight resource with timeout to verify connectivity
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000);

                await fetch('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', {
                    method: 'HEAD',
                    mode: 'no-cors', 
                    signal: controller.signal
                }).catch(() => {});

                clearTimeout(timeoutId);
                // Even if fetch fails but browser is online
                if (navigator.onLine !== false) {
                    hideNetworkLoader();
                } else {
                    // double-check with online property
                    setTimeout(() => {
                        if (navigator.onLine) hideNetworkLoader();
                        else {
                            // fallback: hide anyway after 2.5 sec to not block forever
                            setTimeout(hideNetworkLoader, 2500);
                        }
                    }, 300);
                }
            } catch (err) {
                // Fallback
                setTimeout(hideNetworkLoader, 1800);
            }

            //when connection becomes online
            window.addEventListener('online', () => hideNetworkLoader());
            // Also hide when page fully loaded (network ready)
            window.addEventListener('load', () => {
                setTimeout(() => {
                    if (globalLoader && globalLoader.style.display !== 'none') hideNetworkLoader();
                }, 800);
            });
        }

        // Execute network detection and loader hiding
        checkNetworkAndProceed();

        // Mobile menu functions
        function openMobileMenu() {
            mobileNav.classList.add('active');
            menuOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeMobileMenu() {
            mobileNav.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', openMobileMenu);
        if (menuOverlay) menuOverlay.addEventListener('click', closeMobileMenu);
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && mobileNav.classList.contains('active')) closeMobileMenu();
        });

        function validateSAPhoneNumber(phone) {
            const cleaned = phone.replace(/[\s\-\(\)]/g, '');
            return /^\+27[0-9]{9}$/.test(cleaned) || /^0[0-9]{9}$/.test(cleaned);
        }

        function formatSAPhoneNumber(phone) {
            const cleaned = phone.replace(/[\s\-\(\)]/g, '');
            return cleaned.startsWith('0') ? '+27' + cleaned.substring(1) : cleaned;
        }

        function showNotification(title, message, type = 'info', duration = 4000) {
            const container = document.getElementById('OpsInsyts-notificationContainer');
            const notification = document.createElement('div');
            notification.className = `OpsInsyts-notification ${type}`;
            let icon = {
                success: '<i class="fas fa-check-circle"></i>',
                error: '<i class="fas fa-exclamation-circle"></i>',
                warning: '<i class="fas fa-exclamation-triangle"></i>',
                info: '<i class="fas fa-info-circle"></i>'
            } [type] || '<i class="fas fa-info-circle"></i>';
            notification.innerHTML = `<div class="OpsInsyts-notification-content"><div class="OpsInsyts-notification-icon">${icon}</div><div class="OpsInsyts-notification-message"><strong>${title}</strong><p>${message}</p></div><div class="OpsInsyts-notification-close" onclick="this.closest('.OpsInsyts-notification').remove()"><i class="fas fa-times"></i></div></div>`;
            container.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }

        function scrollToSection(sectionId) {
            closeMobileMenu();
            document.getElementById(sectionId).scrollIntoView({
                behavior: 'smooth'
            });
        }

        function openRegisterModal() {
            closeMobileMenu();
            closeAllModals();
            document.getElementById('registerModal').classList.add('active');
            clearRegisterForm();
        }

        function openLoginModal() {
            closeMobileMenu();
            closeAllModals();
            document.getElementById('loginModal').classList.add('active');
            clearLoginForm();
        }

        function closeAllModals() {
            document.querySelectorAll('.OpsInsyts-modal').forEach(modal => modal.classList.remove('active'));
        }

        function switchToLogin() {
            closeAllModals();
            openLoginModal();
        }

        function switchToRegister() {
            closeAllModals();
            openRegisterModal();
        }

        function clearRegisterForm() {
            ['regFullName', 'regCompanyName', 'regEmail', 'regPhone', 'regPassword'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('passwordStrength').innerHTML = '';
        }

        function clearLoginForm() {
            document.getElementById('loginEmail').value = '';
            document.getElementById('loginPassword').value = '';
        }

        function checkPasswordStrength() {
            const pwd = document.getElementById('regPassword').value;
            const el = document.getElementById('passwordStrength');
            if (!pwd.length) {
                el.innerHTML = '';
                return;
            }
            let strength = (pwd.length >= 8 ? 1 : 0) + (/[a-z]/.test(pwd) ? 1 : 0) + (/[A-Z]/.test(pwd) ? 1 : 0) + (/[0-9]/.test(pwd) ? 1 : 0) + (/[^a-zA-Z0-9]/.test(pwd) ? 1 : 0);
            el.innerHTML = strength <= 2 ? '<span class="OpsInsyts-strength-weak">Weak password (8+ chars, uppercase, number)</span>' : (strength <= 4 ? '<span class="OpsInsyts-strength-medium">Medium password</span>' : '<span class="OpsInsyts-strength-strong">Strong password</span>');
        }

        function setButtonLoading(btn, isLoading) {
            if (isLoading) {
                btn.disabled = true;
                btn.innerHTML = '<span class="OpsInsyts-spinner"></span> Processing...';
            } else {
                btn.disabled = false;
                btn.innerHTML = btn.getAttribute('data-original-text');
            }
        }

        async function registerUser() {
            const fullName = document.getElementById('regFullName').value.trim(),
                companyName = document.getElementById('regCompanyName').value.trim(),
                email = document.getElementById('regEmail').value.trim(),
                phoneRaw = document.getElementById('regPhone').value.trim(),
                password = document.getElementById('regPassword').value;
            const btn = document.getElementById('registerBtn');
            if (!fullName || !companyName || !email || !phoneRaw || !password) return showNotification('Registration Failed', 'Please fill in all fields', 'error');
            if (!validateSAPhoneNumber(phoneRaw)) return showNotification('Registration Failed', 'Valid SA phone number required (+27XXXXXXXXX or 0XXXXXXXXX)', 'error');
            const phone = formatSAPhoneNumber(phoneRaw);
            if (password.length < 8) return showNotification('Registration Failed', 'Password must be at least 8 characters', 'error');
            if (!/[A-Z]/.test(password)) return showNotification('Registration Failed', 'Password must contain an uppercase letter', 'error');
            if (!/[0-9]/.test(password)) return showNotification('Registration Failed', 'Password must contain a number', 'error');
            btn.setAttribute('data-original-text', btn.innerHTML);
            setButtonLoading(btn, true);
            try {
                const res = await fetch(`${API_BASE}/register_and_login.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'register',
                        full_name: fullName,
                        company_name: companyName,
                        email,
                        phone,
                        password
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showNotification('Registration Successful', `Welcome ${fullName}! Redirecting...`, 'success');
                    setTimeout(() => loginUserDirect(email, password), 1500);

                } else {
                    showNotification('Registration Failed', data.message || 'Something went wrong', 'error');
                    setButtonLoading(btn, false);
                }
            } catch (e) {
                showNotification('Registration Failed', 'Network error. Please try again.', 'error');
                setButtonLoading(btn, false);
            }
        }

        async function loginUser() {
            const email = document.getElementById('loginEmail').value.trim(),
                password = document.getElementById('loginPassword').value,
                btn = document.getElementById('loginBtn');
            if (!email || !password) return showNotification('Login Failed', 'Please enter email and password', 'error');
            btn.setAttribute('data-original-text', btn.innerHTML);
            setButtonLoading(btn, true);
            try {
                const res = await fetch(`${API_BASE}/register_and_login.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email,
                        password
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showNotification('Login Successful', `Welcome back ${data.user?.full_name || 'User'}!`, 'success');
                    //not secure
                    //localStorage.setItem('opsinsyts_user', JSON.stringify(data.user));
                    //localStorage.setItem('opsinsyts_token', data.token);
                    setTimeout(() => {
                        closeAllModals();
                        showNotification('Redirecting', 'Taking you to your dashboard...', 'info', 2000);
                    }, 1500);
                    setTimeout(() => {
                        window.location.href = "company/";
                    }, 2500);
                } else {
                    showNotification('Login Failed', data.message || 'Invalid credentials', 'error');
                    setButtonLoading(btn, false);
                }
            } catch (e) {
                showNotification('Login Failed', 'Network error. Please try again.', 'error');
                setButtonLoading(btn, false);
            }
        }

        async function loginUserDirect(email, password) {
            try {
                const res = await fetch(`${API_BASE}/register_and_login.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'login',
                        email,
                        password
                    })
                });
                const data = await res.json();
                if (data.success) {
                  //  localStorage.setItem('opsinsyts_user', JSON.stringify(data.user));
                 //   localStorage.setItem('opsinsyts_token', data.token);
                    closeAllModals();
                    showNotification('Welcome!', 'You are now logged in', 'success');
                    setTimeout(() => {


                        window.location.href = "company/";
                    }, 2500);

                }
            } catch (e) {
                console.error('auto-login error');
            }
        }

        document.querySelectorAll('.OpsInsyts-modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('active');
            });
        });


        // Auto-scroll-up widget button 
        function createAutoScrollUpWidget() {
            // Check if widget already exists
            if (document.querySelector('.OpsInsyts-scrollup-widget')) return;

            // Create widget container
            const widget = document.createElement('div');
            widget.className = 'OpsInsyts-scrollup-widget';
            widget.innerHTML = `
        <button class="OpsInsyts-scrollup-btn" id="OpsInsytsScrollUpBtn" title="Scroll to top">
            <i class="fas fa-arrow-up"></i>
            <span class="OpsInsyts-scrollup-text"></span>
        </button>
    `;

            // Add styles for the widget
            const style = document.createElement('style');
            style.textContent = `
        .OpsInsyts-scrollup-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
            font-family: 'Inter', sans-serif;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }
        
        .OpsInsyts-scrollup-widget.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .OpsInsyts-scrollup-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--OpsInsyts-primary, #2373BA);
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 2px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
        }
        
        .OpsInsyts-scrollup-btn:hover {
            background: var(--OpsInsyts-primary-dark, #1b5a96);
            transform: scale(1.05);
        }
        
        .OpsInsyts-scrollup-btn:active {
            transform: scale(0.95);
        }
        
        .OpsInsyts-scrollup-btn i {
            font-size: 20px;
        }
        
        .OpsInsyts-scrollup-text {
            font-size: 10px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .OpsInsyts-scrollup-widget {
                bottom: 20px;
                right: 20px;
            }
            
            .OpsInsyts-scrollup-btn {
                width: 48px;
                height: 48px;
            }
        }
    `;
            document.head.appendChild(style);
            document.body.appendChild(widget);

            // Get the button element
            const scrollUpBtn = document.getElementById('OpsInsytsScrollUpBtn');

            // Function to check scroll position and show/hide button
            function toggleScrollUpButton() {
                if (window.scrollY > 300) {
                    widget.classList.add('show');
                } else {
                    widget.classList.remove('show');
                }
            }

            // Function to scroll to top smoothly
            function scrollToTop() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }

            // Add scroll event listener
            window.addEventListener('scroll', toggleScrollUpButton);

            // Add click event listener for scroll to top
            scrollUpBtn.addEventListener('click', scrollToTop);

            // Initial check
            toggleScrollUpButton();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', createAutoScrollUpWidget);
        } else {
            createAutoScrollUpWidget();
        }
    </script>
</body>

</html>