<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Government_Offices;
use App\Models\Services;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CitizenAppointmentController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}

    public function index()
    {
        $appointments = Appointments::where('citizen_id', Auth::id())
            ->with(['service', 'office'])
            ->orderBy('date', 'desc')
            ->get();

        return view('users.appointments.index', compact('appointments'));
    }

    public function create(Request $request)
    {
        $officeId  = $request->query('office_id');
        $serviceId = $request->query('service_id');

        $offices  = Government_Offices::all();
        $services = $officeId
            ? Services::where('office_id', $officeId)->get()
            : Services::with('office')->get();

        $office  = $officeId ? Government_Offices::find($officeId) : null;
        $service = $serviceId ? Services::find($serviceId) : null;

        return view('users.appointments.create', compact('offices', 'services', 'office', 'service'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'office_id'   => 'required|integer|exists:government_offices,id',
            'service_id'  => 'required|integer|exists:services,id',
            'date'        => 'required|date|after:today',
            'time_slot'   => 'required|string',
            'notes'       => 'nullable|string|max:500',
        ]);

        try {
            $validated['time_slot'] = Appointments::normalizeTimeSlot($validated['time_slot']);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages([
                'time_slot' => 'Please select a valid time slot.',
            ]);
        }

        $appointment = Appointments::create([
            'office_id'     => $validated['office_id'],
            'service_id'    => $validated['service_id'],
            'citizen_id'    => Auth::id(),
            'citizen_name'  => Auth::user()->username,
            'citizen_email' => Auth::user()->email,
            'citizen_phone' => Auth::user()->tel ?? '',
            'date'          => $validated['date'],
            'time_slot'     => $validated['time_slot'],
            'status'        => 'Scheduled',
            'notes'         => $validated['notes'] ?? null,
        ]);

        $when = $validated['date'] . ' at ' . $appointment->formatted_time_slot;

        $this->notifications->notifyCitizenForAppointment(
            $appointment,
            'Appointment Scheduled',
            "Your appointment is confirmed for {$when}.",
            'appointment_reminder',
            'Appointment Scheduled',
            "Your appointment is confirmed for {$when}."
        );

        $this->notifications->notifyOfficeForAppointment(
            $appointment,
            'New Appointment Booked',
            Auth::user()->username . " booked an appointment for {$when}.",
            'appointment_reminder',
            'New Appointment Booked',
            Auth::user()->username . " booked an appointment for {$when}."
        );

        ActivityLogger::created(
            'appointment',
            $appointment->id,
            Auth::user()->username . " booked an appointment for {$when}",
            $appointment->only(['office_id', 'service_id', 'date', 'time_slot', 'status'])
        );

        return redirect()->route('user.appointments.index')
            ->with('success', 'Appointment booked successfully!');
    }

    public function destroy($id)
    {
        $appointment = Appointments::where('id', $id)
            ->where('citizen_id', Auth::id())
            ->firstOrFail();

        $when = $appointment->date->format('Y-m-d') . ' at ' . $appointment->formatted_time_slot;

        ActivityLogger::updated(
            'appointment',
            $appointment->id,
            Auth::user()->username . " cancelled appointment for {$when}",
            ['status' => $appointment->status],
            ['status' => 'Cancelled']
        );

        $appointment->update(['status' => 'Cancelled']);

        $this->notifications->notifyCitizenForAppointment(
            $appointment,
            'Appointment Cancelled',
            "Your appointment scheduled for {$when} has been cancelled.",
            'appointment_reminder',
            'Appointment Cancelled',
            "Your appointment scheduled for {$when} has been cancelled."
        );

        $this->notifications->notifyOfficeForAppointment(
            $appointment,
            'Appointment Cancelled',
            Auth::user()->username . " cancelled their appointment for {$when}.",
            'appointment_reminder',
            'Appointment Cancelled',
            Auth::user()->username . " cancelled their appointment for {$when}."
        );

        return redirect()->route('user.appointments.index')
            ->with('success', 'Appointment cancelled.');
    }
}
