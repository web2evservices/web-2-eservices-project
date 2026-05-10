@extends('office.layouts.app')
@section('title', 'Office Profile')
@section('content')

<h3 class="mb-4"><i class="bi bi-building-gear me-2 text-primary"></i>Office Profile</h3>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('office.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Office Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $office->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Info <span class="text-danger">*</span></label>
                    <input type="text" name="contact_info"
                           class="form-control @error('contact_info') is-invalid @enderror"
                           value="{{ old('contact_info', $office->contact_info ?? '') }}"
                           placeholder="Phone or email" required>
                    @error('contact_info')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <input type="text" name="address"
                           class="form-control @error('address') is-invalid @enderror"
                           value="{{ old('address', $office->address ?? '') }}" required>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Working Hours <span class="text-danger">*</span></label>
                    <input type="text" name="working_hours"
                           class="form-control @error('working_hours') is-invalid @enderror"
                           value="{{ old('working_hours', $office->working_hours ?? '') }}"
                           placeholder="e.g. Mon-Fri 8:00AM – 4:00PM" required>
                    @error('working_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Latitude</label>
                    <input type="number" step="any" name="latitude" id="lat-input"
                           class="form-control @error('latitude') is-invalid @enderror"
                           value="{{ old('latitude', $office->latitude ?? '') }}"
                           placeholder="e.g. 33.8938">
                    <small class="text-muted">Used for Google Maps display</small>
                    @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Longitude</label>
                    <input type="number" step="any" name="longitude" id="lng-input"
                           class="form-control @error('longitude') is-invalid @enderror"
                           value="{{ old('longitude', $office->longitude ?? '') }}"
                           placeholder="e.g. 35.5018">
                    @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Google Maps Preview --}}
            @if($office && $office->latitude && $office->longitude)
            <div class="mt-4">
                <label class="form-label fw-semibold">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>Map Preview
                </label>
                <div id="map" style="height: 300px; border-radius: 12px; border: 1px solid #dee2e6;"></div>
            </div>
            @else
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle me-2"></i>
                Enter latitude and longitude above and save to see the map preview.
            </div>
            @endif

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@if($office && $office->latitude && $office->longitude)
<script>
function initMap() {
    const position = {
        lat: parseFloat('{{ $office->latitude }}'),
        lng: parseFloat('{{ $office->longitude }}')
    };
    const map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: position,
    });
    new google.maps.Marker({ position: position, map: map });
}
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap">
</script>
@endif
@endsection