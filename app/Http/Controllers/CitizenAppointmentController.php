<?php
namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Government_Offices;
use App\Models\Services;
use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenAppointmentController extends Controller
{
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

        $offices   = Government_Offices::all();
        $services  = $officeId
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

        $appointment = Appointments::create([
            'office_id'    => $validated['office_id'],
            'service_id'   => $validated['service_id'],
            'citizen_id'   => Auth::id(),
            'citizen_name' => Auth::user()->username,
            'citizen_email'=> Auth::user()->email,
            'citizen_phone'=> Auth::user()->tel ?? '',
            'date'         => $validated['date'],
            'time_slot'    => $validated['time_slot'],
            'status'       => 'Scheduled',
            'notes'        => $validated['notes'] ?? null,
        ]);

        // Notify citizen
        Notifications::create([
            'user_id' => Auth::id(),
            'title'   => 'Appointment Scheduled',
            'message' => 'Your appointment is confirmed for ' . $validated['date'] . ' at ' . $validated['time_slot'],
            'type'    => 'appointment',
            'is_read' => false,
        ]);

        return redirect()->route('user.appointments.index')
            ->with('success', 'Appointment booked successfully!');
    }

    public function destroy($id)
    {
        $appointment = Appointments::where('id', $id)
            ->where('citizen_id', Auth::id())
            ->firstOrFail();

        $appointment->update(['status' => 'Cancelled']);

        return redirect()->route('user.appointments.index')
            ->with('success', 'Appointment cancelled.');
    }
}