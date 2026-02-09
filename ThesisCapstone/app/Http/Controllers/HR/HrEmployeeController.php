<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HrEmployeeController extends Controller
{
    public function index()
    {
        return Employee::query()
            ->orderByDesc('id')
            ->get()
            ->map(fn (Employee $employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'role' => $employee->role,
                'team' => $employee->team,
                'status' => $employee->status,
                'start_date' => optional($employee->start_date)->format('Y-m-d'),
                'notes' => $employee->notes,
            ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:employees,email', 'unique:users,email'],
            'given_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'max:255'],
            'team' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Email already exists.'], 422);
        }

        $middleInitial = $data['middle_name'] ? mb_substr($data['middle_name'], 0, 1) : null;

        $user = User::create([
            'first_name' => $data['given_name'],
            'middle_initial' => $middleInitial,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'employee',
            'is_approved' => 1,
            'status' => 'approved',
        ]);

        $employee = Employee::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'user_id' => $user->id,
            'role' => $data['role'] ?? null,
            'team' => $data['team'] ?? null,
            'status' => $data['status'] ?? 'Active',
            'start_date' => $data['start_date'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->email,
            'user_id' => $employee->user_id,
            'role' => $employee->role,
            'team' => $employee->team,
            'status' => $employee->status,
            'start_date' => optional($employee->start_date)->format('Y-m-d'),
            'notes' => $employee->notes,
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:employees,email,' . $employee->id],
            'role' => ['nullable', 'string', 'max:255'],
            'team' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (isset($data['email']) && $employee->user_id) {
            $exists = User::where('email', $data['email'])
                ->where('id', '!=', $employee->user_id)
                ->exists();
            if ($exists) {
                return response()->json(['message' => 'Email already exists.'], 422);
            }
            User::where('id', $employee->user_id)->update(['email' => $data['email']]);
        }

        $employee->update($data);

        return response()->json([
            'id' => $employee->id,
            'name' => $employee->name,
            'email' => $employee->email,
            'user_id' => $employee->user_id,
            'role' => $employee->role,
            'team' => $employee->team,
            'status' => $employee->status,
            'start_date' => optional($employee->start_date)->format('Y-m-d'),
            'notes' => $employee->notes,
        ]);
    }

    public function destroy(Employee $employee)
    {
        if ($employee->user_id) {
            User::where('id', $employee->user_id)->delete();
        }
        $employee->delete();
        return response()->json(['ok' => true]);
    }
}
