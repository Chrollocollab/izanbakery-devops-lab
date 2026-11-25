<?php

namespace App\Http\Controllers;

use App\Models\CustomerFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * Display the customer feedback page with user's feedback history.
     */
    public function index()
    {
        $feedbacks = CustomerFeedback::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.customer-feedback', compact('feedbacks'));
    }

    /**
     * Store a new customer feedback.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:suggestion,complaint,question,praise',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        CustomerFeedback::create([
            'user_id' => Auth::id(),
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'rating' => $validated['rating'] ?? null,
        ]);

        return redirect()->route('user.feedback')
            ->with('success', 'Your feedback has been submitted successfully.');
    }

    /**
     * Display a specific feedback.
     */
    public function show(CustomerFeedback $feedback)
    {
        if ($feedback->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.feedback-detail', compact('feedback'));
    }

    /**
     * Delete a feedback (only if pending).
     */
    public function destroy(CustomerFeedback $feedback)
    {
        if ($feedback->user_id !== Auth::id()) {
            abort(403);
        }

        $feedback->delete();

        return redirect()->route('user.feedback')
            ->with('success', 'Feedback deleted successfully.');
    }
}
