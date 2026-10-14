@extends('layouts.app')
@section('content')
    <div class="mb-4 pb-4"></div>
    <section class="my-account container">
        <h2 class="page-title">Customer Feedback</h2>
        <div class="row">
            <div class="col-lg-3">
                @include('user.account-nav')
            </div>
            <div class="col-lg-9">
                <div class="page-content my-account__dashboard">
                    
                    {{-- Success/Error Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Feedback Form --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Submit Your Feedback</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('user.feedback.store') }}" method="POST">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="type" class="form-label">Feedback Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <option value="suggestion" {{ old('type') == 'suggestion' ? 'selected' : '' }}>Suggestion</option>
                                            <option value="complaint" {{ old('type') == 'complaint' ? 'selected' : '' }}>Complaint</option>
                                            <option value="question" {{ old('type') == 'question' ? 'selected' : '' }}>Question</option>
                                            <option value="praise" {{ old('type') == 'praise' ? 'selected' : '' }}>Praise</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="rating" class="form-label">Overall Rating</label>
                                        <div class="rating-input">
                                            <div class="d-flex align-items-center gap-2">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="rating" id="rating{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="rating{{ $i }}">
                                                            {{ $i }} <i class="bi bi-star-fill text-warning"></i>
                                                        </label>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                        @error('rating')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Brief subject of your feedback" required>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" placeholder="Please describe your feedback in detail..." required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Maximum 2000 characters</div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-2"></i>Submit Feedback
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Feedback History --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Your Feedback History</h5>
                        </div>
                        <div class="card-body">
                            @if($feedbacks->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Subject</th>
                                                <th>Type</th>
                                                <th>Rating</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($feedbacks as $feedback)
                                                <tr>
                                                    <td>{{ $feedback->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <strong>{{ Str::limit($feedback->subject, 30) }}</strong>
                                                        @if($feedback->admin_response)
                                                            <span class="badge bg-info text-white ms-1" title="Admin has responded">
                                                                <i class="bi bi-reply-fill"></i>
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $typeColors = [
                                                                'suggestion' => 'primary',
                                                                'complaint' => 'danger',
                                                                'question' => 'info',
                                                                'praise' => 'success',
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $typeColors[$feedback->type] ?? 'secondary' }}">
                                                            {{ ucfirst($feedback->type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($feedback->rating)
                                                            @for($i = 1; $i <= 5; $i++)
                                                                <i class="bi bi-star-fill {{ $i <= $feedback->rating ? 'text-warning' : 'text-muted' }}" style="font-size: 12px;"></i>
                                                            @endfor
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'pending' => 'warning',
                                                                'reviewed' => 'info',
                                                                'resolved' => 'success',
                                                            ];
                                                        @endphp
                                                        <span class="badge bg-{{ $statusColors[$feedback->status] ?? 'secondary' }}">
                                                            {{ ucfirst($feedback->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#feedbackModal{{ $feedback->id }}" title="View Details">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        @if($feedback->status === 'pending')
                                                            <form action="{{ route('user.feedback.destroy', $feedback) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this feedback?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>

                                                {{-- Feedback Detail Modal --}}
                                                <div class="modal fade" id="feedbackModal{{ $feedback->id }}" tabindex="-1" aria-labelledby="feedbackModalLabel{{ $feedback->id }}" aria-hidden="true">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="feedbackModalLabel{{ $feedback->id }}">
                                                                    Feedback Details
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row mb-3">
                                                                    <div class="col-md-6">
                                                                        <strong>Subject:</strong>
                                                                        <p>{{ $feedback->subject }}</p>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <strong>Submitted On:</strong>
                                                                        <p>{{ $feedback->created_at->format('F d, Y h:i A') }}</p>
                                                                    </div>
                                                                </div>
                                                                <div class="row mb-3">
                                                                    <div class="col-md-4">
                                                                        <strong>Type:</strong>
                                                                        <p>
                                                                            <span class="badge bg-{{ $typeColors[$feedback->type] ?? 'secondary' }}">
                                                                                {{ ucfirst($feedback->type) }}
                                                                            </span>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Rating:</strong>
                                                                        <p>
                                                                            @if($feedback->rating)
                                                                                @for($i = 1; $i <= 5; $i++)
                                                                                    <i class="bi bi-star-fill {{ $i <= $feedback->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                                                @endfor
                                                                            @else
                                                                                <span class="text-muted">Not rated</span>
                                                                            @endif
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <strong>Status:</strong>
                                                                        <p>
                                                                            <span class="badge bg-{{ $statusColors[$feedback->status] ?? 'secondary' }}">
                                                                                {{ ucfirst($feedback->status) }}
                                                                            </span>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <strong>Your Message:</strong>
                                                                    <div class="p-3 bg-light rounded">
                                                                        {!! nl2br(e($feedback->message)) !!}
                                                                    </div>
                                                                </div>
                                                                @if($feedback->admin_response)
                                                                    <div class="mb-3">
                                                                        <strong><i class="bi bi-reply-fill text-primary me-2"></i>Admin Response:</strong>
                                                                        <div class="p-3 bg-primary bg-opacity-10 rounded border-start border-primary border-3">
                                                                            {!! nl2br(e($feedback->admin_response)) !!}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Pagination --}}
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $feedbacks->links() }}
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bi bi-chat-square-text fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">You haven't submitted any feedback yet.</p>
                                    <p class="text-muted small">Use the form above to share your thoughts with us!</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .rating-input .form-check-input:checked + .form-check-label {
        font-weight: bold;
    }
    .card {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #eee;
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush