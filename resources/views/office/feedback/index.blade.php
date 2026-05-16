@extends('office.layouts.app')

@section('title', 'Citizen Feedback')

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    function respondToFeedback(feedbackId) {
        const responseText = $('#response-' + feedbackId).val();
        if (!responseText.trim()) return alert('Response cannot be empty.');

        $.ajax({
            url: `/office/feedback/${feedbackId}/respond`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                response: responseText
            },
            success: function(res) {
                $('#response-block-' + feedbackId).html(`
                    <div class="p-3 bg-light rounded mt-3 border-start border-4 border-primary">
                        <strong class="text-primary d-block mb-1">Your Response:</strong>
                        <p class="mb-0 text-dark">${res.data.response}</p>
                    </div>
                `);
                alert('Response submitted successfully.');
            },
            error: function(err) {
                alert('Failed to submit response.');
            }
        });
    }
</script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="mb-0 text-dark fw-bold"><i class="bi bi-star-fill text-warning me-2"></i>Citizen Feedback</h3>
            <p class="text-muted">Review and respond to feedback from citizens regarding your services.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($feedbacks->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-emoji-smile text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-dark fw-bold">No Feedback Yet</h4>
                        <p class="text-muted">Once citizens submit feedback for completed service requests, they will appear here.</p>
                    </div>
                </div>
            @else
                <div class="row row-cols-1 row-cols-lg-2 g-4">
                    @foreach($feedbacks as $feedback)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-4">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                                    <h5 class="fw-bold mb-0 text-dark">{{ $feedback->serviceRequest->service->name ?? 'Unknown Service' }}</h5>
                                    <div class="text-warning">
                                        @for($i=1; $i<=5; $i++)
                                            <i class="bi bi-star-fill {{ $i <= $feedback->rating ? 'text-warning' : 'text-light' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                            {{ strtoupper(substr($feedback->citizen->username ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">{{ $feedback->citizen->username ?? 'Unknown Citizen' }}</h6>
                                            <small class="text-muted">{{ $feedback->created_at->format('M d, Y h:i A') }}</small>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-light p-3 rounded mb-3">
                                        <p class="mb-0 fst-italic text-secondary">"{{ $feedback->comment ?: 'No written comment provided.' }}"</p>
                                    </div>

                                    <div id="response-block-{{ $feedback->id }}">
                                        @if($feedback->response)
                                            <div class="p-3 bg-white border rounded border-start border-4 border-primary">
                                                <strong class="text-primary d-block mb-1">Your Response:</strong>
                                                <p class="mb-0 text-dark">{{ $feedback->response }}</p>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <label class="form-label text-muted small fw-bold">Respond to this feedback</label>
                                                <div class="input-group">
                                                    <textarea id="response-{{ $feedback->id }}" class="form-control" rows="1" placeholder="Type your response..."></textarea>
                                                    <button class="btn btn-primary" onclick="respondToFeedback({{ $feedback->id }})">Reply</button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
