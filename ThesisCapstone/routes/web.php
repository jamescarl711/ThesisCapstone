<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\User\UserServiceRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceProviderController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\HR\HRServiceRequestController;
use App\Http\Controllers\HR\HrEmployeeController;
use App\Http\Controllers\HR\HrDashboardController;
use App\Http\Controllers\HR\HrPayrollController;
use App\Http\Controllers\HR\HrRequestStatusController;
use App\Http\Controllers\HR\HrAttendanceController;
use App\Http\Controllers\Employee\EmployeeDashboardController;
use App\Http\Controllers\Employee\EmployeeAttendanceController;
use App\Http\Controllers\JobPostController;
use App\Http\Controllers\ProcurementController;
use App\Models\ServiceProvider;
use App\Models\Business;
use App\Http\Controllers\ProcurementMaterialController;
use App\Http\Controllers\Auth\RegisterOtpController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
]));
Route::get('/careers', [JobPostController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::post('/check-email', [RegisteredUserController::class, 'checkEmail'])->name('check-email');

    Route::prefix('register')->group(function () {
        Route::post('/send-otp', [RegisterOtpController::class,'sendOtp'])->name('register.send-otp');
        Route::post('/verify-otp', [RegisterOtpController::class,'verifyOtp'])->name('register.verify-otp');
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Dashboard redirect by role
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->serviceProvider && $user->serviceProvider->is_approved) {
            return redirect()->route('service-provider.dashboard');
        }

        return match (strtolower($user->role)) {
            'admin' => redirect()->route('admin.dashboard'),
            'hr' => redirect()->route('hr.dashboard'),
            'finance' => redirect()->route('finance.dashboard'),
            'procurement' => redirect()->route('procurement.dashboard'),
            'business' => redirect()->route('business.dashboard'),
            'employee' => redirect()->route('employee.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | User Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('UserDashboard'))->name('user.dashboard');
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);

        Route::get('/service-requests', [UserServiceRequestController::class, 'index']);
        Route::post('/service-requests', [UserServiceRequestController::class, 'store']);
        Route::post('/service-request', [ServiceRequestController::class, 'store']);

        Route::post('/location', [UserController::class, 'updateLocation']);

        Route::get('/current', function () {
            $user = auth()->user();
            return response()->json([
                'first_name' => $user->first_name,
                'middle_initial' => $user->middle_initial,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'id' => $user->id,
            ]);
        });

        // Service Provider
        Route::get('/application-status', [ServiceProviderController::class, 'applicationStatus'])->name('user.application-status');
        Route::post('/apply-service-provider', [ServiceProviderController::class, 'apply'])->name('user.apply-service-provider');
        Route::get('/service-provider-details', [ServiceProviderController::class, 'details'])->name('user.service-provider-details');

        Route::get('/service-providers', function () {
            return ServiceProvider::with('user')->orderByDesc('created_at')->get()
                ->map(fn($sp) => [
                    'id' => $sp->id,
                    'user_id' => $sp->user_id,
                    'first_name' => $sp->user->first_name,
                    'middle_initial' => $sp->user->middle_initial,
                    'last_name' => $sp->user->last_name,
                    'email' => $sp->user->email,
                    'category' => $sp->category,
                    'service_description' => $sp->service_description,
                    'experience_years' => $sp->experience_years,
                    'valid_id' => $sp->valid_id,
                    'latitude' => $sp->latitude,
                    'longitude' => $sp->longitude,
                    'is_available' => (bool)$sp->is_available,
                ]);
        });

        Route::get('/all-businesses', [BusinessController::class, 'index']);

        // Service Provider Assigned Requests
        Route::get('/service-provider/assigned-requests', [ServiceProviderController::class, 'assignedRequests']);
    });

    /*
    |--------------------------------------------------------------------------
    | Service Provider Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('service-provider')->middleware('role:service-provider')->group(function () {
        Route::post('/update-request/{id}', [ServiceProviderController::class, 'updateRequest']);
        Route::post('/complete-job/{id}', [ServiceProviderController::class, 'completeJob']);
    });

    Route::middleware('auth')->group(function() {
        Route::post('/service-provider/update', [ServiceProviderController::class, 'update']);
        Route::post('/service-provider/toggle', [ServiceProviderController::class, 'toggleAvailability']);
        Route::post('/service-provider/work-history', [ServiceProviderController::class, 'addWorkHistory']);
        Route::put('/service-provider/work-history/{id}', [ServiceProviderController::class, 'updateWorkHistory']);
        Route::delete('/service-provider/work-history/{id}', [ServiceProviderController::class, 'deleteWorkHistory']);
    });

    Route::get('/service-provider/dashboard-data', [ServiceProviderController::class, 'dashboard']);
    Route::get('/provider/service-requests', [ServiceRequestController::class, 'providerRequests']);
    Route::post('/provider/service-requests/{id}/{status}', [ServiceRequestController::class, 'updateStatus']);

    /*
    |--------------------------------------------------------------------------
    | Business Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('business')->middleware('role:business')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('BusinessDashboard'))->name('business.dashboard');
        Route::post('/register-business', [BusinessController::class, 'registerBusiness'])->name('register.business');

        Route::get('/service-providers', [BusinessController::class, 'activeProviders']);
        Route::get('/provider-applications', [BusinessController::class, 'pendingApplications']);
        Route::post('/provider-applications/{id}/review', [BusinessController::class, 'reviewApplication']);
        Route::get('/service-requests', [BusinessController::class, 'serviceRequests']);
        Route::post('/service-requests/{id}/review', [BusinessController::class, 'reviewServiceRequest']);

        Route::post('/service-providers/{provider}/review', [ServiceProviderController::class, 'review'])->name('sp.review');
    });

    /*
    |--------------------------------------------------------------------------
    | Procurement Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('procurement')->group(function() {
        Route::get('/suggested-materials/{id}', [ProcurementController::class, 'getSuggested']);
        Route::post('/prepare-materials/{id}', [ProcurementController::class, 'addMaterials']);
        Route::get('/requests-awaiting-material', [ProcurementController::class, 'awaitingMaterial']);
        Route::post('/mark-job-ready/{id}', [ProcurementController::class, 'markJobReady']);
    });

    /*
    |--------------------------------------------------------------------------
    | HR Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('hr')->middleware('role:hr')->group(function () {
        Route::get('/', fn () => Inertia::render('HrDashboard'))->name('hr.dashboard');
        Route::get('dashboard-data', [HrDashboardController::class, 'index'])->name('hr.dashboard.data');
        Route::patch('service-requests/{serviceRequest}/status', [HrDashboardController::class, 'updateRequestStatus'])->name('hr.service-requests.status');
        Route::get('service-requests/accepted', [HrRequestStatusController::class, 'accepted'])->name('hr.service-requests.accepted');
        Route::get('service-requests/rejected', [HrRequestStatusController::class, 'rejected'])->name('hr.service-requests.rejected');
        Route::get('employees', [HrEmployeeController::class, 'index'])->name('hr.employees.index');
        Route::post('employees', [HrEmployeeController::class, 'store'])->name('hr.employees.store');
        Route::patch('employees/{employee}', [HrEmployeeController::class, 'update'])->name('hr.employees.update');
        Route::delete('employees/{employee}', [HrEmployeeController::class, 'destroy'])->name('hr.employees.destroy');
        Route::post('payrolls', [HrPayrollController::class, 'store'])->name('hr.payrolls.store');
        Route::get('attendance/summary', [HrPayrollController::class, 'attendanceSummary'])->name('hr.attendance.summary');
        Route::get('attendance/records', [HrAttendanceController::class, 'index'])->name('hr.attendance.index');
        Route::get('attendance/employees', [HrAttendanceController::class, 'employees'])->name('hr.attendance.employees');
        Route::get('attendance/qr', [HrAttendanceController::class, 'qr'])->name('hr.attendance.qr');
        Route::post('attendance', [HrAttendanceController::class, 'store'])->name('hr.attendance.store');
        Route::patch('attendance/{attendance}', [HrAttendanceController::class, 'update'])->name('hr.attendance.update');
        Route::delete('attendance/{attendance}', [HrAttendanceController::class, 'destroy'])->name('hr.attendance.destroy');

        Route::get('service-requests', [HRServiceRequestController::class, 'index']);
        Route::get('available-providers', [HRServiceRequestController::class, 'availableProviders']);
        Route::post('service-requests/{id}/assign', [HRServiceRequestController::class, 'assignProvider']);
        Route::post('service-requests/{id}/assign-employee', [HRServiceRequestController::class, 'assignEmployee']);
    });

    /*
    |--------------------------------------------------------------------------
    | HR Static Views
    |--------------------------------------------------------------------------
    */
    Route::get('/hr/payroll', fn () => Inertia::render('HrPayroll'))->middleware('role:hr')->name('hr.payroll');
    Route::get('/hr/recruitment', fn () => Inertia::render('HrRecruitment'))->middleware('role:hr')->name('hr.recruitment');
    Route::get('/hr/attendance', fn () => Inertia::render('HrAttendance'))->middleware('role:hr')->name('hr.attendance');
    Route::get('/hr/assigned-requests', fn () => Inertia::render('AssignedRequestsView'))->middleware('role:hr')->name('hr.assigned-requests');
    Route::get('/hr/accepted-requests', fn () => Inertia::render('AcceptedRequestsView'))->middleware('role:hr')->name('hr.accepted-requests');
    Route::get('/hr/rejected-requests', fn () => Inertia::render('RejectedRequestsView'))->middleware('role:hr')->name('hr.rejected-requests');
    Route::get('/hr/reports', fn () => Inertia::render('ReportsDashboard'))->middleware('role:hr')->name('hr.reports');
    Route::post('/hr/recruitment/publish', [JobPostController::class, 'store'])->middleware('role:hr')->name('hr.recruitment.publish');

    /*
    |--------------------------------------------------------------------------
    | Employee Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('employee')->middleware('role:employee')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('EmployeeDashboard'))->name('employee.dashboard');
        Route::get('/profile', fn () => Inertia::render('EmployeeProfile'))->name('employee.profile');
        Route::get('/notifications', fn () => Inertia::render('EmployeeNotifications'))->name('employee.notifications');
        Route::get('/payslips', fn () => Inertia::render('EmployeePayslips'))->name('employee.payslips');
        Route::get('/assigned-requests', fn () => Inertia::render('EmployeeAssignedRequests'))->name('employee.assigned-requests');
        Route::get('/attendance', fn () => Inertia::render('EmployeeAttendance'))->name('employee.attendance');

        Route::get('/dashboard-data', [EmployeeDashboardController::class, 'dashboard'])->name('employee.dashboard.data');
        Route::put('/profile', [EmployeeDashboardController::class, 'updateProfile'])->name('employee.profile.update');
        Route::patch('/assigned-requests/{serviceRequest}', [EmployeeDashboardController::class, 'updateAssignedRequest'])->name('employee.assigned-requests.update');
        Route::get('/attendance/status', [EmployeeAttendanceController::class, 'status'])->name('employee.attendance.status');
        Route::get('/attendance/records', [EmployeeAttendanceController::class, 'records'])->name('employee.attendance.records');
        Route::get('/attendance/qr', [EmployeeAttendanceController::class, 'qr'])->name('employee.attendance.qr');
        Route::get('/attendance/scan', [EmployeeAttendanceController::class, 'scan'])->name('employee.attendance.scan');
        Route::post('/attendance/check-in', [EmployeeAttendanceController::class, 'checkIn'])->name('employee.attendance.checkin');
        Route::post('/attendance/check-out', [EmployeeAttendanceController::class, 'checkOut'])->name('employee.attendance.checkout');
    });

    /*
    |--------------------------------------------------------------------------
    | Other Dashboards
    |--------------------------------------------------------------------------
    */
    Route::get('/finance', fn () => Inertia::render('FinanceDashboard'))->middleware('role:finance')->name('finance.dashboard');
    Route::get('/procurement', fn () => Inertia::render('ProcurementDashboard'))->middleware('role:procurement')->name('procurement.dashboard');
    Route::get('/business', fn () => Inertia::render('BusinessDashboard'))->middleware('role:business')->name('business.dashboard');
    Route::get('/serviceprovider', fn () => Inertia::render('ServiceProviderDashboard'))->middleware('role:serviceprovider')->name('serviceprovider.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/', fn () => Inertia::render('AdminDashboard'))->name('admin.dashboard');

        // Users
        Route::get('/users', [RegisteredUserController::class, 'index']);
        Route::post('/users', [RegisteredUserController::class, 'storeAdminUser']);
        Route::get('/users/{id}', [RegisteredUserController::class, 'show']);
        Route::post('/users/{id}/toggle-approval', [RegisteredUserController::class, 'toggleApproval']);
        Route::delete('/users/{id}', [RegisteredUserController::class, 'destroy']);
        Route::post('/users/{id}/reject', [RegisteredUserController::class, 'reject']);

        // Businesses
        Route::get('/businesses', fn () => Business::with('user')->get());
        Route::post('/businesses/{id}/toggle-approval', function ($id) {
            $b = Business::findOrFail($id);
            $b->is_approved = !$b->is_approved;
            $b->save();
            return response()->json($b);
        });
        Route::get('businesses/{id}', [BusinessController::class, 'show']);
    });

});
