@extends('layouts.app')

@section('content')
<div class="container py-5 my-3" style="max-width:860px;">
  <h1 class="mb-4">Data Deletion Instructions</h1>
  <p class="text-secondary">Last updated: {{ now()->format('F j, Y') }}</p>

  <p>You can request the deletion of your Izan Bakery account and personal data at any time, including data linked from Google or Facebook login.</p>

  <h2 class="h4 mt-4">How to Request Deletion</h2>
  <p>Send an email to <a href="mailto:wannadzmin339@gmail.com">wannadzmin339@gmail.com</a> from the email address associated with your account, with the subject line "Data Deletion Request". Please include:</p>
  <ul>
    <li>The name on your account</li>
    <li>The email address used to register or log in</li>
  </ul>

  <h2 class="h4 mt-4">What Gets Deleted</h2>
  <p>Once verified, we will delete your account information (name, email, mobile number, Google/Facebook login ID) and any personal data associated with your profile within 30 days. Order records required for accounting, tax, or legal purposes may be retained as required by law, with personal identifiers removed where possible.</p>

  <h2 class="h4 mt-4">Removing Izan Bakery from Facebook or Google</h2>
  <p>You can also revoke Izan Bakery's access to your Google or Facebook account directly:</p>
  <ul>
    <li><strong>Facebook:</strong> Settings &amp; Privacy → Settings → Apps and Websites → remove Izan Bakery.</li>
    <li><strong>Google:</strong> Google Account → Security → Third-party apps with account access → remove Izan Bakery.</li>
  </ul>
  <p>Revoking access stops future logins but does not automatically delete your existing Izan Bakery account — please also send us a deletion request as described above.</p>

  <p class="mt-4">See our <a href="{{ route('privacy-policy') }}">Privacy Policy</a> for more on how we handle your data.</p>
</div>
@endsection
