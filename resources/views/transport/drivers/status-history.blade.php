@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status History for {{ $driver->first_name }} {{ $driver->last_name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.drivers.show', $driver) }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Driver
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                    <th>Notes</th>
                                    <th>Changed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($statusHistory as $history)
                                <tr>
                                    <td>{{ $history->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $history->status->color ?? 'secondary' }}">
                                            {{ $history->status->name }}
                                        </span>
                                    </td>
                                    <td>{{ $history->status_reason }}</td>
                                    <td>{{ $history->status_notes ?? 'N/A' }}</td>
                                    <td>{{ $history->user->name ?? 'System' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No status history found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $statusHistory->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection