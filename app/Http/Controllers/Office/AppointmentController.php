<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\Office;
use App\Models\Services;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $query = Appointments::with(['service', 'citizen'])
            ->where('office_id', $office->id);

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('date', 'asc')
            ->orderBy('time_slot', 'asc')
            ->paginate(15);

        return view('office.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $office = Office::where('user_id', Auth::id())->firstOrFail();
        $services = Services::where('office_id', $office->id)->get();

        return view('office.appointments.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'citizen_name' => 'required|string|max:255',
            'citizen_email' => 'required|email',
            'citizen_phone' => 'nullable|string|max:20',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string',
        ]);

        $office = Office::where('user_id', Auth::id())->firstOrFail();

        // Check if the service belongs to this office
        $service = Services::where('id', $validated['service_id'])
            ->where('office_id', $office->id)
            ->firstOrFail();

        $citizenId = Users::where('email', $validated['citizen_email'])->value('id');

        Appointments::create([
            'office_id' => $office->id,
            'service_id' => $validated['service_id'],
            'citizen_id' => $citizenId,
            'citizen_name' => $validated['citizen_name'],
            'citizen_email' => $validated['citizen_email'],
            'citizen_phone' => $validated['citizen_phone'],
            'date' => $validated['appointment_date'],
            'time_slot' => $validated['appointment_time'],
            'status' => 'Scheduled',
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('office.appointments.index')->with('success', 'Appointment scheduled successfully.');
    }

    public function show($id)
    {
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $appointment = Appointments::where('office_id', $office->id)
            ->with(['service', 'citizen'])
            ->findOrFail($id);

        return view('office.appointments.show', compact('appointment'));
    }

    public function edit($id)
    {
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $appointment = Appointments::where('office_id', $office->id)->findOrFail($id);
        $services = Services::where('office_id', $office->id)->get();

        return view('office.appointments.edit', compact('appointment', 'services'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'citizen_name' => 'required|string|max:255',
            'citizen_email' => 'required|email',
            'citizen_phone' => 'nullable|string|max:20',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|string|in:Scheduled,Confirmed,Completed,Cancelled',
            'notes' => 'nullable|string',
        ]);

        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $appointment = Appointments::where('office_id', $office->id)->findOrFail($id);

        // Check if the service belongs to this office
        $service = Services::where('id', $validated['service_id'])
            ->where('office_id', $office->id)
            ->firstOrFail();

        $updateData = $validated;
        $updateData['date'] = $validated['appointment_date'];
        $updateData['time_slot'] = $validated['appointment_time'];
        $updateData['citizen_id'] = Users::where('email', $validated['citizen_email'])->value('id');
        unset($updateData['appointment_date'], $updateData['appointment_time']);

        $appointment->update($updateData);

        return redirect()->route('office.appointments.show', $appointment->id)->with('success', 'Appointment updated successfully.');
    }

    public function destroy($id)
    {
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $appointment = Appointments::where('office_id', $office->id)->findOrFail($id);
        $appointment->delete();

        return redirect()->route('office.appointments.index')->with('success', 'Appointment deleted successfully.');
    }
}