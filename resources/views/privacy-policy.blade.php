@extends('layouts.app')

@section('content')
<div class="container py-5 my-3" style="max-width:860px;">
  <h1 class="mb-4">Privacy Policy</h1>
  <p class="text-secondary">Last updated: {{ now()->format('F j, Y') }}</p>

  <p>Izan Bakery ("we", "us", "our") operates izanbakery.com. This page explains what information we collect from you, how we use it, and how you can control it.</p>

  <h2 class="h4 mt-4">Information We Collect</h2>
  <ul>
    <li><strong>Account information:</strong> name, email address, and mobile number when you register, or when you sign in with Google or Facebook.</li>
    <li><strong>Order information:</strong> delivery name, phone number, email, and address when you place an order.</li>
    <li><strong>Social login identifiers:</strong> a unique ID from Google or Facebook if you choose to log in using those services, used only to link your account.</li>
  </ul>

  <h2 class="h4 mt-4">How We Use Your Information</h2>
  <ul>
    <li>To create and manage your account.</li>
    <li>To process and deliver your orders.</li>
    <li>To contact you about your orders, account, or customer support requests.</li>
  </ul>

  <h2 class="h4 mt-4">Login with Google / Facebook</h2>
  <p>When you sign in with Google or Facebook, we receive your name, email address, and a unique account ID from that provider. We do not receive your password. We use this information only to create or sign in to your Izan Bakery account, and we do not post to your Google or Facebook account on your behalf.</p>

  <h2 class="h4 mt-4">Data Sharing</h2>
  <p>We do not sell your personal information. We do not share your data with third parties except as needed to fulfil your order (e.g. delivery) or as required by law.</p>

  <h2 class="h4 mt-4">Your Rights</h2>
  <p>You may request access to, correction of, or deletion of your personal data at any time. See our <a href="{{ route('data-deletion') }}">Data Deletion Instructions</a> for details.</p>

  <h2 class="h4 mt-4">Contact Us</h2>
  <p>If you have any questions about this Privacy Policy, contact us at <a href="mailto:wannadzmin339@gmail.com">wannadzmin339@gmail.com</a>.</p>
</div>
@endsection
