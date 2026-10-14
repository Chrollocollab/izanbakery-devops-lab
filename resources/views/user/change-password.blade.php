@extends('layouts.app')
@section('content')
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <h2 class="page-title">Change Password</h2>
        <div class="row">
            <div class="col-lg-3">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__dashboard">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.password.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                                               id="current_password" name="current_password" required>
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">At least 8 characters.</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control"
                                               id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection
