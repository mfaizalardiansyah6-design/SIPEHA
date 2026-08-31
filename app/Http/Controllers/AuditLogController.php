<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $logs = AuditLog::with('user')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('action', 'like', "%{$search}%")
                    ->orWhere('target_table', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('audit-logs.index', compact('logs', 'search'));
    }
}
