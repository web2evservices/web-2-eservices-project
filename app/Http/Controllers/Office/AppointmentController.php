<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use App\Models\Appointments;
use App\Models\Office;
use App\Models\Services;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    public function __construct(protected NotificationService $notifications) {}
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

        $citizen = User::firstOrCreate(
            ['email' => $validated['citizen_email']],
            [
                'username' => $validated['citizen_name'] ?: explode('@', $validated['citizen_email'])[0],
                'password' => Str::random(16),
                'role' => 'citizen',
                'tel' => $validated['citizen_phone'] ?? null,
            ]
        );

        $appointment = Appointments::create([
            'office_id' => $office->id,
            'service_id' => $validated['service_id'],
            'citizen_id' => $citizen->id,
            'citizen_name' => $validated['citizen_name'],
            'citizen_email' => $validated['citizen_email'],
            'citizen_phone' => $validated['citizen_phone'],
            'date' => $validated['appointment_date'],
            'time_slot' => $validated['appointment_time'],
            'status' => 'Scheduled',
            'notes' => $validated['notes'],
        ]);

        $when = $validated['appointment_date'] . ' at ' . $validated['appointment_time'];

        $this->notifications->notifyWithEmail(
            Auth::id(),
            'Appointment Scheduled',
            "You scheduled an appointment for {$validated['citizen_name']} on {$when}.",
            'appointment_reminder',
            new \App\Mail\AppointmentEventMail(
                $appointment,
                'Appointment Scheduled',
                'Appointment Scheduled',
                "You scheduled an appointment for {$validated['citizen_name']} on {$when}.",
                Auth::user()->username ?? 'Office Staff'
            )
        );

        $this->notifications->notifyCitizenForAppointment(
            $appointment,
            'Appointment Scheduled',
            "An appointment has been scheduled for you on {$when}.",
            'appointment_reminder',
            'Appointment Scheduled',
            "An appointment has been scheduled for you on {$when}."
        );

        ActivityLogger::created(
            'appointment',
            $appointment->id,
            "Scheduled appointment for {$validated['citizen_name']} on {$when}",
            $appointment->only(['service_id', 'citizen_name', 'date', 'time_slot', 'status'])
        );

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
        Services::where('id', $validated['service_id'])
            ->where('office_id', $office->id)
            ->firstOrFail();

        $oldStatus = $appointment->status;
        $when      = $validated['appointment_date'] . ' at ' . $validated['appointment_time'];
        $oldData   = $appointment->only(['service_id', 'citizen_name', 'date', 'time_slot', 'status']);

        $updateData = $validated;
        $updateData['date'] = $validated['appointment_date'];
        $updateData['time_slot'] = $validated['appointment_time'];

        $citizen = User::firstOrCreate(
            ['email' => $validated['citizen_email']],
            [
                'username' => $validated['citizen_name'] ?: explode('@', $validated['citizen_email'])[0],
                'password' => Str::random(16),
                'role' => 'citizen',
                'tel' => $validated['citizen_phone'] ?? null,
            ]
        );

        $updateData['citizen_id'] = $citizen->id;
        unset($updateData['appointment_date'], $updateData['appointment_time']);

        $appointment->update($updateData);
        $appointment->refresh();

        $statusChanged = $oldStatus !== $validated['status'];

        if ($statusChanged && $validated['status'] === 'Cancelled') {
            $this->notifications->notifyBothForAppointment(
                $appointment,
                'Appointment Cancelled',
                "Your appointment on {$when} has been cancelled by the office.",
                'Appointment Cancelled',
                "Appointment for {$validated['citizen_name']} on {$when} was cancelled.",
                'appointment_reminder'
            );
        } elseif ($statusChanged) {
            $this->notifications->notifyBothForAppointment(
                $appointment,
                'Appointment Status Updated',
                "Your appointment on {$when} is now: {$validated['status']}.",
                'Appointment Status Updated',
                "Appointment for {$validated['citizen_name']} is now: {$validated['status']}.",
                'appointment_reminder'
            );
        } else {
            $this->notifications->notifyBothForAppointment(
                $appointment,
                'Appointment Updated',
                "Your appointment has been updated to {$when}.",
                'Appointment Updated',
                "Appointment for {$validated['citizen_name']} was updated to {$when}.",
                'appointment_reminder'
            );
        }

        ActivityLogger::updated(
            'appointment',
            $appointment->id,
            "Updated appointment for {$validated['citizen_name']} on {$when}",
            $oldData,
            $appointment->only(['service_id', 'citizen_name', 'date', 'time_slot', 'status'])
        );

        return redirect()->route('office.appointments.show', $appointment->id)->with('success', 'Appointment updated successfully.');
    }

    public function destroy($id)
    {
        $office = Office::where('user_id', Auth::id())->firstOrFail();

        $appointment = Appointments::where('office_id', $office->id)->findOrFail($id);

        $when = $appointment->date->format('Y-m-d') . ' at ' . $appointment->time_slot;
        $name = $appointment->citizen_name;

        ActivityLogger::deleted(
            'appointment',
            $appointment->id,
            "Deleted appointment for {$name} on {$when}",
            $appointment->only(['service_id', 'citizen_name', 'date', 'time_slot', 'status'])
        );

        $this->notifications->notifyWithEmail(
            Auth::id(),
            'Appointment Deleted',
            "Appointment for {$name} on {$when} was removed.",
            'appointment_reminder',
            new \App\Mail\AppointmentEventMail(
                $appointment,
                'Appointment Deleted',
                'Appointment Deleted',
                "Appointment for {$name} on {$when} was removed.",
                Auth::user()->username ?? 'Office Staff'
            )
        );

        $this->notifications->notifyCitizenForAppointment(
            $appointment,
            'Appointment Deleted',
            "Your appointment on {$when} was removed by the office.",
            'appointment_reminder',
            'Appointment Deleted',
            "Your appointment on {$when} was removed by the office."
        );

        $appointment->delete();

        return redirect()->route('office.appointments.index')->with('success', 'Appointment deleted successfully.');
    }
}